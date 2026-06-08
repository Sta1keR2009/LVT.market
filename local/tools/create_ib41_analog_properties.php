<?php
/**
 * Создание свойств «Полные аналоги» и «Функциональные аналоги» в инфоблоке 41 (ETM).
 * Запуск: sudo -u www-root php local/tools/create_ib41_analog_properties.php
 */
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!CModule::IncludeModule('iblock')) {
    fwrite(STDERR, "iblock module not loaded\n");
    exit(1);
}

const IBLOCK_ID = 41;

$properties = [
    [
        'CODE' => 'FULL_ANALOG_CODES',
        'NAME' => 'Полные аналоги',
        'PROPERTY_TYPE' => 'S',
        'MULTIPLE' => 'N',
    ],
    [
        'CODE' => 'INTERCHANGEABLE_ANALOG_CODES',
        'NAME' => 'Функциональные аналоги',
        'PROPERTY_TYPE' => 'S',
        'MULTIPLE' => 'N',
    ],
];

foreach ($properties as $def) {
    $exists = CIBlockProperty::GetList([], ['IBLOCK_ID' => IBLOCK_ID, 'CODE' => $def['CODE']])->Fetch();
    if ($exists) {
        echo "Property {$def['CODE']} already exists (ID={$exists['ID']})\n";
        continue;
    }

    $prop = new CIBlockProperty();
    $id = $prop->Add([
        'IBLOCK_ID' => IBLOCK_ID,
        'NAME' => $def['NAME'],
        'ACTIVE' => 'Y',
        'CODE' => $def['CODE'],
        'PROPERTY_TYPE' => $def['PROPERTY_TYPE'],
        'MULTIPLE' => $def['MULTIPLE'],
        'SORT' => 500,
    ]);

    if (!$id) {
        fwrite(STDERR, "Failed to create {$def['CODE']}: " . $prop->LAST_ERROR . "\n");
        exit(1);
    }

    echo "Created property {$def['CODE']} (ID=$id)\n";
}

CIBlock::clearIblockTagCache(IBLOCK_ID);
echo "Done.\n";
