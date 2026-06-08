<?php
/**
 * Обогащение IB41 из ETM API по свойству «Код товара» (kod_tovara_, №2568).
 * Один GET /goods/{id} на товар: связи, описание; характеристики — по возможности.
 *
 * CLI: --etm_id=1930662 (только API)
 *      --etm-ids=1,2 --max=10
 *      --skip-enriched — пропуск с непустым ETM_VARIANT_CODES
 */

require_once __DIR__ . '/_etm_ib40_cron_bootstrap.php';
require_once dirname(__DIR__) . '/includes/etm_element_code.php';
require_once dirname(__DIR__) . '/includes/etm_iblock_properties.php';
require_once dirname(__DIR__) . '/includes/etm_iblock_section_tree.php';
require_once dirname(__DIR__) . '/includes/etm_enrich_mapping.php';

$maxItems = 3600;
$doReset = false;
$dryRun = false;
$skipEnriched = false;
$singleId = '';
$etmIdsFilter = null;

foreach ($argv ?? [] as $arg) {
    if (preg_match('/^--max=(\d+)$/', $arg, $m)) {
        $maxItems = (int)$m[1];
    }
    if ($arg === '--reset') {
        $doReset = true;
    }
    if ($arg === '--dry') {
        $dryRun = true;
    }
    if ($arg === '--skip-enriched') {
        $skipEnriched = true;
    }
    if (preg_match('/^--etm_id=(.+)$/', $arg, $m)) {
        $singleId = trim($m[1]);
    }
    if (preg_match('/^--etm-ids=(.+)$/', $arg, $m)) {
        $etmIdsFilter = array_map('strval', array_filter(array_map('trim', explode(',', $m[1]))));
    }
}

$iblockId = (int)API_ETM_IBLOCK_ID;
$propEtmCode = API_ETM_PROP_ETM_CODE;
$propLegacy = API_ETM_PROP_ETM_CODE_LEGACY;
$propDesc = API_ETM_PROP_DESC;
$propVariantCodes = 'ETM_VARIANT_CODES';
$propRelatedCodes = 'ETM_RELATED_CODES';
$propPurchasedCodes = 'ETM_PURCHASED_CODES';
$propVariantOptions = 'ETM_VARIANT_OPTIONS';
$propSimilarAttr = 'ETM_SIMILAR_ATTR';
$propAnalogs = 'ETM_ANALOGS';
$propFullAnalogCodes = 'FULL_ANALOG_CODES';
$propInterchangeableAnalogCodes = 'INTERCHANGEABLE_ANALOG_CODES';
$stateFile = API_ETM_ENRICH_STATE;

function ecLoadState(string $file): array {
    if (!is_readable($file)) {
        return ['offset' => 0, 'total_done' => 0];
    }
    $a = json_decode((string)file_get_contents($file), true);
    return is_array($a) ? $a : ['offset' => 0, 'total_done' => 0];
}

