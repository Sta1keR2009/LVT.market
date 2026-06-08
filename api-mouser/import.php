<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

// Подключаем конфигурацию и классы
require_once('config.php');
require_once('classes/MouserAPI.php');
require_once('proxy.php');

// Инициализируем логирование
$log_file = $_SERVER['DOCUMENT_ROOT'].'/api-mouser/import_log.txt';
$log_messages = [];

function addLog($message) {
    global $log_messages;
    $timestamp = date('Y-m-d H:i:s');
    $log_messages[] = "[$timestamp] $message";
    echo $message . PHP_EOL;
}

// Проверяем доступность модуля инфоблоков
if (!CModule::IncludeModule("iblock")) {
    addLog("Ошибка: Модуль инфоблоков не подключен");
    die();
}

// Инициализируем API
$mouser = new MouserAPI(MOUSER_API_KEY, API_ENDPOINT, $proxies, $proxy_auth);

// Проверяем соединение
addLog("Проверка соединения с Mouser API...");
if (!$mouser->testConnection()) {
    addLog("Ошибка: Не удалось подключиться к Mouser API через прокси");
    die();
}
addLog("Соединение установлено успешно");

// Функция для получения или создания раздела
function getOrCreateSection($section_name, $parent_id = 0) {
    $section = CIBlockSection::GetList(
        [],
        ['IBLOCK_ID' => INFOBLOCK_ID, 'NAME' => $section_name, 'SECTION_ID' => $parent_id],
        false,
        ['ID'],
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
        "NAME" => $section_name,
        "ACTIVE" => "Y",
        "SORT" => 500
    ];
    
    $new_section_id = $bs->Add($arFields);
    
    if (!$new_section_id) {
        addLog("Ошибка создания раздела: " . $bs->LAST_ERROR);
        return false;
    }
    
    addLog("Создан новый раздел: $section_name (ID: $new_section_id)");
    return $new_section_id;
}

// Функция для загрузки изображения
function downloadImage($url, $element_id) {
    if (empty($url)) {
        return false;
    }
    
    // Проверяем, не скачивали ли уже это изображение
    $filename = basename(parse_url($url, PHP_URL_PATH));
    $local_path = $_SERVER['DOCUMENT_ROOT'] . '/upload/mouser/' . $filename;
    
    // Создаем директорию если не существует
    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/mouser/')) {
        mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/mouser/', 0777, true);
    }
    
    // Скачиваем изображение через прокси
    BitrixProxy::init($GLOBALS['proxies']);
    $image_data = BitrixProxy::makeRequest($url);
    
    if (isset($image_data['error'])) {
        addLog("Ошибка загрузки изображения: " . $image_data['error']);
        return false;
    }
    
    file_put_contents($local_path, $image_data);
    
    // Добавляем файл в медиабиблиотеку Битрикс
    $arFile = CFile::MakeFileArray($local_path);
    $arFile['MODULE_ID'] = 'iblock';
    
    return $arFile;
}

