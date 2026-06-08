<?php
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = '/var/www/www-root/data/www/lvtgroup.ru';
}

$prologPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
if (!file_exists($prologPath)) {
    die("Файл prolog_before.php не найден по пути: $prologPath\n");
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use Bitrix\Iblock\IblockSectionTable;
use Bitrix\Catalog\PriceTable;

if (!CModule::IncludeModule('iblock')) {
    die('Не загружён модуль iblock');
}
$priceTypeId = 1;
$iblock_id = 11;
$logFile = __DIR__ . '/product_import.log';
$lastProcessedIdFile = __DIR__ . '/last_processed_item_id.txt';

function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function fetchApiDataWithChangedTime($changedTime) {
    global $login, $password_md5, $api_url;
    $request_data = [
        'login' => $login,
        'password' => $password_md5,
        'method' => 'items_data_get',
        'changed_time' => $changedTime
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
        logMessage("Ошибка API: HTTP $http_code, ответ пустой");
        return false;
    }
    $data = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        logMessage("Ошибка разбора JSON: " . json_last_error_msg());
        return false;
    }
    return $data;
}

function ensureEnumValueExists($propertyId, $value) {
    static $enumCache = [];
    $cacheKey = $propertyId . '|' . $value;
    if (isset($enumCache[$cacheKey])) {
        return $enumCache[$cacheKey];
    }

    $dbRes = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $propertyId, 'VALUE' => $value]);
    if ($enum = $dbRes->Fetch()) {
        $enumCache[$cacheKey] = $enum['ID'];
        return $enum['ID'];
    }

    $enumFields = [
        'PROPERTY_ID' => $propertyId,
        'VALUE'       => $value,
        'XML_ID'      => strtolower($value),
    ];
    $enumValueId = CIBlockPropertyEnum::Add($enumFields);
    if ($enumValueId) {
        $enumCache[$cacheKey] = $enumValueId;
        return $enumValueId;
    } else {
        global $APPLICATION;
        $errorMessage = $APPLICATION->GetException() ? $APPLICATION->GetException()->GetString() : 'Неизвестная ошибка';
        logMessage("❌ Ошибка добавления значения '{$value}' в список (PROPERTY_ID={$propertyId}): " . $errorMessage);
        return false;
    }
}

function getOrCreateSection($sectionName, $iblockId, $parentId = false) {
    $parentId = (int)$parentId;
    static $sectionCache = [];
    $cacheKey = $iblockId . '|' . $sectionName . '|' . $parentId;
    if (isset($sectionCache[$cacheKey])) {
        return $sectionCache[$cacheKey];
    }

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
        $sectionCache[$cacheKey] = $section['ID'];
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
        $errorMessage = $bs->LAST_ERROR ?: 'Неизвестная ошибка';
        logMessage('❌ Ошибка создания раздела "' . $sectionName . '" (IBLOCK_ID=' . $iblockId . ', PARENT=' . $parentId . '): ' . $errorMessage);
        return false;
    }

    $sectionCache[$cacheKey] = $newId;
    return $newId;
}

