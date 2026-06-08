<?php
// Подключаем ядро Битрикс
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use Bitrix\Iblock\IblockSectionTable;
use Bitrix\Catalog\PriceTable;

if (!CModule::IncludeModule('iblock')) {
    die('Не загружён модуль iblock');
}

$priceTypeId = 1; // Например, тип цены "Базовая"

// === 1. Запрос к API за товарами ===
$login = 'lvtgroup2';
$password = '30316';
$api_url = 'https://aaa.na4u.ru/rpc_test/';
$password_md5 = strtoupper(md5($password));

$request_data = [
    'login' => $login,
    'password' => $password_md5,
    'method' => 'items_data_get'
];

$request_json = json_encode($request_data, JSON_UNESCAPED_UNICODE);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $request_json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    die("Ошибка API: HTTP $http_code, ответ пустой");
}

$data = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data) || empty($data)) {
    die("Ошибка разбора JSON: " . json_last_error_msg());
}

// Ограничиваем обработку первыми 5 товарами
$data = array_slice($data, 0, 5);

$iblock_id = 11; // ID инфоблока товаров
function ensureEnumValueExists($propertyId, $value) {
    $dbRes = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $propertyId, 'VALUE' => $value]);
    if ($enum = $dbRes->Fetch()) {
        echo "✅ Значение '{$value}' уже есть в списке. ID: {$enum['ID']}\n";
        return $enum['ID'];
    }

    $enumFields = [
        'PROPERTY_ID' => $propertyId,
        'VALUE'       => $value,
        'XML_ID'      => strtolower($value),
    ];
    $enumValueId = CIBlockPropertyEnum::Add($enumFields);
    if ($enumValueId) {
        echo "🆕 Добавлено новое значение '{$value}' в список. ID: {$enumValueId}\n";
        return $enumValueId;
    } else {
        echo "❌ Ошибка добавления значения '{$value}' в список.\n";
        return false;
    }
}
// Функция: найти или создать раздел с учётом родителя
function getOrCreateSection($sectionName, $iblockId, $parentId = false) {
    $parentId = (int)$parentId;

    $filter = [
        'IBLOCK_ID'         => $iblockId,
        'NAME'              => $sectionName,
        'GLOBAL_ACTIVE'     => 'Y',
        'CHECK_PERMISSIONS' => 'N',
    ];

    if ($parentId > 0) {
        $filter['IBLOCK_SECTION_ID'] = $parentId;
    } else {
        $filter['DEPTH_LEVEL'] = 1;
    }

    $dbRes = CIBlockSection::GetList(
        ['ID' => 'ASC'],
        $filter,
        false,
        ['ID', 'IBLOCK_SECTION_ID']
    );

    if ($section = $dbRes->Fetch()) {
        $parentInfo = $section['IBLOCK_SECTION_ID'] ?: 'root';
        echo "✅ Найден раздел: '{$sectionName}' (ID={$section['ID']}, PARENT={$parentInfo})\n";
        return $section['ID'];
    }

    $bs = new CIBlockSection();
    $fields = [
        'IBLOCK_ID'          => $iblockId,
        'NAME'               => $sectionName,
        'ACTIVE'             => 'Y',
        'CODE'               => CUtil::translit($sectionName, 'ru', [
            'max_len' => 100,
            'replace_space' => '_',
            'replace_other' => '_',
            'delete_repeat_replace' => true,
        ]),
        'DESCRIPTION'        => "Раздел импортирован из API",
        'DESCRIPTION_TYPE'   => 'text',
    ];

    if ($parentId > 0) {
        $fields['IBLOCK_SECTION_ID'] = $parentId;
    }

    $newId = $bs->Add($fields);

    if (!$newId) {
        throw new Exception('Ошибка создания раздела "' . $sectionName . '": ' . $bs->LAST_ERROR);
    }

    $parentInfo = $parentId > 0 ? $parentId : 'root';
    echo "🆕 Создан раздел: '{$sectionName}' (ID={$newId}, PARENT={$parentInfo})\n";

    return $newId;
}

