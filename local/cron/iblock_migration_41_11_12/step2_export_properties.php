<?php

$cfg = require __DIR__ . '/config.php';
require_once __DIR__ . '/common.php';

$iblocks = [
    (int)$cfg['iblocks']['products'],
    (int)$cfg['iblocks']['offers'],
    (int)$cfg['iblocks']['source'],
];

migEnsureDir($cfg['paths']['log_dir']);
migEnsureDir($cfg['paths']['export_dir']);
$logFile = $cfg['paths']['log_dir'] . '/step2_export_properties_' . date('Ymd_His') . '.log';

migLog($logFile, 'Start export for iblocks: ' . implode(', ', $iblocks));

foreach ($iblocks as $iblockId) {
    $filePath = $cfg['paths']['export_dir'] . '/properties_iblock_' . $iblockId . '.csv';
    $fh = fopen($filePath, 'wb');
    if (!$fh) {
        migLog($logFile, "ERROR open file: {$filePath}");
        continue;
    }

    fputcsv($fh, [
        'IBLOCK_ID',
        'PROPERTY_ID',
        'CODE',
        'NAME',
        'PROPERTY_TYPE',
        'USER_TYPE',
        'MULTIPLE',
        'IS_REQUIRED',
        'ACTIVE',
        'SORT',
        'WITH_DESCRIPTION',
        'LINK_IBLOCK_ID',
        'DEFAULT_VALUE',
    ], ';');

    $count = 0;
    $res = CIBlockProperty::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], ['IBLOCK_ID' => $iblockId]);
    while ($row = $res->Fetch()) {
        fputcsv($fh, [
            $iblockId,
            (int)$row['ID'],
            (string)$row['CODE'],
            (string)$row['NAME'],
            (string)$row['PROPERTY_TYPE'],
            (string)$row['USER_TYPE'],
            (string)$row['MULTIPLE'],
            (string)$row['IS_REQUIRED'],
            (string)$row['ACTIVE'],
            (int)$row['SORT'],
            (string)$row['WITH_DESCRIPTION'],
            (int)$row['LINK_IBLOCK_ID'],
            is_scalar($row['DEFAULT_VALUE']) ? (string)$row['DEFAULT_VALUE'] : '',
        ], ';');
        $count++;
    }

    fclose($fh);
    migLog($logFile, "Exported iblock={$iblockId}, rows={$count}, file={$filePath}");
}

migLog($logFile, 'Done.');
