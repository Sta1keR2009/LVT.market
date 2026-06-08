<?php
/**
 * Маппинг полей ETM API → свойства IB41 (артикул, описание, медиа, pipe→HTML).
 */

declare(strict_types=1);

/** gdsCharName → CODE свойства IB41 для значений с разделителем «|» → HTML <ul> */
const ETM_CHAR_PIPE_HTML_MAP = [
    'Дополнительная информация' => 'DOPOLNITELNAYA_INFORMATSIYA',
    'Комплектация' => 'PROP_284',
    'Сфера применения' => 'SFERA_PRIMENENIYA',
];

/** gdsCharName — не писать через общий ecSaveGdsChars (обрабатываются отдельно) */
const ETM_CHAR_HANDLED_SEPARATELY = [
    'Дополнительная информация',
    'Комплектация',
    'Сфера применения',
    'Артикул',
    'ТН ВЭД',
    'Код ОКПД2',
    'Код ОКПД 2',
];

function etmNormalizeCharNameKeyLocal(string $name): string {
    if (function_exists('etmNormalizeCharNameKey')) {
        return etmNormalizeCharNameKey($name);
    }
    $name = trim($name);
    $name = mb_strtolower($name);
    return preg_replace('/\s+/u', ' ', $name) ?? $name;
}

function etmPipeValueToHtmlList(string $value): string {
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (mb_strpos($value, '|') === false) {
        return $value;
    }
    $parts = array_values(array_filter(array_map('trim', explode('|', $value)), static fn($v) => $v !== ''));
    if ($parts === []) {
        return '';
    }
    $html = '<ul>';
    foreach ($parts as $part) {
        $html .= '<li>' . htmlspecialchars($part, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>';
    }
    return $html . '</ul>';
}

function etmExtractClassCode(array $goodsData, string $classType): string {
    foreach ((array)($goodsData['classes'] ?? []) as $row) {
        if (!is_array($row)) {
            continue;
        }
        if ((string)($row['type'] ?? '') === $classType) {
            return trim((string)($row['name'] ?? ''));
        }
    }
    return '';
}

function etmFindGdsCharValue(array $goodsData, array $charNames): string {
    $needles = [];
    foreach ($charNames as $name) {
        $needles[etmNormalizeCharNameKeyLocal($name)] = true;
    }
    foreach ((array)($goodsData['gdsChars'] ?? []) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $name = etmNormalizeCharNameKeyLocal((string)($row['gdsCharName'] ?? ''));
        if (isset($needles[$name])) {
            return trim((string)($row['gdsCharVal'] ?? ''));
        }
    }
    return '';
}

function etmNormalizeEtmMediaUrl(string $url): string {
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    if (strpos($url, '//') === 0) {
        return 'https:' . $url;
    }
    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }
    if ($url[0] === '/') {
        return 'https://cdn.etm.ru' . $url;
    }
    return '';
}

function etmDownloadUrlToFileArray(string $url): ?array {
    $url = etmNormalizeEtmMediaUrl($url);
    if ($url === '') {
        return null;
    }

    $path = (string)(parse_url($url, PHP_URL_PATH) ?? '');
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg');
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true)) {
        $ext = 'jpg';
    }

    $tmpDir = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/') . '/upload/etm';
    if (!is_dir($tmpDir)) {
        @mkdir($tmpDir, 0755, true);
    }
    $tmpPath = $tmpDir . '/ib41_' . md5($url) . '.' . $ext;

    if (!is_file($tmpPath) || filesize($tmpPath) < 128) {
        $fp = @fopen($tmpPath, 'wb');
        if (!$fp) {
            return null;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'LVT-ETM-Import/1.0',
        ]);
        $ok = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);
        if (!$ok || $httpCode !== 200 || !is_file($tmpPath) || filesize($tmpPath) < 128) {
            @unlink($tmpPath);
            return null;
        }
    }

    $fileArray = CFile::MakeFileArray($tmpPath);
    if (!is_array($fileArray) || empty($fileArray['tmp_name'])) {
        return null;
    }
    $fileArray['MODULE_ID'] = 'iblock';
    return $fileArray;
}

function etmEnsureGalleryProperty(int $iblockId): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $propCode = defined('API_ETM_PROP_PICTURES') ? API_ETM_PROP_PICTURES : 'MORE_PHOTO';
    $prop = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $propCode])->Fetch();
    if (!$prop) {
        return;
    }
    if (($prop['PROPERTY_TYPE'] ?? '') === 'F' && ($prop['MULTIPLE'] ?? '') === 'Y') {
        return;
    }

    $p = new CIBlockProperty();
    $p->Update((int)$prop['ID'], [
        'PROPERTY_TYPE' => 'F',
        'MULTIPLE' => 'Y',
        'FILE_TYPE' => 'jpg, gif, bmp, png, jpeg, webp',
        'WITH_DESCRIPTION' => 'N',
    ]);
}