function ecSaveState(string $file, array $state): void {
    @file_put_contents($file, json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}

function ecGoodsData(array $goods): array {
    if (!empty($goods['data']) && is_array($goods['data'])) {
        return $goods['data'];
    }
    return is_array($goods) ? $goods : [];
}

function ecExtractRelatedCodes(array $goods): array {
    $d = ecGoodsData($goods);
    $out = [];
    foreach ((array)($d['relatedProducts'] ?? []) as $row) {
        $c = trim((string)($row['gdsCode'] ?? $row['id'] ?? ''));
        if ($c !== '' && preg_match('/^(\d+)/', $c, $m)) {
            $out[] = $m[1];
        }
    }
    return array_values(array_unique($out));
}

function ecExtractPurchasedCodes(array $goods): array {
    $d = ecGoodsData($goods);
    $p = $d['purchased'] ?? null;
    if ($p === false || $p === null) {
        return [];
    }
    $out = [];
    if (is_array($p)) {
        foreach ($p as $row) {
            if (is_array($row)) {
                $c = trim((string)($row['gdsCode'] ?? $row['code'] ?? ''));
            } else {
                $c = trim((string)$row);
            }
            if ($c !== '' && preg_match('/^(\d+)/', $c, $m)) {
                $out[] = $m[1];
            }
        }
    }
    return array_values(array_unique($out));
}

function ecExtractSimilarData(array $goods): array {
    $d = ecGoodsData($goods);
    $similar = $d['similar'] ?? null;
    if (!is_array($similar)) {
        return ['attr' => '', 'codes' => [], 'options' => []];
    }
    $codes = [];
    $options = [];
    $attr = '';
    $extractCode = static function (array $row): string {
        $candidates = [
            (string)($row['gdsCode'] ?? ''),
            (string)($row['code'] ?? ''),
            (string)($row['id'] ?? ''),
            (string)($row['etmCode'] ?? ''),
        ];
        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);
            if ($candidate !== '' && preg_match('/^(\d{3,})/', $candidate, $m)) {
                return $m[1];
            }
        }
        foreach ($row as $cell) {
            if (!is_array($cell)) {
                continue;
            }
            $val = trim((string)($cell['val'] ?? ''));
            if ($val !== '' && preg_match('/^(\d{3,})$/', $val, $m)) {
                return $m[1];
            }
        }
        return '';
    };

    foreach ($similar as $block) {
        if (!is_array($block)) {
            continue;
        }
        $attr = trim((string)($block['name'] ?? $attr));
        $values = $block['values'] ?? $block['rows'] ?? $block['data'] ?? [];
        if (!is_array($values)) {
            continue;
        }
        foreach ($values as $row) {
            if (!is_array($row)) {
                continue;
            }
            $code = $extractCode($row);
            if ($code === '') {
                continue;
            }
            $label = trim((string)($row['name'] ?? $row['val'] ?? ''));
            $selected = (!empty($row['selected']) && ($row['selected'] === true || $row['selected'] === 'Y' || $row['selected'] === '1')) ? 'Y' : 'N';
            $codes[] = $code;
            $options[] = $label . '|' . $code . '|' . $selected;
        }
    }
    return [
        'attr' => $attr,
        'codes' => array_values(array_unique($codes)),
        'options' => $options,
    ];
}

function ecEnsureProperty(int $iblockId, string $code, string $name, string $type = 'S', string $multiple = 'N'): void {
    $exists = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code])->Fetch();
    if ($exists) {
        return;
    }
    $p = new CIBlockProperty();
    $p->Add([
        'IBLOCK_ID' => $iblockId,
        'NAME' => $name,
        'ACTIVE' => 'Y',
        'CODE' => $code,
        'PROPERTY_TYPE' => $type,
        'MULTIPLE' => $multiple,
    ]);
}

function ecSaveGdsChars(int $iblockId, int $elId, array $goods, bool $dryRun): int {
    $d = ecGoodsData($goods);
    $chars = $d['gdsChars'] ?? [];
    if (!is_array($chars) || $chars === []) {
        return 0;
    }
    $propVals = [];
    $foundByName = 0;
    $created = 0;
    foreach ($chars as $row) {
        if (!is_array($row)) {
            continue;
        }
        $name = trim((string)($row['gdsCharName'] ?? ''));
        $val = trim((string)($row['gdsCharVal'] ?? ''));
        if ($name === '' || $val === '') {
            continue;
        }
        $nameKey = etmNormalizeCharNameKeyLocal($name);
        $skipKeys = array_map('etmNormalizeCharNameKeyLocal', ETM_CHAR_HANDLED_SEPARATELY);
        if (in_array($nameKey, $skipKeys, true)) {
            continue;
        }
        $before = etmFindPropertyCodeByCharName($iblockId, $name);
        $code = etmEnsureCharProperty($iblockId, $name);
        if ($code === '') {
            continue;
        }
        if ($before !== null && $before === $code) {
            $foundByName++;
        } elseif ($before === null) {
            $created++;
        }
        $propVals[$code] = $val;
    }
    if (!$dryRun && $propVals !== []) {
        CIBlockElement::SetPropertyValuesEx($elId, $iblockId, $propVals);
    }
    ecLog("  el=$elId характеристик: " . count($propVals) . " (найдено по NAME: $foundByName, создано: $created)");
    return count($propVals);
}

