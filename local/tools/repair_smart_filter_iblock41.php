<?php

/**
 * One-time repair: sync b_iblock_section_property for catalog iblock 41.
 * Run: sudo -u www-root php /var/www/www-root/data/www/lvtgroup.ru/local/tools/repair_smart_filter_iblock41.php
 */

$_SERVER['DOCUMENT_ROOT'] = '/var/www/www-root/data/www/lvtgroup.ru';
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionPropertyTable;
use Bitrix\Iblock\PropertyIndex\Manager as PropertyIndexManager;

if (!CModule::IncludeModule('iblock')) {
    fwrite(STDERR, "iblock module not loaded\n");
    exit(1);
}

const TARGET_IBLOCK_ID = 41;
const SOURCE_IBLOCK_ID = 11;

function defaultDisplayType(string $propertyType): string
{
    return match ($propertyType) {
        PropertyTable::TYPE_NUMBER => SectionPropertyTable::NUMBERS_WITH_SLIDER,
        PropertyTable::TYPE_LIST => SectionPropertyTable::CHECKBOXES,
        default => SectionPropertyTable::CHECKBOXES,
    };
}

function loadSectionPropertyTemplateByCode(int $iblockId): array
{
    $template = [];
    $rows = SectionPropertyTable::getList([
        'filter' => ['IBLOCK_ID' => $iblockId, 'SECTION_ID' => 0, 'SMART_FILTER' => 'Y'],
        'select' => ['PROPERTY_ID', 'DISPLAY_TYPE', 'DISPLAY_EXPANDED', 'FILTER_HINT'],
    ]);

    while ($row = $rows->fetch()) {
        $property = CIBlockProperty::GetByID((int)$row['PROPERTY_ID'])->Fetch();
        if (!$property || empty($property['CODE'])) {
            continue;
        }
        $template[$property['CODE']] = [
            'DISPLAY_TYPE' => $row['DISPLAY_TYPE'] ?: defaultDisplayType((string)$property['PROPERTY_TYPE']),
            'DISPLAY_EXPANDED' => $row['DISPLAY_EXPANDED'] ?: 'N',
            'FILTER_HINT' => $row['FILTER_HINT'] ?: '',
        ];
    }

    return $template;
}

$existingCount = SectionPropertyTable::getList([
    'filter' => ['IBLOCK_ID' => TARGET_IBLOCK_ID, 'SMART_FILTER' => 'Y'],
    'count_total' => true,
])->getCount();

echo "Existing smart section properties for iblock " . TARGET_IBLOCK_ID . ": {$existingCount}\n";

$templateByCode = loadSectionPropertyTemplateByCode(SOURCE_IBLOCK_ID);
echo 'Template codes from iblock ' . SOURCE_IBLOCK_ID . ': ' . count($templateByCode) . "\n";

$added = 0;
$updated = 0;
$skipped = 0;

$propertyResult = CIBlockProperty::GetList(
    ['SORT' => 'ASC', 'ID' => 'ASC'],
    ['IBLOCK_ID' => TARGET_IBLOCK_ID, 'ACTIVE' => 'Y', 'SMART_FILTER' => 'Y']
);

while ($property = $propertyResult->Fetch()) {
    $propertyId = (int)$property['ID'];
    $exists = SectionPropertyTable::getList([
        'filter' => [
            'IBLOCK_ID' => TARGET_IBLOCK_ID,
            'SECTION_ID' => 0,
            'PROPERTY_ID' => $propertyId,
        ],
        'limit' => 1,
    ])->fetch();

    $code = (string)$property['CODE'];
    $config = $templateByCode[$code] ?? [
        'DISPLAY_TYPE' => defaultDisplayType((string)$property['PROPERTY_TYPE']),
        'DISPLAY_EXPANDED' => 'N',
        'FILTER_HINT' => '',
    ];

    if ($exists) {
        if (($exists['SMART_FILTER'] ?? 'N') === 'Y') {
            $skipped++;
            continue;
        }

        $result = SectionPropertyTable::update(
            [
                'IBLOCK_ID' => TARGET_IBLOCK_ID,
                'SECTION_ID' => 0,
                'PROPERTY_ID' => $propertyId,
            ],
            [
                'SMART_FILTER' => 'Y',
                'DISPLAY_TYPE' => $exists['DISPLAY_TYPE'] ?: $config['DISPLAY_TYPE'],
                'DISPLAY_EXPANDED' => $exists['DISPLAY_EXPANDED'] ?: $config['DISPLAY_EXPANDED'],
                'FILTER_HINT' => $exists['FILTER_HINT'] ?: $config['FILTER_HINT'],
            ]
        );

        if (!$result->isSuccess()) {
            echo 'Update failed for property #' . $propertyId . ' (' . $code . '): ' . implode('; ', $result->getErrorMessages()) . "\n";
            continue;
        }

        $updated++;
        continue;
    }

    $result = SectionPropertyTable::add([
        'IBLOCK_ID' => TARGET_IBLOCK_ID,
        'SECTION_ID' => 0,
        'PROPERTY_ID' => $propertyId,
        'SMART_FILTER' => 'Y',
        'DISPLAY_TYPE' => $config['DISPLAY_TYPE'],
        'DISPLAY_EXPANDED' => $config['DISPLAY_EXPANDED'],
        'FILTER_HINT' => $config['FILTER_HINT'],
    ]);

    if (!$result->isSuccess()) {
        echo 'Failed for property #' . $propertyId . ' (' . $code . '): ' . implode('; ', $result->getErrorMessages()) . "\n";
        continue;
    }

    $added++;
}

echo "Added: {$added}, updated: {$updated}, skipped already enabled: {$skipped}\n";

$finalCount = SectionPropertyTable::getList([
    'filter' => ['IBLOCK_ID' => TARGET_IBLOCK_ID, 'SMART_FILTER' => 'Y'],
    'count_total' => true,
])->getCount();
echo "Final smart section properties: {$finalCount}\n";

echo "Reindexing facet for iblock " . TARGET_IBLOCK_ID . "...\n";
$indexer = PropertyIndexManager::createIndexer(TARGET_IBLOCK_ID);
$indexer->startIndex();
$steps = 0;
while ($indexer->continueIndex(20)) {
    $steps++;
}
$indexer->endIndex();
echo "Facet reindex done, steps={$steps}, lastElement=" . $indexer->getLastElementId() . "\n";

echo "Done.\n";