/**
 * @return int количество загруженных файлов
 */
function etmSaveMorePhotoFromUrls(int $iblockId, int $elementId, array $imageUrls): int {
    if ($imageUrls === []) {
        return 0;
    }
    etmEnsureGalleryProperty($iblockId);
    $propCode = defined('API_ETM_PROP_PICTURES') ? API_ETM_PROP_PICTURES : 'MORE_PHOTO';

    $files = [];
    foreach ($imageUrls as $url) {
        try {
            $fileArray = etmDownloadUrlToFileArray((string)$url);
            if ($fileArray !== null) {
                $files[] = $fileArray;
            }
        } catch (Throwable $e) {
            continue;
        }
    }
    if ($files === []) {
        return 0;
    }
    CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, [$propCode => $files]);
    return count($files);
}

/**
 * Артикул, описание, ТН ВЭД, ОКПД2 + pipe→HTML для выделенных характеристик.
 *
 * @return array<string, string|int> счётчики для лога
 */
function etmSaveMappedEnrichFields(int $iblockId, int $elementId, array $goodsData, bool $dryRun): array {
    $stats = ['article' => 0, 'description' => 0, 'tn_ved' => 0, 'okpd2' => 0, 'pipe_html' => 0, 'more_photo' => 0];

    $article = trim((string)($goodsData['gdsArt'] ?? ''));
    if ($article === '') {
        $article = trim((string)($goodsData['gdsExtArt'] ?? ''));
    }
    if ($article === '') {
        $article = etmFindGdsCharValue($goodsData, ['Артикул']);
    }

    $description = trim((string)($goodsData['description'] ?? ''));
    $tnVed = etmFindGdsCharValue($goodsData, ['ТН ВЭД']);
    if ($tnVed === '') {
        $tnVed = etmExtractClassCode($goodsData, '555');
    }
    $okpd2 = etmFindGdsCharValue($goodsData, ['Код ОКПД2', 'Код ОКПД 2']);
    if ($okpd2 === '') {
        $okpd2 = etmExtractClassCode($goodsData, '546');
    }

    $propVals = [];
    if ($article !== '') {
        $propVals['CML2_ARTICLE'] = $article;
        $propVals['pr_article'] = $article;
        $stats['article'] = 1;
    }
    if ($tnVed !== '') {
        $propCode = defined('API_ETM_PROP_TN_VED') ? API_ETM_PROP_TN_VED : 'TN_VED';
        $propVals[$propCode] = $tnVed;
        $stats['tn_ved'] = 1;
    }
    if ($okpd2 !== '') {
        $propCode = defined('API_ETM_PROP_OKPD2') ? API_ETM_PROP_OKPD2 : 'ETM_KOD_OKPD_2';
        $propVals[$propCode] = $okpd2;
        $stats['okpd2'] = 1;
    }

    foreach (ETM_CHAR_PIPE_HTML_MAP as $charName => $propCode) {
        $raw = etmFindGdsCharValue($goodsData, [$charName]);
        if ($raw === '') {
            continue;
        }
        $propVals[$propCode] = etmPipeValueToHtmlList($raw);
        $stats['pipe_html']++;
    }

    if (!$dryRun) {
        if ($tnVed !== '' && defined('API_ETM_PROP_TN_VED')) {
            $exists = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => API_ETM_PROP_TN_VED])->Fetch();
            if (!$exists) {
                $p = new CIBlockProperty();
                $p->Add([
                    'IBLOCK_ID' => $iblockId,
                    'NAME' => 'ТН ВЭД',
                    'ACTIVE' => 'Y',
                    'CODE' => API_ETM_PROP_TN_VED,
                    'PROPERTY_TYPE' => 'S',
                    'MULTIPLE' => 'N',
                ]);
            }
        }

        if ($propVals !== []) {
            CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, $propVals);
        }

        if ($description !== '') {
            $elObj = new CIBlockElement();
            $elObj->Update($elementId, [
                'DETAIL_TEXT' => $description,
                'DETAIL_TEXT_TYPE' => 'html',
            ]);
            $stats['description'] = 1;
        }
    } elseif ($description !== '') {
        $stats['description'] = 1;
    }

    return $stats;
}