// Обработка первых 5 товаров
foreach ($data as $product) {
    echo "<pre>";
    echo "🔧 Обработка товара:
";

    // Извлекаем данные товара
    $item_id       = $product['item_id'];
    $raw_name      = $product['name'];
    $description   = !empty($product['description']) ? $product['description'] : '';
    $quant         = (int)$product['quant'];
    $photo_url     = !empty($product['photo_url']) ? $product['photo_url'] : null;

    // Формируем название товара
    $name = $raw_name;
    if (!empty($description)) {
        $name .= ' - ' . $description;
    }

    echo " - Артикул API: $item_id
";
    echo " - Название: $name
";
    echo " - Описание: $description
";
    echo " - Количество: $quant шт.
";

    // === 1. Проверяем, существует ли товар по XML_ID ===
    $existing = CIBlockElement::GetList(
        [],
        ['XML_ID' => 'API_' . $item_id, 'IBLOCK_ID' => $iblock_id],
        false,
        ['nPageSize' => 1],
        ['ID']
    );
    if ($existing->Fetch()) {
        echo "
🔄 Товар с XML_ID=API_$item_id уже существует. Пропускаем создание.
";
        continue; // Переходим к следующему товару
    }

    // === 2. Создаём цепочку разделов по иерархии ===
    try {
        $category_0 = $product['class0name'];  // Например: "Микросхемы"
        $category_1 = $product['class1name'];  // Например: "Усилители и компараторы"
        $category_2 = $product['class2name'];  // Например: "Операционные усилители"

        echo " - Категории: $category_0 → $category_1 → $category_2
";

        $sec0_id = getOrCreateSection($category_0, $iblock_id);                    // Уровень 0
        $sec1_id = getOrCreateSection($category_1, $iblock_id, $sec0_id);          // Уровень 1
        $final_section_id = getOrCreateSection($category_2, $iblock_id, $sec1_id); // Уровень 2

        echo "📁 Все категории обработаны. Товар будет в разделе ID=$final_section_id
";
    } catch (Exception $e) {
        die("<b>❌ Ошибка при работе с разделами:</b> " . $e->getMessage());
    }

    // === 3. Создание элемента (товара) ===
    $el = new CIBlockElement();

    // Генерация символьного кода
    $code = CUtil::translit($name, 'ru', [
        'max_len' => 100,
        'change_case' => 'L',
        'replace_space' => '_',
        'replace_other' => '_',
        'delete_repeat_delimiter' => true
    ]);

    // Проверим, нет ли уже такого товара по XML_ID
    $existing = CIBlockElement::GetList(
        [],
        ['XML_ID' => 'API_' . $item_id, 'IBLOCK_ID' => $iblock_id],
        false,
        ['nPageSize' => 1],
        ['ID']
    );

    if ($existing->Fetch()) {
        echo "\n🔄 Товар с XML_ID=API_$item_id уже существует. Пропускаем создание.\n";
        continue;
    }

    // ID свойства "Примечание"
    $propertyId = 494;
    $remarkValue = $product['remark'] ?? ''; // Значение из API
    $enumValueId = false;

    if (!empty($remarkValue)) {
        // Проверяем, существует ли значение в списке
        $dbRes = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $propertyId, 'VALUE' => $remarkValue]);
        if ($enum = $dbRes->Fetch()) {
            $enumValueId = $enum['ID']; // Значение уже существует
            echo "✅ Значение '{$remarkValue}' уже есть в списке. ID: {$enumValueId}\n";
        } else {
            // Добавляем новое значение в список
            $enumFields = [
                'PROPERTY_ID' => $propertyId,
                'VALUE'       => $remarkValue,
                'XML_ID'      => strtolower($remarkValue), // XML_ID может быть полезен для уникальности
            ];
            $enumValueId = CIBlockPropertyEnum::Add($enumFields);
            if ($enumValueId) {
                echo "🆕 Добавлено новое значение '{$remarkValue}' в список. ID: {$enumValueId}\n";
            } else {
                die("❌ Ошибка добавления значения '{$remarkValue}' в список.");
            }
        }
    }

    // Свойства типа "список"
    $yearOfIssueId = !empty($product['year_of_issue']) ? ensureEnumValueExists(495, $product['year_of_issue']) : false;
    $packageId     = !empty($product['package'])     ? ensureEnumValueExists(496, $product['package'])     : false;
    $packQuantId   = !empty($product['pack_quant'])   ? ensureEnumValueExists(497, $product['pack_quant']) : false;
    $packagingId   = !empty($product['packaging'])   ? ensureEnumValueExists(498, $product['packaging']) : false;
    $weightId      = !empty($product['weight'])      ? ensureEnumValueExists(499, $product['weight'])     : false;
