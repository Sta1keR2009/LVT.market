<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();

if ($arParams['ELEMENT_IN_ROW'] === 'FROM_MODULE') {
	$arParams['ELEMENT_IN_ROW'] = $arParams['THEME']['ELEMENT_IN_ROW'];
}

if ($arParams['ITEMS_TYPE'] === 'FROM_MODULE') {
	$arParams['ITEMS_TYPE'] = $arParams["THEME"]['ITEMS_TYPE'];
}

$bItemsTypeAlbums = $arParams['ITEMS_TYPE'] != "PHOTOS";

if($bItemsTypeAlbums){
	foreach($arResult['ITEMS'] as $key => &$arItem){
		TSolution::getFieldImageData($arItem, array('PREVIEW_PICTURE'));
	}
	unset($arItem);
}
else{
	// get all photos of all items for new pagination
	$arNewPhotos = $arPhotosIDs = array();

	$this->__component->AbortResultCache();

	$arSort = array(
		$arParams['SORT_BY1'] => $arParams['SORT_ORDER1'],
		$arParams['SORT_BY2'] => $arParams['SORT_ORDER2'],
		'CACHE' => array(
			'TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID']),
			'MULTI' => 'Y',
		),
	);
	if(!array_key_exists('ID', $arSort)){
		$arSort['ID'] = 'DESC';
	}

	$arFilter = array_merge(
		array(
			'IBLOCK_ID' => $arParams['IBLOCK_ID'],
			'ACTIVE' => 'Y',
		),
		(array)$GLOBALS[$arParams['FILTER_NAME']]
	);

	$arItems = TSolution\Cache::CIBlockElement_GetList(
		$arSort,
		$arFilter,
		false,
		false,
		array(
			'ID',
			'IBLOCK_ID',
			'NAME',
			'DETAIL_PAGE_URL',
			'PROPERTY_PHOTOS',
		)
	);
	$arResult['ITEMS'] = array();
	foreach($arItems as $arItem){
		$arButtons = CIBlock::GetPanelButtons(
			$arItem['IBLOCK_ID'],
			$arItem['ID'],
			0,
			array('SECTION_BUTTONS' => false, 'SESSID' => false)
		);
		$arItem['EDIT_LINK'] = $arButtons['edit']['edit_element']['ACTION_URL'];
		$arItem['DELETE_LINK'] = $arButtons['edit']['delete_element']['ACTION_URL'];

		$arItem['PROPERTIES'] = array();
		$arResult['ITEMS'][] = $arItem;

		if($arItem['PROPERTY_PHOTOS_VALUE']){
			$arPhotosIDs = array_merge($arPhotosIDs, (array)$arItem['PROPERTY_PHOTOS_VALUE']);
		}
	}

	// new pagination
	$rsPhotos = new CDBResult;
	$rsPhotos->InitFromArray($arPhotosIDs);
	$rsPhotos->NavStart(
		array(
			'nPageSize' => $arParams['NEWS_COUNT'],
			'bDescPageNumbering' => $arParams['PAGER_DESC_NUMBERING'],
			'bShowAll' => $arParams['PAGER_SHOW_ALL'],
		)
	);

	if($arParams['DISPLAY_TOP_PAGER'] || $arParams['DISPLAY_BOTTOM_PAGER']){
		$arResult['NAV_STRING'] = $rsPhotos->GetPageNavStringEx(
			$navComponentObject,
			$arParams['PAGER_TITLE'],
			$arParams['PAGER_TEMPLATE'],
			$arParams['PAGER_SHOW_ALWAYS'],
			$this->__component,
			$arResult['NAV_PARAM']
		);
	}
	else{
		$arResult['NAV_STRING'] = '';
	}
	// new photos of current page
	while($photoId = $rsPhotos->Fetch()){
		$arNewPhotos[] = $photoId;
	}
	$arNewItems = $arAllItems = array();
	foreach($arResult['ITEMS'] as $key => $arItem){
		if($arPhotos = (array_key_exists('VALUE', (array)$arItem['PROPERTIES']['PHOTOS']) && $arItem['PROPERTIES']['PHOTOS']['VALUE']) ? (array)$arItem['PROPERTIES']['PHOTOS']['VALUE'] : (array)$arItem['PROPERTY_PHOTOS_VALUE']){
			foreach($arPhotos as $photoId){
				if(in_array($photoId, $arNewPhotos)){
					$arImage = CFile::GetFileArray($photoId);
					$arNewItem = $arItem;
					$arNewItem['~PREVIEW_PICTURE'] = $photoId;
					$arNewItem['PREVIEW_PICTURE'] = $arNewItem['FIELDS']['PREVIEW_PICTURE'] = $arImage;
					CLite::getFieldImageData($arNewItem, array('PREVIEW_PICTURE'));
					$arNewItems[] = $arNewItem;
				}
			}
		}
	}
	$arResult['ITEMS'] = $arNewItems;
}
if($arParams['HIDE_PAGINATION'] === 'Y'){
	unset($arResult['NAV_STRING']);
}
?>
