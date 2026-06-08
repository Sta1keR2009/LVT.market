<?php
/**
 * Хелперы поиска элемента IB41 по коду ETM (свойство kod_tovara_ / №2568).
 */

declare(strict_types=1);

function etmEnsureIb41Config(): void {
    if (!defined('API_ETM_PROP_ETM_CODE')) {
        require_once dirname(__DIR__) . '/config_ib40.php';
    }
}

function etmPropValueEmpty($value): bool {
    if (is_array($value)) {
        foreach ($value as $v) {
            if (!etmPropValueEmpty($v)) {
                return false;
            }
        }
        return true;
    }
    $s = trim((string)$value);
    return $s === '' || $s === '0';
}

function etmGetElementPropValue(int $iblockId, int $elementId, string $propCode): string {
    $res = CIBlockElement::GetProperty(
        $iblockId,
        $elementId,
        ['sort' => 'asc', 'id' => 'asc'],
        ['CODE' => $propCode]
    );
    while ($row = $res->Fetch()) {
        $v = trim((string)($row['VALUE'] ?? ''));
        if ($v !== '' && $v !== '0') {
            return $v;
        }
    }
    return '';
}

/**
 * Код ETM у элемента: сначала kod_tovara_, иначе legacy ID_ELEMENTA.
 */
function etmGetElementEtmCode(int $iblockId, int $elementId): string {
    etmEnsureIb41Config();
    $code = etmGetElementPropValue($iblockId, $elementId, API_ETM_PROP_ETM_CODE);
    if ($code !== '') {
        return $code;
    }
    if (defined('API_ETM_PROP_ETM_CODE_LEGACY')) {
        return etmGetElementPropValue($iblockId, $elementId, API_ETM_PROP_ETM_CODE_LEGACY);
    }
    return '';
}

/**
 * При дубликатах kod_tovara_ предпочитаем активный элемент с ЧПU (не etm_*), затем больший ID.
 */
function etmScoreElementRowForDedup(array $row): int {
    $score = 0;
    if (($row['ACTIVE'] ?? '') === 'Y') {
        $score += 1000;
    }
    $code = trim((string)($row['CODE'] ?? ''));
    if ($code !== '' && !preg_match('/^etm_\d+$/i', $code)) {
        $score += 500;
    }
    $score += (int)($row['ID'] ?? 0);
    return $score;
}

function etmPickBestElementId(int $iblockId, array $candidateIds): int {
    $bestId = 0;
    $bestScore = -1;
    foreach ($candidateIds as $id) {
        $id = (int)$id;
        if ($id <= 0) {
            continue;
        }
        $row = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, 'ID' => $id],
            false,
            ['nTopCount' => 1],
            ['ID', 'CODE', 'ACTIVE']
        )->Fetch();
        if (!$row) {
            continue;
        }
        $score = etmScoreElementRowForDedup($row);
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestId = $id;
        }
    }
    return $bestId;
}

function etmMergeEtmCodeMapEntry(int $iblockId, array &$map, string $etmCode, int $elementId): void {
    $etmCode = trim($etmCode);
    if ($etmCode === '' || $elementId <= 0) {
        return;
    }
    if (!isset($map[$etmCode])) {
        $map[$etmCode] = $elementId;
        return;
    }
    $map[$etmCode] = etmPickBestElementId($iblockId, [$map[$etmCode], $elementId]);
}

function etmFindElementIdsByEtmCodeProp(int $iblockId, string $propCode, string $etmCode): array {
    $ids = [];
    $res = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $iblockId, 'PROPERTY_' . $propCode => $etmCode],
        false,
        false,
        ['ID']
    );
    while ($row = $res->Fetch()) {
        $ids[] = (int)$row['ID'];
    }
    return $ids;
}

/**
 * ID элемента по коду ETM (свойство API_ETM_PROP_ETM_CODE).
 */
function etmFindElementIdByEtmCode(int $iblockId, string $etmCode): int {
    etmEnsureIb41Config();
    $etmCode = trim($etmCode);
    if ($etmCode === '') {
        return 0;
    }
    $ids = etmFindElementIdsByEtmCodeProp($iblockId, API_ETM_PROP_ETM_CODE, $etmCode);
    if ($ids !== []) {
        return etmPickBestElementId($iblockId, $ids);
    }
    if (defined('API_ETM_PROP_ETM_CODE_LEGACY')) {
        $ids = etmFindElementIdsByEtmCodeProp($iblockId, API_ETM_PROP_ETM_CODE_LEGACY, $etmCode);
        if ($ids !== []) {
            return etmPickBestElementId($iblockId, $ids);
        }
    }
    $row = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $iblockId, 'CODE' => 'etm_' . $etmCode],
        false,
        ['nTopCount' => 1],
        ['ID']
    )->Fetch();
    return $row ? (int)$row['ID'] : 0;
}
