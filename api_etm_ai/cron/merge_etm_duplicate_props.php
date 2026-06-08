<?php
/**
 * Объединение дублей свойств IB41 без потери данных.
 * 1) По коду: ETM_FOO → FOO (если есть свойство FOO)
 * 2) По названию: ETM_* → существующее свойство с тем же NAME (без префикса ETM_)
 * 3) Удаление пустых повторов ETM_* с тем же CODE
 *
 *   sudo -u www-root php api_etm_ai/cron/merge_etm_duplicate_props.php --dry-run
 *   sudo -u www-root php api_etm_ai/cron/merge_etm_duplicate_props.php
 *   sudo -u www-root php api_etm_ai/cron/merge_etm_duplicate_props.php --pair=ETM_TSVET
 */

declare(strict_types=1);

require_once __DIR__ . '/_etm_ib40_cron_bootstrap.php';

$dryRun = in_array('--dry-run', $argv ?? [], true) || in_array('--dry', $argv ?? [], true);
$pairFilter = null;
$deleteEtm = !in_array('--no-delete', $argv ?? [], true);

foreach ($argv ?? [] as $arg) {
    if (preg_match('/^--pair=(.+)$/', $arg, $m)) {
        $pairFilter = strtoupper(trim($m[1]));
    }
}

/** Служебные ETM-свойства — не трогаем */
const MERGE_ETM_SKIP = [
    'ETM_VARIANT_CODES',
    'ETM_RELATED_CODES',
    'ETM_PURCHASED_CODES',
    'ETM_VARIANT_OPTIONS',
    'ETM_SIMILAR_ATTR',
];

$iblockId = (int)API_ETM_IBLOCK_ID;
$logFile = API_ETM_LOGS_DIR . '/merge_etm_duplicate_props_' . date('Y-m-d_H-i-s') . '.log';

