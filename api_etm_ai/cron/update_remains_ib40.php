<?php
/**
 * Cron IB41: остатки ETM по свойству «Код товара» (kod_tovara_, №2568).
 */
define('MAX_PER_RUN', 1800);

require_once __DIR__ . '/_etm_ib40_cron_bootstrap.php';
require_once dirname(__DIR__) . '/includes/etm_element_code.php';

$etmIdsFilter = null;
foreach ($argv ?? [] as $arg) {
    if (preg_match('/^--etm-ids=(.+)$/', $arg, $m)) {
        $parts = array_map('trim', explode(',', $m[1]));
        $etmIdsFilter = [];
        foreach ($parts as $p) {
            if ($p !== '') {
                $etmIdsFilter[] = (string)$p;
            }
        }
    }
}

rmLog('=== ETM update_remains_ib40 START ===');

$iblockId = (int)API_ETM_IBLOCK_ID;
$propCode = API_ETM_PROP_ETM_CODE;
$storeId  = (int)API_ETM_STORE_ID;

rmLog('IB=' . $iblockId . ', prop=' . $propCode . ' (#' . API_ETM_PROP_ETM_CODE_ID . '), STORE=' . $storeId);

$remainsStateFile = defined('API_ETM_REMAINS_OFFSET_FILE')
    ? API_ETM_REMAINS_OFFSET_FILE
    : API_ETM_LOGS_DIR . '/remains_offset_ib40.json';

rmLog('Загружаем ETM-коды из Bitrix IB' . $iblockId . '...');
$etmMap = [];
$res = CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => $iblockId, '!PROPERTY_' . $propCode => false],
    false,
    false,
    ['ID', 'PROPERTY_' . $propCode]
);
while ($row = $res->Fetch()) {
    $val = (string)($row['PROPERTY_' . strtoupper($propCode) . '_VALUE'] ?? '');
    if ($val !== '') {
        etmMergeEtmCodeMapEntry($iblockId, $etmMap, $val, (int)$row['ID']);
    }
}
$allCodes = array_keys($etmMap);
$total = count($allCodes);
if (is_array($etmIdsFilter) && $etmIdsFilter !== []) {
    $allCodes = array_values(array_filter($etmIdsFilter, static fn($c) => isset($etmMap[$c])));
    $total = count($allCodes);
    rmLog('Фильтр etm-ids: ' . implode(', ', $etmIdsFilter) . ' → найдено ' . $total);
}
rmLog("Найдено $total товаров с ETM-кодом.");

if ($total === 0) {
    rmLog('Нечего обновлять. Запустите migrate_etm_code_to_prop2568.php.');
    exit(0);
}

$offset = 0;
if (!is_array($etmIdsFilter) || $etmIdsFilter === []) {
    if (is_readable($remainsStateFile)) {
        $savedState = json_decode((string)file_get_contents($remainsStateFile), true) ?? [];
    }
    $offset = (int)($savedState['offset'] ?? 0);
    if ($offset >= $total) {
        $offset = 0;
        rmLog('Новый цикл обновления остатков.');
    }
    rmLog('Начинаем с позиции ' . $offset . ' из ' . $total);
} else {
    rmLog('Пилотный режим: все коды из фильтра');
}

$client = new ApiEtmClient(ETM_API_URL, ETM_LOGIN, ETM_PASSWORD);
if (!$client->login()) {
    rmLog('ОШИБКА авторизации ETM API. HTTP=' . $client->lastHttpCode);
    exit(1);
}
rmLog('Авторизация OK.');

$slice   = array_slice($allCodes, $offset, MAX_PER_RUN);
$updated = 0;
$errors  = 0;
$cnt     = count($slice);

foreach ($slice as $i => $etmCode) {
    $elId = $etmMap[$etmCode];
    $data = $client->getGoodsRemains($etmCode, 'etm');

    if ($data === null) {
        $errors++;
        continue;
    }

    $qty = 0;
    $stores = $data['data']['InfoStores'] ?? ($data['InfoStores'] ?? []);
    foreach ((array)$stores as $store) {
        $type = $store['StoreType'] ?? '';
        if ($type === 'all' || $type === '') {
            $qty = max($qty, (int)($store['StoreQuantRem'] ?? 0));
        }
    }
    if ($qty === 0) {
        foreach ((array)$stores as $store) {
            if (($store['StoreType'] ?? '') === 'reg') {
                $qty += (int)($store['StoreQuantRem'] ?? 0);
            }
        }
    }

    $prod = CCatalogProduct::GetByID($elId);
    if ($prod) {
        CCatalogProduct::Update($elId, ['QUANTITY' => $qty]);
    } else {
        CCatalogProduct::Add(['ID' => $elId, 'QUANTITY' => $qty]);
    }
    $updated++;

    if (($i + 1) % 300 === 0) {
        rmLog(sprintf('  [%d/%d] обновлено=%d ошибок=%d', $i + 1, $cnt, $updated, $errors));
    }
}

$newOffset = $offset + $cnt;
if (!is_array($etmIdsFilter) || $etmIdsFilter === []) {
    file_put_contents($remainsStateFile, json_encode([
        'offset'     => $newOffset >= $total ? 0 : $newOffset,
        'total'      => $total,
        'last_run'   => date('Y-m-d H:i:s'),
        'last_range' => $offset . ' - ' . ($offset + $cnt - 1),
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

rmLog(sprintf(
    '=== ГОТОВО: обновлено=%d, ошибок=%d. Позиция: %d/%d ===',
    $updated,
    $errors,
    min($newOffset, $total),
    $total
));
