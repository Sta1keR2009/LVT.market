<?

use Bitrix\Main\Localization\Loc;
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

require_once $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . "/vendor/php/solution.php";

$arDefaultParams = array(
	'TYPE_SKU' => 'N',
	'FILTER_HIT_PROP' => 'block',
	'OFFER_TREE_PROPS' => array('-'),
);
$arParams = array_merge($arDefaultParams, $arParams);

$bShowSKU = $arParams['TYPE_SKU'] !== 'TYPE_2';

if ($arParams['SHOW_PROPS'] == 'Y') {
	$arParams['SHOW_GALLERY'] = 'N';
}

if (!empty($arResult['ITEMS'])) {
	/* get sku tree props */
	if ($bShowSKU) {
		//check catalog
		$bCatalogSKU = false;
		$arSKU = [];
		if (TSolution::isSaleMode()) {
			$arSKU = CCatalogSKU::GetInfoByProductIBlock($arParams['IBLOCK_ID']);
			$bCatalogSKU = !empty($arSKU) && is_array($arSKU);

			if ($bCatalogSKU) {
				$arParams['SKU_IBLOCK_ID'] = $arSKU['IBLOCK_ID'];
				$arParams['LINK_SKU_PROP_CODE'] = 'CML2_LINK';
				$arParams['USE_CATALOG_SKU'] = true;
				
				$bUseModuleProps = \Bitrix\Main\Config\Option::get("iblock", "property_features_enabled", "N") === "Y";
				if ($bUseModuleProps) {
					$arParams['OFFERS_CART_PROPERTIES'] = \Bitrix\Catalog\Product\PropertyCatalogFeature::getBasketPropertyCodes($arSKU['IBLOCK_ID'], ['CODE' => 'Y']);
					if ($featureProps = \Bitrix\Catalog\Product\PropertyCatalogFeature::getOfferTreePropertyCodes($arSKU["IBLOCK_ID"], array('CODE' => 'Y'))) {
						$arParams['SKU_TREE_PROPS'] = $featureProps;
					}
					if ($featureProps = \Bitrix\Iblock\Model\PropertyFeature::getListPageShowPropertyCodes($arSKU["IBLOCK_ID"], array('CODE' => 'Y'))) {
						$arParams['SKU_PROPERTY_CODE'] = $featureProps;
					}
				}
				if (!$arParams['SKU_TREE_PROPS'] && isset($arParams['OFFERS_CART_PROPERTIES']) && is_array($arParams['OFFERS_CART_PROPERTIES'])) {
					$arParams['SKU_TREE_PROPS'] = $arParams['OFFERS_CART_PROPERTIES'];
				}
			}
		}

		$obSKU = new TSolution\SKU($arParams);
		if ($arParams['SKU_IBLOCK_ID'] && $arParams['SKU_TREE_PROPS']) {
			$arTreeFilter = [
				'=IBLOCK_ID' => $arParams['SKU_IBLOCK_ID'],
				'CODE' => $arParams['SKU_TREE_PROPS']
			];
			$obSKU->getTreePropsByFilter($arTreeFilter, $arSKU);
			
			$arResult['SKU_CONFIG'] = $obSKU->config;
			$arResult['SKU_CONFIG']['ADD_PICT_PROP'] = $arParams['ADD_PICT_PROP'];
			$arResult['SKU_CONFIG']['SHOW_GALLERY'] = $arParams['SHOW_GALLERY'];
			$arResult['SKU_CONFIG']['IS_GIFT'] = 'Y';
			$arResult['SKU_CONFIG']['ICONS_PROPS'] = [
				'CLASS' => 'md'
			];
		}
	}
	/* */
	
	$arNewItemsList = [];
	foreach ($arResult['ITEMS'] as $key => $arItem) {
		if ($arItem['OFFERS']) {
			$arParams['SLIDER'] = false;
		}
		if($arItem['PRODUCT_PROPERTIES_FILL']){
			foreach($arItem['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo){
				if(isset($arItem['PRODUCT_PROPERTIES'][$propID])){
					unset($arItem['PRODUCT_PROPERTIES'][$propID]);
				}
			}
		}

		if (is_array($arItem['PROPERTIES']['CML2_ARTICLE']['VALUE']) && $arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE']) {
			$arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] = reset($arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE']);
			$arResult['ITEMS'][$key]['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] = $arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'];
		}

		if (($arItem['DETAIL_PICTURE'] && $arItem['PREVIEW_PICTURE']) || (!$arItem['DETAIL_PICTURE'] && $arItem['PREVIEW_PICTURE'])) {
			$arItem['DETAIL_PICTURE'] = $arItem['PREVIEW_PICTURE'];
		}

		$arItem['GALLERY'] = TSolution\Functions::getSliderForItem([
			'TYPE' => 'catalog_block',
			'PROP_CODE' => $arParams['ADD_PICT_PROP'],
			'ITEM' => $arItem,
			'PARAMS' => $arParams,
		]);
		array_splice($arItem['GALLERY'], $arParams['MAX_GALLERY_ITEMS']);

		if (!empty($arItem['DISPLAY_PROPERTIES'])) {
			foreach ($arItem['DISPLAY_PROPERTIES'] as $propKey => $arDispProp) {
				if ('F' == $arDispProp['PROPERTY_TYPE']) {
					unset($arItem['DISPLAY_PROPERTIES'][$propKey]);
				}
			}
		}

		if (!empty($arItem['DISPLAY_PROPERTIES'])) {
			foreach ($arItem['DISPLAY_PROPERTIES'] as $propKey => $arDispProp) {
				if ('F' == $arDispProp['PROPERTY_TYPE'] || $arDispProp["CODE"] == $arParams["STIKERS_PROP"]) {
					unset($arItem['DISPLAY_PROPERTIES'][$propKey]);
				}
			}
			$arItem['SHOW_PROPERTIES'] = TSolution::PrepareItemProps($arItem['DISPLAY_PROPERTIES']);
		}

		$arItem['LAST_ELEMENT'] = 'N';

		/* get SKU for item */
		if ($arItem['OFFERS']) {
			if ($bShowSKU) {
				if ($bCatalogSKU) {
					$obSKU->setItems($arItem['OFFERS']);
				} else {
					$arFilter = [
						'PROPERTY_'.$obSKU->linkCodeProp => $arItem['ID'],
						'ACTIVE' => 'Y',
						'IBLOCK_ID' => $arParams['SKU_IBLOCK_ID']
					];
					$obSKU->getItemsByFilter($arFilter, []);
					$obSKU->getItemsProps($arParams['SKU_IBLOCK_ID']);
				}
				$obSKU->getMatrix();
				
				$arItem['SKU'] = [
					'CURRENT' => $obSKU->currentItem,
					'OFFERS' => $obSKU->items,
					'PROPS' => $obSKU->treeProps
				];
			} else {
				if (TSolution::isSaleMode() && $arItem['OFFERS']) {
					$arItem['PRICES'] = []; //clear PRICES
					$arItem['PRICES'][] = \TSolution\Product\Price::getMinPriceFromOffersExt($arItem['OFFERS']);
				} else {
					$hasSKU = \TSolution\SKU::hasSKU($arItem, $arParams);
					if (
						$hasSKU
						&& $arItem['DISPLAY_PROPERTIES']
						&& isset($arItem['DISPLAY_PROPERTIES']['PRICE'])
						&& (int)$arItem['DISPLAY_PROPERTIES']['PRICE']['VALUE']
					) {
						$arItem['DISPLAY_PROPERTIES']['PRICE']['VALUE'] = \TSolution\Product\Price::addFromTextBeforePrice($arItem['DISPLAY_PROPERTIES']['PRICE']['VALUE']);
					}
				}
			}
		}
		/* */

		$arNewItemsList[$key] = $arItem;
	}

	$arNewItemsList[$key]['LAST_ELEMENT'] = 'Y';
	$arResult['ITEMS'] = $arNewItemsList;

	unset($arNewItemsList);

	$arResult['CUSTOM_RESIZE_OPTIONS'] = array();
	if ($arParams['USE_CUSTOM_RESIZE_LIST'] == 'Y') {
		$arIBlockFields = CIBlock::GetFields($arParams["IBLOCK_ID"]);
		if ($arIBlockFields['PREVIEW_PICTURE'] && $arIBlockFields['PREVIEW_PICTURE']['DEFAULT_VALUE']) {
			if ($arIBlockFields['PREVIEW_PICTURE']['DEFAULT_VALUE']['WIDTH'] && $arIBlockFields['PREVIEW_PICTURE']['DEFAULT_VALUE']['HEIGHT']) {
				$arResult['CUSTOM_RESIZE_OPTIONS'] = $arIBlockFields['PREVIEW_PICTURE']['DEFAULT_VALUE'];
			}
		}
	}
}?>