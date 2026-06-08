<?php
/**
 * Поиск свойства IB по названию характеристики (без создания дублей ETM_*).
 */

declare(strict_types=1);

/** Служебные свойства — не сопоставлять с gdsChars */
const ETM_CHARS_SKIP_PROP_CODES = [
    'kod_tovara_',
    'ID_ELEMENTA',
    'ETM_VARIANT_CODES',
    'ETM_RELATED_CODES',
    'ETM_PURCHASED_CODES',
    'ETM_VARIANT_OPTIONS',
    'ETM_SIMILAR_ATTR',
    'ETM_ANALOGS',
    'ETM_RELATED_PENDING',
    'FULL_ANALOG_CODES',
    'INTERCHANGEABLE_ANALOG_CODES',
    'BRAND',
    'PROIZVODITEL',
    'ETM_IMAGE_URLS',
    'ETM_VIDEO_URLS',
    'DOPOLNITELNAYA_INFORMATSIYA',
    'PROP_284',
    'SFERA_PRIMENENIYA',
    'TN_VED',
    'ETM_KOD_OKPD_2',
    'CML2_ARTICLE',
    'pr_article',
    'MORE_PHOTO',
];

/**
 * @var array<string, string>|null name_key => property CODE
 */
$GLOBALS['_etm_prop_by_name_cache'] = null;

function etmNormalizeCharNameKey(string $name): string {
    $name = trim($name);
    $name = preg_replace('/^ETM:\s*/iu', '', $name);
    $name = mb_strtolower($name);
    $name = preg_replace('/\s+/u', ' ', $name);
    return $name;
}

function etmTranslitPropCode(string $name): string {
    $code = CUtil::translit($name, 'ru', [
        'max_len' => 48,
        'change_case' => 'U',
        'replace_space' => '_',
        'replace_other' => '_',
    ]);
    $code = preg_replace('/[^A-Z0-9_]/', '', strtoupper((string)$code));
    if ($code === '') {
        $code = 'CHAR_' . substr(md5($name), 0, 8);
    }
    return $code;
}

/**
 * Построить индекс свойств IB по нормализованному NAME.
 *
 * @return array<string, array{code:string,id:int,etm:bool,cnt_hint:int}>
 */
function etmBuildPropertyNameIndex(int $iblockId): array {
    $index = [];
    $res = CIBlockProperty::GetList(['ID' => 'ASC'], ['IBLOCK_ID' => $iblockId]);
    while ($p = $res->Fetch()) {
        $code = (string)$p['CODE'];
        if (in_array($code, ETM_CHARS_SKIP_PROP_CODES, true)) {
            continue;
        }
        if (strpos($code, 'ETM_') === 0 && in_array($code, ['ETM_VARIANT_CODES', 'ETM_RELATED_CODES'], true)) {
            continue;
        }
        $key = etmNormalizeCharNameKey((string)$p['NAME']);
        if ($key === '') {
            continue;
        }
        $isEtm = strpos($code, 'ETM_') === 0;
        if (!isset($index[$key])) {
            $index[$key] = [
                'code' => $code,
                'id' => (int)$p['ID'],
                'etm' => $isEtm,
            ];
            continue;
        }
        // Предпочитаем свойство без префикса ETM_
        if ($index[$key]['etm'] && !$isEtm) {
            $index[$key] = [
                'code' => $code,
                'id' => (int)$p['ID'],
                'etm' => false,
            ];
        }
    }
    return $index;
}

function etmGetPropertyNameIndex(int $iblockId): array {
    if (!is_array($GLOBALS['_etm_prop_by_name_cache'])) {
        $GLOBALS['_etm_prop_by_name_cache'] = etmBuildPropertyNameIndex($iblockId);
    }
    return $GLOBALS['_etm_prop_by_name_cache'];
}

function etmResetPropertyNameIndexCache(): void {
    $GLOBALS['_etm_prop_by_name_cache'] = null;
}

/**
 * Найти CODE существующего свойства по названию характеристики из API.
 */
function etmFindPropertyCodeByCharName(int $iblockId, string $charName): ?string {
    $charName = trim($charName);
    if ($charName === '') {
        return null;
    }
    $index = etmGetPropertyNameIndex($iblockId);
    $key = etmNormalizeCharNameKey($charName);
    if (isset($index[$key])) {
        return $index[$key]['code'];
    }
    // Точное совпадение NAME в Bitrix (на случай отличий нормализации)
    $row = CIBlockProperty::GetList(
        [],
        ['IBLOCK_ID' => $iblockId, 'NAME' => $charName]
    )->Fetch();
    if ($row) {
        $code = (string)$row['CODE'];
        if (!in_array($code, ETM_CHARS_SKIP_PROP_CODES, true)) {
            return $code;
        }
    }
    return null;
}

/**
 * Создать свойство только если по NAME не найдено. CODE — транслит без обязательного ETM_.
 */
function etmEnsureCharProperty(int $iblockId, string $charName): string {
    $existing = etmFindPropertyCodeByCharName($iblockId, $charName);
    if ($existing !== null) {
        return $existing;
    }
    $code = etmTranslitPropCode($charName);
    if (CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code])->Fetch()) {
        $code = $code . '_' . substr(md5($charName), 0, 4);
    }
    $p = new CIBlockProperty();
    $newId = $p->Add([
        'IBLOCK_ID' => $iblockId,
        'NAME' => $charName,
        'ACTIVE' => 'Y',
        'CODE' => $code,
        'PROPERTY_TYPE' => 'S',
        'MULTIPLE' => 'N',
        'FILTRABLE' => 'Y',
        'SEARCHABLE' => 'N',
    ]);
    if ($newId) {
        etmResetPropertyNameIndexCache();
        $key = etmNormalizeCharNameKey($charName);
        $GLOBALS['_etm_prop_by_name_cache'][$key] = [
            'code' => $code,
            'id' => (int)$newId,
            'etm' => false,
        ];
    }
    return $code;
}
