<?
if ($arResult['ITEMS']) {
	$arTmpItems = array();
	foreach ($arResult['ITEMS'] as $key => $arItem) {
		$arTmpItems[$key] = array(
			'NAME' => $arItem['NAME'],
			'IBLOCK_ID' => $arItem['IBLOCK_ID'],
			'DISPLAY_PROPERTIES' => $arItem['DISPLAY_PROPERTIES'],
			'MAP' => $arItem['DISPLAY_PROPERTIES']['MAP']['VALUE'],
		);

		$arResult['ITEMS'][$key]['URL'] = $arItem['DETAIL_PAGE_URL'];
		$arResult['ITEMS'][$key]['ADDRESS'] = $arTmpItems[$key]['ADDRESS'] = $arItem['NAME'].($arItem['DISPLAY_PROPERTIES']['ADDRESS']['VALUE'] ? ', '.$arItem['DISPLAY_PROPERTIES']['ADDRESS']['VALUE'] : '');
		$arResult['ITEMS'][$key]['EMAIL'] = $arTmpItems[$key]['EMAIL'] = $arItem['DISPLAY_PROPERTIES']['EMAIL']['VALUE'];
		$arResult['ITEMS'][$key]['PHONE'] = $arTmpItems[$key]['PHONE'] = $arItem['DISPLAY_PROPERTIES']['PHONE']['VALUE'];
		$arResult['ITEMS'][$key]['METRO'] = $arTmpItems[$key]['METRO'] = $arItem['DISPLAY_PROPERTIES']['METRO']['VALUE'];

		if ($arItem['DISPLAY_PROPERTIES']['PHONE']['VALUE']) {
			if(!is_array($arItem['DISPLAY_PROPERTIES']['PHONE']['VALUE']))
				$arItem['DISPLAY_PROPERTIES']['PHONE']['VALUE'] = array($arItem['DISPLAY_PROPERTIES']['PHONE']['VALUE']);
			
			foreach($arItem['DISPLAY_PROPERTIES']['PHONE']['VALUE'] as $phone){
				$arResult['ITEMS'][$key]['PHONE_HTML'] .= '<div class="value"><a class="dark_link" rel= "nofollow" href="tel:'.str_replace(array(' ', ',', '-', '(', ')'), '', $phone).'">'.$phone.'</a></div>';
			}
		}
		if ($arItem['DISPLAY_PROPERTIES']['METRO']['VALUE']) {
			if(!is_array($arItem['DISPLAY_PROPERTIES']['METRO']['VALUE']))
				$arItem['DISPLAY_PROPERTIES']['METRO']['VALUE'] = array($arItem['DISPLAY_PROPERTIES']['METRO']['VALUE']);
			
			foreach($arItem['DISPLAY_PROPERTIES']['METRO']['VALUE'] as $metro){
				$arResult['ITEMS'][$key]['METRO_HTML'] .= '<div class="metro"><i></i>'.$metro.'</div>';
			}
		}
		if ($arItem['DISPLAY_PROPERTIES']['SCHEDULE']['VALUE']['TYPE'] == 'html') {
			$arResult['ITEMS'][$key]['SCHEDULE'] = htmlspecialchars_decode($arItem['DISPLAY_PROPERTIES']['SCHEDULE']['~VALUE']['TEXT']);
		} else {
			$arResult['ITEMS'][$key]['SCHEDULE'] = nl2br($arItem['DISPLAY_PROPERTIES']['SCHEDULE']['~VALUE']['TEXT']);
		}

		$arTmpItems[$key]['METRO_HTML'] = $arResult['ITEMS'][$key]['METRO_HTML'];
		$arTmpItems[$key]['PHONE_HTML'] = $arResult['ITEMS'][$key]['PHONE_HTML'];
		$arTmpItems[$key]['SCHEDULE'] = $arResult['ITEMS'][$key]['SCHEDULE'];
	}
	$arResult['MAP_ITEMS'] = $arTmpItems;
}

?>