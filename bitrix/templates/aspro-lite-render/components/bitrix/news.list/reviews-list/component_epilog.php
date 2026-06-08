<?
if (!$templateData['ITEMS']) {
	$GLOBALS['APPLICATION']->SetPageProperty('BLOCK_REVIEWS', 'hidden');
}

$arScripts = ['rating', 'swiper'];
TSolution\Extensions::init($arScripts);
