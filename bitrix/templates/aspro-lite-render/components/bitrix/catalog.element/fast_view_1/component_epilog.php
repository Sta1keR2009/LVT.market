<?
$arExtensions = ['fancybox', 'alphanumeric', 'chars', 'stores_amount'];
if($templateData["USE_OFFERS_SELECT"]){
	$arExtensions[] = 'select_offer';
	$arExtensions[] = 'select_offer_load';
}
TSolution\Extensions::init($arExtensions);
?>
<script type="text/javascript">
var viewedCounter = {
	path: '/bitrix/components/bitrix/catalog.element/ajax.php',
	params: {
		AJAX: 'Y',
		SITE_ID: '<?=SITE_ID?>',
		PRODUCT_ID: '<?=$arResult['ID']?>',
		PARENT_ID: '<?=$arResult['ID']?>',
	}
};
BX.ready(
	BX.defer(function(){
		BX.ajax.post(
			viewedCounter.path,
			viewedCounter.params
		);
	})
);

viewItemCounter('<?=$arResult['ID']?>', '<?=current($arParams['PRICE_CODE'])?>');
</script>