function ecNormalizeEtmMediaUrl(string $url): string {
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    if (strpos($url, '//') === 0) {
        $url = 'https:' . $url;
    } elseif (!preg_match('#^https?://#i', $url)) {
        if ($url[0] === '/') {
            $url = 'https://cdn.etm.ru' . $url;
        } else {
            return '';
        }
    }

    $parts = parse_url($url);
    if ($parts === false || empty($parts['host'])) {
        return $url;
    }
    $path = (string) ($parts['path'] ?? '');
    if ($path !== '') {
        $segments = explode('/', $path);
        $encoded = [];
        foreach ($segments as $segment) {
            if ($segment === '') {
                $encoded[] = '';
                continue;
            }
            $encoded[] = rawurlencode(rawurldecode($segment));
        }
        $path = implode('/', $encoded);
    }
    $out = strtolower((string) ($parts['scheme'] ?? 'https')) . '://' . $parts['host'] . $path;
    if (!empty($parts['query'])) {
        $out .= '?' . $parts['query'];
    }
    if (!empty($parts['fragment'])) {
        $out .= '#' . $parts['fragment'];
    }

    return $out;
}

function ecBuildSeoCode(string $name, string $etmCode): string {
    $base = trim($name);
    if ($base === '') {
        return 'product-' . $etmCode;
    }
    $code = CUtil::translit($base, 'ru', [
        'max_len' => 180,
        'change_case' => 'L',
        'replace_space' => '-',
        'replace_other' => '-',
        'delete_repeat_replace' => true,
    ]);
    $code = strtolower(trim((string)$code, '-'));
    if ($code === '') {
        $code = 'product-' . $etmCode;
    }
    return $code;
}

function ecEnsureUniqueElementCode(int $iblockId, int $elementId, string $baseCode): string {
    $baseCode = trim($baseCode);
    if ($baseCode === '') {
        return '';
    }
    $candidate = $baseCode;
    $i = 1;
    while (true) {
        $exists = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, '=CODE' => $candidate, '!ID' => $elementId],
            false,
            ['nTopCount' => 1],
            ['ID']
        )->Fetch();
        if (!$exists) {
            return $candidate;
        }
        $i++;
        $candidate = $baseCode . '-' . $i;
    }
}

