<?

if($arResult['ITEMS']){
	$curPage = $APPLICATION->GetCurPage(false);
	$curPageIndex = $APPLICATION->GetCurPage(true);
	
	foreach ($arResult['ITEMS'] as $key => $arItem) {		
		/* Check items for current banner position */
		if ($arItem['PROPERTIES']['POSITION']['VALUE_XML_ID']!==$arParams['POSITION']) {
			unset($arResult['ITEMS'][$key]);
			continue;
		}

		$arResult['ITEMS'][$key]['DELETE'] = false;
		
		if (
			is_array($arItem['PROPERTIES']['SHOW_PAGE']['VALUE']) ||
			is_array($arItem['PROPERTIES']['SHOW_SECTION']['VALUE'])
		) {
			$arResult['ITEMS'][$key]['DELETE'] = true;
		}
		
		/* Check pages rules */
		if (is_array($arItem['PROPERTIES']['SHOW_PAGE']['VALUE'])) {						
			foreach ($arItem['PROPERTIES']['SHOW_PAGE']['VALUE'] as $page) {
				if (
					$page == $curPage ||
					$page == $curPageIndex
				) {
					$arResult['ITEMS'][$key]['DELETE'] = false;
					break;
				}
			}			
		}
		
		/* Check section rules */
		if (is_array($arItem['PROPERTIES']['SHOW_SECTION']['VALUE'])) {						
			foreach ($arItem['PROPERTIES']['SHOW_SECTION']['VALUE'] as $section) {				
				if(strpos($curPage, $section) === 0) {
					$arResult['ITEMS'][$key]['DELETE'] = false;
					break;
				}
			}
		}

		if (is_array($arItem['PROPERTIES']['NO_SHOW_PAGE']['VALUE'])) {						
			foreach ($arItem['PROPERTIES']['NO_SHOW_PAGE']['VALUE'] as $page) {
				if (
					$page == $curPage ||
					$page == $curPageIndex
				) {
					$arResult['ITEMS'][$key]['DELETE'] = true;
					break;
				}
			}			
		}

		if (is_array($arItem['PROPERTIES']['NO_SHOW_SECTION']['VALUE'])) {						
			foreach ($arItem['PROPERTIES']['NO_SHOW_SECTION']['VALUE'] as $section) {				
				if(strpos($curPage, $section) === 0) {
					$arResult['ITEMS'][$key]['DELETE'] = true;
					break;
				}
			}
		}

		if ($arResult['ITEMS'][$key]['DELETE'] == true) {
			unset($arResult['ITEMS'][$key]);
			continue;
		}
	}	
	
	
	/* Get One random banner */
	if (
		count($arResult['ITEMS']) > 1 &&
		$arItem['PROPERTIES']['POSITION']['VALUE_XML_ID'] != 'SIDE'
	) {
		$arItem = $arResult['ITEMS'][array_rand($arResult['ITEMS'])];		
		unset($arResult['ITEMS']);
		$arResult['ITEMS'] = array();
		$arResult['ITEMS'][0] = $arItem;
	}
}

?>