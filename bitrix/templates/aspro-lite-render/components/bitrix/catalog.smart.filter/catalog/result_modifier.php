<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
if (count($arResult['COMBO']) == 1) {
	$hidden = true;
	foreach ($arResult['COMBO'][0] as $value) {
		if ($value) {
			$hidden = false;
		}
	}
}

if ($arResult['ITEMS']) {
	foreach ($arResult["ITEMS"] as $key => $arItem) {
		if (isset($arItem['PRICE']) && $arItem['PRICE']) {
			if (
				(isset($arItem['VALUES']['MIN']['HTML_VALUE']) && $arItem['VALUES']['MIN']['HTML_VALUE'])
				|| (isset($arItem['VALUES']['MAX']['HTML_VALUE']) && $arItem['VALUES']['MAX']['HTML_VALUE'])
			) {
				$arResult['PRICE_SET'] = 'Y';
				break;
			}
		}

		$i = 0;

		if ($arItem['PROPERTY_TYPE'] == 'S' || $arItem['PROPERTY_TYPE'] == 'L' || $arItem['PROPERTY_TYPE'] == 'E') {
			foreach ($arItem['VALUES'] as $arValue) {
				if (isset($arValue['CHECKED']) && $arValue['CHECKED']) {
					$arResult["ITEMS"][$key]['PROPERTY_SET'] = 'Y';
					++$i;
				}
			}

			if ($i) {
				$arResult["ITEMS"][$key]['COUNT_SELECTED'] = $i;
			}
		}

		if ($arItem['PROPERTY_TYPE'] == 'N') {
			foreach ($arItem['VALUES'] as $arValue) {
				if (isset($arValue['HTML_VALUE']) && $arValue['HTML_VALUE']) {
					$arResult['ITEMS'][$key]['PROPERTY_SET'] = 'Y';
				}
			}
		}

		$arResult['ITEMS'][$key]['IS_PROP_INLINE'] = false;

		if ($arItem['PROPERTY_TYPE'] === 'L') {
			$iPropValuesCount = CIBlockPropertyEnum::GetList([], [
				'PROPERTY_ID' => $arItem['ID'],
				'IBLOCK_ID' => $arItem['IBLOCK_ID']
			])->SelectedRowsCount();

			if ($iPropValuesCount === 1) {
				if (is_array($arResult["ITEMS"][$key]["VALUES"]))
					sort($arResult["ITEMS"][$key]["VALUES"]);

				if ($arResult["ITEMS"][$key]["VALUES"])
					$arResult["ITEMS"][$key]["VALUES"][0]["VALUE"] = $arItem["NAME"];

				$arResult['ITEMS'][$key]['IS_PROP_INLINE'] = true;
			}
		}
	}
}

$GLOBALS['SHOW_SMART_FILTER'] = !$hidden;

$arResult["JS_FILTER_PARAMS"]["AJAX"] = $arParams["AJAX"] === "Y";

if ($hidden) {
	$arResult['ITEMS'] = array();
}

global $sotbitFilterResult;
$sotbitFilterResult = $arResult;
