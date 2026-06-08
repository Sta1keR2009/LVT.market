<?
$arSectionsIDs = array();
foreach ($arResult['ITEMS'] as $key => &$arItem) {
    $arItem['DETAIL_PAGE_URL'] = TSolution::FormatNewsUrl($arItem);
    
	TSolution::getFieldImageData($arResult['ITEMS'][$key], array('PREVIEW_PICTURE'));
    if ($SID = $arItem['IBLOCK_SECTION_ID']) {
        $arSectionsIDs[] = $SID;
    }

    if($arItem['DISPLAY_PROPERTIES']){
        $arItem['VIDEO'] = $arResult['VIDEO_IFRAME'] = [];

        foreach($arItem['DISPLAY_PROPERTIES'] as $PCODE => $arProp){
            if(
                $arProp["VALUE"] ||
                strlen($arProp["VALUE"])
            ){
                if($arProp['USER_TYPE'] === 'video') {
                    if(count($arProp['PROPERTY_VALUE_ID']) >= 1) {
                        foreach($arProp['VALUE'] as $val){
                            if($val['path']){
                                $arItem['VIDEO'][] = $val;
                            }
                        }
                    }
                    elseif($arProp['VALUE']['path']){
                        $arItem['VIDEO'][] = $arProp['VALUE'];
                    }
                }
                elseif($arProp['CODE'] === 'VIDEO_IFRAME'){
                    $arItem['VIDEO'] = array_merge($arItem['VIDEO'], $arProp["~VALUE"]);
                }
                elseif($arProp['CODE'] === 'POPUP_VIDEO'){
                    $arItem['POPUP_VIDEO'] = $arProp["VALUE"];
                }
            }
        }
    }
}
unset($arItem);

if ($arSectionsIDs && $arParams['SHOW_SECTION_NAME'] == 'Y') {
    $arResult['SECTIONS'] = TSolution\Cache::CIBLockSection_GetList(array('SORT' => 'ASC', 'NAME' => 'ASC', 'CACHE' => array('TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 'GROUP' => 'ID', 'MULTI' => 'N')), array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ID' => $arSectionsIDs, 'ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y', 'ACTIVE_DATE' => 'Y'), false, array('ID', 'NAME'));
}
?>