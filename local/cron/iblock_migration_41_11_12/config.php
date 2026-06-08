<?php

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("CLI only\n");
}

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = '/var/www/www-root/data/www/lvtgroup.ru';
}

$prologPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
if (!file_exists($prologPath)) {
    die("prolog_before.php not found: {$prologPath}\n");
}

require_once $prologPath;

if (!CModule::IncludeModule('iblock')) {
    die("Module iblock is not available\n");
}
if (!CModule::IncludeModule('catalog')) {
    die("Module catalog is not available\n");
}

return [
    'iblocks' => [
        'products' => 11,
        'offers' => 12,
        'source' => 41,
    ],
    // Укажите точный код свойства в IBLOCK 11 для поиска дублей.
    'manufacturer_code_property_11' => 'MANUFACTURER_CODE',
    // Укажите код свойства в IBLOCK 41, где хранится код производителя.
    'manufacturer_code_property_41' => 'MANUFACTURER_CODE',
    // Можно указать либо ID, либо CODE целевого раздела "Электротехника".
    'target_section' => [
        'id' => 0,
        'code' => 'elektrotekhnika',
        'name_fallback' => 'Электротехника',
    ],
    'sku' => [
        // Если 0, определяется автоматически через CCatalogSKU::GetInfoByOfferIBlock(12).
        'link_property_id' => 0,
    ],
    'price' => [
        'catalog_group_id' => 1,
        'currency' => 'RUB',
        // Поля/свойства источника (IBLOCK 41), откуда брать цену/остаток.
        'source_price_field' => 'CATALOG_PRICE_1',
        'source_quantity_property' => '',
    ],
    'paths' => [
        'dir' => __DIR__,
        'export_dir' => __DIR__ . '/export',
        'log_dir' => __DIR__ . '/logs',
        'map_41_to_11' => __DIR__ . '/mapping_41_to_11.php',
        'map_41_to_12' => __DIR__ . '/mapping_41_to_12.php',
    ],
];