$remarkValue = $product['remark'] ?? '';
$remarkId = !empty($remarkValue) ? ensureEnumValueExists(494, $remarkValue) : false;
// Обработка datasheet
$datasheetUrl = $product['datasheet'] ?? null; // Ссылка на datasheet
$datasheetFileId = false; // ID загруженного файла

if (!empty($datasheetUrl)) {
    // Создаем временную директорию, если её нет
    $tempDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/';
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    // Извлекаем оригинальное имя файла
    $originalFileName = basename($datasheetUrl);

    // Скачиваем файл
    $tempFile = $tempDir . $originalFileName;
    if (file_put_contents($tempFile, file_get_contents($datasheetUrl))) {
        // Преобразуем файл в массив для загрузки
        $fileArray = CFile::MakeFileArray($tempFile);

        // Устанавливаем оригинальное имя файла
        $fileArray['name'] = iconv('UTF-8', 'Windows-1251//IGNORE', $originalFileName); // ← Преобразуем кодировку

        // Загружаем файл в свойство
        if ($fileArray) {
            $datasheetFileId = CFile::SaveFile($fileArray, 'datasheet'); // Сохраняем файл в Битрикс
            echo "✅ Файл datasheet успешно загружен. ID: $datasheetFileId\n";
        } else {
            echo "❌ Ошибка при подготовке файла datasheet.\n";
        }
    } else {
        echo "❌ Ошибка при скачивании файла datasheet.\n";
    }

    // Удаляем временный файл после загрузки
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
}

// Подготовка полей товара
$arFields = [
    'IBLOCK_ID'      => $iblock_id,
    'NAME'           => $name,
    'CODE'           => CUtil::translit($raw_name, 'ru', [
        'max_len' => 100,
        'change_case' => 'L',
        'replace_space' => '_',
        'replace_other' => '_',
        'delete_repeat_delimiter' => true
    ]),
    'XML_ID'         => 'API_' . $item_id,
    'ACTIVE'         => 'Y',
    'PREVIEW_TEXT'   => $description,
    'DETAIL_TEXT'    => $description,
    'IBLOCK_SECTION' => $final_section_id,
    'PROPERTY_VALUES' => [
        494 => $remarkId,       // Примечание
        495 => $yearOfIssueId,  // Год выпуска
        496 => $packageId,      // Упаковка
        497 => $packQuantId,    // Количество в упаковке
        498 => $packagingId,    // Тип упаковки
        499 => $weightId,       // Вес
        500 => $datasheetFileId, // Datasheet (ID свойства 500)
    ],
];

    // Загрузка фото (если есть)
    if ($photo_url) {
        $temp_file = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/' . basename($photo_url);
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp')) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp', 0755, true);
        }
        file_put_contents($temp_file, file_get_contents($photo_url));
        if (file_exists($temp_file)) {
            $arFields['DETAIL_PICTURE'] = CFile::MakeFileArray($temp_file);
        }
    }

    // === Добавление элемента ===
    $newElementId = $el->Add($arFields);

    if (!$newElementId) {
        echo "<b>❌ Ошибка добавления товара:</b> " . $el->LAST_ERROR . "\n";
        continue;
    }

    // Включаем расширенный режим управления ценами
    $productFields = [
        'ID' => $newElementId,
        'CATALOG_EXTRA' => 'Y',
    ];

    $catalogProduct = new \CCatalogProduct();
    if (!$catalogProduct->Update($newElementId, $productFields)) {
        echo "❌ Ошибка при включении расширенного режима управления ценами: " . $catalogProduct->LAST_ERROR . "\n";
        continue;
    }
    echo "✅ Расширенный режим управления ценами включен для товара.\n";

    // Добавляем цены из price breaks
