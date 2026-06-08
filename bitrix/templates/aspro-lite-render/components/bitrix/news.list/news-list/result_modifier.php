<?
$arSectionsIDs = array();
foreach ($arResult['ITEMS'] as $key => &$arItem) {
    $arItem['DETAIL_PAGE_URL'] = TSolution::FormatNewsUrl($arItem);
    
	TSolution::getFieldImageData($arResult['ITEMS'][$key], array('PREVIEW_PICTURE'));
    if ($SID = $arItem['IBLOCK_SECTION_ID']) {
        $arSectionsIDs[] = $SID;
    }
}
unset($arItem);

if ($arSectionsIDs && $arParams['SHOW_SECTION_NAME'] == 'Y') {
    $arResult['SECTIONS'] = TSolution\Cache::CIBLockSection_GetList(array('SORT' => 'ASC', 'NAME' => 'ASC', 'CACHE' => array('TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 'GROUP' => 'ID', 'MULTI' => 'N')), array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ID' => $arSectionsIDs, 'ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y', 'ACTIVE_DATE' => 'Y'), false, array('ID', 'NAME'));
}
?>