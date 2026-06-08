<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?
$catalogIblockID = \Bitrix\Main\Config\Option::get('aspro.lite', 'CATALOG_IBLOCK_ID', TSolution\Cache::$arIBlocks[SITE_ID]['aspro_lite_catalog']['aspro_lite_catalog'][0]);
$normalCount = count($arResult["ITEMS"]["AnDelCanBuy"]);
$delayCount = count($arResult["ITEMS"]["DelDelCanBuy"]);
$subscribeCount = count($arResult["ITEMS"]["ProdSubscribe"]);
$naCount = count($arResult["ITEMS"]["nAnCanBuy"]);

if (is_array($_SESSION["CATALOG_COMPARE_LIST"][$catalogIblockID]["ITEMS"]))
	$compareCount = count($_SESSION["CATALOG_COMPARE_LIST"][$catalogIblockID]["ITEMS"]);
else
	$compareCount = 0;

$arParamsExport = $arParams;
$paramsString = urlencode(serialize($arParamsExport));

// update basket counters
\Bitrix\Main\Loader::includeModule('aspro.lite');
$title_basket =  ($normalCount ? GetMessage("BASKET_COUNT", array("#PRICE#" => $arResult['allSum_FORMATED'])) : GetMessage("EMPTY_BLOCK_BASKET"));
$title_delay = ($delayCount ? GetMessage("BASKET_DELAY_COUNT", array("#PRICE#" => $arResult["DELAY_PRICE"]["SUMM_FORMATED"])) : GetMessage("EMPTY_BLOCK_DELAY"));

?>

<div class="wrap_cont dropdown-product-wrap">
	<? $frame = $this->createFrame()->begin(''); ?>
	<input type="hidden" name="total_price" value="<?= $arResult['allSum_FORMATED'] ?>" />
	<input type="hidden" name="total_discount_price" value="<?= $arResult['allSum_FORMATED'] ?>" />
	<input type="hidden" name="total_count" value="<?= $normalCount; ?>" />

	<? if ($_POST['firstTime']) : ?>
		<script src="<?=$GLOBALS['APPLICATION']->oAsset->getFullAssetPath($templateFolder.'/script.js')?>" type="text/javascript"></script>
	<? endif; ?>
	<?
	include($_SERVER["DOCUMENT_ROOT"] . $templateFolder . "/functions.php");
	
	if (is_array($arResult["WARNING_MESSAGE"]) && !empty($arResult["WARNING_MESSAGE"])) {
		foreach ($arResult["WARNING_MESSAGE"] as $v) {
			echo ShowError($v);
		}
	}

	$arMenu = array(array("ID" => "AnDelCanBuy", "TITLE" => GetMessage("SALE_BASKET_ITEMS"), "COUNT" => $normalCount, "FILE" => "/basket_items.php"));
	?>

	<? $arError = TSolution\Product\Basket::checkAllowDelivery($arResult["allSum"], CSaleLang::GetLangCurrency(SITE_ID)); ?>
	<form method="post" action="<?= POST_FORM_ACTION_URI ?>" name="basket_form" id="basket_form" class="basket_wrapp">
		<? if (strlen($arResult["ERROR_MESSAGE"]) <= 0) { ?>
			<ul class="tabs_content basket">
				<? foreach ($arMenu as $key => $arElement) { ?>
					<li class="<?= ($arElement["SELECTED"] ? ' cur' : ''); ?><?= ($arError["ERROR"] ? ' min-price' : ''); ?>" item-section="<?= $arElement["ID"] ?>"><? include($_SERVER["DOCUMENT_ROOT"] . $templateFolder . $arElement["FILE"]); ?></li>
				<? } ?>
			</ul>
		<? } else { ?>
			<ul class="tabs_content basket">
				<li class="cur" item-section="AnDelCanBuy"><? include($_SERVER["DOCUMENT_ROOT"] . $templateFolder . "/basket_items.php"); ?>
			</ul>
		<? } ?>
		<input id="fly_basket_params" type="hidden" name="PARAMS" value='<?= $paramsString ?>' />
	</form>

	<script>
		<? if ($arParams["AJAX_MODE_CUSTOM"] == "Y") : ?>
			$("#basket_form").ready(function() {
				$('form[name^=basket_form] .remove').unbind('click').click(function(e) {
					e.preventDefault();
					var row = $(this).parents(".dropdown-product__item").first();
					row.fadeTo(100, 0.05, function() {});
					deleteProduct($(this).parents("[data-id]").attr('data-id'), $(this).parents("[data-id]").attr('product-id'), $(this).parents("[data-id]"));
					return false;
				});
			});
		<? endif; ?>
	</script>
	<? if (\Bitrix\Main\Loader::includeModule("currency")) {
		CJSCore::Init(array('currency'));
		$currencyFormat = CCurrencyLang::GetFormatDescription(CSaleLang::GetLangCurrency(SITE_ID));
	}
	?>
	<script type="text/javascript">
		<? if (is_array($currencyFormat)) : ?>
			function jsPriceFormat(_number) {
				BX.Currency.setCurrencyFormat('<?= CSaleLang::GetLangCurrency(SITE_ID); ?>', <? echo CUtil::PhpToJSObject($currencyFormat, false, true); ?>);
				return BX.Currency.currencyFormat(_number, '<?= CSaleLang::GetLangCurrency(SITE_ID); ?>', true);
			}
		<? endif; ?>
		BX.loadCSS(['<?=$GLOBALS['APPLICATION']->oAsset->getFullAssetPath(SITE_TEMPLATE_PATH.'/css/basket.css');?>']);
	</script>
	<? $frame->end(); ?>
</div>