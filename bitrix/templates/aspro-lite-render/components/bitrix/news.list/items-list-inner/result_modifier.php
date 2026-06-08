<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();

$arParams = array_merge(
	array(
		'ROW_VIEW' => false,
		'BORDER' => false,
		'ITEM_HOVER_SHADOW' => false,
		'DARK_HOVER' => false,
		'ROUNDED' => true,
		'ROUNDED_IMAGE' => false,
		'ITEM_PADDING' => false,
		'ELEMENTS_ROW' => 1,
		'MAXWIDTH_WRAP' => false,
		'MOBILE_SCROLLED' => false,
		'NARROW' => false,
		'ITEMS_OFFSET' => true,
		'IMAGES' => 'PICTURES',
		'IMAGE_POSITION' => 'TOP',
		'SHOW_PREVIEW' => true,
		'SHOW_TITLE' => false,
		'SHOW_SECTION' => 'Y',
		'PRICE_POSITION' => 'BOTTOM',
		'TITLE_POSITION' => '',
		'TITLE' => '',
		'RIGHT_TITLE' => '',
		'RIGHT_LINK' => '',
		'NAME_SIZE' => 18,
		'SUBTITLE' => '',
		'SHOW_PREVIEW_TEXT' => 'N',
		'IS_AJAX' => false,
	),
	$arParams
);

$arSections = $arSectionsIDs = array();

foreach($arResult['ITEMS'] as $key => &$arItem){
	$arItem['DETAIL_PAGE_URL'] = TSolution::FormatNewsUrl($arItem);

	$arItem['MIDDLE_PROPS'] = array();
	if($arItem['DISPLAY_PROPERTIES']){
		foreach($arItem['DISPLAY_PROPERTIES'] as $key2 => $arProp){
			if(($key2 === 'EMAIL' || $key2 === 'PHONE'|| $key2 === 'SITE') && $arProp['VALUE']){
				$arItem['MIDDLE_PROPS'][$key2] = $arProp;
				unset($arItem['DISPLAY_PROPERTIES'][$key2]);
			}
		}
	}

	$arItem['FORMAT_PROPS'] = TSolution::PrepareItemProps($arItem['DISPLAY_PROPERTIES']);

	TSolution::getFieldImageData($arItem, array('PREVIEW_PICTURE'));

	if($arItem['IBLOCK_SECTION_ID']){
		$dbRes = CIBlockElement::GetElementGroups($arItem['ID'], true, array('ID'));
		while($arSection = $dbRes->Fetch()){
			$arItem['SECTIONS'][$arSection['ID']] = $arSection['ID'];
			$arSectionsIDs[$arSection['ID']] = $arSection['ID'];
		}
	}
}
unset($arItem);

if($arSectionsIDs){
	$arSections = TSolution\Cache::CIBLockSection_GetList(
		array(
			'SORT' => 'ASC',
			'NAME' => 'ASC',
			'CACHE' => array(
				'TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID']),
				'GROUP' => array('ID'),
				'MULTI' => 'N',
				'RESULT' => array('NAME')
			)
		),
		array('ID' => $arSectionsIDs),
		false,
		array(
			'ID',
			'NAME'
		)
	);

	foreach($arResult['ITEMS'] as $key => &$arItem){
		if($arItem['IBLOCK_SECTION_ID']){
			foreach($arItem['SECTIONS'] as $id => $name){
				$arItem['SECTIONS'][$id] = $arSections[$id];
			}
		}
	}
	unset($arItem);
}

if($arParams['HIDE_PAGINATION'] === 'Y'){
	unset($arResult['NAV_STRING']);
}
?>