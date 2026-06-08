<?php
// Скрипт создания свойства ETMCODE для хранения кода товара ETM
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = '/var/www/www-root/data/www/lvtgroup.ru';
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
\Bitrix\Main\Loader::includeModule('iblock');

header('Content-Type: text/plain; charset=utf-8');

$iblockId = 11; // ID инфоблока каталога

// Проверяем, существует ли уже такое свойство
$existing = CIBlockProperty::GetList(
    [],
    ['IBLOCK_ID' => $iblockId, 'CODE' => 'ETMCODE']
)->Fetch();

if ($existing) {
    echo "Свойство ETMCODE уже существует!\n";
    echo "ID: " . $existing['ID'] . "\n";
    echo "Название: " . $existing['NAME'] . "\n";
    exit;
}

// Создаём новое свойство
$property = new CIBlockProperty;
$arFields = [
    'IBLOCK_ID' => $iblockId,
    'NAME' => 'Код товара ETM',
    'ACTIVE' => 'Y',
    'SORT' => 600,
    'CODE' => 'ETMCODE',
    'PROPERTY_TYPE' => 'S', // Строка
    'ROW_COUNT' => 1,
    'COL_COUNT' => 30,
    'MULTIPLE' => 'N', // Не множественное
    'IS_REQUIRED' => 'N',
    'SEARCHABLE' => 'Y', // Участвует в поиске
    'FILTRABLE' => 'Y', // Участвует в фильтрации
    'WITH_DESCRIPTION' => 'N',
    'HINT' => 'Код товара для заказа через ETM iPRO API (SupplierItemCode)',
];

$propId = $property->Add($arFields);

if ($propId) {
    echo "=== Свойство успешно создано! ===\n\n";
    echo "ID свойства: $propId\n";
    echo "Код: ETMCODE\n";
    echo "Название: Код товара ETM\n\n";
    echo "Теперь обновите ID в файле OrderETMOrderHandler.php:\n";
    echo "private static \$etmCodePropertyId = $propId;\n";
} else {
    echo "ОШИБКА при создании свойства!\n";
    echo $property->LAST_ERROR . "\n";
}
