<?php

function migEnsureDir(string $dir): void
{
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

function migCliArgs(array $argv): array
{
    $result = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (strpos($arg, '--') !== 0) {
            continue;
        }
        $arg = substr($arg, 2);
        if (strpos($arg, '=') === false) {
            $result[$arg] = '1';
            continue;
        }
        [$key, $value] = explode('=', $arg, 2);
        $result[$key] = $value;
    }
    return $result;
}

function migLog(string $logFile, string $message): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    echo $line;
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

function migGetSectionId(array $cfg): int
{
    $productsIblockId = (int)$cfg['iblocks']['products'];
    $target = $cfg['target_section'];

    if (!empty($target['id'])) {
        return (int)$target['id'];
    }

    $filter = ['IBLOCK_ID' => $productsIblockId];
    if (!empty($target['code'])) {
        $filter['=CODE'] = (string)$target['code'];
    } elseif (!empty($target['name_fallback'])) {
        $filter['=NAME'] = (string)$target['name_fallback'];
    } else {
        return 0;
    }

    $row = CIBlockSection::GetList([], $filter, false, ['ID'])->Fetch();
    return $row ? (int)$row['ID'] : 0;
}

function migGetSkuLinkPropertyId(array $cfg): int
{
    $configured = (int)($cfg['sku']['link_property_id'] ?? 0);
    if ($configured > 0) {
        return $configured;
    }

    $offersIblockId = (int)$cfg['iblocks']['offers'];
    $skuInfo = CCatalogSKU::GetInfoByOfferIBlock($offersIblockId);
    if (is_array($skuInfo) && !empty($skuInfo['SKU_PROPERTY_ID'])) {
        return (int)$skuInfo['SKU_PROPERTY_ID'];
    }

    return 0;
}