function ecBuildApiVideoMarkup(array $goodsData): array {
    $out = [];
    foreach ((array)($goodsData['gdsVideos'] ?? []) as $video) {
        if (!is_array($video)) {
            continue;
        }
        $url = ecNormalizeEtmMediaUrl((string)($video['gdsVidSrc'] ?? ''));
        if ($url === '') {
            continue;
        }
        $name = htmlspecialchars((string)($video['gdsVidName'] ?? 'Видео'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $src = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $out[] = '<div class="video_from_file ui-card__image"><video class="video-js" preload="metadata" controls="controls"><source src="' . $src . '" type="video/mp4"></video></div><div class="title">' . $name . '</div>';
    }
    return $out;
}

function ecBuildApiImageUrls(array $goodsData): array {
    $out = [];
    foreach ((array)($goodsData['gdsImages'] ?? []) as $image) {
        if (!is_array($image)) {
            continue;
        }
        $url = ecNormalizeEtmMediaUrl((string)($image['gdsImgSrc'] ?? ''));
        if ($url === '') {
            $url = ecNormalizeEtmMediaUrl((string)($image['gdsImgRef'] ?? ''));
        }
        if ($url !== '' && !preg_match('#/small_#i', $url)) {
            $out[] = $url;
        }
    }
    return array_values(array_unique($out));
}

function ecLinkAnalogs(int $iblockId, string $propEtmCode, array $codes, array $codeToId): array {
    $ids = [];
    foreach ($codes as $code) {
        $id = $codeToId[$code] ?? etmFindElementIdByEtmCode($iblockId, $code);
        if ($id > 0) {
            $ids[] = $id;
        }
    }
    return array_values(array_unique($ids));
}

ecLog('=== ETM enrich_chars START (prop=' . $propEtmCode . ' #' . API_ETM_PROP_ETM_CODE_ID . ') ===');

if ($singleId !== '') {
    $client = new ApiEtmClient(ETM_API_URL, ETM_LOGIN, ETM_PASSWORD);
    if (!$client->login()) {
        ecLog('Ошибка авторизации HTTP=' . $client->lastHttpCode, 'ERROR');
        exit(1);
    }
    $goods = $client->getGoods($singleId, 'etm');
    ecLog('etm_id=' . $singleId . ' HTTP=' . $client->lastHttpCode);
    if ($goods) {
        $rel = ecExtractRelatedCodes($goods);
        $sim = ecExtractSimilarData($goods);
        $pur = ecExtractPurchasedCodes($goods);
        ecLog('related=' . count($rel) . ' similar=' . count($sim['codes']) . ' purchased=' . count($pur));
        ecLog('ВНИМАНИЕ: --etm_id только смотрит API. Для записи в Bitrix: --etm-ids=' . $singleId . ' --max=1', 'WARN');
        echo json_encode([
            'related' => $rel,
            'similar' => $sim,
            'purchased' => $pur,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    }
    exit(0);
}

$state = ecLoadState($stateFile);
if ($doReset) {
    $state = ['offset' => 0, 'total_done' => 0];
    ecSaveState($stateFile, $state);
}

ecLog('max=' . $maxItems . ' offset=' . ($state['offset'] ?? 0) . ' dry=' . ($dryRun ? 'Y' : 'N'));

$allElements = [];
$res = CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => $iblockId, '!PROPERTY_' . $propEtmCode => false],
    false,
    false,
    ['ID', 'PROPERTY_' . $propEtmCode]
);
while ($row = $res->Fetch()) {
    $val = trim((string)($row['PROPERTY_' . strtoupper($propEtmCode) . '_VALUE'] ?? ''));
    if ($val !== '') {
        etmMergeEtmCodeMapEntry($iblockId, $allElements, $val, (int)$row['ID']);
    }
}

if (count($allElements) < 100) {
    $res = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $iblockId, '!PROPERTY_' . $propLegacy => false],
        false,
        false,
        ['ID', 'PROPERTY_' . $propLegacy]
    );
    while ($row = $res->Fetch()) {
        $val = trim((string)($row['PROPERTY_' . strtoupper($propLegacy) . '_VALUE'] ?? ''));
        if ($val !== '') {
            etmMergeEtmCodeMapEntry($iblockId, $allElements, $val, (int)$row['ID']);
        }
    }
}

if (is_array($etmIdsFilter) && $etmIdsFilter !== []) {
    $allElements = array_intersect_key($allElements, array_flip($etmIdsFilter));
    $state['offset'] = 0;
    $maxItems = max(1, min($maxItems, count($allElements)));
}

$total = count($allElements);
ecLog('К обработке: ' . $total . ' товаров');

if ($total === 0) {
    ecLog('Нет товаров с ' . $propEtmCode . '. Запустите migrate_etm_code_to_prop2568.php.', 'WARN');
    exit(0);
}

$etmCodes = array_keys($allElements);
$offset = (int)($state['offset'] ?? 0);
if ($offset >= $total) {
    $offset = 0;
    $state['offset'] = 0;
}
$chunk = array_slice($etmCodes, $offset, $maxItems);

$ecRunOffset = $offset;
$ecRunDone = 0;
register_shutdown_function(static function () use (
    &$state,
    $stateFile,
    &$ecRunOffset,
    &$ecRunDone,
    $total
): void {
    if ($ecRunDone <= 0) {
        return;
    }
    $state['offset'] = $ecRunOffset + $ecRunDone;
    if ($state['offset'] >= $total) {
        $state['offset'] = 0;
    }
    $state['total_done'] = (int)($state['total_done'] ?? 0) + $ecRunDone;
    $state['last_run'] = date('Y-m-d H:i:s');
    $state['catalog_total'] = $total;
    ecSaveState($stateFile, $state);
});

