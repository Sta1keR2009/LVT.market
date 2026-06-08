<?php
/**
 * Импорт каталога ETM в Bitrix IBLOCK_ID=11, раздел 4064.
 *
 * Логика:
 *  1. Читает goods_report.json (выгрузка /job/create/40029846).
 *  2. Создаёт разделы по class_code / class под rootSectionId.
 *  3. Создаёт/обновляет элементы в IB 11.
 *  4. Обновляет цены пакетами по 50 (pricewnds — с НДС).
 *
 * GET-параметры:
 *  ?action=import          — полный импорт (по умолчанию)
 *  ?action=refresh_report  — перезапустить job и скачать свежую выгрузку
 *  ?limit=N                — обработать только N товаров (тест)
 *  ?offset=N               — начать с позиции N
 *  ?prices=0               — пропустить обновление цен
 */

set_time_limit(3600);
ini_set('memory_limit', '512M');
ignore_user_abort(true); // Продолжаем работу даже если nginx закрыл соединение

require_once __DIR__ . '/bootstrap.php';

if (!CModule::IncludeModule('iblock'))   { die('Модуль iblock не подключен.');   }
if (!CModule::IncludeModule('catalog'))  { die('Модуль catalog не подключен.');  }

header('Content-Type: text/html; charset=utf-8');

$action     = $_GET['action'] ?? 'import';
$limit      = isset($_GET['limit'])  ? (int)$_GET['limit']  : 0;
$offset     = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$doPrices   = ($_GET['prices'] ?? '1') !== '0';

$iblockId      = (int)API_ETM_IBLOCK_ID;       // 11
$rootSectionId = (int)API_ETM_ROOT_SECTION_ID;  // 4064
$propEtmCode   = API_ETM_PROP_ETM_CODE;         // promelec
$priceTypeId   = (int)API_ETM_PRICE_TYPE_ID;    // 1
$reportFile    = API_ETM_REPORT_FILE;
$logDir        = API_ETM_LOGS_DIR;

if (!is_dir($logDir)) { @mkdir($logDir, 0755, true); }
$importLog = $logDir . '/import_' . date('Y-m-d_H-i-s') . '.log';

function etmLog(string $msg, string $level = 'INFO'): void {
    global $importLog;
    $line = '[' . date('H:i:s') . '] [' . $level . '] ' . $msg . "\n";
    echo $line;
    @file_put_contents($importLog, $line, FILE_APPEND | LOCK_EX);
    if (ob_get_level()) ob_flush();
    flush();
}

echo "<!DOCTYPE html><html><head><meta charset=utf-8><title>ETM Import</title></head><body><pre>\n";
flush();

$client = new ApiEtmClient(ETM_API_URL, ETM_LOGIN, ETM_PASSWORD);

// =============================================
// ACTION: refresh_report — скачать свежую выгрузку
// =============================================
if ($action === 'refresh_report') {
    etmLog('Авторизация в ETM API...');
    if (!$client->login()) {
        etmLog('Ошибка авторизации: HTTP ' . $client->lastHttpCode, 'ERROR');
        die("</pre></body></html>");
    }
    etmLog('Запуск job /job/create/40029846...');
    $job = $client->createGoodsJob();
    if (!$job || empty($job['uuid'])) {
        etmLog('Не удалось запустить job. Ответ: ' . $client->lastRawResponse, 'ERROR');
        die("</pre></body></html>");
    }
    $uuid = $job['uuid'];
    etmLog("Job создан. UUID: $uuid");
    etmLog('Ожидаем завершения (проверка каждые 15 сек, макс 10 минут)...');

    $maxWait   = 600;
    $waited    = 0;
    $reportUrl = null;
    while ($waited < $maxWait) {
        sleep(15);
        $waited += 15;
        $status = $client->getJobStatus($uuid);
        if (!$status) {
            etmLog("Ошибка получения статуса ({$waited} сек)", 'WARN');
            continue;
        }
        $state     = $status['state'] ?? '';
        $completed = $status['completed'] ?? 'false';
        etmLog("  state=$state completed=$completed ({$waited} сек)");
        if ($completed === 'true' || $state === '1') {
            $reportUrl = $status['urls'][0]['url'] ?? null;
            break;
        }
        if ($state === '2') {
            etmLog('Job завершён с ошибкой: ' . ($status['msg'] ?? ''), 'ERROR');
            die("</pre></body></html>");
        }
    }

    if (!$reportUrl) {
        etmLog("URL отчёта не получен за {$maxWait} сек", 'ERROR');
        die("</pre></body></html>");
    }

    etmLog("Скачиваем отчёт: $reportUrl");
    $content = $client->downloadReportFile($reportUrl);
    if ($content === false) {
        etmLog('Ошибка скачивания. HTTP: ' . $client->lastHttpCode, 'ERROR');
        die("</pre></body></html>");
    }
    $dir = dirname($reportFile);
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    file_put_contents($reportFile, $content);
    $count = count(json_decode($content, true) ?? []);
    etmLog("Сохранено: $reportFile ($count товаров, " . round(strlen($content) / 1024 / 1024, 1) . " МБ)");
    etmLog('Теперь запустите ?action=import');
    echo "</pre></body></html>";
    exit;
}

