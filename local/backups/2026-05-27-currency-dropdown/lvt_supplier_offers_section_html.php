<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/getchips_site.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/getchips_offers_section_html.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/MouserPartOffersHelper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/PromElecOnlineHelper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtEcatalogCrosslinkHelper.php';
use Bitrix\Main\Loader;
use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;

/**
 * Унифицированный вызов Mouser поиска по part number.
 * 1) Использует штатный клиент /mouser/mouser_client.php (если есть),
 * 2) иначе fallback на /api-mouser/classes/MouserAPI.php.
 *
 * @return array<string, mixed>
 */
function lvt_mouser_part_number_search_bridge(string $query): array
{
    if (!function_exists('lvt_is_mouser_api_enabled') || !lvt_is_mouser_api_enabled()) {
        return ['ok' => false, 'error' => 'mouser api disabled'];
    }

    $query = trim($query);
    if ($query === '') {
        return ['ok' => false, 'error' => 'empty query'];
    }

    $dr = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    if ($dr === '') {
        return ['ok' => false, 'error' => 'empty document root'];
    }

    $classicClient = $dr . '/mouser/mouser_client.php';
    if (is_file($classicClient)) {
        require_once $classicClient;
        if (function_exists('mouser_part_number_search')) {
            $r = mouser_part_number_search($query);
            if (is_array($r)) {
                return $r;
            }
        }
    }

    $apiMouserConfigLocal = $dr . '/api-mouser/config_local.php';
    $apiMouserConfig = is_file($apiMouserConfigLocal) ? $apiMouserConfigLocal : ($dr . '/api-mouser/config.php');
    if (!is_file($apiMouserConfig)) {
        return ['ok' => false, 'error' => 'mouser client files missing'];
    }

    require_once $apiMouserConfig;
    if (!defined('MOUSER_API_KEY') || !defined('API_ENDPOINT') || !function_exists('curl_init')) {
        return ['ok' => false, 'error' => 'invalid mouser api-mouser bootstrap'];
    }

    $proxyList = isset($proxies) && is_array($proxies) ? $proxies : [];
    $proxyAuthCfg = isset($proxy_auth) && is_array($proxy_auth) ? $proxy_auth : ['username' => '', 'password' => ''];
    if ($proxyList === []) {
        $proxyList = [null];
    }

    $lastError = 'unknown';
    $lastHttp = 0;
    $endpoint = rtrim((string) API_ENDPOINT, '/');
    $url = $endpoint . '/search/keyword?apiKey=' . rawurlencode((string) MOUSER_API_KEY);
    $payload = json_encode([
        'SearchByKeywordRequest' => [
            'keyword' => $query,
            'records' => 25,
            'startingRecord' => 0,
            'searchOptions' => '',
            'searchWithYourSignUpLanguage' => '',
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    foreach ($proxyList as $proxyRow) {
        $ch = curl_init($url);
        if ($ch === false) {
            $lastError = 'curl_init failed';
            continue;
        }
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => (string) $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Accept-Language: en-US,en;q=0.9',
            ],
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
        ];

        if (is_string($proxyRow) && $proxyRow !== '') {
            $proxyHostPort = preg_replace('#^socks5h?://#i', '', $proxyRow);
            $proxyUser = trim((string)($proxyAuthCfg['username'] ?? ''));
            $proxyPass = trim((string)($proxyAuthCfg['password'] ?? ''));
            if ($proxyUser !== '' || $proxyPass !== '') {
                $proxyUrl = $proxyUser . ':' . $proxyPass . '@' . $proxyHostPort;
            } else {
                $proxyUrl = $proxyHostPort;
            }
            $opts[CURLOPT_PROXY] = $proxyUrl;
            $opts[CURLOPT_PROXYTYPE] = defined('CURLPROXY_SOCKS5_HOSTNAME') ? CURLPROXY_SOCKS5_HOSTNAME : CURLPROXY_SOCKS5;
        }

        curl_setopt_array($ch, $opts);
        $resp = curl_exec($ch);
        $errno = curl_errno($ch);
        $err = curl_error($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0 || !is_string($resp) || $resp === '') {
            $lastError = $errno !== 0 ? $err : 'empty response';
            $lastHttp = $http;
            continue;
        }
        if ($http < 200 || $http >= 300) {
            $lastError = 'HTTP ' . $http;
            $lastHttp = $http;
            continue;
        }

        $decoded = json_decode($resp, true);
        if (!is_array($decoded) || !isset($decoded['SearchResults']) || !is_array($decoded['SearchResults'])) {
            $lastError = 'invalid response payload';
            $lastHttp = $http;
            continue;
        }
        $parts = is_array($decoded['SearchResults']['Parts'] ?? null) ? $decoded['SearchResults']['Parts'] : [];
        return [
            'ok' => true,
            'http_code' => $http,
            'data' => ['SearchResults' => ['Parts' => $parts]],
        ];
    }

    return [
        'ok' => false,
        'http_code' => $lastHttp,
        'error' => $lastError,
        'data' => ['SearchResults' => ['Parts' => []]],
    ];
}

/**
 * Последняя диагностика PromElec для AJAX (см. lvt_supplier_offers_render.php).
 *
 * @return array<string, mixed>
 */
function lvt_supplier_offers_promelec_diag(): array
{
    global $lvtLastPromelecDiag;

    return is_array($lvtLastPromelecDiag ?? null) ? $lvtLastPromelecDiag : [];
}

/**
 * ID товара в PromElec API (item_data_get): свойство promelec, 501 (импорт), XML_ID API_*.
 *
 * @return array{id:int, source:string}
 */
function lvt_resolve_promelec_item_id(array $arResult): array
{
    $extractDigits = static function ($val): int {
        if (is_array($val)) {
            $val = reset($val);
        }

        return (int) preg_replace('/\D/', '', trim((string) $val));
    };

    $fromPromelec = $extractDigits(LvtEcatalogCrosslinkHelper::propertyPlainValue($arResult, 'promelec'));
    if ($fromPromelec > 0) {
        return ['id' => $fromPromelec, 'source' => 'promelec_property'];
    }

    if (is_array($arResult['PROPERTIES'] ?? null)) {
        foreach ($arResult['PROPERTIES'] as $propCode => $prop) {
            if (!is_array($prop)) {
                continue;
            }
            $propId = (int) ($prop['ID'] ?? 0);
            $code = mb_strtolower(trim((string) ($prop['CODE'] ?? $propCode)));
            if ($propId !== 501 && $code !== '501') {
                continue;
            }
            $id = $extractDigits($prop['VALUE'] ?? $prop['~VALUE'] ?? '');
            if ($id > 0) {
                return ['id' => $id, 'source' => 'property_501'];
            }
        }
    }
    foreach (['PROPERTY_501_VALUE', 'PROPERTY_501'] as $listKey) {
        if (!array_key_exists($listKey, $arResult)) {
            continue;
        }
        $id = $extractDigits($arResult[$listKey]);
        if ($id > 0) {
            return ['id' => $id, 'source' => 'property_501_list'];
        }
    }

    $xmlId = trim((string) ($arResult['XML_ID'] ?? ''));
    if ($xmlId !== '' && preg_match('/^API_(\d+)$/i', $xmlId, $xmlMatch)) {
        return ['id' => (int) $xmlMatch[1], 'source' => 'xml_id'];
    }

    $promCodeCandidates = [];
    if (is_array($arResult['PROPERTIES'] ?? null)) {
        foreach ($arResult['PROPERTIES'] as $propCode => $prop) {
            if (!is_array($prop)) {
                continue;
            }
            $code = trim((string) ($prop['CODE'] ?? $propCode));
            $name = trim((string) ($prop['NAME'] ?? ''));
            $needle = mb_strtolower($code . ' ' . $name);
            if (!preg_match('/код\s*товар|product[_\s-]*code|producer[_\s-]*code|код/i', $needle)) {
                continue;
            }
            $val = $prop['VALUE'] ?? '';
            if (is_array($val)) {
                $val = reset($val);
            }
            $val = trim((string) $val);
            if ($val === '') {
                continue;
            }
            $promCodeCandidates[] = [
                'code' => $code,
                'name' => $name,
                'value' => $val,
                'digits' => preg_replace('/\D/', '', $val),
            ];
        }
    }
    foreach ($promCodeCandidates as $cand) {
        $id = (int) ($cand['digits'] ?? 0);
        if ($id > 0) {
            return ['id' => $id, 'source' => 'code_candidates'];
        }
    }

    if (!\Bitrix\Main\Loader::includeModule('iblock')) {
        return ['id' => 0, 'source' => ''];
    }

    $lookupElementIds = [];
    $mainElementId = (int) ($arResult['ID'] ?? 0);
    if ($mainElementId > 0) {
        $lookupElementIds[] = $mainElementId;
    }
    $skuElementId = (int) ($arResult['SKU']['CURRENT']['ID'] ?? 0);
    if ($skuElementId > 0 && !in_array($skuElementId, $lookupElementIds, true)) {
        $lookupElementIds[] = $skuElementId;
    }
    $iblockIdForLookup = (int) ($arResult['IBLOCK_ID'] ?? 0);
    foreach ($lookupElementIds as $lookupElementId) {
        if ($iblockIdForLookup <= 0 || $lookupElementId <= 0) {
            continue;
        }
        $rs501 = \CIBlockElement::GetProperty(
            $iblockIdForLookup,
            $lookupElementId,
            ['sort' => 'asc'],
            ['ID' => 501]
        );
        if ($p501 = $rs501->Fetch()) {
            $id501 = $extractDigits($p501['VALUE'] ?? $p501['VALUE_ENUM'] ?? '');
            if ($id501 > 0) {
                return ['id' => $id501, 'source' => 'db_property:501'];
            }
        }

        $rsProps = \CIBlockElement::GetProperty(
            $iblockIdForLookup,
            $lookupElementId,
            ['sort' => 'asc'],
            []
        );
        while ($p = $rsProps->Fetch()) {
            $propCode = trim((string) ($p['CODE'] ?? ''));
            $propName = trim((string) ($p['NAME'] ?? ''));
            $needle = mb_strtolower($propCode . ' ' . $propName);
            if (!preg_match('/promelec|код\s*товар|product[_\s-]*code|producer[_\s-]*code|item[_\s-]*id|id[_\s-]*elementa|id\s*элемент/i', $needle)) {
                continue;
            }
            $rawVal = trim((string) ($p['VALUE'] ?? ''));
            if ($rawVal === '' && !empty($p['VALUE_ENUM'])) {
                $rawVal = trim((string) $p['VALUE_ENUM']);
            }
            $candidateId = $extractDigits($rawVal);
            if ($candidateId > 0) {
                return [
                    'id' => $candidateId,
                    'source' => 'db_property:' . ($propCode !== '' ? $propCode : $propName),
                ];
            }
        }
    }

    return ['id' => 0, 'source' => ''];
}

/**
 * Единый epilog-блок поставщиков (Getchips + Mouser + PromElec) для карточки ИБ 11.
 *
 * @param array<string, mixed> $arResult
 * @param array<string, mixed> $options
 */
function lvt_supplier_offers_section_html(array $arResult, array $options = []): string
{
    global $APPLICATION;

    $mouserApiOn = function_exists('lvt_is_mouser_api_enabled') && lvt_is_mouser_api_enabled();
    $includeBitrix = array_key_exists('include_bitrix', $options) ? (bool)$options['include_bitrix'] : false;
    $includeGetchips = array_key_exists('include_getchips', $options) ? (bool)$options['include_getchips'] : true;
    $includeMouser = $mouserApiOn && (array_key_exists('include_mouser', $options) ? (bool)$options['include_mouser'] : false);
    $includePromelec = array_key_exists('include_promelec', $options) ? (bool)$options['include_promelec'] : true;
    $wrapSection = array_key_exists('wrap_section', $options) ? (bool)$options['wrap_section'] : true;
    $singleTable = !empty($options['single_table']);

    $iblockId = (int) ($arResult['IBLOCK_ID'] ?? 0);
    if ($iblockId !== 11) {
        return '';
    }

    $article = GetchipsCatalogOffersHelper::resolvePrArticle($arResult);
    $previewSrc = GetchipsCatalogOffersHelper::getProductPreviewSrcForGetchipsCartRow($arResult);
    $detailUrl = (string) ($arResult['DETAIL_PAGE_URL'] ?? '');
    $elementId = (int) ($arResult['ID'] ?? 0);

    global $lvtLastPromelecDiag;
    $lvtLastPromelecDiag = ['resolved_id' => 0, 'resolved_source' => '', 'api_ok' => false, 'api_error' => '', 'rows' => 0];

    $promResolve = lvt_resolve_promelec_item_id($arResult);
    $resolvedPromItemId = (int) ($promResolve['id'] ?? 0);
    $resolvedPromSource = (string) ($promResolve['source'] ?? '');
    $lvtLastPromelecDiag['resolved_id'] = $resolvedPromItemId;
    $lvtLastPromelecDiag['resolved_source'] = $resolvedPromSource;
    $body = '';
    $bitrixRowsHtml = '';
    $promRowsHtml = '';
    $stubProductId = (int) (defined('GETCHIPS_STUB_PRODUCT_ID') ? GETCHIPS_STUB_PRODUCT_ID : 0);
    $usdToRub = GetchipsCatalogOffersHelper::getUsdToRubByCbr();
    if ($usdToRub <= 0) {
        $usdToRub = 74.8806;
    }
    $rateDate = GetchipsCatalogOffersHelper::getCbrRateDate();
    $rateDateLabel = htmlspecialchars($rateDate !== '' ? $rateDate : date('d.m.Y'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $usdRateLabel = htmlspecialchars(number_format($usdToRub, 2, ',', ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $priceHeaderHtml = '<span class="getchips-currency-switch-wrap">'
        . '<span class="getchips-currency-switch-label">Цена:</span>'
        . '<span class="getchips-currency-switch js-getchips-currency-switch" data-display-currency="USD">'
        . '<button type="button" class="getchips-currency-switch__trigger">'
        . '<span class="js-getchips-currency-label">$ USD</span>'
        . '<span class="getchips-currency-switch__caret" aria-hidden="true"></span>'
        . '</button>'
        . '<span class="getchips-currency-switch__menu">'
        . '<button type="button" class="getchips-currency-switch__item" data-currency="RUB">🇷🇺 ₽ RUB</button>'
        . '<button type="button" class="getchips-currency-switch__item is-active" data-currency="USD">🇺🇸 $ USD</button>'
        . '</span>'
        . '</span>'
        . '<span class="getchips-currency-rate js-getchips-cbr-rate" data-rate-date="' . $rateDateLabel . '">USD ' . $usdRateLabel . ' ₽</span>'
        . '<span class="getchips-currency-alert js-getchips-usd-alert" data-notice="Курс ЦБ получен: ' . $rateDateLabel . '.">!</span>'
        . '<span class="getchips-currency-alert js-getchips-rub-alert" data-notice="Цена в рублях ориентировочная: если курс ЦБ изменится более чем на 2%, итоговую сумму уточним по актуальному курсу.">!</span>'
        . '</span>';
    $leadHeaderHtml = '<span class="getchips-offers__th-sort">Срок'
        . '<button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="lead" data-dir="asc">↑</button>'
        . '<button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="lead" data-dir="desc">↓</button>'
        . '</span>';
    $nameHeaderHtml = '<span class="getchips-offers__th-sort">Наименование'
        . '<button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="supplier" data-dir="asc">↑</button>'
        . '<button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="supplier" data-dir="desc">↓</button>'
        . '</span>';

    if ($includeBitrix && $article !== '' && mb_strlen($article) >= 3) {
            $storeRowsHtml = '';
            $storeData = is_array($GLOBALS['STORE_DATA_FOR_PRODUCT'] ?? null) ? $GLOBALS['STORE_DATA_FOR_PRODUCT'] : [];
            $ownBitrixStoreId = 7;
            $storeData = array_values(array_filter($storeData, static function ($storeRow) use ($ownBitrixStoreId) {
                return (int)($storeRow['ID'] ?? 0) === $ownBitrixStoreId;
            }));
            $priceProductId = (int)($arResult['ID'] ?? 0);
            if (!empty($arResult['SKU']['CURRENT']['ID'])) {
                $priceProductId = (int)$arResult['SKU']['CURRENT']['ID'];
            }
            $storeFallbackUsed = false;

                $basePrices = [];
                if ($priceProductId > 0 && Loader::includeModule('catalog')) {
                    $priceRows = PriceTable::getList([
                        'filter' => ['PRODUCT_ID' => $priceProductId, 'CATALOG_GROUP_ID' => 1],
                        'order' => ['QUANTITY_FROM' => 'ASC']
                    ])->fetchAll();
                    foreach ($priceRows as $row) {
                        $qtyFrom = (int)($row['QUANTITY_FROM'] ?? 1);
                        if ($qtyFrom <= 0) {
                            $qtyFrom = 1;
                        }
                        $basePrices[] = [
                            'qty' => $qtyFrom,
                            'price' => (float)($row['PRICE'] ?? 0),
                            'currency' => (string)($row['CURRENCY'] ?? 'RUB'),
                        ];
                    }
                }

                if ($storeData === [] && $priceProductId > 0 && Loader::includeModule('catalog')) {
                    $storeFallbackUsed = true;
                    $rows = StoreProductTable::getList([
                        'filter' => ['=PRODUCT_ID' => $priceProductId, '>AMOUNT' => 0],
                        'select' => ['STORE_ID', 'AMOUNT']
                    ])->fetchAll();
                    foreach ($rows as $spRow) {
                        $storeId = (int)($spRow['STORE_ID'] ?? 0);
                        if ($storeId !== $ownBitrixStoreId) {
                            continue;
                        }
                        if ($storeId <= 0) {
                            continue;
                        }
                        $store = StoreTable::getList([
                            'filter' => ['=ID' => $storeId],
                            'select' => ['ID', 'TITLE', 'ADDRESS', 'ACTIVE', 'SORT']
                        ])->fetch();
                        if (!$store || (string)($store['ACTIVE'] ?? '') !== 'Y') {
                            continue;
                        }
                        $title = (string)($store['TITLE'] ?? '');
                        if (in_array($storeId, [4, 5], true) || stripos($title, 'digi-key') !== false || stripos($title, 'mouser') !== false) {
                            continue;
                        }
                        $storeData[] = [
                            'ID' => $storeId,
                            'NAME' => $title !== '' ? $title : ('Склад ' . $storeId),
                            'ADDRESS' => (string)($store['ADDRESS'] ?? ''),
                            'QUANTITY' => (float)($spRow['AMOUNT'] ?? 0),
                            'SORT' => (int)($store['SORT'] ?? 500),
                        ];
                    }
                    usort($storeData, static function ($a, $b) {
                        return ((int)($a['SORT'] ?? 500)) <=> ((int)($b['SORT'] ?? 500));
                    });
                }

                $articleLabel = htmlspecialchars($article !== '' ? $article : (string)($arResult['NAME'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $brandRaw = trim((string)($arResult['BRAND_ITEM']['NAME'] ?? ''));
                $brandCandidates = [];
                $propertyKeysPreview = [];
                if (is_array($arResult['PROPERTIES'] ?? null)) {
                    foreach ($arResult['PROPERTIES'] as $propCode => $propData) {
                        $code = (string)$propCode;
                        $metaCode = is_array($propData) ? (string)($propData['CODE'] ?? '') : '';
                        $metaName = is_array($propData) ? (string)($propData['NAME'] ?? '') : '';
                        $needle = strtolower($code . ' ' . $metaCode . ' ' . $metaName);
                        if (count($propertyKeysPreview) < 20) {
                            $propertyKeysPreview[] = trim($code . '|' . $metaCode . '|' . $metaName);
                        }
                        if (preg_match('/brand|manufacturer|производ|бренд/i', $needle)) {
                            $value = '';
                            if (is_array($propData) && array_key_exists('VALUE', $propData)) {
                                $rawVal = $propData['VALUE'];
                                if (is_array($rawVal)) {
                                    $value = trim((string)reset($rawVal));
                                } else {
                                    $value = trim((string)$rawVal);
                                }
                            }
                            if ($value === '' && is_array($propData) && array_key_exists('DISPLAY_VALUE', $propData)) {
                                $dispVal = $propData['DISPLAY_VALUE'];
                                if (is_array($dispVal)) {
                                    $value = trim((string)reset($dispVal));
                                } else {
                                    $value = trim((string)$dispVal);
                                }
                            }
                            if ($value !== '') {
                                $brandCandidates[$code] = $value;
                                if ($brandRaw === '') {
                                    $brandRaw = $value;
                                }
                            }
                        }
                    }
                }
                if ($brandRaw === '' && is_array($arResult['DISPLAY_PROPERTIES'] ?? null)) {
                    foreach ($arResult['DISPLAY_PROPERTIES'] as $propCode => $propData) {
                        $code = (string)$propCode;
                        $metaCode = is_array($propData) ? (string)($propData['CODE'] ?? '') : '';
                        $metaName = is_array($propData) ? (string)($propData['NAME'] ?? '') : '';
                        $needle = strtolower($code . ' ' . $metaCode . ' ' . $metaName);
                        if (preg_match('/brand|manufacturer|производ|бренд/i', $needle)) {
                            $value = '';
                            if (is_array($propData) && array_key_exists('VALUE', $propData)) {
                                $rawVal = $propData['VALUE'];
                                if (is_array($rawVal)) {
                                    $value = trim((string)reset($rawVal));
                                } else {
                                    $value = trim((string)$rawVal);
                                }
                            }
                            if ($value !== '') {
                                $brandCandidates[$code] = $value;
                                if ($brandRaw === '') {
                                    $brandRaw = $value;
                                }
                            }
                        }
                    }
                }
                if ($brandRaw === '' && (int)($arResult['IBLOCK_ID'] ?? 0) > 0 && (int)($arResult['ID'] ?? 0) > 0 && Loader::includeModule('iblock')) {
                    $brandCodes = ['BRAND', 'MANUFACTURER', 'CML2_MANUFACTURER', 'PROIZVODITEL', 'PRODUCER'];
                    foreach ($brandCodes as $brandCode) {
                        $rsProp = CIBlockElement::GetProperty(
                            (int)$arResult['IBLOCK_ID'],
                            (int)$arResult['ID'],
                            ['sort' => 'asc'],
                            ['CODE' => $brandCode]
                        );
                        while ($prop = $rsProp->Fetch()) {
                            $value = trim((string)($prop['VALUE'] ?? ''));
                            if ($value === '' && !empty($prop['VALUE_ENUM'])) {
                                $value = trim((string)$prop['VALUE_ENUM']);
                            }
                            if ($value !== '' && ctype_digit($value)) {
                                $el = CIBlockElement::GetByID((int)$value)->GetNext();
                                if (!empty($el['NAME'])) {
                                    $value = trim((string)$el['NAME']);
                                }
                            }
                            if ($value !== '') {
                                $brandCandidates['IBLOCK_' . $brandCode] = $value;
                                $brandRaw = $value;
                                break 2;
                            }
                        }
                    }
                }
                if ($brandRaw === '') {
                    $brandRaw = '—';
                }
                $brandLabel = htmlspecialchars($brandRaw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                if ($brandLabel === '') {
                    $brandLabel = '—';
                }
                $sessid = htmlspecialchars(bitrix_sessid(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $productIdForCart = (int)($priceProductId > 0 ? $priceProductId : (int)($arResult['ID'] ?? 0));
                $pricePreview = [];

                foreach ($storeData as $store) {
                    $storeId = (int)($store['ID'] ?? 0);
                    $qtyMax = (int)($store['QUANTITY'] ?? 0);
                    $term = 'В наличии';

                    $priceHtml = '—';
                    $tiersRub = [];
                    if ($basePrices) {
                        $lines = [];
                        foreach ($basePrices as $p) {
                            $qtyTier = (int)$p['qty'];
                            $priceTier = (float)$p['price'];
                            $priceFormatted = rtrim(rtrim(number_format($priceTier, 2, '.', ''), '0'), '.');
                            $line = 'х ' . $qtyTier . ' шт. ' . $priceFormatted . ' руб.;';
                            $lines[] = '<div class="getchips-offers__price-tier js-getchips-price-tier" data-tier-qty="' . $qtyTier . '" data-price-rub="' . htmlspecialchars((string)$priceTier, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">' . $line . '</div>';
                            $pricePreview[] = $line;
                            $tiersRub[] = ['qty' => $qtyTier, 'rub' => $priceTier];
                        }
                        $priceHtml = implode('', $lines);
                    }
                    $minOrder = 1;
                    if ($tiersRub) {
                        $minOrder = (int)($tiersRub[0]['qty'] ?? 1);
                        if ($minOrder < 1) {
                            $minOrder = 1;
                        }
                    }
                    $orderStep = $minOrder;
                    $packNorm = $minOrder;
                    $firstRub = $tiersRub ? (float)($tiersRub[0]['rub'] ?? 0) : 0.0;
                    $tiersJson = htmlspecialchars(json_encode($tiersRub, JSON_UNESCAPED_UNICODE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                    $storeRowsHtml .= '<tr class="js-getchips-offer-row lvt-bitrix-store-row" data-sort-lead="0" data-sort-price="' . htmlspecialchars((string)$firstRub, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" data-tiers-rub-json="' . $tiersJson . '" data-min-order="' . $minOrder . '" data-order-step="' . $orderStep . '" data-available-qty="' . (int)$qtyMax . '">'
                    . '<td class="getchips-offers__name-cell"><div class="getchips-offers__part">' . $articleLabel . '</div><div class="getchips-offers__supplier">Собственный склад</div></td>'
                        . '<td class="getchips-offers__brand-cell"><span class="getchips-offers__brand-fallback"><span class="getchips-offers__brand-name">' . $brandLabel . '</span></span></td>'
                        . '<td class="getchips-offers__price-cell">' . $priceHtml . '</td>'
                        . '<td>' . (int)$qtyMax . '</td>'
                        . '<td>' . $term . '</td>'
                        . '<td class="getchips-offers__qty-cell">'
                        . '<div class="getchips-offers__qty-input-row">'
                        . '<input type="number" class="getchips-offers__qty-input js-getchips-qty-input js-bitrix-store-qty" value="' . $minOrder . '" min="' . $minOrder . '" max="' . (int)$qtyMax . '" step="' . $orderStep . '" inputmode="numeric" autocomplete="off" aria-label="Количество" data-min-order="' . $minOrder . '" data-order-step="' . $orderStep . '" data-store-id="' . $storeId . '">'
                        . '<div class="store-add-to-cart-button getchips-offers__cart-btn-wrap"><button type="button" class="btn btn-default btn-sm js-getchips-add-basket getchips-offers__add-basket has-ripple js-bitrix-store-add" data-bitrix-store-id="' . $storeId . '" data-product-id="' . $productIdForCart . '" data-sessid="' . $sessid . '" data-price-rub="' . htmlspecialchars((string)$firstRub, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" data-tiers-rub-json="' . $tiersJson . '" data-min-order="' . $minOrder . '" data-order-step="' . $orderStep . '" data-lead-days="0" data-brand-display="' . $brandLabel . '" data-source-currency="RUB" data-source-price="' . htmlspecialchars((string)$firstRub, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" data-supplier="Bitrix" data-part="' . $articleLabel . '" data-provider="bitrix-store" data-url="">В корзину</button></div>'
                        . '</div>'
                        . '<div class="getchips-offers__qty-hint js-getchips-qty-hint"></div>'
                        . '<div class="getchips-offers__qty-meta"><span>MIN: ' . $minOrder . '</span><span>Кратность: ' . $orderStep . '</span><span>Норма уп.: ' . $packNorm . '</span><span class="getchips-offers__row-total js-getchips-row-total"></span></div>'
                        . '</td>'
                        . '</tr>';
                }

        if ($singleTable) {
            $bitrixRowsHtml = $storeRowsHtml;
        } else {
            $shell = getchips_offers_table_shell_html([
                'article' => $article,
                'pageCatalogElementId' => $elementId,
                'detailPageUrl' => $detailUrl,
                'productImageSrc' => $previewSrc,
                'supplier_price_usd' => true,
            ]);
            if ($storeRowsHtml !== '') {
                $shell = preg_replace('/<tbody>/i', '<tbody>' . $storeRowsHtml, $shell, 1) ?? $shell;
            }
            $body .= '<div class="getchips-offers-section">' . $shell . '</div>';
        }
    }

    if ($includeGetchips && $article !== '' && mb_strlen($article) >= 3) {
        $gc = getchips_offers_section_html([
            'article' => $article,
            'pageCatalogElementId' => $elementId,
            'detailPageUrl' => $detailUrl,
            'productImageSrc' => $previewSrc,
            'withAssets' => true,
            'supplier_price_usd' => true,
            'skip_heading' => true,
        ]);
        if ($gc !== '') {
            $body .= '<h3 class="lvt-supplier-offers__sub">Getchips</h3>' . $gc;
        }
    }

    if ($includePromelec && $resolvedPromItemId <= 0) {
        $lvtLastPromelecDiag['api_error'] = 'no_promelec_item_id';
    }

    if ($includePromelec && $resolvedPromItemId > 0) {
        $pe = PromElecOnlineHelper::fetchItemCached($resolvedPromItemId);
        $lvtLastPromelecDiag['api_ok'] = !empty($pe['ok']);
        if (!empty($pe['error'])) {
            $lvtLastPromelecDiag['api_error'] = (string) $pe['error'];
        }
        if (!empty($pe['ok']) && is_array($pe['item'] ?? null)) {
            $promRows = PromElecOnlineHelper::buildGetchipsRows($pe['item']);
            if ($promRows !== []) {
                $promRowsHtml = '';
                foreach ($promRows as $pr) {
                    if (!is_array($pr)) {
                        continue;
                    }
                    $providerProbe = strtolower(trim((string)($pr['provider'] ?? '')));
                    $supplierProbe = strtolower(trim((string)($pr['supplier'] ?? '')));
                    if ($providerProbe === 'lvt.market' || $supplierProbe === 'lvt.market') {
                        continue;
                    }
                    $brandProbe = GetchipsCatalogOffersHelper::resolveBrandFromIblock6((string)($pr['brand'] ?? ''));
                    $partRaw = trim((string)($pr['part'] ?? $article));
                    if ($partRaw === '') {
                        $partRaw = (string)($arResult['NAME'] ?? '—');
                    }
                    $partLabel = htmlspecialchars($partRaw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $supplierLabel = htmlspecialchars((string)($pr['supplier'] ?? 'PromElec'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $brandPromRaw = trim((string)($pr['brand'] ?? ''));
                    if ($brandPromRaw === '') {
                        $brandPromRaw = '—';
                    }
                    $brandPromLabel = htmlspecialchars($brandPromRaw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $brandCardName = trim((string)($brandProbe['NAME'] ?? ''));
                    $brandCardUrl = trim((string)($brandProbe['URL'] ?? ''));
                    $brandCardImg = trim((string)($brandProbe['IMG_SRC'] ?? ''));
                    $brandCellHtml = '<span class="getchips-offers__brand-fallback"><span class="getchips-offers__brand-name">' . $brandPromLabel . '</span></span>';
                    if ($brandCardName !== '' && $brandCardUrl !== '') {
                        $brandCardNameEsc = htmlspecialchars($brandCardName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $brandCardUrlEsc = htmlspecialchars($brandCardUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $brandCardImgEsc = htmlspecialchars($brandCardImg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $brandCellHtml = '<a class="getchips-offers__brand-link" href="' . $brandCardUrlEsc . '">'
                            . ($brandCardImgEsc !== '' ? '<span class="getchips-offers__brand-logo-wrap"><img class="getchips-offers__brand-logo" src="' . $brandCardImgEsc . '" alt="" loading="lazy"></span>' : '')
                            . '<span class="getchips-offers__brand-name">' . $brandCardNameEsc . '</span>'
                            . '</a>';
                    }
                    $stockDisp = max(0, (int)($pr['stock'] ?? 0));
                    $leadLabel = htmlspecialchars((string)($pr['lead_label'] ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $leadSort = (float)($pr['lead_sort'] ?? 999999);
                    $tiersRub = is_array($pr['tiers_rub'] ?? null) ? $pr['tiers_rub'] : [];
                    usort($tiersRub, static function ($a, $b) {
                        return ((int)($a['qty'] ?? 0)) <=> ((int)($b['qty'] ?? 0));
                    });

                    $priceHtml = '—';
                    $firstRub = 0.0;
                    if ($tiersRub !== []) {
                        $firstRub = (float)($tiersRub[0]['rub'] ?? 0);
                        $sourceCurrency = strtoupper(trim((string)($pr['source_currency'] ?? 'RUB')));
                        if ($sourceCurrency === '') {
                            $sourceCurrency = 'RUB';
                        }
                        $currencyLabel = $sourceCurrency === 'RUB' ? 'руб.' : $sourceCurrency;
                        $lines = [];
                        foreach ($tiersRub as $tier) {
                            $tierQty = max(1, (int)($tier['qty'] ?? 1));
                            $tierPrice = (float)($tier['rub'] ?? 0);
                            if ($tierPrice <= 0) {
                                continue;
                            }
                            $priceFormatted = rtrim(rtrim(number_format($tierPrice, 2, '.', ''), '0'), '.');
                            $lines[] = '<div class="getchips-offers__price-tier js-getchips-price-tier" data-tier-qty="' . $tierQty . '" data-price-rub="' . htmlspecialchars((string)$tierPrice, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">х ' . $tierQty . ' шт. ' . $priceFormatted . ' ' . $currencyLabel . ';</div>';
                        }
                        if ($lines !== []) {
                            $priceHtml = implode('', $lines);
                        }
                    }
                    $minOrder = max(1, (int)($pr['min_order'] ?? ($tiersRub[0]['qty'] ?? 1)));
                    $orderStep = max(1, (int)($pr['order_step'] ?? $minOrder));
                    $packNorm = max(1, (int)($pr['pack_norm'] ?? $minOrder));
                    $tiersJson = htmlspecialchars(json_encode($tiersRub, JSON_UNESCAPED_UNICODE), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $sourceCurrencyAttr = htmlspecialchars((string)($pr['source_currency'] ?? 'RUB'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $sourcePriceVal = (float)($pr['source_price'] ?? $firstRub);
                    $sourcePriceAttr = htmlspecialchars((string)$sourcePriceVal, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $providerAttr = htmlspecialchars((string)($pr['provider'] ?? 'promelec'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $urlAttr = htmlspecialchars((string)($pr['url'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $canAddProm = $stubProductId > 0 && $firstRub > 0;

                    $cartHtml = '<span class="getchips-offers__muted">—</span>';
                    if ($canAddProm) {
                        $cartHtml = '<div class="store-add-to-cart-button getchips-offers__cart-btn-wrap"><button type="button" class="btn btn-default btn-sm js-getchips-add-basket getchips-offers__add-basket has-ripple" data-stub-product-id="' . $stubProductId . '" data-price-rub="' . htmlspecialchars((string)$firstRub, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" data-tiers-rub-json="' . $tiersJson . '" data-min-order="' . $minOrder . '" data-order-step="' . $orderStep . '" data-lead-days="0" data-brand-display="' . $brandPromLabel . '" data-source-currency="' . $sourceCurrencyAttr . '" data-source-price="' . $sourcePriceAttr . '" data-supplier="' . $supplierLabel . '" data-part="' . $partLabel . '" data-provider="' . $providerAttr . '" data-url="' . $urlAttr . '">В корзину</button></div>';
                    }

                    $promRowsHtml .= '<tr class="js-getchips-offer-row lvt-promelec-row" data-sort-lead="' . htmlspecialchars((string)$leadSort, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" data-sort-price="' . htmlspecialchars((string)$firstRub, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" data-tiers-rub-json="' . $tiersJson . '" data-min-order="' . $minOrder . '" data-order-step="' . $orderStep . '" data-available-qty="' . $stockDisp . '">'
                        . '<td class="getchips-offers__name-cell"><div class="getchips-offers__part">' . $partLabel . '</div><div class="getchips-offers__supplier">' . $supplierLabel . '</div></td>'
                        . '<td class="getchips-offers__brand-cell">' . $brandCellHtml . '</td>'
                        . '<td class="getchips-offers__price-cell">' . $priceHtml . '</td>'
                        . '<td>' . $stockDisp . '</td>'
                        . '<td>' . $leadLabel . '</td>'
                        . '<td class="getchips-offers__qty-cell">'
                        . '<div class="getchips-offers__qty-input-row">'
                        . '<input type="number" class="getchips-offers__qty-input js-getchips-qty-input" value="' . $minOrder . '" min="' . $minOrder . '" max="' . $stockDisp . '" step="' . $orderStep . '" inputmode="numeric" autocomplete="off" aria-label="Количество" data-min-order="' . $minOrder . '" data-order-step="' . $orderStep . '">'
                        . $cartHtml
                        . '</div>'
                        . '<div class="getchips-offers__qty-hint js-getchips-qty-hint"></div>'
                        . '<div class="getchips-offers__qty-meta"><span>MIN: ' . $minOrder . '</span><span>Кратность: ' . $orderStep . '</span><span>Норма уп.: ' . $packNorm . '</span><span class="getchips-offers__row-total js-getchips-row-total"></span></div>'
                        . '</td>'
                        . '</tr>';
                }

                $lvtLastPromelecDiag['rows'] = substr_count($promRowsHtml, 'lvt-promelec-row');
                if ($promRowsHtml !== '') {
                    if ($singleTable) {
                        // строки PromElec добавятся в общую таблицу ниже
                    } else {
                        $body .= '<div class="detail-block ordered-block getchips-offers-section">'
                            . '<div class="getchips-offers__table-wrap"><table class="getchips-offers__table js-getchips-offers-table">'
                            . '<thead><tr><th>Наименование</th><th>Бренд</th><th>Цена</th><th>Доступно, шт.</th><th>Срок</th><th>Кол-во</th></tr></thead>'
                            . '<tbody>' . $promRowsHtml . '</tbody>'
                            . '</table></div>'
                            . '</div>';
                    }
                }
            } else {
                // Фолбэк старого вида на случай неожиданной структуры API.
                $rows = PromElecOnlineHelper::buildTableRows($pe['item']);
                $body .= '<h3 class="lvt-supplier-offers__sub">PromElec</h3>';
                $body .= '<div class="lvt-supplier-offers__table-wrap"><table class="lvt-supplier-offers__table"><thead><tr><th>Позиция</th><th>Кол-во</th><th>Цена</th></tr></thead><tbody>';
                foreach ($rows as $r) {
                    $body .= '<tr><td>' . htmlspecialchars((string) ($r['name'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</td>';
                    $body .= '<td>' . htmlspecialchars((string) ($r['qty'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</td>';
                    $body .= '<td>' . htmlspecialchars((string) ($r['price'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</td></tr>';
                }
                $body .= '</tbody></table></div>';
            }
        }
    }

    if ($singleTable && $article !== '' && mb_strlen($article) >= 3 && ($includeBitrix || $includePromelec)) {
        $mergedRowsHtml = $bitrixRowsHtml . $promRowsHtml;
        $shell = getchips_offers_table_shell_html([
            'article' => $article,
            'pageCatalogElementId' => $elementId,
            'detailPageUrl' => $detailUrl,
            'productImageSrc' => $previewSrc,
            'supplier_price_usd' => true,
        ]);
        if ($mergedRowsHtml !== '') {
            $shell = preg_replace('/<tbody>/i', '<tbody>' . $mergedRowsHtml, $shell, 1) ?? $shell;
        }
        $body = $shell . $body;
    }

    $eco = LvtEcatalogCrosslinkHelper::resolveEcatalogDetailUrl($arResult);
    if ($eco !== '') {
        $body .= '<p class="lvt-supplier-offers__ecatalog" style="margin-top:1rem;"><a class="btn btn-default" href="' . htmlspecialchars($eco, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">Также в E-каталоге</a></p>';
    }

    if ($body === '') {
        return '';
    }

    if (strpos($body, 'js-getchips-currency-switch') === false) {
        $body = (string)preg_replace('/<th\b[^>]*>(?:(?!<\/th>).)*Наименование(?:(?!<\/th>).)*<\/th>/isu', '<th>' . $nameHeaderHtml . '</th>', $body, 1);
        $body = (string)preg_replace('/<th\b[^>]*>(?:(?!<\/th>).)*Цена(?:(?!<\/th>).)*<\/th>/isu', '<th>' . $priceHeaderHtml . '</th>', $body, 1);
        $body = (string)preg_replace('/<th\b[^>]*>(?:(?!<\/th>).)*Срок(?:(?!<\/th>).)*<\/th>/isu', '<th>' . $leadHeaderHtml . '</th>', $body, 1);
    }
    $body = str_replace('class="getchips-offers__table js-getchips-offers-table"', 'class="getchips-offers__table js-getchips-offers-table" data-usd-to-rub="' . htmlspecialchars((string)$usdToRub, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" data-display-currency="USD"', $body);

    $APPLICATION->SetAdditionalCSS('/local/css/lvt_supplier_offers.css');

    if (!$wrapSection) {
        return $body;
    }

    return '<section class="lvt-supplier-offers" id="lvt-supplier-offers">'
        . '<h2 class="lvt-supplier-offers__title">Предложения поставщиков</h2>'
        . '<p class="lvt-supplier-offers__muted">Данные: собственный склад (Bitrix), PromElec и Getchips. Цены поставщиков в исходной валюте; корзина Getchips — в ₽.</p>'
        . $body
        . '</section>';
}
