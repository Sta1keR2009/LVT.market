<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

// Подключаем конфигурацию и классы
require_once('config.php');
require_once('classes/MouserAPI.php');

// Проверяем права
global $USER;
if (!$USER->IsAdmin()) {
    die("Доступ запрещен. Только для администраторов.");
}

// Проверяем наличие модуля инфоблоков
if (!CModule::IncludeModule("iblock")) {
    die("Ошибка: Модуль инфоблоков не подключен");
}

// Создаем директории если их нет
if (!file_exists(IMAGE_PATH)) {
    mkdir(IMAGE_PATH, 0755, true);
}
if (!file_exists(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

// Инициализируем логирование
$log_file = LOG_PATH . 'import_batch_' . date('Y-m-d_H-i-s') . '.log';
$import_stats_file = LOG_PATH . 'import_stats.json';

// Статистика
$stats = [
    'start_time' => date('Y-m-d H:i:s'),
    'categories_total' => 0,
    'categories_processed' => 0,
    'items_total' => 0,
    'items_success' => 0,
    'items_failed' => 0,
    'categories' => []
];

function addLog($message, $type = 'INFO') {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $formatted = "[$timestamp] [$type] $message\n";
    
    // Пишем в файл
    file_put_contents($log_file, $formatted, FILE_APPEND);
    
    // Выводим в браузер
    $colors = [
        'INFO' => 'black',
        'SUCCESS' => 'green',
        'WARNING' => 'orange',
        'ERROR' => 'red',
        'DEBUG' => 'blue'
    ];
    $color = $colors[$type] ?? 'black';
    echo "<div style='color: $color; margin: 2px 0; font-family: monospace; font-size: 12px;'>$formatted</div>";
    
    // Для консоли
    if (php_sapi_name() === 'cli') {
        echo $formatted;
    }
    
    // Обновляем статистику
    if (strpos($message, 'Импортировано товаров') !== false) {
        preg_match('/Импортировано товаров: (\d+)/', $message, $matches);
        if (isset($matches[1])) {
            updateStats(['items_success' => $matches[1]]);
        }
    }
}

function updateStats($data) {
    global $import_stats_file, $stats;
    $stats = array_merge($stats, $data);
    file_put_contents($import_stats_file, json_encode($stats, JSON_PRETTY_PRINT));
}

// Функция для загрузки изображения
function downloadImageWithProxy($url, $filename) {
    if (empty($url)) {
        return false;
    }
    
    $local_path = IMAGE_PATH . $filename;
    
    // Если файл уже существует, возвращаем его
    if (file_exists($local_path)) {
        return $local_path;
    }
    
    // Используем прокси
    global $proxies, $proxy_auth;
    $proxy = $proxies[array_rand($proxies)];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_auth['username'] . ':' . $proxy_auth['password']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $image_data = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($error || $http_code != 200 || empty($image_data)) {
        addLog("Ошибка загрузки изображения: $url (HTTP: $http_code, Error: $error)", 'ERROR');
        return false;
    }
    
    // Сохраняем файл
    if (file_put_contents($local_path, $image_data)) {
        addLog("Изображение скачано: $filename", 'SUCCESS');
        return $local_path;
    }
    
    return false;
}

// Функция для получения или создания раздела
function getOrCreateSection($category_name, $parent_id = 0) {
    if (empty($category_name)) {
        return 0;
    }
    
    $category_name = trim($category_name);
    
    // Ищем раздел
    $filter = [
        'IBLOCK_ID' => INFOBLOCK_ID,
        'NAME' => $category_name
    ];
    
    if ($parent_id > 0) {
        $filter['SECTION_ID'] = $parent_id;
    }
    
    $section = CIBlockSection::GetList(
        [],
        $filter,
        false,
        ['ID', 'NAME'],
        ['nTopCount' => 1]
    )->Fetch();
    
    if ($section) {
        return $section['ID'];
    }
    
    // Создаем новый раздел
    $bs = new CIBlockSection;
    $arFields = [
        "IBLOCK_ID" => INFOBLOCK_ID,
        "IBLOCK_SECTION_ID" => $parent_id,
        "NAME" => $category_name,
        "ACTIVE" => "Y",
        "SORT" => 500,
        "CODE" => CUtil::translit($category_name, "ru")
    ];
    
    $new_section_id = $bs->Add($arFields);
    
    if (!$new_section_id) {
        addLog("Ошибка создания раздела '$category_name': " . $bs->LAST_ERROR, 'ERROR');
        return 0;
    }
    
    addLog("Создан новый раздел: $category_name (ID: $new_section_id)", 'SUCCESS');
    return $new_section_id;
}

// Функция для создания файлового массива
function createBitrixFileArray($file_path) {
    if (!file_exists($file_path)) {
        return false;
    }
    
    $file_array = CFile::MakeFileArray($file_path);
    if ($file_array) {
        $file_array['MODULE_ID'] = 'iblock';
        return $file_array;
    }
    
    return false;
}

// Основная функция импорта категории
function importCategory($search_term, $category_stats = []) {
    global $proxies, $proxy_auth;
    
    addLog("=== НАЧИНАЕМ ИМПОРТ КАТЕГОРИИ: $search_term ===", 'INFO');
    
    $mouser = new MouserAPI(
        MOUSER_API_KEY, 
        API_ENDPOINT, 
        $proxies, 
        $proxy_auth
    );
    
    $items_imported = 0;
    $items_updated = 0;
    $items_failed = 0;
    $pages_processed = 0;
    $starting_record = 0;
    
    try {
        // Проверяем соединение
        addLog("Проверка соединения...", 'INFO');
        if (!$mouser->testConnection()) {
            addLog("Не удалось подключиться к Mouser API", 'ERROR');
            return ['success' => false, 'message' => 'Connection failed'];
        }
        
        while ($items_imported + $items_updated < MAX_ITEMS_PER_CATEGORY) {
            $page_number = $pages_processed + 1;
            addLog("Страница $page_number, запрашиваем с позиции $starting_record...", 'INFO');
            
            $result = $mouser->search($search_term, ITEMS_PER_PAGE, $starting_record);
            
            if (empty($result['SearchResults']['Parts'])) {
                addLog("Товары не найдены или достигнут конец", 'WARNING');
                break;
            }
            
            $parts = $result['SearchResults']['Parts'];
            $total_results = $result['SearchResults']['NumberOfResult'] ?? 0;
            
            addLog("Найдено товаров: $total_results, обрабатываем страницу $page_number", 'INFO');
            
            foreach ($parts as $index => $part) {
                $mouser_part_number = $part['MouserPartNumber'] ?? '';
                
                if (empty($mouser_part_number)) {
                    $items_failed++;
                    continue;
                }
                
                // Проверяем существование товара
                $existing_element = CIBlockElement::GetList(
                    [],
                    [
                        'IBLOCK_ID' => INFOBLOCK_ID,
                        '=PROPERTY_MOUSER_PART_NUMBER' => $mouser_part_number
                    ],
                    false,
                    false,
                    ['ID']
                )->Fetch();
                
                // 1. Обрабатываем категорию
                $section_id = 0;
                if (!empty($part['Category'])) {
                    $section_id = getOrCreateSection($part['Category']);
                }
                
                // 2. Загружаем изображение
                $preview_picture = false;
                $detail_picture = false;
                
                if (!empty($part['ImagePath'])) {
                    $image_ext = pathinfo($part['ImagePath'], PATHINFO_EXTENSION);
                    $image_filename = md5($mouser_part_number) . '.' . ($image_ext ?: 'jpg');
                    $local_image_path = downloadImageWithProxy($part['ImagePath'], $image_filename);
                    
                    if ($local_image_path) {
                        $file_array = createBitrixFileArray($local_image_path);
                        if ($file_array) {
                            $preview_picture = $file_array;
                            $detail_picture = $file_array;
                        }
                    }
                }
                
                // 3. Подготавливаем описание
                $description = $part['Description'] ?? '';
                $manufacturer = $part['Manufacturer'] ?? '';
                $mpn = $part['ManufacturerPartNumber'] ?? $mouser_part_number;
                
                $preview_text = strip_tags($description);
                if (strlen($preview_text) > 200) {
                    $preview_text = substr($preview_text, 0, 197) . '...';
                }
                
                $detail_text = $description;
                
                // Технические характеристики
                if (!empty($part['ProductAttributes']) && is_array($part['ProductAttributes'])) {
                    $detail_text .= "\n\n<h3>Технические характеристики:</h3>\n<ul>";
                    foreach ($part['ProductAttributes'] as $attr) {
                        $attr_name = htmlspecialchars($attr['AttributeName'] ?? '');
                        $attr_value = htmlspecialchars($attr['AttributeValue'] ?? '');
                        $detail_text .= "\n<li><strong>{$attr_name}:</strong> {$attr_value}</li>";
                    }
                    $detail_text .= "\n</ul>";
                }
                
                // 4. Подготавливаем данные элемента
                $arFields = [
                    "IBLOCK_ID" => INFOBLOCK_ID,
                    "IBLOCK_SECTION_ID" => $section_id,
                    "NAME" => $mpn . ' - ' . $manufacturer . ' - ' . substr($description, 0, 100),
                    "CODE" => CUtil::translit($mouser_part_number, "ru", ['replace_space' => '_', 'replace_other' => '_']),
                    "ACTIVE" => "Y",
                    "PREVIEW_TEXT" => $preview_text,
                    "PREVIEW_TEXT_TYPE" => "html",
                    "DETAIL_TEXT" => $detail_text,
                    "DETAIL_TEXT_TYPE" => "html",
                    "SORT" => 500,
                    "XML_ID" => $mouser_part_number,
                ];
                
                // Добавляем изображения
                if ($preview_picture) {
                    $arFields["PREVIEW_PICTURE"] = $preview_picture;
                }
                if ($detail_picture) {
                    $arFields["DETAIL_PICTURE"] = $detail_picture;
                }
                
                // 5. Подготавливаем свойства
                $properties = [
                    'MOUSER_PART_NUMBER' => $mouser_part_number,
                    'MANUFACTURER_PART_NUMBER' => $mpn,
                    'MANUFACTURER' => $manufacturer,
                    'AVAILABILITY' => $part['Availability'] ?? '',
                    'QUANTITY' => $part['FactoryStock'] ?? 0,
                    'LEAD_TIME' => $part['LeadTime'] ?? '',
                    'MIN_ORDER_QTY' => $part['Min'] ?? 1,
                    'DATASHEET_URL' => $part['DataSheetUrl'] ?? '',
                    'PRODUCT_URL' => $part['ProductDetailUrl'] ?? '',
                    'ROHS_STATUS' => $part['ROHSStatus'] ?? '',
                    'PACKAGING' => $part['Mult'] ?? '',
                    'ALTERNATE_PACKAGING' => $part['AlternatePackaging'] ?? '',
                    'MOUSER_LAST_UPDATE' => date('d.m.Y H:i:s'),
                    'IMAGE_URL' => $part['ImagePath'] ?? ''
                ];
                
                // Цены
                if (!empty($part['PriceBreaks']) && is_array($part['PriceBreaks'])) {
                    $price_breaks_text = [];
                    foreach ($part['PriceBreaks'] as $price_break) {
                        $price_breaks_text[] = "{$price_break['Quantity']}+: {$price_break['Price']} {$price_break['Currency']}";
                    }
                    $properties['PRICE_BREAKS'] = implode("\n", $price_breaks_text);
                    
                    $main_price = $part['PriceBreaks'][0]['Price'] ?? 0;
                    $properties['PRICE'] = (float)$main_price;
                }
                
                // Атрибуты в JSON
                if (!empty($part['ProductAttributes']) && is_array($part['ProductAttributes'])) {
                    $properties['PRODUCT_ATTRIBUTES'] = json_encode($part['ProductAttributes'], JSON_UNESCAPED_UNICODE);
                }
                
                // REACH compliance
                if (!empty($part['ReachStatus'])) {
                    $properties['REACH_COMPLIANT'] = $part['ReachStatus'] == 'Compliant' ? 'Да' : 'Нет';
                }
                
                $arFields["PROPERTY_VALUES"] = $properties;
                
                // 6. Сохраняем/обновляем элемент
                if ($existing_element) {
                    $element = new CIBlockElement;
                    if ($element->Update($existing_element['ID'], $arFields)) {
                        $items_updated++;
                        if ($items_updated % 10 == 0) {
                            addLog("Обновлено $items_updated товаров в категории $search_term", 'INFO');
                        }
                    } else {
                        $items_failed++;
                    }
                } else {
                    $element = new CIBlockElement;
                    $element_id = $element->Add($arFields);
                    
                    if ($element_id) {
                        $items_imported++;
                        if ($items_imported % 10 == 0) {
                            addLog("Импортировано $items_imported товаров в категории $search_term", 'SUCCESS');
                        }
                    } else {
                        $items_failed++;
                        addLog("Ошибка создания товара $mouser_part_number: " . $element->LAST_ERROR, 'ERROR');
                    }
                }
                
                // Небольшая пауза между товарами
                usleep(10000); // 0.01 секунды
            }
            
            $starting_record += ITEMS_PER_PAGE;
            $pages_processed++;
            
            // Проверяем лимиты
            if ($starting_record >= $total_results || $starting_record >= 10000) {
                addLog("Достигнут лимит товаров для категории", 'INFO');
                break;
            }
            
            // Пауза между страницами
            sleep(REQUEST_DELAY);
            
            // Проверяем общий прогресс
            $total_processed = $items_imported + $items_updated;
            if ($total_processed >= MAX_ITEMS_PER_CATEGORY) {
                addLog("Достигнут лимит в " . MAX_ITEMS_PER_CATEGORY . " товаров для категории", 'INFO');
                break;
            }
        }
        
        $total_processed = $items_imported + $items_updated;
        addLog("=== ЗАВЕРШЕНО: $search_term ===", 'SUCCESS');
        addLog("Итого: $total_processed товаров (новых: $items_imported, обновлено: $items_updated, ошибок: $items_failed)", 'SUCCESS');
        
        return [
            'success' => true,
            'category' => $search_term,
            'items_imported' => $items_imported,
            'items_updated' => $items_updated,
            'items_failed' => $items_failed,
            'total_processed' => $total_processed,
            'pages_processed' => $pages_processed
        ];
        
    } catch (Exception $e) {
        addLog("Ошибка импорта категории $search_term: " . $e->getMessage(), 'ERROR');
        return [
            'success' => false,
            'category' => $search_term,
            'error' => $e->getMessage()
        ];
    }
}

// Запускаем импорт
echo "<html><head><title>Массовый импорт Mouser</title>
<style>
body { font-family: monospace; font-size: 12px; margin: 20px; }
.status { padding: 10px; margin: 10px 0; border-radius: 5px; }
.status-success { background: #d4edda; border: 1px solid #c3e6cb; }
.status-error { background: #f8d7da; border: 1px solid #f5c6cb; }
.status-warning { background: #fff3cd; border: 1px solid #ffeaa7; }
.progress-bar { width: 100%; height: 20px; background: #f0f0f0; border-radius: 10px; margin: 10px 0; }
.progress-fill { height: 100%; background: #007bff; border-radius: 10px; transition: width 0.3s; }
</style>
</head><body>";

echo "<h1>Массовый импорт товаров из Mouser</h1>";
echo "<p><strong>План:</strong> 50 категорий × 1000 товаров = до 50,000 товаров</p>";

addLog("=== НАЧАЛО МАССОВОГО ИМПОРТА ===", 'INFO');
addLog("Дата и время: " . date('Y-m-d H:i:s'), 'INFO');
addLog("Категорий для импорта: " . count($search_terms), 'INFO');
addLog("Лимит на категорию: " . MAX_ITEMS_PER_CATEGORY . " товаров", 'INFO');

$total_start_time = microtime(true);
$stats['categories_total'] = count($search_terms);

// Обработка GET параметров для поэтапного импорта
$start_from = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : count($search_terms);

if ($start_from > 0) {
    addLog("Продолжение импорта с категории №$start_from", 'INFO');
}

// Импортируем категории
$processed_categories = 0;
$all_items_imported = 0;
$all_items_updated = 0;

for ($i = $start_from; $i < min($start_from + $limit, count($search_terms)); $i++) {
    $category = $search_terms[$i];
    $category_number = $i + 1;
    
    echo "<div class='status status-success'>";
    addLog("Категория $category_number из " . count($search_terms) . ": $category", 'INFO');
    echo "</div>";
    
    $result = importCategory($category);
    
    if ($result['success']) {
        $processed_categories++;
        $all_items_imported += $result['items_imported'];
        $all_items_updated += $result['items_updated'];
        
        // Сохраняем статистику по категории
        $stats['categories'][] = [
            'name' => $category,
            'items_imported' => $result['items_imported'],
            'items_updated' => $result['items_updated'],
            'total' => $result['total_processed']
        ];
        
        $stats['categories_processed'] = $processed_categories;
        $stats['items_success'] = $all_items_imported + $all_items_updated;
        updateStats($stats);
        
        // Прогресс
        $progress = round(($processed_categories / count($search_terms)) * 100, 1);
        echo "<div class='progress-bar'>
                <div class='progress-fill' style='width: {$progress}%'></div>
              </div>";
        echo "<p>Прогресс: $processed_categories из " . count($search_terms) . " категорий ($progress%)</p>";
        
        // Пауза между категориями
        if ($i < count($search_terms) - 1) {
            $pause = 5;
            addLog("Пауза $pause секунд перед следующей категорией...", 'INFO');
            sleep($pause);
        }
    } else {
        addLog("Пропускаем категорию $category из-за ошибки: " . $result['error'], 'ERROR');
    }
}

$total_end_time = microtime(true);
$total_time = round($total_end_time - $total_start_time, 2);

// Финальная статистика
addLog("=== ИМПОРТ ЗАВЕРШЕН ===", 'INFO');
addLog("Обработано категорий: $processed_categories из " . count($search_terms), 'SUCCESS');
addLog("Всего товаров: " . ($all_items_imported + $all_items_updated), 'SUCCESS');
addLog("  - Новых: $all_items_imported", 'SUCCESS');
addLog("  - Обновлено: $all_items_updated", 'SUCCESS');
addLog("Общее время: {$total_time} секунд (" . round($total_time / 60, 1) . " минут)", 'INFO');

// Обновляем финальную статистику
$stats['end_time'] = date('Y-m-d H:i:s');
$stats['total_time_seconds'] = $total_time;
updateStats($stats);

// Ссылки для продолжения
echo "<hr>";
echo "<h2>Результаты импорта</h2>";
echo "<p><strong>Обработано категорий:</strong> $processed_categories из " . count($search_terms) . "</p>";
echo "<p><strong>Всего товаров:</strong> " . ($all_items_imported + $all_items_updated) . "</p>";
echo "<p><strong>Время выполнения:</strong> {$total_time} секунд</p>";

echo "<h3>Действия:</h3>";
echo "<ul>";
echo "<li><a href='/bitrix/admin/iblist.php?IBLOCK_ID=" . INFOBLOCK_ID . "&type=catalog&lang=ru' target='_blank'>Просмотреть импортированные товары</a></li>";
echo "<li><a href='/bitrix/admin/iblock_section_admin.php?IBLOCK_ID=" . INFOBLOCK_ID . "&type=catalog&lang=ru' target='_blank'>Просмотреть категории (разделы)</a></li>";
echo "<li><a href='$log_file' target='_blank'>Скачать лог файл</a></li>";
echo "<li><a href='$import_stats_file' target='_blank'>Скачать статистику</a></li>";
echo "</ul>";

// Если нужно продолжить
$next_start = $start_from + $limit;
if ($next_start < count($search_terms)) {
    echo "<div class='status status-warning'>";
    echo "<h3>Продолжение импорта</h3>";
    echo "<p>Еще " . (count($search_terms) - $next_start) . " категорий осталось импортировать.</p>";
    echo "<a href='import_batch.php?start=$next_start&limit=$limit' class='button'>Продолжить импорт</a>";
    echo "</div>";
}

echo "</body></html>";

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog.php');
?>