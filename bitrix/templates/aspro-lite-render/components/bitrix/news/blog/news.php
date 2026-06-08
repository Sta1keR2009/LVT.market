<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?
global $arTheme, $APPLICATION;
$bShowLeftBlock = ($arTheme['BLOG_PAGE_LEFT_BLOCK']['VALUE'] === 'Y' && !defined('ERROR_404'));
$APPLICATION->SetPageProperty('MENU', ($bShowLeftBlock ? 'Y' : 'N' ));
?>
<?
$arItemFilter = TSolution::GetIBlockAllElementsFilter($arParams);

if ($arParams['CACHE_GROUPS'] == 'Y') {
	$arItemFilter['CHECK_PERMISSIONS'] = 'Y';
	$arItemFilter['GROUPS'] = $GLOBALS["USER"]->GetGroups();
}

$itemsCnt = TSolution\Cache::CIblockElement_GetList(array("CACHE" => array("TAG" => TSolution\Cache::GetIBlockCacheTag($arParams["IBLOCK_ID"]))), $arItemFilter, array());?>

	<?$this->SetViewTarget('more_text_title');?>
		<?if($arParams['USE_RSS'] !== 'N'):?>
			<?TSolution\Functions::showHeadingIcons([
				'CONTENT' => TSolution\Functions::ShowRSSIcon(
					array(
						'INNER_CLASS' => 'item-action__inner--md  item-action__inner',
						'URL' => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['rss'],
						'RETURN' => true,
					)
				)])?>
			<?$arExtensions[] = 'item_action';?>
			<?\TSolution\Extensions::init($arExtensions);?>
		<?endif;?>
	<?$this->endViewTarget()?>

<?if(!$itemsCnt):?>
	<div class="alert alert-warning"><?=GetMessage("SECTION_EMPTY")?></div>
<?else:?>
	<?TSolution::CheckComponentTemplatePageBlocksParams($arParams, __DIR__);?>

	<?// get SECTIONS and TAG for right block?>
	<?$arSideInfo = \TSolution\Functions::getSectionsWithElementCount([
		'FILTER' => $arItemFilter,
		'PARAMS' => $arParams
	])?>

	<?$allElementCount = 0;
	foreach($arSideInfo['SECTIONS'] as $arSection){
		$allElementCount += $arSection["ELEMENT_COUNT"];
	}
	?>
	<?$this->__component->__template->SetViewTarget('under_sidebar_content');?>
		<?if(count($arSideInfo['SECTIONS']) > 1):?>
			<?\TSolution\Functions::showBlockHtml([
				'FILE' => '/menu/blog_side.php',
				'PARAMS' => [
					'SECTIONS' => $arSideInfo['SECTIONS'],
					'ALL_ARTICLES_ITEM'	=> [
						'TEXT' => GetMessage('ALL_ARTICLES'),
						'LINK' => $arParams['SEF_FOLDER'],
						'CURRENT' => 'Y',
						'IS_PARENT' => 'Y',
						'ELEMENT_COUNT' => $allElementCount
					],
				]
			])?>
		<?endif;?>
		<?if($arSideInfo['TAGS']):?>
			<?$APPLICATION->IncludeComponent(
				"bitrix:search.tags.cloud",
				"main",
				Array( 
					"CACHE_TIME" => "86400",
					"CACHE_TYPE" => "A",
					"CHECK_DATES" => "Y",
					"COLOR_NEW" => "3E74E6",
					"COLOR_OLD" => "C0C0C0",
					"COLOR_TYPE" => "N",
					"FILTER_NAME" => "",
					"FONT_MAX" => "50",
					"FONT_MIN" => "10",
					"PAGE_ELEMENTS" => "150",
					"TAGS_ELEMENT" => $arSideInfo['TAGS'],
					"FILTER_NAME" => $arParams["FILTER_NAME"],
					"PERIOD" => "",
					"PERIOD_NEW_TAGS" => "",
					"SHOW_CHAIN" => "N",
					"SORT" => "NAME",
					"TAGS_INHERIT" => "Y",
					"URL_SEARCH" => SITE_DIR."search/index.php",
					"WIDTH" => "100%",
					"arrFILTER" => array("iblock_aspro_lite_content"),
					"arrFILTER_iblock_aspro_lite_content" => array($arParams["IBLOCK_ID"])
				), $component
			);?>
		<?endif;?>
		<?
		TSolution\Functions::showBlockHtml([
			'FILE' => '/menu/blog_side_subscribe.php',
			'PARAMS' => [
					'DOP_CLASS' => 'font_weight--500 color_dark font_normal switcher-title',
				],
			]
		);
		?>
	<?$this->__component->__template->EndViewTarget();?>

	<?if (TSolution::checkAjaxRequest()):?>
		<?$APPLICATION->RestartBuffer()?>
	<?endif;?>
		
	<?// section elements?>
	<?$sViewElementsTemplate = ($arParams["SECTION_ELEMENTS_TYPE_VIEW"] == "FROM_MODULE" ? $arTheme["BLOG_PAGE"]["VALUE"] : $arParams["SECTION_ELEMENTS_TYPE_VIEW"]);?>
	<?@include_once('page_blocks/'.$sViewElementsTemplate.'.php');?>

	<?if (TSolution::checkAjaxRequest()):?>
		<?die()?>
	<?endif;?>
<?endif;?>