// =============================================
// ACTION: import — импорт из JSON-файла
// =============================================
if (!file_exists($reportFile)) {
    etmLog("Файл $reportFile не найден. Запустите ?action=refresh_report", 'ERROR');
    die("</pre></body></html>");
}

etmLog('Читаем ' . basename($reportFile) . ' ...');
$allGoods = json_decode(file_get_contents($reportFile), true);
if (!is_array($allGoods) || empty($allGoods)) {
    etmLog('Некорректный или пустой файл', 'ERROR');
    die("</pre></body></html>");
}
etmLog('Всего в выгрузке: ' . count($allGoods));

if ($offset > 0) { $allGoods = array_slice($allGoods, $offset); }
if ($limit  > 0) { $allGoods = array_slice($allGoods, 0, $limit); }
etmLog('Будет обработано: ' . count($allGoods) . ($offset ? " (с позиции $offset)" : ''));

// --- Кеш разделов ---
$sectionCache = [];

function etmGetOrCreateSection(string $name, string $code, int $iblockId, int $parentId): int {
    global $sectionCache;
    $cacheKey = $parentId . '|' . $code;
    if (isset($sectionCache[$cacheKey])) {
        return $sectionCache[$cacheKey];
    }
    // Ищем по коду
    $res = CIBlockSection::GetList(
        [], ['IBLOCK_ID' => $iblockId, 'CODE' => $code, 'SECTION_ID' => $parentId],
        false, false, ['ID']
    )->Fetch();
    if ($res) {
        return $sectionCache[$cacheKey] = (int)$res['ID'];
    }
    // Ищем по имени (на случай если код не совпал)
    $res2 = CIBlockSection::GetList(
        [], ['IBLOCK_ID' => $iblockId, 'NAME' => $name, 'SECTION_ID' => $parentId],
        false, false, ['ID']
    )->Fetch();
    if ($res2) {
        return $sectionCache[$cacheKey] = (int)$res2['ID'];
    }
    // Создаём
    $bs = new CIBlockSection();
    $id = $bs->Add([
        'IBLOCK_ID'         => $iblockId,
        'IBLOCK_SECTION_ID' => $parentId,
        'NAME'              => $name,
        'CODE'              => $code,
        'ACTIVE'            => 'Y',
        'SORT'              => 500,
    ]);
    if (!$id) {
        etmLog("Ошибка раздела '$name': " . $bs->LAST_ERROR, 'ERROR');
        return $parentId;
    }
    etmLog("  + Раздел: $name [code=$code, id=$id]");
    return $sectionCache[$cacheKey] = (int)$id;
}

// --- Индекс существующих элементов по promelec ---
etmLog('Строим индекс существующих ETM-кодов в Bitrix IB' . $iblockId . '...');
$existingByEtm = []; // etmCode => elementId
$res = CIBlockElement::GetList(
    [], ['IBLOCK_ID' => $iblockId, '!PROPERTY_' . $propEtmCode => false],
    false, false, ['ID', 'PROPERTY_' . $propEtmCode]
);
while ($row = $res->Fetch()) {
    $val = $row['PROPERTY_' . strtoupper($propEtmCode) . '_VALUE'];
    if ((string)$val !== '') {
        $existingByEtm[(string)$val] = (int)$row['ID'];
    }
}
etmLog('Уже есть в Bitrix: ' . count($existingByEtm) . ' элементов с ETM-кодом');

// --- Главный цикл ---
$stats      = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];
$priceBatch = []; // etmCode => elementId
$obEl       = new CIBlockElement();

