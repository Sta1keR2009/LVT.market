<?
if ($arResult['STORES']) {
	$arStoresIDs = $arStoresByID = [];

	foreach ($arResult['STORES'] as $key => $arStore) {
		$arResult['STORES'][$key]['KEY'] = $key;
		$arStoresIDs[] = $arStore['ID'];
		$arStoresByID[$arStore['ID']] = &$arResult['STORES'][$key];
	}

	if ($arStoresIDs) {
		$arSelect = [
			'ID',
			'ADDRESS',
			'SORT',
			'TITLE',
			'GPS_N',
			'GPS_S',
			'SCHEDULE',
			'EMAIL',
			'UF_METRO',
			'UF_PHONES',
		];

		$arFilter = array('ID' => $arStoresIDs, 'ACTIVE' => 'Y');

		if (
			strlen($arParams['FILTER_NAME']) &&
			$GLOBALS[$arParams['FILTER_NAME']]
		) {
			$arFilter = array_merge($arFilter, $GLOBALS[$arParams['FILTER_NAME']]);
		}

		$dbRes = CCatalogStore::GetList(
			[
				'ID' => 'ASC'
			],
			$arFilter,
			false,
			false,
			$arSelect
		);
		while ($store = $dbRes->GetNext()) {
			if ($arStoresByID[$store['ID']]) {
				$arStore =& $arStoresByID[$store['ID']];

				$arStore['SORT'] = $store['SORT'];
				$arStore['TITLE'] = htmlspecialchars_decode($store['TITLE']);
				$arStore['ADDRESS'] = htmlspecialchars_decode($store['ADDRESS']);
				$arStore['ADDRESS'] = $arStore['TITLE'].((strlen($arStore['TITLE']) && strlen($arStore['ADDRESS'])) ? ', ' : '').$arStore['ADDRESS'];
				$arStore['EMAIL'] = htmlspecialchars_decode($store['EMAIL']);
				$arStore['SCHEDULE'] = htmlspecialchars_decode($arStore['SCHEDULE']);
				$arStore['IMAGE'] = CFile::ResizeImageGet($store['IMAGE_ID'], array('width' => 450, 'height' => 300), BX_RESIZE_IMAGE_EXACT);
				$arStore['GPS_N'] = $store['GPS_N'];
				$arStore['GPS_S'] = $store['GPS_S'];
				$arStore['METRO'] = TSolution::unserialize($store['~UF_METRO']);
				$store['UF_PHONES'] = TSolution::unserialize($store['~UF_PHONES']);
				$arStore['PHONE'] = $store['UF_PHONES'] ? array_unique(array_merge((array)$arStore['PHONE'], (array)$store['UF_PHONES'])) : $arStore['PHONE'];

				unset($arStore);
			}
		}

		foreach ($arResult['STORES'] as $key => $arStore) {
			if (!isset($arStore['SORT'])) {
				unset($arResult['STORES'][$key]);
			}
		}
	}
	
	usort($arResult['STORES'], function($a, $b) {
		return ($a['SORT'] == $b['SORT'] ? ($a['KEY'] <=> $b['KEY']) : ($a['SORT'] <=> $b['SORT']));
	});
}