function mergeLog(string $msg): void {
    global $logFile;
    $line = '[' . date('H:i:s') . '] ' . $msg . "\n";
    echo $line;
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

function mergeNormalizeName(string $name): string {
    $name = trim($name);
    $name = preg_replace('/^ETM:\s*/iu', '', $name);
    return mb_strtolower($name);
}

function mergeIsEtmCode(string $code): bool {
    return strpos($code, 'ETM_') === 0;
}

/**
 * @return array<int, array{id:int,code:string,name:string,etm:bool,cnt:int}>
 */
function mergeLoadProperties(int $iblockId): array {
    global $DB;
    $props = [];
    $res = CIBlockProperty::GetList(['ID' => 'ASC'], ['IBLOCK_ID' => $iblockId]);
    while ($p = $res->Fetch()) {
        $code = (string)$p['CODE'];
        if (in_array($code, MERGE_ETM_SKIP, true)) {
            continue;
        }
        $id = (int)$p['ID'];
        $cntRow = $DB->Query(
            'SELECT COUNT(*) AS C FROM b_iblock_element_property WHERE IBLOCK_PROPERTY_ID = ' . $id
        )->Fetch();
        $props[$id] = [
            'id' => $id,
            'code' => $code,
            'name' => (string)$p['NAME'],
            'etm' => mergeIsEtmCode($code),
            'cnt' => (int)($cntRow['C'] ?? 0),
        ];
    }
    return $props;
}

/**
 * Выбор целевого свойства (не ETM): больше значений, затем меньший ID.
 *
 * @param list<array{id:int,code:string,name:string,etm:bool,cnt:int}> $candidates
 */
function mergePickTarget(array $candidates): ?array {
    $bases = array_values(array_filter($candidates, static fn(array $p): bool => !$p['etm']));
    if ($bases === []) {
        return null;
    }
    usort($bases, static function (array $a, array $b): int {
        if ($b['cnt'] !== $a['cnt']) {
            return $b['cnt'] <=> $a['cnt'];
        }
        return $a['id'] <=> $b['id'];
    });
    return $bases[0];
}

/**
 * @return list<array{etm_id:int, etm_code:string, base_id:int, base_code:string, match:string, name_key:string}>
 */
function mergeCollectPairs(int $iblockId, ?string $pairFilter): array {
    $props = mergeLoadProperties($iblockId);
    $pairs = [];
    $usedEtmIds = [];

    $addPair = static function (array $etm, array $target, string $match) use (&$pairs, &$usedEtmIds, $pairFilter): void {
        if (isset($usedEtmIds[$etm['id']])) {
            return;
        }
        if ($pairFilter !== null && $etm['code'] !== $pairFilter) {
            return;
        }
        if ($etm['id'] === $target['id']) {
            return;
        }
        $usedEtmIds[$etm['id']] = true;
        $pairs[] = [
            'etm_id' => $etm['id'],
            'etm_code' => $etm['code'],
            'base_id' => $target['id'],
            'base_code' => $target['code'],
            'match' => $match,
            'name_key' => mergeNormalizeName($etm['name']),
        ];
    };

    // 1) По коду ETM_FOO → FOO
    $byCode = [];
    foreach ($props as $p) {
        $byCode[$p['code']] = $p;
    }
    foreach ($props as $p) {
        if (!$p['etm']) {
            continue;
        }
        $baseCode = substr($p['code'], 4);
        if ($baseCode === '' || !isset($byCode[$baseCode]) || $byCode[$baseCode]['etm']) {
            continue;
        }
        $addPair($p, $byCode[$baseCode], 'code');
    }

    // 2) По названию
    $byName = [];
    foreach ($props as $p) {
        $key = mergeNormalizeName($p['name']);
        if ($key === '') {
            continue;
        }
        $byName[$key][] = $p;
    }
    foreach ($byName as $key => $group) {
        if (count($group) < 2) {
            continue;
        }
        $target = mergePickTarget($group);
        if ($target === null) {
            continue;
        }
        foreach ($group as $p) {
            if (!$p['etm']) {
                continue;
            }
            $addPair($p, $target, 'name');
        }
    }

    usort($pairs, static fn(array $a, array $b): int => strcmp($a['etm_code'], $b['etm_code']));
    return $pairs;
}

/**
 * Пустые ETM-свойства с CODE, который уже занят другим свойством с данными.
 *
 * @return list<array{id:int,code:string}>
 */
function mergeCollectEmptyEtmCodeDuplicates(int $iblockId): array {
    $props = mergeLoadProperties($iblockId);
    $byCode = [];
    foreach ($props as $p) {
        if (!$p['etm']) {
            continue;
        }
        $byCode[$p['code']][] = $p;
    }
    $toDelete = [];
    foreach ($byCode as $code => $list) {
        if (count($list) < 2) {
            continue;
        }
        usort($list, static function (array $a, array $b): int {
            if ($b['cnt'] !== $a['cnt']) {
                return $b['cnt'] <=> $a['cnt'];
            }
            return $a['id'] <=> $b['id'];
        });
        for ($i = 1, $n = count($list); $i < $n; $i++) {
            if ($list[$i]['cnt'] === 0) {
                $toDelete[] = ['id' => $list[$i]['id'], 'code' => $list[$i]['code']];
            }
        }
    }
    return $toDelete;
}

function mergeCountConflicts(int $etmPropId, int $basePropId): int {
    global $DB;
    $sql = "
        SELECT COUNT(*) AS C
        FROM b_iblock_element_property etm
        INNER JOIN b_iblock_element_property base
            ON base.IBLOCK_PROPERTY_ID = " . (int)$basePropId . "
            AND base.IBLOCK_ELEMENT_ID = etm.IBLOCK_ELEMENT_ID
        WHERE etm.IBLOCK_PROPERTY_ID = " . (int)$etmPropId . "
          AND TRIM(COALESCE(etm.VALUE, '')) <> ''
          AND TRIM(COALESCE(base.VALUE, '')) <> ''
          AND TRIM(etm.VALUE) <> TRIM(base.VALUE)
    ";
    $row = $DB->Query($sql)->Fetch();
    return (int)($row['C'] ?? 0);
}

function mergeCopyValues(int $etmPropId, int $basePropId, bool $dryRun): int {
    global $DB;
    $cntRow = $DB->Query(
        'SELECT COUNT(*) AS C FROM b_iblock_element_property WHERE IBLOCK_PROPERTY_ID = ' . (int)$etmPropId
    )->Fetch();
    $toCopy = (int)($cntRow['C'] ?? 0);
    if ($toCopy === 0 || $dryRun) {
        return $toCopy;
    }
    $sql = "
        INSERT INTO b_iblock_element_property
            (IBLOCK_PROPERTY_ID, IBLOCK_ELEMENT_ID, VALUE, VALUE_TYPE, VALUE_ENUM, VALUE_NUM, DESCRIPTION)
        SELECT
            " . (int)$basePropId . ",
            etm.IBLOCK_ELEMENT_ID,
            etm.VALUE,
            etm.VALUE_TYPE,
            etm.VALUE_ENUM,
            etm.VALUE_NUM,
            etm.DESCRIPTION
        FROM b_iblock_element_property etm
        WHERE etm.IBLOCK_PROPERTY_ID = " . (int)$etmPropId . "
          AND NOT EXISTS (
              SELECT 1
              FROM b_iblock_element_property base
              WHERE base.IBLOCK_PROPERTY_ID = " . (int)$basePropId . "
                AND base.IBLOCK_ELEMENT_ID = etm.IBLOCK_ELEMENT_ID
          )
    ";
    $DB->Query($sql);
    return $toCopy;
}

function mergeDeleteEtmValues(int $etmPropId, bool $dryRun): void {
    global $DB;
    if ($dryRun) {
        return;
    }
    $DB->Query('DELETE FROM b_iblock_element_property WHERE IBLOCK_PROPERTY_ID = ' . (int)$etmPropId);
}

function mergeDeleteProperty(int $propId, bool $dryRun): bool {
    if ($dryRun) {
        return true;
    }
    return (bool)CIBlockProperty::Delete($propId);
}

mergeLog('=== merge_etm_duplicate_props START iblock=' . $iblockId . ' dry=' . ($dryRun ? 'Y' : 'N') . ' ===');

$pairs = mergeCollectPairs($iblockId, $pairFilter);
$emptyDupes = $pairFilter === null ? mergeCollectEmptyEtmCodeDuplicates($iblockId) : [];

if ($pairs === [] && $emptyDupes === []) {
    mergeLog('Нет дублей для объединения' . ($pairFilter ? " (filter=$pairFilter)" : ''));
    exit(0);
}

mergeLog('Пар ETM→целевое: ' . count($pairs) . ', пустых повторов CODE: ' . count($emptyDupes));

$totalCopied = 0;
$totalConflicts = 0;
$deletedProps = 0;
$errors = 0;

foreach ($pairs as $pair) {
    $conflicts = mergeCountConflicts($pair['etm_id'], $pair['base_id']);
    if ($conflicts > 0) {
        $totalConflicts += $conflicts;
        mergeLog(
            'WARN SKIP [' . $pair['match'] . '] ' . $pair['etm_code'] . ' → ' . $pair['base_code']
            . " (конфликтов: $conflicts, name={$pair['name_key']})"
        );
        $errors++;
        continue;
    }

    $copied = mergeCopyValues($pair['etm_id'], $pair['base_id'], $dryRun);
    $totalCopied += $copied;
    mergeLog(
        ($dryRun ? '[DRY] ' : '')
        . '[' . $pair['match'] . '] '
        . $pair['etm_code'] . ' #' . $pair['etm_id']
        . ' → ' . $pair['base_code'] . ' #' . $pair['base_id']
        . " (строк: $copied)"
    );

    if (!$dryRun) {
        mergeDeleteEtmValues($pair['etm_id'], false);
        if ($deleteEtm && mergeDeleteProperty($pair['etm_id'], false)) {
            $deletedProps++;
        } else {
            mergeLog('WARN Не удалось удалить свойство ' . $pair['etm_code']);
            $errors++;
        }
    }
}

foreach ($emptyDupes as $dup) {
    mergeLog(($dryRun ? '[DRY] ' : '') . '[empty-code] DELETE ' . $dup['code'] . ' #' . $dup['id']);
    if (!$dryRun && $deleteEtm) {
        mergeDeleteEtmValues($dup['id'], false);
        if (mergeDeleteProperty($dup['id'], false)) {
            $deletedProps++;
        } else {
            mergeLog('WARN Не удалось удалить пустой дубль ' . $dup['code']);
            $errors++;
        }
    }
}

if (!$dryRun) {
    if (class_exists('\Bitrix\Iblock\PropertyIndex\Manager')) {
        \Bitrix\Iblock\PropertyIndex\Manager::markAsInvalid($iblockId);
    }
    CIBlock::ClearIblockTagCache($iblockId);
}

mergeLog(
    '=== DONE pairs=' . count($pairs)
    . ' copied_rows=' . $totalCopied
    . ' conflicts=' . $totalConflicts
    . ' deleted_props=' . $deletedProps
    . ' errors=' . $errors
    . ' log=' . $logFile
    . ' ==='
);

exit($errors > 0 ? 1 : 0);