foreach ($allGoods as $idx => $item) {
    $etmId     = (string)($item['id'] ?? '');
    $name      = trim($item['name'] ?? '');
    $brand     = trim($item['brand'] ?? '');
    $article   = trim($item['article'] ?? '');
    $class     = trim($item['class'] ?? '');
    $classCode = trim($item['class_code'] ?? '');

    if ($etmId === '' || $name === '') { $stats['skipped']++; continue; }

    // Нормализация кода раздела
    if ($classCode === '') {
        $classCode = 'etm_' . preg_replace('/[^a-z0-9]/i', '_', strtolower($class));
        $classCode = preg_replace('/_+/', '_', trim($classCode, '_'));
    }

    // Получаем/создаём раздел
    $sectionId = $rootSectionId;
    if ($class !== '') {
        $sectionId = etmGetOrCreateSection($class, $classCode, $iblockId, $rootSectionId);
    }

    $elCode = 'etm_' . $etmId;
    $props  = [
        $propEtmCode   => $etmId,
        'CML2_ARTICLE' => $article,
    ];

    if (isset($existingByEtm[$etmId])) {
        // Обновляем
        $ok = $obEl->Update($existingByEtm[$etmId], [
            'NAME'              => $name,
            'IBLOCK_SECTION_ID' => $sectionId,
            'PROPERTY_VALUES'   => $props,
        ]);
        if ($ok) {
            $stats['updated']++;
            $priceBatch[$etmId] = $existingByEtm[$etmId];
        } else {
            $stats['errors']++;
            etmLog("Ошибка обновления #{$existingByEtm[$etmId]}: " . $obEl->LAST_ERROR, 'ERROR');
        }
    } else {
        // Создаём новый
        $newId = $obEl->Add([
            'IBLOCK_ID'         => $iblockId,
            'IBLOCK_SECTION_ID' => $sectionId,
            'NAME'              => $name,
            'CODE'              => $elCode,
            'ACTIVE'            => 'Y',
            'DETAIL_TEXT'       => '',
            'DETAIL_TEXT_TYPE'  => 'html',
            'PROPERTY_VALUES'   => $props,
        ]);
        if ($newId) {
            $stats['created']++;
            $existingByEtm[$etmId] = $newId;
            $priceBatch[$etmId]    = $newId;
        } else {
            // Если элемент с таким CODE уже существует — найдём его и обновим
            $errMsg = $obEl->LAST_ERROR;
            if (strpos($errMsg, 'символьным кодом') !== false || strpos($errMsg, 'CODE') !== false) {
                $found = CIBlockElement::GetList(
                    [], ['IBLOCK_ID' => $iblockId, 'CODE' => $elCode],
                    false, ['nTopCount' => 1], ['ID']
                )->Fetch();
                if ($found) {
                    $ok = $obEl->Update($found['ID'], [
                        'NAME'              => $name,
                        'IBLOCK_SECTION_ID' => $sectionId,
                        'PROPERTY_VALUES'   => $props,
                    ]);
                    if ($ok) {
                        $stats['updated']++;
                        $existingByEtm[$etmId] = (int)$found['ID'];
                        $priceBatch[$etmId]    = (int)$found['ID'];
                    } else {
                        $stats['errors']++;
                        etmLog("Ошибка обновления по CODE #{$found['ID']}: " . $obEl->LAST_ERROR, 'ERROR');
                    }
                } else {
                    $stats['errors']++;
                    etmLog("Ошибка создания '$name' (CODE не найден): $errMsg", 'ERROR');
                }
            } else {
                $stats['errors']++;
                etmLog("Ошибка создания '$name': $errMsg", 'ERROR');
            }
        }
    }

    if (($idx + 1) % 500 === 0) {
        etmLog(sprintf('[%d/%d] создано=%d обновлено=%d ошибок=%d',
            $idx + 1, count($allGoods), $stats['created'], $stats['updated'], $stats['errors']));
    }
}

etmLog('');
etmLog(sprintf('=== ИМПОРТ ЗАВЕРШЁН: создано=%d, обновлено=%d, пропущено=%d, ошибок=%d ===',
    $stats['created'], $stats['updated'], $stats['skipped'], $stats['errors']));

// =============================================
// Обновление цен
// =============================================
if ($doPrices && !empty($priceBatch)) {
    etmLog('');
    etmLog('Обновляем цены (' . count($priceBatch) . ' товаров, пакеты по 50)...');
    if (!$client->login()) {
        etmLog('Ошибка авторизации для обновления цен', 'ERROR');
    } else {
        $etmIds       = array_keys($priceBatch);
        $priceUpdated = 0;
        $priceErrors  = 0;
        foreach (array_chunk($etmIds, 50) as $chunk) {
            $rows = $client->getGoodsPrice($chunk, 'etm');
            if (!$rows) { $priceErrors += count($chunk); continue; }
            foreach ($rows as $row) {
                $code  = (string)($row['gdscode'] ?? '');
                $price = (float)($row['pricewnds'] ?? $row['price'] ?? 0);
                $elId  = $priceBatch[$code] ?? null;
                if (!$elId || $price <= 0) { continue; }

                // Сначала создаём товар в каталоге если не существует
                $product = CCatalogProduct::GetByID($elId);
                if (!$product) {
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
                $priceUpdated++;
            }
        }
        etmLog("Цены: обновлено=$priceUpdated, ошибок=$priceErrors");
    }
}

// Сохраняем статистику
@file_put_contents($logDir . '/last_import_stats.json', json_encode([
    'date'    => date('Y-m-d H:i:s'),
    'created' => $stats['created'],
    'updated' => $stats['updated'],
    'skipped' => $stats['skipped'],
    'errors'  => $stats['errors'],
    'log'     => $importLog,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

etmLog('Готово. Лог: ' . $importLog);
echo "</pre></body></html>";
