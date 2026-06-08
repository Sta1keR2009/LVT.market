<?php

use \Bitrix\Main\Localization\Loc;

$arExtensions = ['fancybox', 'detail'];

if($arParams["USE_SHARE"] || $arParams["USE_RSS"]) {
	$arExtensions[] = 'item_action';
	$arExtensions[] = 'share';
}

Loc::loadMessages(__FILE__);?>

<?
if($templateData['BANNER_TOP_ON_HEAD']){
	// single detail image
	$GLOBALS['bodyDopClass'] .= ' has-long-banner header_opacity front_page';
	$APPLICATION->SetPageProperty('HEADER_COLOR', 'light');
	$APPLICATION->SetPageProperty('HEADER_LOGO', 'light');
	$arExtensions[] = 'header_opacity';
	$arExtensions[] = 'banners';
}
?>
<div class="partner-epilog">	
	<?
	$arBlockOrder = explode(",", $arParams['DETAIL_BLOCKS_ORDER']);
	foreach ($arBlockOrder as $code) {
		switch ($code) {
			default :
				include_once 'epilog_blocks/' . $code . '.php';
				break;
		}
	}
	?>
</div>
<?
\TSolution\Extensions::init($arExtensions);
?>