$client = new ApiEtmClient(ETM_API_URL, ETM_LOGIN, ETM_PASSWORD);
if (!$client->login()) {
    ecLog('Ошибка авторизации', 'ERROR');
    exit(1);
}

$done = 0;
$errors = 0;
$relationsSaved = 0;

foreach ($chunk as $i => $etmCode) {
    $elId = $allElements[$etmCode];

    if ($skipEnriched && etmGetElementPropValue($iblockId, $elId, $propVariantCodes) !== '') {
        $done++;
        continue;
    }

    if ($i > 0) {
        sleep(1);
    }

    if ($i > 0 && $i % 50 === 0) {
        $state['offset'] = $offset + $done;
        $state['total_done'] = (int)($state['total_done'] ?? 0);
        $state['last_run'] = date('Y-m-d H:i:s');
        ecSaveState($stateFile, $state);
    }

    $goods = $client->getGoods((string)$etmCode, 'etm');
    if (!$goods) {
        $errors++;
        ecLog("etm=$etmCode el=$elId HTTP=" . $client->lastHttpCode, 'WARN');
        continue;
    }

    $related = ecExtractRelatedCodes($goods);
    $purchased = ecExtractPurchasedCodes($goods);
    $similar = ecExtractSimilarData($goods);
    $data = ecGoodsData($goods);
    $desc = trim((string)($data['gdsNameInMnf'] ?? ''));

    if (!$dryRun) {
        ecEnsureProperty($iblockId, $propRelatedCodes, 'ETM: Коды аналогов', 'S', 'Y');
        ecEnsureProperty($iblockId, $propPurchasedCodes, 'ETM: С этим покупают', 'S', 'Y');
        ecEnsureProperty($iblockId, $propVariantCodes, 'ETM: Коды вариантов', 'S', 'Y');
        ecEnsureProperty($iblockId, $propVariantOptions, 'ETM: Варианты', 'S', 'Y');
        ecEnsureProperty($iblockId, $propSimilarAttr, 'ETM: Атрибут вариантов', 'S', 'N');
        ecEnsureProperty($iblockId, $propAnalogs, 'Аналоги', 'E', 'Y');
        ecEnsureProperty($iblockId, $propFullAnalogCodes, 'Полные аналоги', 'S', 'N');
        ecEnsureProperty($iblockId, $propInterchangeableAnalogCodes, 'Функциональные аналоги', 'S', 'N');
        ecEnsureProperty($iblockId, 'ETM_IMAGE_URLS', 'ETM: URL изображений', 'S', 'Y');
        ecEnsureProperty($iblockId, 'ETM_VIDEO_URLS', 'ETM: Видео URL/HTML', 'S', 'Y');
        ecEnsureProperty($iblockId, 'PROIZVODITEL', 'Производитель', 'S', 'N');
        ecEnsureProperty($iblockId, 'BRAND', 'Бренд', 'S', 'N');

        $analogIds = ecLinkAnalogs($iblockId, $propEtmCode, $related, $allElements);
        $analogIdsStr = $analogIds !== [] ? implode(',', $analogIds) : '';
        $variantCodesStr = $similar['codes'] !== [] ? implode(',', $similar['codes']) : '';
        $manufacturer = trim((string)($data['gdsMnfName'] ?? ''));
        $imageUrls = ecBuildApiImageUrls($data);
        $videoMarkup = ecBuildApiVideoMarkup($data);

        $propVals = [
            $propRelatedCodes => $related,
            $propPurchasedCodes => $purchased,
            $propVariantCodes => $similar['codes'],
            $propVariantOptions => $similar['options'],
            $propSimilarAttr => $similar['attr'],
            $propAnalogs => $analogIds,
            $propFullAnalogCodes => $analogIdsStr,
            $propInterchangeableAnalogCodes => $variantCodesStr,
            'ETM_IMAGE_URLS' => $imageUrls,
            'ETM_VIDEO_URLS' => $videoMarkup,
        ];
        CIBlockElement::SetPropertyValuesEx($elId, $iblockId, $propVals);

        $updateFields = [];
        $elementRow = CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, 'ID' => $elId], false, ['nTopCount' => 1], ['ID', 'CODE', 'NAME'])->Fetch();
        if ($elementRow) {
            $currentCode = trim((string)($elementRow['CODE'] ?? ''));
            if ($currentCode === '' || preg_match('/^etm_\d+$/i', $currentCode)) {
                $seoCode = ecBuildSeoCode((string)($elementRow['NAME'] ?? ''), (string)$etmCode);
                $seoCode = ecEnsureUniqueElementCode($iblockId, $elId, $seoCode);
                if ($seoCode !== '' && $seoCode !== $currentCode) {
                    $updateFields['CODE'] = $seoCode;
                }
            }
        }

        if ($imageUrls !== []) {
            $fileArray = etmDownloadUrlToFileArray($imageUrls[0]);
            if (is_array($fileArray) && !empty($fileArray['tmp_name'])) {
                $updateFields['PREVIEW_PICTURE'] = $fileArray;
                $updateFields['DETAIL_PICTURE'] = $fileArray;
            }
        }
        if ($updateFields !== []) {
            $elObj = new CIBlockElement();
            $elObj->Update($elId, $updateFields);
        }

        $morePhotoCnt = etmSaveMorePhotoFromUrls($iblockId, $elId, $imageUrls);
        if ($morePhotoCnt > 0) {
            ecLog("  el=$elId MORE_PHOTO: $morePhotoCnt");
        }

        $tree = $data['gdsClassTree'] ?? [];
        if (is_array($tree) && $tree !== []) {
            $sectionId = etmResolveSectionPathFromClassTree($iblockId, $tree, false);
            if ($sectionId > 0) {
                $elObj = new CIBlockElement();
                $elObj->Update($elId, ['IBLOCK_SECTION_ID' => $sectionId]);
                ecLog("  etm=$etmCode el=$elId раздел ID=$sectionId");
            }
        }

        ecSaveGdsChars($iblockId, $elId, $goods, false);

        $mapStats = etmSaveMappedEnrichFields($iblockId, $elId, $data, false);
        if ($mapStats['article'] || $mapStats['description'] || $mapStats['tn_ved'] || $mapStats['okpd2'] || $mapStats['pipe_html']) {
            ecLog(sprintf(
                '  el=%d mapped: art=%d desc=%d tn=%d okpd=%d html=%d',
                $elId,
                $mapStats['article'],
                $mapStats['description'],
                $mapStats['tn_ved'],
                $mapStats['okpd2'],
                $mapStats['pipe_html']
            ));
        }

        if ($manufacturer !== '') {
            CIBlockElement::SetPropertyValuesEx($elId, $iblockId, [
                'PROIZVODITEL' => $manufacturer,
                'BRAND' => $manufacturer,
            ]);
            ecLog("  el=$elId производитель: $manufacturer");
        }

        if (etmGetElementPropValue($iblockId, $elId, $propEtmCode) === '') {
            CIBlockElement::SetPropertyValuesEx($elId, $iblockId, [$propEtmCode => (string)$etmCode]);
        }
    } else {
        ecLog("  etm=$etmCode el=$elId dry-run: related=" . count($related) . ' similar=' . count($similar['codes']) . ' purchased=' . count($purchased));
    }

    $done++;
    $ecRunDone = $done;
    $relationsSaved++;
}

$state['offset'] = $offset + $done;
if ($state['offset'] >= $total) {
    $state['offset'] = 0;
}
$state['total_done'] = (int)($state['total_done'] ?? 0) + $done;
$state['last_run'] = date('Y-m-d H:i:s');
$state['catalog_total'] = $total;
$ecRunDone = 0;
ecSaveState($stateFile, $state);

ecLog("=== DONE chunk=$done errors=$errors relations=$relationsSaved offset={$state['offset']}/$total total_done={$state['total_done']} ===");
