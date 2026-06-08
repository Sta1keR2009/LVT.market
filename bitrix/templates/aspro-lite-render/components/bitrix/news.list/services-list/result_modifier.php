<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();

$arParams = array_merge(
	array(
		'ROW_VIEW' => false,
		'BORDER' => true,
		'ITEM_HOVER_SHADOW' => true,
		'ROUNDED' => true,
		'ROUNDED_IMAGE' => true,
		'ELEMENTS_ROW' => 4,
		'MAXWIDTH_WRAP' => false,
		'MOBILE_SCROLLED' => false,
		'NARROW' => true,
		'ITEMS_OFFSET' => true,
		'IMAGES' => 'ROUND_PICTURES',
		'IMAGE_POSITION' => 'TOP',
		'STICKY_IMAGES' => 'N',
		'ITEMS_TYPE' => 'ELEMENTS',
		'SHOW_PREVIEW' => true,
		'HIDE_PAGINATION' => 'N',
		'HIDE_LEFT_TEXT_BLOCK' => 'Y',
		'SHOW_CHILD_SECTIONS' => 'Y',
		'SHOW_CHILD_ELEMENTS' => 'N',
		'SHOW_ELEMENTS_IN_LAST_SECTION' => 'N',
		'SHOW_TITLE' => false,
		'SHOW_SECTION' => 'Y',
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
$bItemsTypeElements = $arParams['ITEMS_TYPE'] !== 'SECTIONS';

if($bItemsTypeElements){
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
}
else{
	unset($arResult['ITEMS']);

	$this->__component->AbortResultCache();

	// get all subsections of PARENT_SECTION or root sections
	$arSections = $arSectionsIDs = $arNewSections = array();
	$arSectionsFilter = array(
		'IBLOCK_ID' => $arParams['IBLOCK_ID'],
		'ACTIVE' => 'Y',
		'GLOBAL_ACTIVE' => 'Y',
		'ACTIVE_DATE' => 'Y'
	);

	if(
		$arParams['FILTER_NAME'] &&
		$GLOBALS[$arParams['FILTER_NAME']] &&
		is_array($GLOBALS[$arParams['FILTER_NAME']]) &&
		$GLOBALS[$arParams['FILTER_NAME']]['PROPERTY_SHOW_ON_INDEX_PAGE_VALUE'] === 'Y'
	){
		$arSectionsFilter['UF_SHOW_ON_INDEX_PAG'] = true;
	}

	if(!isset($arParams['DEPTH_LEVEL'])){
		$arParams['DEPTH_LEVEL'] = 1;
	}

	$start_level = $arParams['DEPTH_LEVEL'];
	$end_level = $arParams['DEPTH_LEVEL'] + 1;

	if($arParams['PARENT_SECTION']){
		$arParentSection = TSolution\Cache::CIBLockSection_GetList(
			array(
				'SORT' => 'ASC', 
				'NAME' => 'ASC', 
				'CACHE' => array(
					'TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 
					'MULTI' => 'N'
				)
			), 
			array('ID' => $arParams['PARENT_SECTION']), 
			false, 
			array(
				'ID', 
				'IBLOCK_ID', 
				'LEFT_MARGIN', 
				'RIGHT_MARGIN'
			)
		);

		$arSectionsFilter = array_merge(
			$arSectionsFilter, 
			array(
				'>LEFT_MARGIN' => $arParentSection['LEFT_MARGIN'],
				'<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'],
				'>DEPTH_LEVEL' => '1'
			)
		);

		if($arParams['SHOW_CHILD_SECTIONS'] == 'Y'){
			$arSectionsFilter['INCLUDE_SUBSECTIONS'] = 'Y';
			$arSectionsFilter['<=DEPTH_LEVEL'] = $end_level;
		}
		else{
			$arSectionsFilter['DEPTH_LEVEL'] = $start_level;
		}
	}
	else{
		if($arParams['SHOW_CHILD_SECTIONS'] == 'Y'){
			$arSectionsFilter['<=DEPTH_LEVEL'] = $end_level;
		}
		else{
			$arSectionsFilter['DEPTH_LEVEL'] = '1';
		}
	}

	$arResult['SECTIONS'] = TSolution\Cache::CIBLockSection_GetList(
		array(
			'SORT' => 'ASC', 
			'NAME' => 'ASC', 
			'CACHE' => array(
				'TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 
				'GROUP' => array('ID'), 
				'MULTI' => 'N',
				'URL_TEMPLATE' => $arParams['SECTION_URL'] ?? '',
			)
		), 
		$arSectionsFilter, 
		false, 
		array(
			'ID', 
			'NAME', 
			'IBLOCK_ID', 
			'DEPTH_LEVEL', 
			'IBLOCK_SECTION_ID', 
			'SECTION_PAGE_URL', 
			'PICTURE', 
			'DETAIL_PICTURE', 
			'UF_TOP_SEO', 
			'DESCRIPTION',
			'UF_ICON',
			'UF_TRANSPARENT_PICTURE',
			'UF_SHOW_ON_INDEX_PAG',
		),
		false
	);

	if($arResult['SECTIONS']){
		$arSections = array();
		foreach($arResult['SECTIONS'] as $key => $arSection){
			$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($arSection['IBLOCK_ID'], $arSection['ID']);
			$arResult['SECTIONS'][$key]['IPROPERTY_VALUES'] = $ipropValues->getValues();
			TSolution::getFieldImageData($arResult['SECTIONS'][$key], array('PICTURE'), 'SECTION');
		}
		
		foreach($arResult['SECTIONS'] as $arItem){
			if($arItem['DEPTH_LEVEL'] == $start_level){
				if($arSections[$arItem['ID']]){
					$arSections[$arItem['ID']] = array_merge($arItem, $arSections[$arItem['ID']]);
				}
				else{
					$arSections[$arItem['ID']] = $arItem;
				}
			}
			elseif($arItem['DEPTH_LEVEL'] == $end_level){
				$arSections[$arItem['IBLOCK_SECTION_ID']]['CHILD'][$arItem['ID']] = $arItem;
			}
		}

		\Bitrix\Main\Type\Collection::sortByColumn($arSections, array("SORT" => array(SORT_NUMERIC, SORT_ASC), "NAME" => SORT_ASC));

		if($arParams['SHOW_CHILD_ELEMENTS'] == 'Y'){
			// fill elements
			foreach($arSections as $key => $arSection){
				if($arParams['SHOW_ELEMENTS_IN_LAST_SECTION'] != 'Y' || ($arParams['SHOW_ELEMENTS_IN_LAST_SECTION'] == 'Y' && !$arSection['CHILD'])){
					$arItems = TSolution\Cache::CIBlockElement_GetList(
						array(
							'SORT' => 'ASC', 
							'NAME' => 'ASC', 
							'CACHE' => array(
								'TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID'])
							)
						), 
						array_merge(
							$arSectionsFilter, 
							array('SECTION_ID' => $arSection['ID'])
						), 
						false, 
						false, 
						array(
							'ID', 
							'NAME', 
							'IBLOCK_ID', 
							'DETAIL_PAGE_URL',
							'DETAIL_TEXT',
						)
					);
					if($arItems){
						if(!$arSections[$key]['CHILD']){
							$arSections[$key]['CHILD'] = $arItems;
						}
						else{	
							$arSections[$key]['CHILD'] = array_merge(array_values($arSections[$key]['CHILD']), $arItems);
						}
					}
				}
			}
		}

		// new pagination
		$rsSections = new CDBResult;
		$rsSections->InitFromArray($arSections);
		$rsSections->NavStart(
			array(
				'nPageSize' => $arParams['NEWS_COUNT'],
				'bDescPageNumbering' => $arParams['PAGER_DESC_NUMBERING'],
				'bShowAll' => $arParams['PAGER_SHOW_ALL'],
			)
		);

		if($arParams['DISPLAY_TOP_PAGER'] || $arParams['DISPLAY_BOTTOM_PAGER']){
			$arResult['NAV_STRING'] = $rsSections->GetPageNavStringEx(
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

		// new sections of current page
		while($arSection = $rsSections->Fetch()){
			$arNewSections[] = $arSection;
		}

		$arResult['SECTIONS'] = $arNewSections;
		unset($arSections);
	}
}

if($arParams['HIDE_PAGINATION'] === 'Y'){
	unset($arResult['NAV_STRING']);
}
?>