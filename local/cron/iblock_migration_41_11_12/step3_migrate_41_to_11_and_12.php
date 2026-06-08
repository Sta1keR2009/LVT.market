<?php

$cfg = require __DIR__ . '/config.php';
require_once __DIR__ . '/common.php';

$args = migCliArgs($argv ?? []);
$dryRun = ($args['dry-run'] ?? '1') !== '0';
$limit = isset($args['limit']) ? max(0, (int)$args['limit']) : 0;
$offset = isset($args['offset']) ? max(0, (int)$args['offset']) : 0;
$onlyElementId = isset($args['element-id']) ? (int)$args['element-id'] : 0;

$productsIblockId = (int)$cfg['iblocks']['products'];
$offersIblockId = (int)$cfg['iblocks']['offers'];
$sourceIblockId = (int)$cfg['iblocks']['source'];
$manufacturerCode11 = (string)$cfg['manufacturer_code_property_11'];
$manufacturerCode41 = (string)$cfg['manufacturer_code_property_41'];
$targetSectionId = migGetSectionId($cfg);
$skuLinkPropertyId = migGetSkuLinkPropertyId($cfg);
$map41to11 = require $cfg['paths']['map_41_to_11'];
$map41to12 = require $cfg['paths']['map_41_to_12'];

migEnsureDir($cfg['paths']['log_dir']);
$logFile = $cfg['paths']['log_dir'] . '/step3_migrate_41_to_11_and_12_' . date('Ymd_His') . '.log';

migLog($logFile, "Start migrate dry-run=" . ($dryRun ? 'Y' : 'N'));
migLog($logFile, "IBLOCKs: source={$sourceIblockId}, products={$productsIblockId}, offers={$offersIblockId}");
migLog($logFile, "Target section ID: {$targetSectionId}, SKU link property ID: {$skuLinkPropertyId}");

if ($targetSectionId <= 0) {
    migLog($logFile, 'ERROR: target section "Электротехника" not found. Configure target_section in config.php');
    exit(1);
}
if ($skuLinkPropertyId <= 0) {
    migLog($logFile, 'ERROR: SKU link property ID not found. Configure sku.link_property_id in config.php');
    exit(1);
}
if ($manufacturerCode11 === '' || $manufacturerCode41 === '') {
    migLog($logFile, 'ERROR: manufacturer code property is not configured in config.php');
    exit(1);
}

$sourceFilter = ['IBLOCK_ID' => $sourceIblockId, 'ACTIVE' => 'Y'];
if ($onlyElementId > 0) {
    $sourceFilter['ID'] = $onlyElementId;
}

$nav = false;
if ($limit > 0) {
    $nav = ['nTopCount' => $limit + $offset];
}

$sourceRes = CIBlockElement::GetList(
    ['ID' => 'ASC'],
    $sourceFilter,
    false,
    $nav,
    [
        'ID', 'IBLOCK_ID', 'NAME', 'CODE', 'XML_ID', 'DETAIL_TEXT', 'DETAIL_TEXT_TYPE',
        'PREVIEW_TEXT', 'PREVIEW_TEXT_TYPE', 'ACTIVE', 'PROPERTY_' . $manufacturerCode41,
        'CATALOG_PRICE_1',
    ]
);

$processed = 0;
$createdProducts = 0;
$linkedToExisting = 0;
$createdOffers = 0;
$errors = 0;

$el = new CIBlockElement();
$skipByOffset = $offset;

