<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arExtensions = ['catalog', 'notice', 'catalog_block'];
if($arParams['SLIDER'] === true || $arParams['SLIDER'] === 'Y'){
	$arExtensions[] = 'swiper';
}
if ($arParams['SHOW_RATING'] === 'Y') {
	$arExtensions[] = 'rating';
}
if ($arParams['TYPE_SKU'] !== 'TYPE_2') {
	$arExtensions[] = 'select_offer_load';
}
if ($templateData['SHOW_DISCOUNT_COUNTER']) {
	$arExtensions[] = 'countdown';
}

//	big data json answers
if ($arParams['BIG_DATA_MODE'] === 'Y') {
	$request = \Bitrix\Main\Context::getCurrent()->getRequest();

	if (
		$request->isAjaxRequest() &&
		($request->get('action') === 'deferredLoad')
	) {
		$content = ob_get_contents();
		ob_end_clean();
		$arExtensions = [];

		list(, $itemsContainer) = explode('<!-- items-container -->', $content);

		$component::sendJsonAnswer(array(
			'items' => $itemsContainer,
		));
	}

	$arExtensions[] = 'countdown';
	$arExtensions[] = 'bigdata';
}

TSolution\Extensions::init($arExtensions);

if (isset($templateData['TEMPLATE_LIBRARY']) && !empty($templateData['TEMPLATE_LIBRARY'])){
	$loadCurrency = false;
	if (!empty($templateData['CURRENCIES']))
		$loadCurrency = \Bitrix\Main\Loader::includeModule('currency');
	CJSCore::Init($templateData['TEMPLATE_LIBRARY']);
	if ($loadCurrency){?>
	<script type="text/javascript">
		BX.Currency.setCurrencies(<? echo $templateData['CURRENCIES']; ?>);
	</script>
	<?}
}
if (!$templateData['ITEMS']) {
	$GLOBALS['APPLICATION']->SetPageProperty('BLOCK_CATALOG_TAB', 'hidden');
}
