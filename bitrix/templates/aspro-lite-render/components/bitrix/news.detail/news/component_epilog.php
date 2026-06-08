<?
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
global $arTheme, $APPLICATION;

//CJSCore::Init('aspro_fancybox');
$arExtensions = ['fancybox', 'detail'];

if ($templateData['SHOW_DISCOUNT_COUNTER']) {
	$arExtensions[] = 'countdown';
}

if($templateData['SECTION_BNR_CONTENT']){
    // top banner
    $GLOBALS['SECTION_BNR_CONTENT'] = true;
    $GLOBALS['bodyDopClass'] .= ' has-long-banner has-top-banner '.($templateData['SECTION_BNR_UNDER_HEADER'] === 'YES' ? 'header_opacity front_page' : '');
    if($templateData['SECTION_BNR_COLOR'] !== 'dark'){
        $APPLICATION->SetPageProperty('HEADER_COLOR', 'light');
        $APPLICATION->SetPageProperty('HEADER_LOGO', 'light');
    }
    if($templateData['SECTION_BNR_UNDER_HEADER'] === 'YES'){
        $arExtensions[] = 'header_opacity';
    }
    $arExtensions[] = 'banners';
}
elseif($templateData['BANNER_TOP_ON_HEAD']){
    // single detail image
    $GLOBALS['bodyDopClass'] .= ' has-long-banner header_opacity front_page';
    $APPLICATION->SetPageProperty('HEADER_COLOR', 'light');
    $APPLICATION->SetPageProperty('HEADER_LOGO', 'light');
    $arExtensions[] = 'header_opacity';
    $arExtensions[] = 'banners';
}

if($arParams["USE_SHARE"] || $arParams["USE_RSS"]) {
    $arExtensions[] = 'item_action';
    $arExtensions[] = 'share';
}

// can order?
$bOrderViewBasket = $templateData["ORDER"];

// use tabs?
if($arParams['USE_DETAIL_TABS'] === 'Y'){
    $bUseDetailTabs = true;
}
else{
    $bUseDetailTabs = false;
}

if ($arTheme['SHOW_PROJECTS_MAP_DETAIL']['VALUE'] == 'N') {
    unset($templateData['MAP']);
}

// blocks order
if(
    !$bUseDetailTabs &&
    array_key_exists('DETAIL_BLOCKS_ALL_ORDER', $arParams) &&
    $arParams["DETAIL_BLOCKS_ALL_ORDER"]
){
    $arBlockOrder = explode(",", $arParams["DETAIL_BLOCKS_ALL_ORDER"]);
}
else{
    $arBlockOrder = explode(",", $arParams["DETAIL_BLOCKS_ORDER"]);
    $arTabOrder = explode(",", $arParams["DETAIL_BLOCKS_TAB_ORDER"]);
}
$arExtensions[] = 'video';

\TSolution\Extensions::init($arExtensions);
?>
<div class="services-detail__bottom-info">
    <?foreach($arBlockOrder as $blockCode):?>
        <?include 'epilog_blocks/'.$blockCode.'.php';?>
    <?endforeach;?>
    <?include 'epilog_blocks/tags.php';?>
</div>
