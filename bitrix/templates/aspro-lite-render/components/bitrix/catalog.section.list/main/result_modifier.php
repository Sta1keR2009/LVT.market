<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<?
if (is_array($arParams['THEME'])) {
    if ($arParams['BORDERED'] == 'FROM_THEME') {
        $arParams['BORDERED'] = $arParams['THEME']['BORDERED'] ?? $arParams['BORDERED'];
    }
    if ($arParams['ELEMENTS_IN_ROW'] == 'FROM_THEME') {
        $arParams['ELEMENTS_IN_ROW'] = $arParams['THEME']['ELEMENTS_IN_ROW'] ?? $arParams['ELEMENTS_IN_ROW'];
    }
    if ($arParams['LINES_COUNT'] == 'FROM_THEME') {
        $arParams['LINES_COUNT'] = $arParams['THEME']['LINES_COUNT'] ?? $arParams['LINES_COUNT'];
    }
    if ($arParams['IMAGES'] == 'FROM_THEME') {
        $arParams['IMAGES'] = $arParams['THEME']['IMAGES'] ?? $arParams['IMAGES'];
    }
}

if (
    $arResult['SECTIONS']
    && in_array('PICTURE', (array)$arParams['SECTION_FIELDS'], true)
    && \Bitrix\Main\Loader::includeModule('iblock')
) {
    foreach ($arResult['SECTIONS'] as &$arSection) {
        $bHasSectionPicture = (
            (is_array($arSection['PICTURE']) && (int)$arSection['PICTURE']['ID'] > 0)
            || (int)$arSection['~PICTURE'] > 0
        );

        if ($bHasSectionPicture) {
            continue;
        }

        $iSectionId = (int)$arSection['ID'];
        $iIblockId = (int)($arSection['IBLOCK_ID'] ?: $arParams['IBLOCK_ID']);

        if (!$iSectionId || !$iIblockId) {
            continue;
        }

        $arBaseFilter = [
            'IBLOCK_ID' => $iIblockId,
            'SECTION_ID' => $iSectionId,
            'INCLUDE_SUBSECTIONS' => 'Y',
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
            'CHECK_PERMISSIONS' => 'Y',
            'MIN_PERMISSION' => 'R',
        ];

        $iPictureId = 0;

        $rsElement = CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            $arBaseFilter + ['!PREVIEW_PICTURE' => false],
            false,
            ['nTopCount' => 1],
            ['ID', 'PREVIEW_PICTURE']
        );
        if ($arElement = $rsElement->Fetch()) {
            $iPictureId = (int)$arElement['PREVIEW_PICTURE'];
        }

        if (!$iPictureId) {
            $rsElement = CIBlockElement::GetList(
                ['SORT' => 'ASC', 'ID' => 'ASC'],
                $arBaseFilter + ['!DETAIL_PICTURE' => false],
                false,
                ['nTopCount' => 1],
                ['ID', 'DETAIL_PICTURE']
            );
            if ($arElement = $rsElement->Fetch()) {
                $iPictureId = (int)$arElement['DETAIL_PICTURE'];
            }
        }

        if ($iPictureId) {
            $arPicture = CFile::GetFileArray($iPictureId);
            if ($arPicture) {
                $arSection['~PICTURE'] = $iPictureId;
                $arSection['PICTURE'] = $arPicture;
            }
        }
    }
    unset($arSection);
}

if ($arResult['SECTIONS']) {
    if ($arParams['LINES_COUNT'] && $arParams['LINES_COUNT'] != 'ALL' && $arParams['ELEMENTS_IN_ROW']) {
        array_splice($arResult['SECTIONS'], $arParams['LINES_COUNT'] * $arParams['ELEMENTS_IN_ROW']);
    }
}
?>