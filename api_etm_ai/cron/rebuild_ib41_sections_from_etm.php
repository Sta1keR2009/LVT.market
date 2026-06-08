<?php
/**
 * Разделы IB41 из gdsClassTree (ETM API) + привязка товаров.
 *
 *   sudo -u www-root php api_etm_ai/cron/rebuild_ib41_sections_from_etm.php --dry-run --max=5
 *   sudo -u www-root php api_etm_ai/cron/rebuild_ib41_sections_from_etm.php --etm-ids=9752235
 *   sudo -u www-root php api_etm_ai/cron/rebuild_ib41_sections_from_etm.php --max=100
 */

declare(strict_types=1);

require_once __DIR__ . '/_etm_ib40_cron_bootstrap.php';
require_once dirname(__DIR__) . '/includes/etm_element_code.php';
require_once dirname(__DIR__) . '/includes/etm_iblock_section_tree.php';

$dryRun = !in_array('--apply', $argv ?? [], true);
$maxItems = 0;
$etmIdsFilter = null;
$fromId = 0;

foreach ($argv ?? [] as $arg) {
    if ($arg === '--dry-run' || $arg === '--dry') {
        $dryRun = true;
    }
    if ($arg === '--apply') {
        $dryRun = false;
    }
    if (preg_match('/^--max=(\d+)$/', $arg, $m)) {
        $maxItems = max(0, (int)$m[1]);
    }
    if (preg_match('/^--from-id=(\d+)$/', $arg, $m)) {
        $fromId = max(0, (int)$m[1]);
    }
    if (preg_match('/^--etm-ids=(.+)$/', $arg, $m)) {
        $etmIdsFilter = array_map('strval', array_filter(array_map('trim', explode(',', $m[1]))));
    }
}

$iblockId = (int)API_ETM_IBLOCK_ID;
$propEtmCode = API_ETM_PROP_ETM_CODE;

$logFile = API_ETM_LOGS_DIR . '/rebuild_ib41_sections_' . date('Y-m-d_H-i-s') . '.log';
function rbLog(string $msg): void {
    global $logFile;
    $line = '[' . date('H:i:s') . '] ' . $msg . "\n";
    echo $line;
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

rbLog('=== rebuild_ib41_sections_from_etm START dry=' . ($dryRun ? 'Y' : 'N') . ' ===');
rbLog('Категории ETM создаются в корне инфоблока');

$filter = ['IBLOCK_ID' => $iblockId, '!PROPERTY_' . $propEtmCode => false];
if ($fromId > 0) {
    $filter['>ID'] = $fromId;
}

/** @var list<array{code:string,id:int}> */
$elements = [];
if (is_array($etmIdsFilter) && $etmIdsFilter !== []) {
    foreach ($etmIdsFilter as $code) {
        $code = (string)$code;
        $elId = etmFindElementIdByEtmCode($iblockId, $code);
        if ($elId > 0) {
            $elements[] = ['code' => $code, 'id' => $elId];
        } else {
            rbLog("etm=$code — элемент не найден", 'WARN');
        }
    }
} else {
    $res = CIBlockElement::GetList(
        ['ID' => 'ASC'],
        $filter,
        false,
        false,
        ['ID', 'PROPERTY_' . $propEtmCode]
    );
    while ($row = $res->Fetch()) {
        $code = trim((string)($row['PROPERTY_' . strtoupper($propEtmCode) . '_VALUE'] ?? ''));
        if ($code !== '') {
            $elements[] = ['code' => $code, 'id' => (int)$row['ID']];
        }
    }
}

$total = count($elements);
rbLog('Товаров с kod_tovara_: ' . $total);
if ($total === 0) {
    exit(0);
}

$client = new ApiEtmClient(ETM_API_URL, ETM_LOGIN, ETM_PASSWORD);
if (!$client->login()) {
    rbLog('Ошибка авторизации HTTP=' . $client->lastHttpCode);
    exit(1);
}

$done = 0;
$sectionsCreated = 0;
$assigned = 0;
$errors = 0;

foreach ($elements as $item) {
    $etmCode = $item['code'];
    $elId = $item['id'];
    if ($maxItems > 0 && $done >= $maxItems) {
        break;
    }
    if ($done > 0) {
        sleep(1);
    }

    $goods = $client->getGoods($etmCode, 'etm');
    if (!$goods) {
        $errors++;
        rbLog("etm=$etmCode HTTP=" . $client->lastHttpCode, 'WARN');
        continue;
    }
    $data = $goods['data'] ?? $goods;
    $tree = is_array($data) ? ($data['gdsClassTree'] ?? []) : [];
    if (!is_array($tree) || $tree === []) {
        rbLog("etm=$etmCode el=$elId — пустой gdsClassTree");
        $done++;
        continue;
    }

    $pathNames = array_map(static fn($n) => (string)($n['name'] ?? ''), $tree);
    $sectionId = etmResolveSectionPathFromClassTree($iblockId, $tree, $dryRun);

    if ($dryRun) {
        rbLog('DRY etm=' . $etmCode . ' el=' . $elId . ' путь: ' . implode(' > ', $pathNames) . ' → section ' . ($sectionId ?: 'new'));
    } else {
        if ($sectionId > 0) {
            $elObj = new CIBlockElement();
            $elObj->Update($elId, ['IBLOCK_SECTION_ID' => $sectionId]);
            $assigned++;
            rbLog('etm=' . $etmCode . ' el=' . $elId . ' → section ' . $sectionId . ' (' . implode(' > ', $pathNames) . ')');
        }
    }
    $done++;
}

rbLog("=== DONE processed=$done assigned=$assigned errors=$errors ===");
rbLog('Лог: ' . $logFile);
