<?php

use Bitrix\Main\Loader;

/**
 * Создание/обновление элемента ИБ 11 по данным Mouser (partnumber API).
 */
class MouserCatalogElementHelper
{
    public const SECTION_CODE_FALLBACK = 'mouser-import';
    public const XML_PREFIX = 'MOUSER_';

    /** @param array<string, mixed> $data */
    private static function debugLvtNdjson(string $hypothesisId, string $location, string $message, array $data = []): void
    {
    }

    /** @param array<string, mixed> $data */
    private static function debugSessionNdjson(string $runId, string $hypothesisId, string $location, string $message, array $data = []): void
    {
    }

    /** @param array<string, mixed> $data */
    private static function debugCurrentSessionNdjson(string $runId, string $hypothesisId, string $location, string $message, array $data = []): void
    {
    }

    /**
     * Синхронизирует остаток каталога по данным Mouser Availability.
     * Берем максимум между текущим QUANTITY и остатком Mouser, чтобы не затирать больший локальный остаток.
     *
     * @param array<string, mixed> $part
     */
    private static function syncCatalogQuantityFromMouserPart(int $productId, array $part): void
    {
        if ($productId <= 0 || !Loader::includeModule('catalog')) {
            return;
        }

        $mouserQtyRaw = MouserPartOffersHelper::parseStockQtyFromPart($part);
        $mouserQty = is_int($mouserQtyRaw) ? max(0, min($mouserQtyRaw, 999999)) : 0;
        $current = \CCatalogProduct::GetByID($productId);
        $currentQty = isset($current['QUANTITY']) ? (int) $current['QUANTITY'] : 0;
        $targetQty = max($currentQty, $mouserQty);

        if ($targetQty <= 0) {
            return;
        }

        $fields = [
            'QUANTITY' => $targetQty,
            'QUANTITY_TRACE' => 'N',
            'AVAILABLE' => 'Y',
            'CAN_BUY_ZERO' => 'Y',
        ];
        if (is_array($current)) {
            \CCatalogProduct::Update($productId, $fields);
        } else {
            $typeProduct = class_exists(\Bitrix\Catalog\ProductTable::class)
                ? (int) \Bitrix\Catalog\ProductTable::TYPE_PRODUCT
                : 1;
            \CCatalogProduct::Add(array_merge([
                'ID' => $productId,
                'TYPE' => $typeProduct,
            ], $fields));
        }

    }

    /**
     * @return array<string, int>
     */
    private static function categoryMap(): array
    {
        $path = dirname(__DIR__) . '/mouser_category_to_section.php';
        if (!is_file($path)) {
            return [];
        }
        $m = include $path;

        return is_array($m) ? $m : [];
    }

    public static function normalizeCategoryKey(string $s): string
    {
        return mb_strtolower(trim($s));
    }

