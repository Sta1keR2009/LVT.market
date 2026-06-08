<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$frame = $this->createFrame()->begin();

$injectId = 'sale_gift_main_products_' . rand();

$templateData['JS_OBJ'] = "BX.Sale['GiftMainProductsClass_{$component->getComponentId()}']";

// component parameters
$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedParameters = $signer->sign(
	base64_encode(serialize($arResult['_ORIGINAL_PARAMS'])),
	'bx.sale.gift.main.products'
);
$signedTemplate = $signer->sign($arResult['RCM_TEMPLATE'], 'bx.sale.gift.main.products');

?>
<div class="bx_item_list_you_looked_horizontal detail">
	<div id="<?= $injectId ?>" class="bx_sale_gift_main_products common_product wrapper_block">
		<?if ($arResult['HAS_MAIN_PRODUCTS']):?>
			<?
			global $searchFilter;
			$searchFilter = array();
			if ($arResult['MAIN_ELEMENT_IDS']) {
				$searchFilter = array(
					"=ID" => $arResult['MAIN_ELEMENT_IDS'],
				);
			}
			?>
			<?
			$arAdditionalParams = [
				"OFFERS_FIELD_CODE" => ['ID', 'NAME'],
				"SKU_IBLOCK_ID"	=>	$arParams["SKU_IBLOCK_ID"],
				"SKU_TREE_PROPS"	=>	$arParams["SKU_TREE_PROPS"],
				"OFFER_TREE_PROPS"	=>	$arParams["SKU_TREE_PROPS"],
				"SKU_PROPERTY_CODE"	=>	$arParams["SKU_PROPERTY_CODE"],
				"OFFERS_PROPERTY_CODE"	=>	$arParams["SKU_PROPERTY_CODE"],
				"SKU_SORT_FIELD" => $arParams["SKU_SORT_FIELD"],
				"SKU_SORT_ORDER" => $arParams["SKU_SORT_ORDER"],
				"SKU_SORT_FIELD2" => $arParams["SKU_SORT_FIELD2"],
				"SKU_SORT_ORDER2" =>$arParams["SKU_SORT_ORDER2"],
				"PRICE_CODE" => explode(',', TSolution::GetFrontParametrValue('PRICES_TYPE')),
				"USE_PRICE_COUNT" => 'N',
				"STORES" => explode(',', TSolution::GetFrontParametrValue('STORES')),
			];
			$bSlider = TSolution::GetFrontParametrValue('VIEW_LINKED_GOODS') === 'catalog_slider';
			if (!$bSlider) {
				$arAdditionalParams = array_merge($arAdditionalParams, [
					"TYPE_SKU" => "TYPE_1",
					"DISPLAY_BOTTOM_PAGER" => "Y",
					"ITEM_HOVER_SHADOW" => true,
				]);
			}
			?>
			<?
			ob_start();
			$APPLICATION->IncludeComponent(
				"bitrix:catalog.section",
				"catalog_block",
				array_merge([
					"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
					"IBLOCK_ID"	=> $arParams["IBLOCK_ID"],
					"SECTION_ID" => reset($arResult['MAIN_SECTION_IDS']),

					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
					"CACHE_FILTER" => $arParams["CACHE_FILTER"],
					"DETAIL_URL" => $arParams["DETAIL_URL"],
					"FILTER_NAME" => "searchFilter",
					"HIT_PROP" => "HIT",
					"PAGE_ELEMENT_COUNT" => $arParams['PAGE_ELEMENT_COUNT'],
					"PROPERTY_CODE" => $arParams["PROPERTY_CODE"],
					"ELEMENT_SORT_FIELD" => "SORT",
					"ELEMENT_SORT_ORDER" => "ASC",
					"ELEMENT_SORT_FIELD2" => "ID",
					"ELEMENT_SORT_ORDER2" => "DESC",
					"ELEMENTS_TABLE_TYPE_VIEW" => "FROM_MODULE",
					"SHOW_SECTION" => "Y",
					"COUNT_IN_LINE" => "",
					"LINE_ELEMENT_COUNT" => "4",
					"SHOW_GALLERY" => TSolution::GetFrontParametrValue('SHOW_CATALOG_GALLERY_IN_LIST'),
					"MAX_GALLERY_ITEMS" => TSolution::GetFrontParametrValue('MAX_GALLERY_ITEMS'),
					"ADD_PICT_PROP" => \Bitrix\Main\Config\Option::get(VENDOR_MODULE_ID, 'GALLERY_PROPERTY_CODE', 'MORE_PHOTO'),
					"OFFER_ADD_PICT_PROP" => \Bitrix\Main\Config\Option::get(VENDOR_MODULE_ID, 'GALLERY_PROPERTY_CODE', 'MORE_PHOTO'),
					"DISPLAY_TOP_PAGER"	=>	"N",
					"DISPLAY_BOTTOM_PAGER"	=>	"N",
					"PAGER_TITLE" => $arParams["PAGER_TITLE"],
					"PAGER_TEMPLATE"	=>	"ajax",
					"PAGER_SHOW_ALWAYS"	=>	"N",
					"PAGER_DESC_NUMBERING"	=>	"N",
					"PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
					"PAGER_SHOW_ALL" => "N",
					"INCLUDE_SUBSECTIONS" => "Y",
					"PRICE_CODE" => $arParams["PRICE_CODE"],
					"SHOW_ALL_WO_SECTION" => "Y",
					"SECTION_COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"],
					"IS_CATALOG_PAGE" => 'Y',
					"META_KEYWORDS" => "",
					"META_DESCRIPTION" => "",
					"BROWSER_TITLE" => "",
					"ADD_SECTIONS_CHAIN" => "N",
					"DISPLAY_COMPARE" => TSolution::GetFrontParametrValue('CATALOG_COMPARE'),
					"SHOW_FAVORITE" => TSolution::GetFrontParametrValue('SHOW_FAVORITE'),
					
					"SHOW_ONE_CLICK_BUY" => TSolution::GetFrontParametrValue('SHOW_ONE_CLICK_BUY'),
					"USE_FAST_VIEW_PAGE_DETAIL" => TSolution::GetFrontParametrValue('USE_FAST_VIEW_PAGE_DETAIL'),
					"EXPRESSION_FOR_FAST_VIEW" => TSolution::GetFrontParametrValue('EXPRESSION_FOR_FAST_VIEW'),
					"ORDER_VIEW" => TSolution::GetFrontParametrValue('ORDER_VIEW'),
					
					"ELEMENT_IN_ROW" => $arParams['ELEMENT_IN_ROW'] ?? 3,
					"ITEM_768" => "3",
					"ITEM_992" => "4",
					"ITEM_1200" => $arParams['ITEM_1200'] ?? 3,
					"POSITION_BTNS" => "4",
					"AJAX_REQUEST" => $arParams['IS_AJAX'],
					"TEXT_CENTER" => false,
					"IMG_CORNER" => false,
					"GRID_GAP" => "20",
					"ROW_VIEW" => true,
					"SLIDER" => $bSlider,
					"SLIDER_BUTTONS_BORDERED" => false,
					"IS_COMPACT_SLIDER" => false,
					"BORDERED" => 'Y',
					"IMG_CORNER" => 'N',
					"ELEMENTS_ROW" => 1,
					"MAXWIDTH_WRAP" => false,
					"MOBILE_SCROLLED" => false,
					"ITEM_0" => "2",
					"ITEM_380" => "2",
					"NARROW" => "Y",
					"IS_CATALOG_PAGE" => "N",
					"IMAGES" => "PICTURE",
					"IMAGE_POSITION" => "LEFT",
					"SHOW_PREVIEW" => true,
					"SHOW_TITLE" => false,
					"TITLE_POSITION" => "",
					"TITLE" => "",
					"RIGHT_TITLE" => "",
					"RIGHT_LINK" => "",
					"CHECK_REQUEST_BLOCK" => $arParams['CHECK_REQUEST_BLOCK'],
					"TYPE_SKU" => "TYPE_2",
					"NAME_SIZE" => "18",
					"SUBTITLE" => "",
					"SHOW_PREVIEW_TEXT" => "N",
					"SHOW_DISCOUNT_TIME" => TSolution::GetFrontParametrValue('SHOW_DISCOUNT_TIME'),
					"SHOW_DISCOUNT_PERCENT" => TSolution::GetFrontParametrValue('SHOW_DISCOUNT_PERCENT'),
					"SHOW_OLD_PRICE" => TSolution::GetFrontParametrValue('SHOW_OLD_PRICE'),
					"SHOW_RATING" => TSolution::GetFrontParametrValue('SHOW_RATING'),
					"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
					"CURRENCY_ID" => $arParams["CURRENCY_ID"],
					"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
					"COMPATIBLE_MODE" => "Y",
				], $arAdditionalParams),
				$component,
				array('HIDE_ICONS' => 'Y')
			);
			$html = trim(ob_get_clean());
			?>
			<?if ($html && strpos($html, 'error') === false):?>
				<?if ($arParams['BLOCK_TITLE'] && (empty($arParams['HIDE_BLOCK_TITLE']) || $arParams['HIDE_BLOCK_TITLE'] === 'N')):?>
					<h3 class="switcher-title"><?=$arParams['BLOCK_TITLE'];?></h3>
				<?endif;?>
				<?=$html;?>
			<?endif;?>
		<?endif;?>
	</div>
</div>
<script>
	BX(function() {
		BX.Sale['GiftMainProductsClass_<?= $component->getComponentId() ?>'] = new BX.Sale.GiftMainProductsClass({
			contextAjaxData: {
				parameters: '<?= CUtil::JSEscape($signedParameters) ?>',
				template: '<?= CUtil::JSEscape($signedTemplate) ?>',
				site_id: '<?= CUtil::JSEscape(SITE_ID) ?>'
			},
			injectId: '<?= $injectId ?>',
			mainProductState: '<?= $arResult['MAIN_PRODUCT_STATE'] ?>',
			isGift: <?= $arResult['HAS_MAIN_PRODUCTS'] ? 'true' : 'false'; ?>,
			productId: <?= $arParams['ELEMENT_ID'] ?: 'null'; ?>,
			offerId: <?= $arParams['OFFER_ID'] ?: 'null'; ?>
		});
	});

	BX.message({});
</script>
<? $frame->beginStub(); ?>
<? $frame->end(); ?>