if (!empty($product['pricebreaks'])) {
    $priceBreaks = $product['pricebreaks']; // Берем pricebreaks из корня товара
    foreach ($priceBreaks as $key => $priceBreak) {
        $quantityFrom = $priceBreak['quant'];
        $quantityTo = isset($priceBreaks[$key + 1]) ? $priceBreaks[$key + 1]['quant'] - 1 : false;
        $priceValue = $priceBreak['price'];
        $priceFields = [
            'PRODUCT_ID' => $newElementId,
            'CATALOG_GROUP_ID' => $priceTypeId,
            'PRICE' => $priceValue,
            'CURRENCY' => 'RUB',
            'QUANTITY_FROM' => $quantityFrom,
            'QUANTITY_TO' => $quantityTo,
        ];
        $priceId = CPrice::Add($priceFields);
        if ($priceId) {
            echo "✅ Добавлена цена: {$priceValue} RUB для количества {$quantityFrom}" . ($quantityTo ? "–{$quantityTo}" : "+") . "
";
        } else {
            echo "❌ Ошибка при добавлении цены: " . $APPLICATION->GetException()->GetString() . "
";
        }
    }
} else {
    echo "⚠️ Данные о pricebreaks не найдены.
";
}
	
// Суммирование остатков для всех поставщиков (кроме vendor 0)
$totalQuantity = 0; // Общее количество товара

foreach ($product['vendors'] as $vendor) {
    if ($vendor['vendor'] !== 0) { // Пропускаем vendor 0
        $totalQuantity += (int)$vendor['quant']; // Суммируем остатки
    }
}

// Записываем суммарное количество на склад с ID 2
if ($newElementId && $totalQuantity > 0) {
    $storeFields = [
        'PRODUCT_ID' => $newElementId,
        'STORE_ID'   => 2, // ID склада для суммарных остатков
        'AMOUNT'     => $totalQuantity,
    ];

    $storeResult = \CCatalogStoreProduct::Add($storeFields);

    if ($storeResult) {
        echo "✅ Остатки на складе (ID=2) успешно установлены: $totalQuantity
";
    } else {
        // Если произошла ошибка, получаем текст ошибки через глобальную переменную $APPLICATION
        global $APPLICATION;
        $errorMessages = $APPLICATION->GetException()->GetString();
        echo "❌ Ошибка при установке остатков на складе (ID=2): $errorMessages
";
    }
}

if ($newElementId) {
    // Устанавливаем общее количество товара
    $arCatalogFields = [
        "ID" => $newElementId,
        "QUANTITY" => $quant, // используем значение из API
        "QUANTITY_TRACE" => "Y", // отслеживать остатки
        "CAN_BUY_ZERO" => "N", // нельзя купить при нулевом остатке
    ];
    
    $res = \CCatalogProduct::Add($arCatalogFields);
    
    if (!$res) {
        echo "❌ Ошибка при добавлении количества товара: " . implode(", ", $APPLICATION->GetException()->GetMessages()) . "\n";
    } else {
        echo "✅ Количество товара установлено: $quant\n";
    }
    
    // Добавляем остатки на конкретный склад
$storeFields = [
    'PRODUCT_ID' => $newElementId,
    'STORE_ID' => 1, // ID склада
    'AMOUNT' => $quant,
];

$storeResult = \CCatalogStoreProduct::Add($storeFields);

if ($storeResult) {
    echo "✅ Остатки на складе (ID=1) успешно установлены: $quant
";
} else {
    // Если произошла ошибка, получаем текст ошибки через глобальную переменную $APPLICATION
    global $APPLICATION;
    $errorMessages = $APPLICATION->GetException()->GetString();
    echo "❌ Ошибка при установке остатков на складе (ID=1): $errorMessages
";
}
}
    echo "\n🎉 Товар успешно создан!\n";
    echo "📌 ID: $newElementId\n";
    echo "📌 Название: $name\n";
    echo "📌 Раздел: $final_section_id ($category_2)\n";
    echo "📌 Ссылка: <a href=\"/bitrix/admin/iblock_element_edit.php?type=catalog&IBLOCK_ID=$iblock_id&ID=$newElementId\" target=\"_blank\">Открыть в админке</a>\n";
    echo "</pre>";
}

// Подключаем футер (не обязательно, но хорошо для стилей)
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');