    public static function ensureFallbackSection(int $iblockId): int
    {
        if ($iblockId <= 0 || !Loader::includeModule('iblock')) {
            return 0;
        }
        $db = CIBlockSection::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, 'CODE' => self::SECTION_CODE_FALLBACK],
            false,
            ['ID']
        );
        if ($s = $db->Fetch()) {
            return (int) $s['ID'];
        }
        $bs = new CIBlockSection();

        return (int) $bs->Add([
            'IBLOCK_ID' => $iblockId,
            'NAME' => 'Импорт Mouser',
            'CODE' => self::SECTION_CODE_FALLBACK,
            'ACTIVE' => 'Y',
        ]);
    }

    /**
     * Подбор раздела по строке категории Mouser; если передан путь «A/B/C», перебираются
     * нормализованный полный путь и сегменты справа налево (лист → корень).
     */
    public static function resolveSectionIdForCategory(int $iblockId, string $categoryPathOrText): int
    {
        $fallback = self::ensureFallbackSection($iblockId);
        $path = trim($categoryPathOrText);
        if ($path === '') {
            return $fallback;
        }
        $keysToTry = [];
        $full = self::normalizeCategoryKey($path);
        if ($full !== '') {
            $keysToTry[] = $full;
        }
        foreach (array_reverse(explode('/', $path)) as $seg) {
            $seg = trim((string) $seg);
            if ($seg === '') {
                continue;
            }
            $nk = self::normalizeCategoryKey($seg);
            if ($nk !== '' && !in_array($nk, $keysToTry, true)) {
                $keysToTry[] = $nk;
            }
        }
        $map = self::categoryMap();
        foreach ($keysToTry as $k) {
            if ($k === '') {
                continue;
            }
            if (isset($map[$k])) {
                return (int) $map[$k];
            }
            foreach ($map as $pattern => $sid) {
                if ($pattern !== '' && (mb_stripos($k, self::normalizeCategoryKey((string) $pattern)) !== false
                    || mb_stripos(self::normalizeCategoryKey((string) $pattern), $k) !== false)) {
                    return (int) $sid;
                }
            }
        }

        return $fallback;
    }

    /**
     * Строка категории для мэпинга разделов: путь из названий через «/» (если в ответе API есть иерархия).
     */
    public static function categoryPathStringFromPart(array $p): string
    {
        $c = $p['Category'] ?? null;
        if (is_string($c)) {
            return trim($c);
        }
        if (!is_array($c)) {
            return '';
        }
        $parts = [];
        $node = $c;
        $guard = 0;
        while (is_array($node) && $guard++ < 24) {
            $name = trim((string) ($node['Name'] ?? $node['Text'] ?? $node['Value'] ?? ''));
            if ($name !== '') {
                array_unshift($parts, $name);
            }
            $next = $node['ParentCategory'] ?? $node['Parent'] ?? null;
            if (!is_array($next)) {
                break;
            }
            $node = $next;
        }
        if ($parts !== []) {
            return implode('/', $parts);
        }

        return trim((string) ($c['Name'] ?? $c['Text'] ?? $c['Value'] ?? ''));
    }

    public static function xmlIdForMouserPart(string $mouserPartNumber): string
    {
        $mouserPartNumber = trim($mouserPartNumber);

        return self::XML_PREFIX . md5(mb_strtoupper($mouserPartNumber));
    }

    /**
     * Найти существующий элемент по XML_ID.
     */
    public static function findElementIdByMouserPart(int $iblockId, string $mouserPartNumber): int
    {
        if ($iblockId <= 0 || !Loader::includeModule('iblock')) {
            return 0;
        }
        $xml = self::xmlIdForMouserPart($mouserPartNumber);
        $res = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, 'XML_ID' => $xml],
            false,
            false,
            ['ID']
        );
        if ($row = $res->Fetch()) {
            return (int) $row['ID'];
        }

        return 0;
    }

    /**
     * Найти уже созданную карточку по партномеру: MOUSER_* (любая строка-ключ), GETCHIPS_PN_* или свойства артикула.
     * Нужно, чтобы поиск + «Открыть» из таблицы Mouser не создавали дубликаты с разным XML_ID.
     */
    public static function findElementIdByPartnerQueries(int $iblockId, string $articleRaw, string $normRaw = ''): int
    {
        if ($iblockId <= 0 || !Loader::includeModule('iblock')) {
            return 0;
        }
        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';
        $norm = GetchipsCatalogOffersHelper::normalizeArticle($normRaw !== '' ? $normRaw : $articleRaw);
        $a = GetchipsCatalogOffersHelper::normalizeArticle($articleRaw);
        $strings = array_values(array_unique(array_filter([
            $norm,
            $a,
            trim($articleRaw),
            trim($normRaw),
        ], static function ($v) {
            return $v !== '' && mb_strlen((string) $v) >= 2;
        })));

        foreach ($strings as $s) {
            $id = self::findElementIdByMouserPart($iblockId, (string) $s);
            if ($id > 0) {
                return $id;
            }
        }

        $getchipsXml = 'GETCHIPS_PN_' . md5($norm);
        $res = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, 'XML_ID' => $getchipsXml],
            false,
            false,
            ['ID']
        );
        if ($row = $res->Fetch()) {
            return (int) $row['ID'];
        }

        $propOr = ['LOGIC' => 'OR'];
        foreach ($strings as $s) {
            $propOr[] = ['PROPERTY_CML2_ARTICLE' => $s];
            $propOr[] = ['PROPERTY_pr_article' => $s];
            $propOr[] = ['PROPERTY_MOUSER_PART_NUMBER' => $s];
        }
        if (count($propOr) > 1) {
            $res2 = CIBlockElement::GetList(
                ['ID' => 'ASC'],
                ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', $propOr],
                false,
                ['nTopCount' => 1],
                ['ID']
            );
            if ($row2 = $res2->Fetch()) {
                return (int) $row2['ID'];
            }
        }

        return 0;
    }

    /**
     * Создать или обновить элемент по номеру Mouser.
     *
     * @return int ID элемента или 0
     */
    public static function upsertFromMouserPartNumber(int $iblockId, string $mouserPartNumber): int
    {
        $runId = 'ds-' . substr(md5($iblockId . '|' . $mouserPartNumber . '|' . microtime(true)), 0, 10);
        $mouserPartNumber = trim($mouserPartNumber);
        if ($mouserPartNumber === '' || $iblockId <= 0 || !Loader::includeModule('iblock')) {
            return 0;
        }
        $client = $_SERVER['DOCUMENT_ROOT'] . '/mouser/mouser_client.php';
        if (!is_file($client)) {
            return 0;
        }
        require_once $client;
        if (!function_exists('mouser_part_number_search')) {
            return 0;
        }
        $api = mouser_part_number_search($mouserPartNumber);
        if (empty($api['ok']) || !is_array($api['data'] ?? null)) {
            return 0;
        }
        $parts = $api['data']['SearchResults']['Parts'] ?? null;
        if (!is_array($parts) || $parts === []) {
            return 0;
        }
        $p = $parts[0];
        if (!is_array($p)) {
            return 0;
        }

        require_once __DIR__ . '/MouserPartOffersHelper.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';
        $catPath = self::categoryPathStringFromPart($p);
        if ($catPath === '') {
            $catPath = MouserPartOffersHelper::categoryTextFromPart($p);
        }
        $sectionId = self::resolveSectionIdForCategory($iblockId, $catPath);

        $mpn = trim((string) ($p['ManufacturerPartNumber'] ?? ''));
        $mfr = trim((string) ($p['Manufacturer'] ?? ''));
        $apiMouserPn = trim((string) ($p['MouserPartNumber'] ?? ''));
        /** Канонический ключ карточки: MPN, иначе номер Mouser из ответа, иначе строка запроса (важно для XML_ID/CODE при разных входах). */
        $canonicalKey = $mpn !== '' ? $mpn : ($apiMouserPn !== '' ? $apiMouserPn : $mouserPartNumber);
        /** Артикул производителя (MPN) из API — в pr_article / CML2_ARTICLE; номер Mouser — в artikul_proizvoditelya. */
        $mpnForArticle = $mpn !== '' ? $mpn : $canonicalKey;
        $desc = trim((string) ($p['Description'] ?? ''));
        $name = $desc !== '' ? $desc : ($mfr !== '' ? ($mfr . ' ' . ($mpn !== '' ? $mpn : $mouserPartNumber)) : ($mpn !== '' ? $mpn : $mouserPartNumber));
        if (function_exists('mb_substr')) {
            $name = mb_substr($name, 0, 255, 'UTF-8');
        } else {
            $name = substr($name, 0, 255);
        }

        $xmlId = self::xmlIdForMouserPart($canonicalKey);
        $existId = self::findElementIdByMouserPart($iblockId, $canonicalKey);
        if ($existId <= 0) {
            $existId = self::findElementIdByMouserPart($iblockId, $mouserPartNumber);
        }
        if ($existId <= 0 && $apiMouserPn !== '' && $apiMouserPn !== $mouserPartNumber) {
            $existId = self::findElementIdByMouserPart($iblockId, $apiMouserPn);
        }
        if ($existId <= 0) {
            $existId = self::findElementIdByPartnerQueries($iblockId, $mouserPartNumber, $canonicalKey);
        }

        $propVals = [];
        self::setPropByCode($iblockId, $propVals, 'pr_article', $mpnForArticle);
        self::setPropByCode($iblockId, $propVals, 'CML2_ARTICLE', $mpnForArticle);
        $mouserPnForProp = $apiMouserPn !== '' ? $apiMouserPn : $mouserPartNumber;
        self::setPropByCode($iblockId, $propVals, 'artikul_proizvoditelya', $mouserPnForProp);
        self::setPropByCode($iblockId, $propVals, 'MOUSER_PART_NUMBER', $mouserPnForProp);
        if ($mfr !== '') {
            $brandCard = GetchipsCatalogOffersHelper::resolveBrandFromIblock6($mfr);
            $brandId = (int) ($brandCard['ID'] ?? 0);
            if ($brandId > 0) {
                self::setPropByCode($iblockId, $propVals, 'BRAND', (string) $brandId);
            }
        }
        $dsUrl = self::resolveDatasheetUrl($p, $runId);
        self::debugCurrentSessionNdjson($runId, 'D1', 'MouserCatalogElementHelper.php:370', 'datasheet source from mouser part', [
            'queryPn' => $mouserPartNumber,
            'apiMouserPn' => trim((string) ($p['MouserPartNumber'] ?? '')),
            'dataSheetUrl' => $dsUrl,
            'isHttpUrl' => (bool) preg_match('#^https?://#i', $dsUrl),
        ]);
        if ($dsUrl !== '' && preg_match('#^https?://#i', $dsUrl)) {
            self::setPropByCode($iblockId, $propVals, 'PDF2', $dsUrl);
        }
        self::attachDatasheetPdfToInstructions($iblockId, $existId, $propVals, $dsUrl, self::mouserImageRefererCandidates($p), $mouserPartNumber, $runId);
        self::debugCurrentSessionNdjson($runId, 'D2', 'MouserCatalogElementHelper.php:385', 'datasheet attachment result in payload', [
            'existId' => $existId,
            'hasInstructionsInPayload' => array_key_exists('INSTRUCTIONS', $propVals),
            'instructionsPayloadCount' => isset($propVals['INSTRUCTIONS']) && is_array($propVals['INSTRUCTIONS']) ? count($propVals['INSTRUCTIONS']) : 0,
            'pdf2Prepared' => isset($propVals['PDF2']) ? (string) $propVals['PDF2'] : '',
        ]);

        $detailPicture = self::imageFileArrayFromMouserImagePath($p['ImagePath'] ?? '', self::mouserImageRefererCandidates($p));

        $attrs = $p['ProductAttributes'] ?? [];
        if (is_array($attrs)) {
            foreach ($attrs as $a) {
                if (!is_array($a)) {
                    continue;
                }
                $n = trim((string) ($a['AttributeName'] ?? $a['Name'] ?? ''));
                $v = trim((string) ($a['AttributeValue'] ?? $a['Value'] ?? ''));
                if ($n === '' || $v === '') {
                    continue;
                }
                $code = self::guessPropertyCodeFromAttributeName($n);
                if ($code !== '') {
                    self::setPropByCode($iblockId, $propVals, $code, $v);
                }
            }
        }

        $normPack = self::normPackFromProductAttributes(is_array($attrs) ? $attrs : []);
        if ($normPack !== '') {
            self::setPropByCode($iblockId, $propVals, 'normoupakovka', $normPack);
        }

        $detailBody = '';

        $previewOneLine = trim($mpn . ($mfr !== '' ? ' · ' . $mfr : ''));
        if ($previewOneLine === '') {
            $previewOneLine = 'Импорт Mouser';
        }
        $previewText = $desc !== '' ? $desc : $previewOneLine;

        $el = new CIBlockElement();
        $fields = [
            'IBLOCK_ID' => $iblockId,
            'IBLOCK_SECTION_ID' => $sectionId,
            'NAME' => $name,
            'ACTIVE' => 'Y',
            'XML_ID' => $xmlId,
            'CODE' => self::makeElementCode($canonicalKey, $mpn),
            'PREVIEW_TEXT' => $previewText,
            'PREVIEW_TEXT_TYPE' => 'text',
            'DETAIL_TEXT' => $detailBody,
            'DETAIL_TEXT_TYPE' => 'text',
            'PROPERTY_VALUES' => $propVals,
        ];
        if ($detailPicture !== null) {
            $fields['DETAIL_PICTURE'] = $detailPicture;
            $fields['PREVIEW_PICTURE'] = $detailPicture;
        }

        if ($existId > 0) {
            if (!empty($fields['PROPERTY_VALUES'])) {
                $payloadSummary = [];
                $payloadByCode = [];
                $dbProps = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId]);
                while ($prop = $dbProps->Fetch()) {
                    $code = (string) ($prop['CODE'] ?? '');
                    if ($code === '' || !array_key_exists($code, $fields['PROPERTY_VALUES'])) {
                        continue;
                    }
                    $v = $fields['PROPERTY_VALUES'][$code];
                    $payloadByCode[$code] = $v;
                    $payloadSummary[$code] = [
                        'propertyType' => (string) ($prop['PROPERTY_TYPE'] ?? ''),
                        'multiple' => (string) ($prop['MULTIPLE'] ?? 'N'),
                        'valuePhpType' => gettype($v),
                        'isArray' => is_array($v),
                    ];
                }
                foreach ($payloadByCode as $code => $value) {
                    try {
                        CIBlockElement::SetPropertyValuesEx($existId, $iblockId, [$code => $value]);
                    } catch (\Throwable $e) {
                    }
                }
            }
            unset($fields['PROPERTY_VALUES']);
            unset($fields['IBLOCK_ID']);
            // Keep existing catalog section for already published products.
            // Mouser category mapping should not move existing cards between sections.
            unset($fields['IBLOCK_SECTION_ID']);
            // Keep original URL and identity for existing products.
            // Prevent CODE/XML_ID rewrite that can break existing detail URLs.
            unset($fields['CODE']);
            unset($fields['XML_ID']);
            $el->Update($existId, $fields);
            GetchipsCatalogOffersHelper::syncCatalogQuantityFromSupplierOffers($existId, $canonicalKey);
            self::syncCatalogQuantityFromMouserPart($existId, $p);
            require_once __DIR__ . '/LvtSupplierBasePriceHelper.php';
            LvtSupplierBasePriceHelper::syncForProduct($existId, $p, $canonicalKey);

            return $existId;
        }

        $newId = (int) $el->Add($fields);
        if ($newId <= 0) {
            self::debugCurrentSessionNdjson($runId, 'D5', 'MouserCatalogElementHelper.php:617', 'element add failed', [
                'queryPn' => $mouserPartNumber,
                'lastError' => (string) $el->LAST_ERROR,
            ]);
            return 0;
        }
        self::debugCurrentSessionNdjson($runId, 'D4', 'MouserCatalogElementHelper.php:621', 'element added successfully', [
            'newId' => $newId,
            'hasInstructionsInPayload' => array_key_exists('INSTRUCTIONS', $propVals),
        ]);
        if (Loader::includeModule('catalog')) {
            $typeProduct = class_exists(\Bitrix\Catalog\ProductTable::class)
                ? (int) \Bitrix\Catalog\ProductTable::TYPE_PRODUCT
                : 1;
            CCatalogProduct::Add([
                'ID' => $newId,
                'TYPE' => $typeProduct,
                'QUANTITY' => 0,
                'QUANTITY_TRACE' => 'N',
                'AVAILABLE' => 'Y',
                'CAN_BUY_ZERO' => 'Y',
            ]);
        }
        GetchipsCatalogOffersHelper::syncCatalogQuantityFromSupplierOffers($newId, $canonicalKey);
        self::syncCatalogQuantityFromMouserPart($newId, $p);
        require_once __DIR__ . '/LvtSupplierBasePriceHelper.php';
        LvtSupplierBasePriceHelper::syncForProduct($newId, $p, $canonicalKey);

        return $newId;
    }

    /**
     * @param array<string, mixed> $propVals
     */
    private static function setPropByCode(int $iblockId, array &$propVals, string $code, string $value): void
    {
        $code = trim($code);
        if ($code === '' || $value === '') {
            return;
        }
        $db = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code]);
        $prop = $db->Fetch();
        if (!is_array($prop)) {
            return;
        }

        if (($prop['PROPERTY_TYPE'] ?? '') === 'F') {
            return;
        }

        $propVals[$code] = $value;
    }

    /**
     * @param array<string, mixed> $propVals
     * @param list<string> $referers
     */
    private static function attachDatasheetPdfToInstructions(int $iblockId, int $elementId, array &$propVals, string $datasheetUrl, array $referers, string $queryPn, string $runId = 'ds-unknown'): void
    {
        $datasheetUrl = trim($datasheetUrl);
        if ($datasheetUrl === '' || !preg_match('#^https?://#i', $datasheetUrl)) {
            return;
        }
        if (!function_exists('curl_init')) {
            return;
        }

        $existingByHash = self::existingInstructionHashes($iblockId, $elementId);
        $download = self::downloadDatasheetPdfToFileArray($datasheetUrl, $referers);
        if (empty($download['ok']) || !is_array($download['file'] ?? null) || empty($download['hash'])) {
            return;
        }

        $newHash = (string) $download['hash'];
        if (isset($existingByHash[$newHash])) {
            return;
        }

        $instructionCodeExists = false;
        $db = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => 'INSTRUCTIONS']);
        if ($db->Fetch()) {
            $instructionCodeExists = true;
        }
        self::debugCurrentSessionNdjson($runId, 'D3', 'MouserCatalogElementHelper.php:769', 'instruction property existence check', [
            'iblockId' => $iblockId,
            'instructionCodeExists' => $instructionCodeExists,
        ]);
        if (!$instructionCodeExists) {
            return;
        }

        $instructionsPayload = [];
        foreach ($existingByHash as $fileId) {
            if ((int) $fileId > 0) {
                $instructionsPayload[] = ['VALUE' => (int) $fileId];
            }
        }
        $instructionsPayload[] = ['VALUE' => $download['file']];
        $propVals['INSTRUCTIONS'] = $instructionsPayload;
    }

    /**
     * @return array<string, int> hash => fileId
     */
    private static function existingInstructionHashes(int $iblockId, int $elementId): array
    {
        $out = [];
        if ($iblockId <= 0 || $elementId <= 0) {
            return $out;
        }
        $res = CIBlockElement::GetProperty($iblockId, $elementId, ['ID' => 'ASC'], ['CODE' => 'INSTRUCTIONS']);
        while ($row = $res->Fetch()) {
            $fid = (int) ($row['VALUE'] ?? 0);
            if ($fid <= 0) {
                continue;
            }
            $path = (string) CFile::GetPath($fid);
            if ($path === '' || strpos($path, '/') !== 0) {
                continue;
            }
            $abs = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/') . $path;
            if (!is_file($abs)) {
                continue;
            }
            $h = @hash_file('sha256', $abs);
            if (is_string($h) && $h !== '') {
                $out[$h] = $fid;
            }
        }

        return $out;
    }

    /**
     * @param list<string> $referers
     * @return array{ok:bool, file?:array<string,mixed>, hash?:string, error?:string}
     */
    private static function downloadDatasheetPdfToFileArray(string $url, array $referers): array
    {
        $refs = array_values(array_filter(array_unique($referers), static function ($s) {
            return is_string($s) && $s !== '' && preg_match('#^https?://#i', $s);
        }));
        if ($refs === []) {
            $refs = ['https://www.mouser.com/'];
        }

        $chain = [null];
        $mouserClient = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/') . '/mouser/mouser_client.php';
        if (is_file($mouserClient)) {
            require_once $mouserClient;
            if (function_exists('mouser_get_proxy_chain')) {
                $x = mouser_get_proxy_chain();
                if (is_array($x) && $x !== []) {
                    $chain = $x;
                }
            }
        }

        $body = '';
        $lastErr = 'download failed';
        foreach ($chain as $proxySpec) {
            foreach ($refs as $referer) {
                $ch = curl_init($url);
                if ($ch === false) {
                    continue;
                }
                $opts = [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_CONNECTTIMEOUT => 25,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_REFERER => $referer,
                    CURLOPT_ENCODING => '',
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/pdf,application/octet-stream;q=0.9,*/*;q=0.8',
                        'Accept-Language: en-US,en;q=0.9',
                    ],
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                ];
                if (is_array($proxySpec) && !empty($proxySpec['proxy'])) {
                    $opts[CURLOPT_PROXY] = $proxySpec['proxy'];
                    if (!empty($proxySpec['userpwd'])) {
                        $opts[CURLOPT_PROXYUSERPWD] = $proxySpec['userpwd'];
                    }
                }
                curl_setopt_array($ch, $opts);
                $resp = curl_exec($ch);
                $errno = curl_errno($ch);
                $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $cerr = (string) curl_error($ch);
                curl_close($ch);

                if ($errno !== 0 || !is_string($resp) || $resp === '') {
                    $lastErr = $errno !== 0 ? $cerr : 'empty response';
                    continue;
                }
                if ($http < 200 || $http >= 400) {
                    $lastErr = 'HTTP ' . $http;
                    continue;
                }
                if (strncmp($resp, '%PDF', 4) !== 0) {
                    $lastErr = 'not a pdf body';
                    continue;
                }
                $body = $resp;
                break 2;
            }
        }
        if ($body === '') {
            return ['ok' => false, 'error' => $lastErr];
        }

        $root = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
        if ($root === '') {
            return ['ok' => false, 'error' => 'no document root'];
        }
        $dir = $root . '/upload/tmp_mouser_docs';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return ['ok' => false, 'error' => 'mkdir failed'];
        }
        $hash = (string) hash('sha256', $body);
        if ($hash === '') {
            return ['ok' => false, 'error' => 'hash failed'];
        }
        $name = 'mouser_datasheet_' . $hash . '.pdf';
        $path = $dir . '/' . $name;
        if (@file_put_contents($path, $body) === false) {
            return ['ok' => false, 'error' => 'write failed'];
        }
        $file = CFile::MakeFileArray($path);
        if (!is_array($file) || empty($file['tmp_name'])) {
            $file = [
                'name' => $name,
                'size' => @filesize($path) ?: 0,
                'tmp_name' => $path,
                'type' => 'application/pdf',
                'MODULE_ID' => 'iblock',
            ];
        }

        return ['ok' => true, 'file' => $file, 'hash' => $hash];
    }

    /**
     * If Mouser API has empty DataSheetUrl, try to extract PDF URL from product page.
     *
     * @param array<string, mixed> $part
     */
    private static function resolveDatasheetUrl(array $part, string $runId): string
    {
        $apiUrl = trim((string) ($part['DataSheetUrl'] ?? ''));
        if ($apiUrl !== '' && preg_match('#^https?://#i', $apiUrl)) {
            self::debugCurrentSessionNdjson($runId, 'D6', 'MouserCatalogElementHelper.php:989', 'datasheet url taken from API', [
                'source' => 'api',
                'url' => $apiUrl,
            ]);

            return $apiUrl;
        }

        $productUrl = trim((string) ($part['ProductDetailUrl'] ?? ''));
        if ($productUrl === '' || !preg_match('#^https?://#i', $productUrl)) {
            self::debugCurrentSessionNdjson($runId, 'D6', 'MouserCatalogElementHelper.php:998', 'datasheet fallback skipped: no product url', [
                'productUrl' => $productUrl,
            ]);

            return '';
        }

        $fromPage = self::extractDatasheetUrlFromProductPage($productUrl, $runId);
        self::debugCurrentSessionNdjson($runId, 'D6', 'MouserCatalogElementHelper.php:1006', 'datasheet fallback from product page', [
            'productUrl' => $productUrl,
            'resolvedUrl' => $fromPage,
            'success' => $fromPage !== '',
        ]);

        return $fromPage;
    }

    private static function extractDatasheetUrlFromProductPage(string $productUrl, string $runId): string
    {
        if (!function_exists('curl_init')) {
            return '';
        }

        $chain = [null];
        $mouserClient = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/') . '/mouser/mouser_client.php';
        if (is_file($mouserClient)) {
            require_once $mouserClient;
            if (function_exists('mouser_get_proxy_chain')) {
                $x = mouser_get_proxy_chain();
                if (is_array($x) && $x !== []) {
                    $chain = $x;
                }
            }
        }

        $html = '';
        $http = 0;
        $attempt = 0;
        foreach ($chain as $proxySpec) {
            ++$attempt;
            $ch = curl_init($productUrl);
            if ($ch === false) {
                continue;
            }
            $opts = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_CONNECTTIMEOUT => 25,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_ENCODING => '',
                CURLOPT_REFERER => 'https://www.mouser.com/',
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.9',
                ],
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            ];
            if (is_array($proxySpec) && !empty($proxySpec['proxy'])) {
                $opts[CURLOPT_PROXY] = $proxySpec['proxy'];
                if (!empty($proxySpec['userpwd'])) {
                    $opts[CURLOPT_PROXYUSERPWD] = $proxySpec['userpwd'];
                }
            }
            curl_setopt_array($ch, $opts);
            $resp = curl_exec($ch);
            $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $errno = curl_errno($ch);
            $err = (string) curl_error($ch);
            curl_close($ch);

            self::debugCurrentSessionNdjson($runId, 'D6', 'MouserCatalogElementHelper.php:1065', 'product page fetch attempt', [
                'attempt' => $attempt,
                'http' => $http,
                'errno' => $errno,
                'error' => $err,
                'htmlLen' => is_string($resp) ? strlen($resp) : 0,
            ]);

            if ($errno === 0 && is_string($resp) && $resp !== '' && $http >= 200 && $http < 400) {
                $html = $resp;
                break;
            }
        }

        if ($html === '') {
            return '';
        }

        $candidates = [];
        if (preg_match_all('#https?:\\\\?/\\\\?/[^"\\\'<\\s]+?\\.pdf(?:\\?[^"\\\'<\\s]*)?#iu', $html, $mEsc)) {
            foreach ($mEsc[0] as $u) {
                $candidates[] = str_replace('\\/', '/', html_entity_decode((string) $u, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
            }
        }
        if (preg_match_all('#https?://[^"\\\'<\\s]+?\\.pdf(?:\\?[^"\\\'<\\s]*)?#iu', $html, $mRaw)) {
            foreach ($mRaw[0] as $u) {
                $candidates[] = html_entity_decode((string) $u, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
        }

        $candidates = array_values(array_unique(array_filter(array_map('trim', $candidates), static function ($u) {
            return preg_match('#^https?://#i', $u) === 1;
        })));

        self::debugCurrentSessionNdjson($runId, 'D6', 'MouserCatalogElementHelper.php:1090', 'datasheet url candidates from product page', [
            'count' => count($candidates),
            'first' => $candidates[0] ?? '',
        ]);

        foreach ($candidates as $u) {
            if (stripos($u, 'datasheet') !== false || stripos($u, '.pdf') !== false) {
                return $u;
            }
        }

        return $candidates[0] ?? '';
    }

    private static function guessPropertyCodeFromAttributeName(string $nameEn): string
    {
        $mapPath = dirname(__DIR__) . '/mouser_attribute_to_property_code.php';
        if (is_file($mapPath)) {
            $m = include $mapPath;
            if (is_array($m)) {
                $k = mb_strtolower(trim($nameEn));
                if (isset($m[$k]) && is_string($m[$k]) && $m[$k] !== '') {
                    return $m[$k];
                }
            }
        }

        return '';
    }

    /**
     * «Кол-во в стандартной упаковке» / Factory pack → normoupakovka.
     *
     * @param list<array<string, mixed>> $attrs
     */
    private static function normPackFromProductAttributes(array $attrs): string
    {
        $labels = [
            'кол-во в стандартной упаковке',
            'количество в стандартной упаковке',
            'factory pack quantity',
            'factory pack qty',
            'standard pack qty',
            'standard pack quantity',
        ];
        foreach ($attrs as $a) {
            if (!is_array($a)) {
                continue;
            }
            $n = mb_strtolower(trim((string) ($a['AttributeName'] ?? $a['Name'] ?? '')), 'UTF-8');
            $v = trim((string) ($a['AttributeValue'] ?? $a['Value'] ?? ''));
            if ($n === '' || $v === '') {
                continue;
            }
            foreach ($labels as $lab) {
                if ($n === $lab) {
                    return $v;
                }
            }
        }

        return '';
    }

    private static function makeElementCode(string $mouserPn, string $mpn): string
    {
        $base = $mpn !== '' ? $mpn : $mouserPn;
        $code = \CUtil::translit($base, 'en', ['replace_space' => '-', 'replace_other' => '-']);
        $code = preg_replace('/-+/', '-', (string) $code);
        $code = trim((string) $code, '-');
        if ($code === '') {
            $code = 'mouser-' . substr(md5($mouserPn), 0, 10);
        }

        return $code . '-' . substr(md5($mouserPn), 0, 6);
    }

    /**
     * @param list<array<string, mixed>> $attrs ProductAttributes из Mouser API
     */
    private static function mergeMouserRawSpecsDetail(string $description, array $attrs): string
    {
        $marker = '<!--lvt-mouser-specs-->';
        $html = '';
        if ($attrs !== []) {
            $rows = '';
            foreach ($attrs as $a) {
                if (!is_array($a)) {
                    continue;
                }
                $n = trim((string) ($a['AttributeName'] ?? $a['Name'] ?? ''));
                $v = trim((string) ($a['AttributeValue'] ?? $a['Value'] ?? ''));
                if ($n === '' || $v === '') {
                    continue;
                }
                $label = htmlspecialchars(MouserPartOffersHelper::attributeNameToRu($n), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $val = htmlspecialchars(MouserPartOffersHelper::attributeValueToRu($n, $v), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $rows .= '<tr><th scope="row">' . $label . '</th><td>' . $val . '</td></tr>';
            }
            if ($rows !== '') {
                $html = $marker . "\n"
                    . '<div class="lvt-mouser-specs"><h4>Характеристики (Mouser)</h4>'
                    . '<table class="lvt-mouser-specs__table"><tbody>' . $rows . '</tbody></table></div>';
            }
        }
        $base = trim($description);
        if ($html === '') {
            return $base;
        }
        if (strpos($base, $marker) !== false) {
            return preg_replace('/' . preg_quote($marker, '/') . '[\s\S]*$/u', $html, $base) ?? ($base . $html);
        }

        return ($base !== '' ? $base . "\n\n" : '') . $html;
    }

    /**
     * Referer для обхода 403 на images.mouser.com (лог: Referer главной недостаточен — нужна страница товара).
     *
     * @param array<string, mixed> $p
     * @return list<string>
     */
    private static function mouserImageRefererCandidates(array $p): array
    {
        $out = [];
        $u = trim((string) ($p['ProductDetailUrl'] ?? ''));
        if ($u !== '' && preg_match('#^https?://#i', $u)) {
            $out[] = $u;
            if (stripos($u, 'https://eu.mouser.com') === 0) {
                $out[] = str_ireplace('https://eu.mouser.com', 'https://www.mouser.com', $u);
            }
            if (stripos($u, 'https://www.mouser.com') === 0) {
                $out[] = str_ireplace('https://www.mouser.com', 'https://eu.mouser.com', $u);
            }
        }
        $out[] = 'https://www.mouser.com/';

        return array_values(array_unique(array_filter($out, static function ($s) {
            return $s !== '';
        })));
    }

    /**
     * @param mixed $imagePath поле ImagePath из Mouser API
     * @param list<string> $refererCandidates
     */
    private static function imageFileArrayFromMouserImagePath($imagePath, array $refererCandidates = []): ?array
    {
        $img = trim((string) $imagePath);
        if ($img === '') {
            return null;
        }
        if (strpos($img, '//') === 0) {
            $img = 'https:' . $img;
        } elseif (isset($img[0]) && $img[0] === '/') {
            $img = 'https://www.mouser.com' . $img;
        }
        if (!preg_match('#^https?://#i', $img)) {
            return null;
        }
        $fa = self::remoteImageToFileArray($img, $refererCandidates);
        if (is_array($fa) && !empty($fa['tmp_name'])) {
            return $fa;
        }
        $faUrl = @\CFile::MakeFileArray($img);
        if (is_array($faUrl) && !empty($faUrl['tmp_name'])) {
            return $faUrl;
        }

        return null;
    }

    private static function saveDownloadedMouserImageBody(string $url, string $body): ?array
    {
        $root = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
        if ($root === '') {
            return null;
        }
        $dir = $root . '/upload/tmp_mouser_img';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return null;
        }

        $ext = '.jpg';
        if (preg_match('#\.(jpe?g|png|gif|webp)(\?|$)#i', $url, $m)) {
            $ext = '.' . strtolower($m[1]);
        }
        $baseName = 'mouser_' . md5($url) . '_' . preg_replace('/\W/', '', uniqid('i', true)) . $ext;
        $path = $dir . '/' . $baseName;
        if (@file_put_contents($path, $body) === false) {
            return null;
        }

        $magicExt = '';
        if (strncmp($body, "\xFF\xD8", 2) === 0) {
            $magicExt = '.jpg';
        } elseif (strncmp($body, "\x89PNG\r\n\x1a\n", 8) === 0) {
            $magicExt = '.png';
        } elseif (strncmp($body, 'RIFF', 4) === 0 && strpos($body, 'WEBP', 4) !== false) {
            $magicExt = '.webp';
        } elseif (strncmp($body, 'GIF8', 4) === 0) {
            $magicExt = '.gif';
        }
        if ($magicExt !== '' && $magicExt !== $ext) {
            $newPath = $dir . '/' . 'mouser_' . md5($url) . '_' . preg_replace('/\W/', '', uniqid('m', true)) . $magicExt;
            if (@rename($path, $newPath)) {
                $path = $newPath;
                $baseName = basename($path);
                $ext = $magicExt;
            }
        }

        $fa = \CFile::MakeFileArray($path);
        if (!is_array($fa) || empty($fa['tmp_name'])) {
            $size = @filesize($path) ?: 0;
            $mime = 'image/jpeg';
            if (stripos($ext, 'png') !== false) {
                $mime = 'image/png';
            } elseif (stripos($ext, 'gif') !== false) {
                $mime = 'image/gif';
            } elseif (stripos($ext, 'webp') !== false) {
                $mime = 'image/webp';
            }
            $fa = [
                'name' => $baseName,
                'size' => $size,
                'tmp_name' => $path,
                'type' => $mime,
                'MODULE_ID' => 'iblock',
            ];
        }

        if (is_array($fa) && !empty($fa['tmp_name'])) {
            return $fa;
        }
        @unlink($path);

        return null;
    }

    /**
     * @param list<string> $refererCandidates
     */
    private static function remoteImageToFileArray(string $url, array $refererCandidates = []): ?array
    {
        $url = trim($url);
        if ($url === '' || !preg_match('#^https?://#i', $url)) {
            return null;
        }
        if (!function_exists('curl_init')) {
            return null;
        }

        $mouserClient = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/') . '/mouser/mouser_client.php';
        if (is_file($mouserClient)) {
            require_once $mouserClient;
        }
        if (function_exists('mouser_fetch_image_body')) {
            $fr = mouser_fetch_image_body($url, $refererCandidates);
            if (!empty($fr['ok']) && isset($fr['body']) && is_string($fr['body']) && $fr['body'] !== '') {
                $saved = self::saveDownloadedMouserImageBody($url, $fr['body']);
                if ($saved !== null) {
                    return $saved;
                }
            }
        }

        $refs = array_values(array_filter(array_unique($refererCandidates), static function ($s) {
            return is_string($s) && $s !== '' && preg_match('#^https?://#i', $s);
        }));
        if ($refs === []) {
            $refs = ['https://www.mouser.com/'];
        }
        $body = null;
        $code = 0;
        foreach ($refs as $referer) {
            $ch = curl_init($url);
            if ($ch === false) {
                continue;
            }
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_CONNECTTIMEOUT => 25,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_REFERER => $referer,
                CURLOPT_ENCODING => '',
                CURLOPT_HTTPHEADER => [
                    'Accept: image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.9',
                    'Sec-Fetch-Dest: image',
                    'Sec-Fetch-Mode: no-cors',
                    'Sec-Fetch-Site: cross-site',
                ],
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ]);
            $body = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($body !== false && $code >= 200 && $code < 400 && $body !== '') {
                break;
            }
            $body = null;
        }
        if ($body === null || $body === '') {
            return null;
        }

        return self::saveDownloadedMouserImageBody($url, $body);
    }
}
