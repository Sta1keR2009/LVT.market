<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


if ($arResult["ELEMENT"]['DETAIL_PICTURE'] || $arResult["ELEMENT"]['PREVIEW_PICTURE']) {
	$arFileTmp = CFile::ResizeImageGet(
		$arResult["ELEMENT"]['DETAIL_PICTURE'] ?: $arResult["ELEMENT"]['PREVIEW_PICTURE'],
		["width" => 140, "height" => 140],
		BX_RESIZE_IMAGE_PROPORTIONAL,
		true
	);
	$arResult["ELEMENT"]['DETAIL_PICTURE'] = $arFileTmp;
	unset($arResult['ELEMENT']['PREVIEW_PICTURE']);
}

$arDefaultSetIDs = [$arResult["ELEMENT"]["ID"]];
foreach (["DEFAULT", "OTHER"] as $type) {
	foreach ($arResult["SET_ITEMS"][$type] as $key => $arItem) {
		$arElement = [
			"ID" => $arItem["ID"],
			"NAME" => $arItem["NAME"],
			"DETAIL_PAGE_URL" => $arItem["DETAIL_PAGE_URL"],
			"DETAIL_PICTURE" => $arItem["DETAIL_PICTURE"],
			"PREVIEW_PICTURE" => $arItem["PREVIEW_PICTURE"],
			"PRICE_CURRENCY" => $arItem["PRICE_CURRENCY"],
			"PRICE_DISCOUNT_VALUE" => $arItem["PRICE_DISCOUNT_VALUE"],
			"PRICE_PRINT_DISCOUNT_VALUE" => $arItem["PRICE_PRINT_DISCOUNT_VALUE"],
			"PRICE_VALUE" => $arItem["PRICE_VALUE"],
			"PRICE_PRINT_VALUE" => $arItem["PRICE_PRINT_VALUE"],
			"PRICE_DISCOUNT_DIFFERENCE_VALUE" => $arItem["PRICE_DISCOUNT_DIFFERENCE_VALUE"],
			"PRICE_DISCOUNT_DIFFERENCE" => $arItem["PRICE_DISCOUNT_DIFFERENCE"],
			"CAN_BUY" => $arItem['CAN_BUY'],
			"SET_QUANTITY" => $arItem['SET_QUANTITY'],
			"MEASURE_RATIO" => $arItem['MEASURE_RATIO'],
			"BASKET_QUANTITY" => $arItem['BASKET_QUANTITY'],
			"MEASURE" => $arItem['MEASURE']
		];
		if ($arItem["PRICE_CONVERT_DISCOUNT_VALUE"])
			$arElement["PRICE_CONVERT_DISCOUNT_VALUE"] = $arItem["PRICE_CONVERT_DISCOUNT_VALUE"];
		if ($arItem["PRICE_CONVERT_VALUE"])
			$arElement["PRICE_CONVERT_VALUE"] = $arItem["PRICE_CONVERT_VALUE"];
		if ($arItem["PRICE_CONVERT_DISCOUNT_DIFFERENCE_VALUE"])
			$arElement["PRICE_CONVERT_DISCOUNT_DIFFERENCE_VALUE"] = $arItem["PRICE_CONVERT_DISCOUNT_DIFFERENCE_VALUE"];

		if ($type == "DEFAULT")
			$arDefaultSetIDs[] = $arItem["ID"];
		if ($arItem['DETAIL_PICTURE'] || $arItem['PREVIEW_PICTURE']) {
			$arFileTmp = CFile::ResizeImageGet(
				$arItem['DETAIL_PICTURE'] ?: $arItem['PREVIEW_PICTURE'],
				["width" => 48, "height" => 48],
				BX_RESIZE_IMAGE_PROPORTIONAL,
				true
			);
			$arElement['DETAIL_PICTURE'] = $arFileTmp;
			unset($arElement['PREVIEW_PICTURE']);
		}

		$arResult["SET_ITEMS"][$type][$key] = $arElement;
	}
}
$arResult["DEFAULT_SET_IDS"] = $arDefaultSetIDs;