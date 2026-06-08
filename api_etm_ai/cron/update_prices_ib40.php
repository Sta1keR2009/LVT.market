<?php
/**
 * Cron IB41: обновление цен ETM по свойству «Код товара» (kod_tovara_, №2568).
 */
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

cpLog('=== ETM update_prices_ib40 START ===');

$iblockId    = (int)API_ETM_IBLOCK_ID;
$propCode    = API_ETM_PROP_ETM_CODE;
$priceTypeId = (int)API_ETM_PRICE_TYPE_ID;

cpLog('IB=' . $iblockId . ', prop=' . $propCode . ' (#' . API_ETM_PROP_ETM_CODE_ID . '), CATALOG_GROUP_ID(price)=' . $priceTypeId);

cpLog('Загружаем ETM-коды из Bitrix IB' . $iblockId . '...');
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
$total = count($etmMap);
if (is_array($etmIdsFilter) && $etmIdsFilter !== []) {
    $filtered = [];
    foreach ($etmIdsFilter as $code) {
        if (isset($etmMap[$code])) {
            $filtered[$code] = $etmMap[$code];
        }
    }
    $etmMap = $filtered;
    $total = count($etmMap);
    cpLog('Фильтр etm-ids: ' . implode(', ', $etmIdsFilter) . ' → найдено ' . $total);
}
cpLog("Найдено $total товаров с ETM-кодом.");

if ($total === 0) {
    cpLog('Нечего обновлять. Запустите migrate_etm_code_to_prop2568.php.');
    exit(0);
}

$client = new ApiEtmClient(ETM_API_URL, ETM_LOGIN, ETM_PASSWORD);
if (!$client->login()) {
    cpLog('ОШИБКА авторизации ETM API. HTTP=' . $client->lastHttpCode);
    exit(1);
}
cpLog('Авторизация OK.');

$etmIds  = array_keys($etmMap);
$updated = 0;
$errors  = 0;
$chunks  = array_chunk($etmIds, (int)API_ETM_PRICE_BATCH_SIZE);
$totalChunks = count($chunks);

cpLog('Обновляем цены: ' . $total . ' товаров, ' . $totalChunks . ' пакетов...');

foreach ($chunks as $i => $chunk) {
    $rows = $client->getGoodsPrice($chunk, 'etm');
    if ($rows === null) {
        $errors += count($chunk);
        cpLog('  [' . ($i + 1) . "/$totalChunks] ОШИБКА получения цен. HTTP=" . $client->lastHttpCode);
        continue;
    }

    foreach ($rows as $row) {
        $code  = (string)($row['gdscode'] ?? '');
        $price = (float)($row['pricewnds'] ?? $row['price'] ?? 0);
        $elId  = $etmMap[$code] ?? null;
        if (!$elId || $price <= 0) {
            continue;
        }

        if (!CCatalogProduct::GetByID($elId)) {
            CCatalogProduct::Add(['ID' => $elId, 'QUANTITY' => 0]);
        }

        $existing = CPrice::GetList([], ['PRODUCT_ID' => $elId, 'CATALOG_GROUP_ID' => $priceTypeId])->Fetch();
        if ($existing) {
            CPrice::Update($existing['ID'], ['PRICE' => $price, 'CURRENCY' => 'RUB']);
        } else {
            $po = new CPrice();
            $po->Add([
                'PRODUCT_ID'       => $elId,
                'CATALOG_GROUP_ID' => $priceTypeId,
                'PRICE'            => $price,
                'CURRENCY'         => 'RUB',
            ]);
        }
        $updated++;
    }

    if (($i + 1) % 100 === 0 || ($i + 1) === $totalChunks) {
        cpLog(sprintf('  [%d/%d] обновлено=%d ошибок=%d', $i + 1, $totalChunks, $updated, $errors));
    }
}

cpLog("=== ГОТОВО: обновлено=$updated, ошибок=$errors ===");
