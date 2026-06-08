<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? $this->setFrameMode(true); ?>

<? global $arTheme; ?>
<? $sViewElementsTemplate = ($arParams["SECTION_ELEMENTS_TYPE_VIEW"] == "FROM_MODULE" ? $arTheme["BRANDS_PAGE"]["VALUE"] : $arParams["SECTION_ELEMENTS_TYPE_VIEW"]); ?>
<?$bViewWithGroups = strpos($sViewElementsTemplate, 'with_group') !== false;?>

<?$this->SetViewTarget('more_text_title');?>
	<?// rss?>
	<?if($arParams['USE_RSS'] !== 'N'):?>
		<?TSolution\Functions::showHeadingIcons([
			'CONTENT' => TSolution\Functions::ShowRSSIcon(
				array(
					'INNER_CLASS' => 'item-action__inner item-action__inner--md',
					'URL' => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['rss'],
					'RETURN' => true,
				)
			)])?>
			<?$arExtensions[] = 'item_action';?>
			<?\TSolution\Extensions::init($arExtensions);?>
	<?endif;?>
<?$this->endViewTarget()?>

<div class="text_before_items">
	<? $APPLICATION->IncludeComponent(
		"bitrix:main.include",
		"",
		array(
			"AREA_FILE_SHOW" => "page",
			"AREA_FILE_SUFFIX" => "inc",
			"EDIT_TEMPLATE" => ""
		)
	); ?>
	</div>

<?if (!$bViewWithGroups):?>
	<?
	$arItemFilter = TSolution::GetIBlockAllElementsFilter($arParams);
	$itemsCnt = TSolution\Cache::CIblockElement_GetList(array("CACHE" => array("TAG" => TSolution\Cache::GetIBlockCacheTag($arParams["IBLOCK_ID"]))), $arItemFilter, array());
	?>
	<? if (!$itemsCnt): ?>
		<div class="alert alert-warning"><?= GetMessage("SECTION_EMPTY") ?></div>
	<? else: ?>
		<? TSolution::CheckComponentTemplatePageBlocksParams($arParams, __DIR__); ?>


		<? // section elements?>
		<? if (strlen($arParams["FILTER_NAME"])): ?>
			<? $GLOBALS[$arParams["FILTER_NAME"]] = array_merge((array)$GLOBALS[$arParams["FILTER_NAME"]], $arItemFilter); ?>
		<? else: ?>
			<? $arParams["FILTER_NAME"] = "arrFilter"; ?>
			<? $GLOBALS[$arParams["FILTER_NAME"]] = $arItemFilter; ?>
		<? endif; ?>
		
		<? @include_once('page_blocks/' . $sViewElementsTemplate . '.php'); ?>
	<? endif; ?>
<?else:?>
	<?@include_once('page_blocks/'.$sViewElementsTemplate.'.php');?>
<?endif;?>