<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<?$this->setFrameMode(true);?>
<?
// get element
$arItemFilter = TSolution::GetCurrentElementFilter($arResult['VARIABLES'], $arParams);

global $APPLICATION, $arTheme;
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/animation/animate.min.css');

$bShowLeftBlock = ($arTheme['LEFT_BLOCK_SALE_DETAIL']['VALUE'] === 'Y' && !defined('ERROR_404'));
$APPLICATION->SetPageProperty('MENU', ($bShowLeftBlock ? 'Y' : 'N' ));

// cart
$bOrderViewBasket = (trim($arTheme['ORDER_VIEW']['VALUE']) === 'Y');

$arElement = TSolution\Cache::CIblockElement_GetList(
	array(
		'CACHE' => array(
			'TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID']),
			'MULTI' => 'N'
		)
	), 
	TSolution::makeElementFilterInRegion($arItemFilter), 
	false, 
	false, 
	array('ID', 'IBLOCK_SECTION_ID')
);

//bug fix bitrix for search element
if ($arElement) {
	$strict_check = $arParams["DETAIL_STRICT_SECTION_CHECK"] === "Y";
	if(!CIBlockFindTools::checkElement($arParams["IBLOCK_ID"], $arResult["VARIABLES"], $strict_check))
		$arElement = array();
}
?>
<?if(!$arElement && $arParams['SET_STATUS_404'] !== 'Y'):?>
	<div class="alert alert-warning"><?=GetMessage("ELEMENT_NOTFOUND")?></div>
<?elseif(!$arElement && $arParams['SET_STATUS_404'] === 'Y'):?>
	<?TSolution::goto404Page();?>
<?else:?>
	<?TSolution::CheckComponentTemplatePageBlocksParams($arParams, __DIR__);?>
	
	<?$this->SetViewTarget('more_text_title');?>
		<?//share?>
		<?if($arParams['USE_SHARE'] === 'Y' && $arElement):?>
			<?TSolution\Functions::showHeadingIcons([
				'CONTENT' => TSolution\Functions::showShareBlock(
					array(
						'INNER_CLASS' => ' item-action__inner item-action__inner--md',
						'CLASS' => 'top',
						'RETURN' => true,
					)
				)]);?>
		<?endif;?>
		<?// rss?>
		<?if($arParams['USE_RSS'] !== 'N'):?>
			<?TSolution\Functions::showHeadingIcons([
				'CONTENT' => TSolution\Functions::ShowRSSIcon(
					array(
						'INNER_CLASS' => ' item-action__inner item-action__inner--md',
						'URL' => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['rss'],
						'RETURN' => true,
					)
				)])?>
		<?endif;?>
	<?$this->endViewTarget()?>
	
	<?TSolution::AddMeta(
		array(
			'og:description' => $arElement['PREVIEW_TEXT'],
			'og:image' => (($arElement['PREVIEW_PICTURE'] || $arElement['DETAIL_PICTURE']) ? CFile::GetPath(($arElement['PREVIEW_PICTURE'] ? $arElement['PREVIEW_PICTURE'] : $arElement['DETAIL_PICTURE'])) : false),
		)
	);?>
	<div class="detail detail-maxwidth <?=($templateName = $component->{'__template'}->{'__name'})?>" itemscope itemtype="http://schema.org/Service">
		<?$arParams["GRUPPER_PROPS"] = $arTheme["GRUPPER_PROPS"]["VALUE"];
		if($arTheme["GRUPPER_PROPS"]["VALUE"] != "NOT")
		{
			$arParams["PROPERTIES_DISPLAY_TYPE"] = "TABLE";

			if($arParams["GRUPPER_PROPS"] == "GRUPPER" && !\Bitrix\Main\Loader::includeModule("redsign.grupper"))
				$arParams["GRUPPER_PROPS"] = "NOT";
			if($arParams["GRUPPER_PROPS"] == "WEBDEBUG" && !\Bitrix\Main\Loader::includeModule("webdebug.utilities"))
				$arParams["GRUPPER_PROPS"] = "NOT";
			if($arParams["GRUPPER_PROPS"] == "YENISITE_GRUPPER" && !\Bitrix\Main\Loader::includeModule("yenisite.infoblockpropsplus"))
				$arParams["GRUPPER_PROPS"] = "NOT";
		}?>

		<?//element?>
		<?$sViewElementTemplate = ($arParams["ELEMENT_TYPE_VIEW"] == "FROM_MODULE" ? $arTheme["SALE_DETAIL_PAGE"]["VALUE"] : $arParams["ELEMENT_TYPE_VIEW"]);?>
		<?@include_once('page_blocks/'.$sViewElementTemplate.'.php');?>

	</div>
	<?
	if (is_array($arElement['IBLOCK_SECTION_ID']) && count($arElement['IBLOCK_SECTION_ID']) > 1) {
		TSolution::CheckAdditionalChainInMultiLevel($arResult, $arParams, $arElement);
	}
	?>
<?endif;?>
<?
if($arElement['IBLOCK_SECTION_ID']){
	$arSection = TSolution\Cache::CIBlockSection_GetList(array('CACHE' => array('TAG' => TSolution\Cache::GetIBlockCacheTag($arElement['IBLOCK_ID']), 'MULTI' => 'N')), array('ID' => $arElement['IBLOCK_SECTION_ID'], 'ACTIVE' => 'Y'), false, array('ID', 'NAME', 'SECTION_PAGE_URL'));
}
?>
<div class="bottom-links-block<?=($arElement ? ' detail-maxwidth' : '')?>">
    <?// back url?>
    <?TSolution\Functions::showBackUrl(
        array(
            'URL' => ((isset($arSection) && $arSection) ? $arSection['SECTION_PAGE_URL'] : $arResult['FOLDER'].$arResult['URL_TEMPLATES']['news']),
            'TEXT' => ($arParams['T_PREV_LINK'] ? $arParams['T_PREV_LINK'] : GetMessage('BACK_LINK')),
        )
    );?>
</div>