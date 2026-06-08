<?php

$cfg = require __DIR__ . '/config.php';
require_once __DIR__ . '/common.php';

$args = migCliArgs($argv ?? []);
$dryRun = ($args['dry-run'] ?? '1') !== '0';
$sourceIblockId = (int)$cfg['iblocks']['source'];

migEnsureDir($cfg['paths']['log_dir']);
$logFile = $cfg['paths']['log_dir'] . '/step1_clean_empty_properties_41_' . date('Ymd_His') . '.log';

migLog($logFile, "Start. IBLOCK_ID={$sourceIblockId}, dry-run=" . ($dryRun ? 'Y' : 'N'));

$deleted = 0;
$kept = 0;
$errors = 0;

$propertyRes = CIBlockProperty::GetList(
    ['SORT' => 'ASC', 'ID' => 'ASC'],
    ['IBLOCK_ID' => $sourceIblockId, 'ACTIVE' => 'Y']
);

while ($property = $propertyRes->Fetch()) {
    $propertyId = (int)$property['ID'];
    $propertyCode = (string)$property['CODE'];
    $propertyName = (string)$property['NAME'];

    $elementRes = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $sourceIblockId, '!PROPERTY_' . $propertyId => false],
        false,
        ['nTopCount' => 1],
        ['ID']
    );
    $hasAnyValue = (bool)$elementRes->Fetch();

    if ($hasAnyValue) {
        $kept++;
        migLog($logFile, "KEEP property [{$propertyId}] {$propertyCode} ({$propertyName})");
        continue;
    }

    if ($dryRun) {
        $deleted++;
        migLog($logFile, "DRY DELETE property [{$propertyId}] {$propertyCode} ({$propertyName})");
        continue;
    }

    if (CIBlockProperty::Delete($propertyId)) {
        $deleted++;
        migLog($logFile, "DELETE property [{$propertyId}] {$propertyCode} ({$propertyName})");
    } else {
        $errors++;
        migLog($logFile, "ERROR delete property [{$propertyId}] {$propertyCode} ({$propertyName})");
    }
}

migLog($logFile, "Done. kept={$kept}, deleted={$deleted}, errors={$errors}");
