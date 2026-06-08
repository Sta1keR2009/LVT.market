<?php
/**
 * Cron: обновление цен ETM-товаров в Bitrix.
 * Запускать ежедневно. Работает через PHP CLI или web.
 *
 * Логика:
 *  1. Выбирает все элементы IB=11 с заполненным свойством promelec.
 *  2. Запрашивает цены пакетами по 50 через /goods/{ids}/price?type=etm.
 *  3. Обновляет BASE-цену (priceTypeId=1) в rub.
 *
 * Запуск из crontab (ежедневно в 03:00):
 *   0 3 * * * php /var/www/www-root/data/www/lvtgroup.ru/api_etm_ai/cron/update_prices.php >> /var/www/www-root/data/www/lvtgroup.ru/api_etm_ai/logs/cron_prices.log 2>&1
 */

set_time_limit(7200);
ini_set('memory_limit', '256M');

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

// Bootstrap: работает и в CLI, и в web
$docRoot = '';
if (php_sapi_name() === 'cli') {
    // В CLI ищем document_root рядом
    $tryRoots = [
        '/var/www/www-root/data/www/lvtgroup.ru',
        dirname(dirname(__DIR__)),
    ];
    foreach ($tryRoots as $r) {
        if (is_dir($r . '/bitrix')) { $docRoot = $r; break; }
    }
} else {
    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
}
if ($docRoot === '') {
    die("Cannot find document root\n");
}

$_SERVER['DOCUMENT_ROOT'] = $docRoot;
require_once $docRoot . '/bitrix/modules/main/include/prolog_before.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/classes/EtmApiClient.php';

if (!CModule::IncludeModule('iblock'))   { die("iblock not loaded\n"); }
if (!CModule::IncludeModule('catalog'))  { die("catalog not loaded\n"); }

$logFile = API_ETM_LOGS_DIR . '/cron_prices_' . date('Y-m-d') . '.log';
if (!is_dir(API_ETM_LOGS_DIR)) { @mkdir(API_ETM_LOGS_DIR, 0755, true); }

function cpLog(string $msg): void {
    global $logFile;
    $line = '[' . date('H:i:s') . '] ' . $msg . "\n";
    echo $line;
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

cpLog('=== ETM update_prices START ===');

$iblockId    = (int)API_ETM_IBLOCK_ID;
$propCode    = API_ETM_PROP_ETM_CODE;
$priceTypeId = (int)API_ETM_PRICE_TYPE_ID;

// Загружаем все ETM-коды + Bitrix element ID
cpLog('Загружаем ETM-коды из Bitrix IB' . $iblockId . '...');
$etmMap = []; // etmCode => elementId
$res = CIBlockElement::GetList(
    [], ['IBLOCK_ID' => $iblockId, '!PROPERTY_' . $propCode => false],
    false, false, ['ID', 'PROPERTY_' . $propCode]
);
while ($row = $res->Fetch()) {
    $val = $row['PROPERTY_' . strtoupper($propCode) . '_VALUE'];
    if ((string)$val !== '') {
        $etmMap[(string)$val] = (int)$row['ID'];
    }
}
$total = count($etmMap);
cpLog("Найдено $total товаров с ETM-кодом.");

if ($total === 0) {
    cpLog('Нечего обновлять.');
    exit(0);
}

// Авторизация
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

cpLog("Обновляем цены: $total товаров, $totalChunks пакетов по " . API_ETM_PRICE_BATCH_SIZE . "...");

foreach ($chunks as $i => $chunk) {
    $rows = $client->getGoodsPrice($chunk, 'etm');
    if ($rows === null) {
        $errors += count($chunk);
        cpLog("  [" . ($i+1) . "/$totalChunks] ОШИБКА получения цен. HTTP=" . $client->lastHttpCode);
        continue;
    }

    foreach ($rows as $row) {
        $code  = (string)($row['gdscode'] ?? '');
        $price = (float)($row['pricewnds'] ?? $row['price'] ?? 0);
        $elId  = $etmMap[$code] ?? null;
        if (!$elId || $price <= 0) { continue; }

        // Создаём запись в каталоге если нет
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
        cpLog(sprintf('  [%d/%d] обновлено=%d ошибок=%d', $i+1, $totalChunks, $updated, $errors));
    }
}

cpLog("=== ГОТОВО: обновлено=$updated, ошибок=$errors ===");