function processProductsBatch($products) {
    global $iblock_id, $priceTypeId;
    $processedCount = 0;

    if (empty($products)) {
        logMessage("Нет товаров для обработки в этом запросе.");
        return;
    }

    foreach ($products as $product) {
        if (!isset($product['item_id'], $product['name'])) {
            logMessage("Пропущен товар без item_id или name: " . print_r($product, true));
            continue;
        }

        $item_id       = $product['item_id'];
        $raw_name      = $product['name'];
        $description   = !empty($product['description']) ? $product['description'] : '';
        $quant         = (int)($product['quant'] ?? 0);
        $photo_url     = !empty($product['photo_url']) ? $product['photo_url'] : null;
        $name = $raw_name;
        if (!empty($description)) {
            $name .= ' - ' . $description;
        }

        $existingElement = CIBlockElement::GetList(
            [],
            ['XML_ID' => 'API_' . $item_id, 'IBLOCK_ID' => $iblock_id],
            false,
            ['nPageSize' => 1],
            ['ID', 'NAME', 'PROPERTY_*']
        )->Fetch();

        $elementId = false;
        $isUpdate = false;

        if ($existingElement) {
            $elementId = $existingElement['ID'];
            $isUpdate = true;
            logMessage("Найден существующий товар для обновления: {$raw_name} (ID: API_$item_id, BX ID: $elementId)");
        } else {
            logMessage("Новый товар для создания: {$raw_name} (ID: API_$item_id)");
        }

        $el = new CIBlockElement();
        $code = CUtil::translit($name, 'ru', [
            'max_len' => 100,
            'change_case' => 'L',
            'replace_space' => '_',
            'replace_other' => '_',
            'delete_repeat_delimiter' => true
        ]);

        try {
            $category_0 = $product['class0name'] ?? 'Без категории';
            $category_1 = $product['class1name'] ?? 'Без подкатегории';
            $category_2 = $product['class2name'] ?? 'Без подподкатегории';
            $sec0_id = getOrCreateSection($category_0, $iblock_id);
            $sec1_id = getOrCreateSection($category_1, $iblock_id, $sec0_id);
            $final_section_id = getOrCreateSection($category_2, $iblock_id, $sec1_id);
            if (!$sec0_id || !$sec1_id || !$final_section_id) {
                logMessage("❌ Не удалось получить/создать разделы для товара {$raw_name}, пропуск.");
                continue;
            }
        } catch (Exception $e) {
            logMessage("❌ Ошибка при работе с разделами для товара {$raw_name}: " . $e->getMessage());
            continue;
        }

        $propertyId = 494;
        $remarkValue = $product['remark'] ?? '';
        $enumValueId = false;
        if (!empty($remarkValue)) {
            $enumValueId = ensureEnumValueExists($propertyId, $remarkValue);
        }
        $yearOfIssueId = !empty($product['year_of_issue']) ? ensureEnumValueExists(495, $product['year_of_issue']) : false;
        $packageId     = !empty($product['package'])     ? ensureEnumValueExists(496, $product['package'])     : false;
        $packQuantId   = !empty($product['pack_quant'])   ? ensureEnumValueExists(497, $product['pack_quant']) : false;
        $packagingId   = !empty($product['packaging'])   ? ensureEnumValueExists(498, $product['packaging']) : false;
        $weightId      = !empty($product['weight'])      ? ensureEnumValueExists(499, $product['weight'])     : false;

        $datasheetUrl = $product['datasheet'] ?? null;
        $datasheetFileId = false;
        if (!empty($datasheetUrl)) {
            $tempDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/';
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $originalFileName = basename(parse_url($datasheetUrl, PHP_URL_PATH));
            $tempFile = $tempDir . $originalFileName;
            $fileContent = file_get_contents($datasheetUrl);
            if ($fileContent !== false && file_put_contents($tempFile, $fileContent)) {
                $fileArray = CFile::MakeFileArray($tempFile);
                if ($fileArray) {
                    $fileArray['name'] = $originalFileName;
                    $datasheetFileId = CFile::SaveFile($fileArray, 'datasheet');
                    if ($datasheetFileId) {
                        logMessage("✅ Файл datasheet успешно загружен. ID: $datasheetFileId");
                    } else {
                        logMessage("❌ Ошибка загрузки файла datasheet: " . $originalFileName);
                    }
                } else {
                    logMessage("❌ Ошибка подготовки файла datasheet: " . $originalFileName);
                }
            } else {
                logMessage("❌ Ошибка скачивания файла datasheet: " . $datasheetUrl);
            }
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }


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
                501 => $item_id,
                627 => $raw_name,
            ],
        ];

        if ($enumValueId !== false && $enumValueId !== null) {
            $arFields['PROPERTY_VALUES'][494] = ['VALUE' => $enumValueId];
        }
        if ($yearOfIssueId !== false && $yearOfIssueId !== null) {
            $arFields['PROPERTY_VALUES'][495] = ['VALUE' => $yearOfIssueId];
        }
        if ($packageId !== false && $packageId !== null) {
            $arFields['PROPERTY_VALUES'][496] = ['VALUE' => $packageId];
        }
        if ($packQuantId !== false && $packQuantId !== null) {
            $arFields['PROPERTY_VALUES'][497] = ['VALUE' => $packQuantId];
        }
        if ($packagingId !== false && $packagingId !== null) {
            $arFields['PROPERTY_VALUES'][498] = ['VALUE' => $packagingId];
        }
        if ($weightId !== false && $weightId !== null) {
            $arFields['PROPERTY_VALUES'][499] = ['VALUE' => $weightId];
        }
        if ($datasheetFileId !== false && $datasheetFileId !== null) {
            $arFields['PROPERTY_VALUES'][500] = ['VALUE' => $datasheetFileId];
        }

        if ($photo_url) {
            $temp_file = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/' . basename($photo_url);
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp')) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp', 0755, true);
            }
            $imageContent = file_get_contents($photo_url);
            if ($imageContent !== false && file_put_contents($temp_file, $imageContent)) {
                $arFields['DETAIL_PICTURE'] = CFile::MakeFileArray($temp_file);
            } else {
                 logMessage("❌ Ошибка скачивания изображения: " . $photo_url);
            }
        }

        if ($isUpdate) {
            if ($el->Update($elementId, $arFields)) {
                logMessage("🔄 Товар успешно обновлён: {$raw_name} (BX ID: $elementId)");
            } else {
                logMessage("<b>❌ Ошибка обновления товара (BX ID: $elementId):</b> " . $el->LAST_ERROR);
                continue;
            }
        } else {
            $newElementId = $el->Add($arFields);
            if (!$newElementId) {
                logMessage("<b>❌ Ошибка добавления товара:</b> " . $el->LAST_ERROR);
                continue;
            } else {
                $elementId = $newElementId;
                logMessage("🎉 Товар успешно создан: {$raw_name} (BX ID: $newElementId)");
            }
        }

        $productFields = [
            'ID' => $elementId,
            'CATALOG_EXTRA' => 'Y',
        ];
        $catalogProduct = new \CCatalogProduct();
        if (!$catalogProduct->Update($elementId, $productFields)) {
            logMessage("❌ Ошибка при обновлении товара в каталоге (BX ID: $elementId): " . $catalogProduct->LAST_ERROR);
            continue;
        }

        $res_old_prices = CPrice::GetList(
            array(),
            array("PRODUCT_ID" => $elementId, "CATALOG_GROUP_ID" => $priceTypeId)
        );
        while ($ar_old_price = $res_old_prices->Fetch()) {
            CPrice::Delete($ar_old_price["ID"]);
        }

        if (!empty($product['pricebreaks'])) {
            $priceBreaks = $product['pricebreaks'];
            foreach ($priceBreaks as $key => $priceBreak) {
                $quantityFrom = (int)$priceBreak['quant'];
                $quantityTo = isset($priceBreaks[$key + 1]) ? (int)$priceBreaks[$key + 1]['quant'] - 1 : false;
                $priceValue = (float)$priceBreak['price'];

                $priceFields = [
                    'PRODUCT_ID' => $elementId,
                    'CATALOG_GROUP_ID' => $priceTypeId,
                    'PRICE' => $priceValue,
                    'CURRENCY' => 'RUB',
                    'QUANTITY_FROM' => $quantityFrom,
                    'QUANTITY_TO' => $quantityTo,
                ];
                $priceId = CPrice::Add($priceFields);
                if (!$priceId) {
                    global $APPLICATION;
                    logMessage("❌ Ошибка при добавлении цены (BX ID: $elementId): " . $APPLICATION->GetException()->GetString());
                }
            }
        } else {
            logMessage("⚠️ У товара {$raw_name} (BX ID: $elementId) отсутствуют pricebreaks, цены не установлены.");
        }


        $totalQuantityFromVendors = 0;
        if (!empty($product['vendors']) && is_array($product['vendors'])) {
            foreach ($product['vendors'] as $vendor) {
                if (isset($vendor['vendor'], $vendor['quant']) && $vendor['vendor'] !== 0) {
                    $totalQuantityFromVendors += (int)$vendor['quant'];
                }
            }
        }

        $storeFields2 = [
            'PRODUCT_ID' => $elementId,
            'STORE_ID'   => 2,
            'AMOUNT'     => $totalQuantityFromVendors,
        ];

        $storeRes = \CCatalogStoreProduct::GetList(
            array(),
            array("PRODUCT_ID" => $elementId, "STORE_ID" => 2),
            false,
            false,
            array("ID")
        );
        $existingStoreRecord = $storeRes->Fetch();

        if ($existingStoreRecord) {
            $updateResult = \CCatalogStoreProduct::Update($existingStoreRecord['ID'], $storeFields2);
            if (!$updateResult) {
                global $APPLICATION;
                $errorMessages = $APPLICATION->GetException()->GetString();
                logMessage("❌ Ошибка при обновлении остатков на складе (ID=2, BX ID: $elementId): $errorMessages");
            } else {
                 logMessage("📦 Обновлены остатки на складе 2 (BX ID: $elementId): $totalQuantityFromVendors");
            }
        } else {
            $storeResult = \CCatalogStoreProduct::Add($storeFields2);
            if (!$storeResult) {
                global $APPLICATION;
                $errorMessages = $APPLICATION->GetException()->GetString();
                logMessage("❌ Ошибка при добавлении остатков на складе (ID=2, BX ID: $elementId): $errorMessages");
            } else {
                 logMessage("📦 Добавлены остатки на складе 2 (BX ID: $elementId): $totalQuantityFromVendors");
            }
        }


        $arCatalogFields = [
            "ID" => $elementId,
            "QUANTITY" => $quant,
            "QUANTITY_TRACE" => "Y",
            "CAN_BUY_ZERO" => "N",
        ];

        $catalogRes = \CCatalogProduct::GetList(
            array(),
            array("ID" => $elementId),
            false,
            false,
            array("ID")
        );
        $existingCatalogRecord = $catalogRes->Fetch();

        if ($existingCatalogRecord) {
            $res = \CCatalogProduct::Update($elementId, $arCatalogFields);
            if (!$res) {
                global $APPLICATION;
                logMessage("❌ Ошибка при обновлении количества товара в каталоге (BX ID: $elementId): " . implode(", ", $APPLICATION->GetException()->GetMessages()));
            } else {
                 logMessage("📦 Обновлено общее количество (BX ID: $elementId): $quant");
            }
        } else {
            $res = \CCatalogProduct::Add($arCatalogFields);
            if (!$res) {
                global $APPLICATION;
                logMessage("❌ Ошибка при добавлении количества товара в каталоге (BX ID: $elementId): " . implode(", ", $APPLICATION->GetException()->GetMessages()));
            } else {
                 logMessage("📦 Добавлено общее количество (BX ID: $elementId): $quant");
            }
        }


        $storeFields1 = [
            'PRODUCT_ID' => $elementId,
            'STORE_ID' => 1,
            'AMOUNT' => $quant,
        ];

        $storeRes1 = \CCatalogStoreProduct::GetList(
            array(),
            array("PRODUCT_ID" => $elementId, "STORE_ID" => 1),
            false,
            false,
            array("ID")
        );
        $existingStoreRecord1 = $storeRes1->Fetch();

        if ($existingStoreRecord1) {
            $updateResult = \CCatalogStoreProduct::Update($existingStoreRecord1['ID'], $storeFields1);
            if (!$updateResult) {
                global $APPLICATION;
                $errorMessages = $APPLICATION->GetException()->GetString();
                logMessage("❌ Ошибка при обновлении остатков на складе (ID=1, BX ID: $elementId): $errorMessages");
            } else {
                 logMessage("📦 Обновлены остатки на складе 1 (BX ID: $elementId): $quant");
            }
        } else {
            $storeResult = \CCatalogStoreProduct::Add($storeFields1);
            if (!$storeResult) {
                global $APPLICATION;
                $errorMessages = $APPLICATION->GetException()->GetString();
                logMessage("❌ Ошибка при добавлении остатков на складе (ID=1, BX ID: $elementId): $errorMessages");
            } else {
                 logMessage("📦 Добавлены остатки на складе 1 (BX ID: $elementId): $quant");
            }
        }

        $processedCount++;
    }

    logMessage("Обработано товаров в этом запросе: $processedCount");
}

logMessage("--- НАЧАЛО ИМПОРТА (по изменению) ---");

$login = 'lvtgroup2';
$password = '30316';
$api_url = 'https://aaa.na4u.ru/rpc/';
$password_md5 = strtoupper(md5($password));


$sevenDaysAgoStart = new DateTime('-1 days');
$sevenDaysAgoStart->setTime(0, 0, 0);
$changedTime = $sevenDaysAgoStart->format('d.m.Y H:i');

logMessage("Запрашиваем товары, изменённые с: $changedTime");

$data = fetchApiDataWithChangedTime($changedTime);

if ($data === false) {
    logMessage("Ошибка при получении данных из API");
} elseif (empty($data)) {
    logMessage("API вернул пустой список товаров для указанного времени изменения.");
} else {
    logMessage("Получено " . count($data) . " изменённых товаров.");
    processProductsBatch($data);
}

if (file_exists($lastProcessedIdFile)) {
    unlink($lastProcessedIdFile);
    logMessage("Файл $lastProcessedIdFile удален.");
}

logMessage("--- ОКОНЧАНИЕ ИМПОРТА (по изменению) ---");

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');