// Основная функция импорта
function importProducts($search_term) {
    global $mouser, $log_messages;
    
    $total_imported = 0;
    $starting_record = 0;
    
    addLog("Начинаем импорт для поискового запроса: $search_term");
    
    while ($total_imported < MAX_ITEMS) {
        try {
            addLog("Запрашиваем записи с $starting_record...");
            $result = $mouser->search($search_term, ITEMS_PER_PAGE, $starting_record);
            
            if (empty($result['SearchResults']['Parts'])) {
                addLog("Товары не найдены или достигнут лимит");
                break;
            }
            
            $parts = $result['SearchResults']['Parts'];
            $total_results = $result['SearchResults']['NumberOfResult'];
            
            addLog("Найдено товаров: $total_results, обрабатываем страницу...");
            
            foreach ($parts as $part) {
                // Проверяем, существует ли уже товар
                $existing = CIBlockElement::GetList(
                    [],
                    [
                        'IBLOCK_ID' => INFOBLOCK_ID,
                        '=PROPERTY_MOUSER_PART_NUMBER' => $part['MouserPartNumber']
                    ],
                    false,
                    false,
                    ['ID']
                )->Fetch();
                
                if ($existing) {
                    addLog("Товар {$part['MouserPartNumber']} уже существует, обновляем...");
                    $element_id = $existing['ID'];
                    $is_new = false;
                } else {
                    $is_new = true;
                }
                
                // Обрабатываем категорию
                $section_id = 0;
                if (!empty($part['Category'])) {
                    $section_id = getOrCreateSection($part['Category']);
                }
                
                // Загружаем изображение
                $image_array = false;
                if (!empty($part['ImagePath'])) {
                    $image_array = downloadImage($part['ImagePath'], $element_id ?? 0);
                }
                
                // Подготавливаем массив свойств
                $properties = [
                    'MOUSER_PART_NUMBER' => $part['MouserPartNumber'],
                    'MANUFACTURER_PART_NUMBER' => $part['ManufacturerPartNumber'],
                    'MANUFACTURER' => $part['Manufacturer'],
                    'QUANTITY' => $part['FactoryStock'] ?? 0,
                    'DATASHEET_URL' => $part['DataSheetUrl'] ?? '',
                    'PRODUCT_URL' => $part['ProductDetailUrl'] ?? '',
                    'AVAILABILITY' => $part['Availability'] ?? '',
                    'ROHS_STATUS' => $part['ROHSStatus'] ?? '',
                    'LEAD_TIME' => $part['LeadTime'] ?? '',
                    'MIN_ORDER_QTY' => $part['Min'] ?? 1,
                    'PACKAGING' => $part['Mult'] ?? '',
                    'DESCRIPTION' => $part['Description'] ?? '',
                ];
                
                // Обрабатываем цены
                if (!empty($part['PriceBreaks']) && is_array($part['PriceBreaks'])) {
                    $prices = [];
                    foreach ($part['PriceBreaks'] as $price_break) {
                        $prices[] = "{$price_break['Quantity']}+: {$price_break['Price']} {$price_break['Currency']}";
                    }
                    $properties['PRICE_BREAKS'] = implode("\n", $prices);
                    
                    // Основная цена (первая из списка)
                    $main_price = $part['PriceBreaks'][0]['Price'] ?? 0;
                    $properties['PRICE'] = (float)$main_price;
                }
                
                // Подготавливаем данные элемента
                $arFields = [
                    "IBLOCK_ID" => INFOBLOCK_ID,
                    "IBLOCK_SECTION_ID" => $section_id,
                    "NAME" => $part['ManufacturerPartNumber'] . ' - ' . ($part['Description'] ?? ''),
                    "CODE" => CUtil::translit($part['MouserPartNumber'], "ru"),
                    "ACTIVE" => "Y",
                    "PREVIEW_TEXT" => $part['Description'] ?? '',
                    "DETAIL_TEXT" => $part['Description'] ?? '',
                    "DETAIL_TEXT_TYPE" => "html",
                    "PREVIEW_TEXT_TYPE" => "html",
                    "PROPERTY_VALUES" => $properties
                ];
                
                if ($image_array) {
                    $arFields["PREVIEW_PICTURE"] = $image_array;
                    $arFields["DETAIL_PICTURE"] = $image_array;
                }
                
                if ($is_new) {
                    // Создаем новый элемент
                    $element = new CIBlockElement;
                    $element_id = $element->Add($arFields);
                    
                    if ($element_id) {
                        addLog("Создан новый товар: {$part['MouserPartNumber']} (ID: $element_id)");
                        $total_imported++;
                    } else {
                        addLog("Ошибка создания товара {$part['MouserPartNumber']}: " . $element->LAST_ERROR);
                    }
                } else {
                    // Обновляем существующий элемент
                    $element = new CIBlockElement;
                    if ($element->Update($element_id, $arFields)) {
                        addLog("Обновлен товар: {$part['MouserPartNumber']} (ID: $element_id)");
                        $total_imported++;
                    } else {
                        addLog("Ошибка обновления товара {$part['MouserPartNumber']}: " . $element->LAST_ERROR);
                    }
                }
                
                // Делаем небольшую паузу между обработкой товаров
                usleep(100000); // 0.1 секунда
            }
            
            $starting_record += ITEMS_PER_PAGE;
            
            // Проверяем, есть ли еще товары
            if ($starting_record >= $total_results) {
                addLog("Все товары для запроса '$search_term' обработаны");
                break;
            }
            
            // Пауза между запросами к API
            sleep(REQUEST_DELAY);
            
        } catch (Exception $e) {
            addLog("Ошибка при импорте: " . $e->getMessage());
            break;
        }
    }
    
    return $total_imported;
}

// Запускаем импорт
addLog("=== НАЧАЛО ИМПОРТА ===");
addLog("Дата и время: " . date('Y-m-d H:i:s'));

$total_all_imported = 0;

foreach ($search_terms as $term) {
    $imported = importProducts($term);
    $total_all_imported += $imported;
    addLog("Импортировано товаров для '$term': $imported");
    
    // Пауза между разными категориями
    sleep(2);
}

addLog("=== ИМПОРТ ЗАВЕРШЕН ===");
addLog("Всего импортировано товаров: $total_all_imported");

// Сохраняем лог в файл
file_put_contents($log_file, implode(PHP_EOL, $log_messages) . PHP_EOL, FILE_APPEND);

// Отправляем результат в админку Битрикс
if (defined('ADMIN_SECTION') && ADMIN_SECTION === true) {
    CAdminMessage::ShowMessage([
        "MESSAGE" => "Импорт завершен",
        "DETAILS" => "Импортировано товаров: $total_all_imported",
        "TYPE" => "OK",
    ]);
}
?>