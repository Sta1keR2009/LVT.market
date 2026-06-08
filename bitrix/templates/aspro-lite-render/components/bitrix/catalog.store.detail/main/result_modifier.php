<?if($arResult["ID"]):?>
	<?
	Bitrix\Main\Loader::includeModule('catalog');

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
		'UF_MORE_PHOTOS',
		'UF_PHONES',
	];

	$dbRes = CCatalogStore::GetList(
		[
			'ID' => 'ASC'
		],
		[
			'ID' => $arResult['ID']
		],
		false,
		false,
		$arSelect
	);
	if ($store = $dbRes->GetNext()) {
		$arResult['SORT'] = $store['SORT'];
		$arResult['TITLE'] = htmlspecialchars_decode($store['TITLE']);
		$arResult['ADDRESS'] = htmlspecialchars_decode($store['ADDRESS']);
		$arResult['ADDRESS'] = $arResult['TITLE'].((strlen($arResult['TITLE']) && strlen($arResult['ADDRESS'])) ? ', ' : '').$arResult['ADDRESS'];
		$arResult['EMAIL'] = htmlspecialchars_decode($store['EMAIL']);
		$arResult['SCHEDULE'] = htmlspecialchars_decode($arResult['SCHEDULE']);
		$arResult['IMAGE'] = CFile::ResizeImageGet($store['IMAGE_ID'], array('width' => 450, 'height' => 300), BX_RESIZE_IMAGE_EXACT);
		$arResult['GPS_N'] = $store['GPS_N'];
		$arResult['GPS_S'] = $store['GPS_S'];
		$arResult['METRO'] = TSolution::unserialize($store['~UF_METRO']);
		$arResult["MORE_PHOTOS"] = TSolution::unserialize($store["~UF_MORE_PHOTOS"]);
		$store['UF_PHONES'] = TSolution::unserialize($store['~UF_PHONES']);
		$arResult['PHONE'] = $store['UF_PHONES'] ? array_unique(array_merge((array)$arResult['PHONE'], (array)$store['UF_PHONES'])) : $arResult['PHONE'];
	}
	?>
<?else:?>
	<?LocalRedirect(SITE_DIR.'contacts/');?>
<?endif;?>