while ($source = $sourceRes->GetNext()) {
    if ($skipByOffset > 0) {
        $skipByOffset--;
        continue;
    }

    $processed++;
    $sourceId = (int)$source['ID'];
    $sourceName = trim((string)$source['NAME']);
    $manufacturerValue = trim((string)$source['PROPERTY_' . strtoupper($manufacturerCode41) . '_VALUE']);

    if ($manufacturerValue === '') {
        $errors++;
        migLog($logFile, "SKIP source={$sourceId}, empty manufacturer code");
        continue;
    }

    $targetProduct = CIBlockElement::GetList(
        ['ID' => 'ASC'],
        [
            'IBLOCK_ID' => $productsIblockId,
            '=PROPERTY_' . $manufacturerCode11 => $manufacturerValue,
        ],
        false,
        ['nTopCount' => 1],
        ['ID', 'NAME']
    )->Fetch();

    $targetProductId = 0;
    if ($targetProduct) {
        $targetProductId = (int)$targetProduct['ID'];
        $linkedToExisting++;
        migLog($logFile, "MATCH source={$sourceId} -> product={$targetProductId} by manufacturer code={$manufacturerValue}");
    } else {
        $productProps = [];
        foreach ($map41to11 as $sourceCode => $targetCode) {
            $sourceCode = trim((string)$sourceCode);
            $targetCode = trim((string)$targetCode);
            if ($sourceCode === '' || $targetCode === '') {
                continue;
            }
            $value = CIBlockElement::GetProperty(
                $sourceIblockId,
                $sourceId,
                ['SORT' => 'ASC'],
                ['CODE' => $sourceCode]
            )->Fetch();
            if ($value && $value['VALUE'] !== '' && $value['VALUE'] !== null) {
                $productProps[$targetCode] = $value['VALUE'];
            }
        }
        $productProps[$manufacturerCode11] = $manufacturerValue;

        $productFields = [
            'IBLOCK_ID' => $productsIblockId,
            'IBLOCK_SECTION_ID' => $targetSectionId,
            'NAME' => $sourceName !== '' ? $sourceName : ('Source 41 #' . $sourceId),
            'ACTIVE' => 'Y',
            'CODE' => trim((string)$source['CODE']) !== '' ? trim((string)$source['CODE']) : 'src41_' . $sourceId,
            'XML_ID' => trim((string)$source['XML_ID']) !== '' ? trim((string)$source['XML_ID']) : 'src41_' . $sourceId,
            'DETAIL_TEXT' => (string)$source['DETAIL_TEXT'],
            'DETAIL_TEXT_TYPE' => (string)$source['DETAIL_TEXT_TYPE'] ?: 'html',
            'PREVIEW_TEXT' => (string)$source['PREVIEW_TEXT'],
            'PREVIEW_TEXT_TYPE' => (string)$source['PREVIEW_TEXT_TYPE'] ?: 'html',
            'PROPERTY_VALUES' => $productProps,
        ];

        if ($dryRun) {
            $targetProductId = -$sourceId;
            $createdProducts++;
            migLog($logFile, "DRY CREATE product for source={$sourceId}, manufacturer={$manufacturerValue}");
        } else {
            $newProductId = $el->Add($productFields);
            if (!$newProductId) {
                $errors++;
                migLog($logFile, "ERROR create product from source={$sourceId}: " . $el->LAST_ERROR);
                continue;
            }
            $targetProductId = (int)$newProductId;
            $createdProducts++;
            migLog($logFile, "CREATE product={$targetProductId} from source={$sourceId}");
        }
    }

    $offerProps = [
        $skuLinkPropertyId => $targetProductId,
    ];
    foreach ($map41to12 as $sourceCode => $targetCode) {
        $sourceCode = trim((string)$sourceCode);
        $targetCode = trim((string)$targetCode);
        if ($sourceCode === '' || $targetCode === '') {
            continue;
        }
        $value = CIBlockElement::GetProperty(
            $sourceIblockId,
            $sourceId,
            ['SORT' => 'ASC'],
            ['CODE' => $sourceCode]
        )->Fetch();
        if ($value && $value['VALUE'] !== '' && $value['VALUE'] !== null) {
            $offerProps[$targetCode] = $value['VALUE'];
        }
    }

    $offerCode = 'offer_src41_' . $sourceId;
    $offerFields = [
        'IBLOCK_ID' => $offersIblockId,
        'NAME' => ($sourceName !== '' ? $sourceName : ('Source 41 #' . $sourceId)) . ' (поставщик)',
        'ACTIVE' => 'Y',
        'CODE' => $offerCode,
        'XML_ID' => $offerCode,
        'PROPERTY_VALUES' => $offerProps,
    ];

    if ($dryRun) {
        $createdOffers++;
        migLog($logFile, "DRY CREATE offer for source={$sourceId} linked_to={$targetProductId}");
        continue;
    }

    $newOfferId = $el->Add($offerFields);
    if (!$newOfferId) {
        $errors++;
        migLog($logFile, "ERROR create offer from source={$sourceId}: " . $el->LAST_ERROR);
        continue;
    }
    $newOfferId = (int)$newOfferId;
    $createdOffers++;
    migLog($logFile, "CREATE offer={$newOfferId} from source={$sourceId} linked_to={$targetProductId}");

    if (!CCatalogProduct::GetByID($newOfferId)) {
        CCatalogProduct::Add([
            'ID' => $newOfferId,
            'TYPE' => CCatalogProduct::TYPE_OFFER,
            'QUANTITY' => 0,
        ]);
    }

    $priceValue = (float)$source['CATALOG_PRICE_1'];
    if ($priceValue > 0) {
        $priceTypeId = (int)$cfg['price']['catalog_group_id'];
        $currency = (string)$cfg['price']['currency'];

        $existingPrice = CPrice::GetList([], [
            'PRODUCT_ID' => $newOfferId,
            'CATALOG_GROUP_ID' => $priceTypeId,
        ])->Fetch();

        if ($existingPrice) {
            CPrice::Update((int)$existingPrice['ID'], [
                'PRICE' => $priceValue,
                'CURRENCY' => $currency,
            ]);
        } else {
            $priceObj = new CPrice();
            $priceObj->Add([
                'PRODUCT_ID' => $newOfferId,
                'CATALOG_GROUP_ID' => $priceTypeId,
                'PRICE' => $priceValue,
                'CURRENCY' => $currency,
            ]);
        }
    }
}

migLog($logFile, "Done. processed={$processed}, createdProducts={$createdProducts}, linkedToExisting={$linkedToExisting}, createdOffers={$createdOffers}, errors={$errors}");
