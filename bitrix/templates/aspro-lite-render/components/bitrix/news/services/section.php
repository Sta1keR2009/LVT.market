<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);

global $arTheme, $APPLICATION;

$bShowLeftBlock = ($arTheme['LEFT_BLOCK_SERVICES_SECTIONS']['VALUE'] === 'Y' && !defined('ERROR_404'));
$APPLICATION->SetPageProperty('MENU', ($bShowLeftBlock ? 'Y' : 'N' ));

// get section items count and subsections
$arItemFilter = TSolution::GetCurrentSectionElementFilter($arResult["VARIABLES"], $arParams);
$arSectionFilter = TSolution::GetCurrentSectionFilter($arResult["VARIABLES"], $arParams);
$itemsCnt = TSolution\Cache::CIblockElement_GetList(array("CACHE" => array("TAG" => TSolution\Cache::GetIBlockCacheTag($arParams["IBLOCK_ID"]))), $arItemFilter, array());
$arSection = TSolution\Cache::CIblockSection_GetList(array("CACHE" => array("TAG" => TSolution\Cache::GetIBlockCacheTag($arParams["IBLOCK_ID"]), "MULTI" => "N")), $arSectionFilter, false, array('ID', 'NAME', 'DESCRIPTION', 'PICTURE', 'DETAIL_PICTURE', 'IBLOCK_ID', 'UF_TOP_SEO'));
$arSubSectionFilter = TSolution::GetCurrentSectionSubSectionFilter($arResult["VARIABLES"], $arParams, $arSection['ID']);
$arSubSections = TSolution\Cache::CIblockSection_GetList(array("CACHE" => array("TAG" => TSolution\Cache::GetIBlockCacheTag($arParams["IBLOCK_ID"]), "MULTI" => "Y")), $arSubSectionFilter, false, array("ID", "DEPTH_LEVEL"));
?>
<?if(!$arSection && $arParams['SET_STATUS_404'] !== 'Y'):?>
	<div class="alert alert-warning"><?=GetMessage("SECTION_NOTFOUND")?></div>
<?elseif(!$arSection && $arParams['SET_STATUS_404'] === 'Y'):?>
	<?TSolution::goto404Page();?>
<?else:?>
	<?
	TSolution::AddMeta(
		array(
			'og:description' => $arSection['DESCRIPTION'],
			'og:image' => (($arSection['PICTURE'] || $arSection['DETAIL_PICTURE']) ? CFile::GetPath(($arSection['PICTURE'] ? $arSection['PICTURE'] : $arSection['DETAIL_PICTURE'])) : false),
		)
	);

	TSolution::CheckComponentTemplatePageBlocksParams($arParams, __DIR__);
	?>
	
	<?$this->SetViewTarget('more_text_title');?>
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
	
	<?if(!$arSubSections && !$itemsCnt):?>
		<div class="alert alert-warning"><?=GetMessage("SECTION_EMPTY")?></div>
	<?endif;?>

	<?if (
		$arSection['UF_TOP_SEO'] &&
		strpos($_SERVER['REQUEST_URI'], 'PAGEN') === false &&
		$arParams['SHOW_SECTION_DESCRIPTION'] != 'N'
	):?>
		<div class="text_before_items">
			<p><?=$arSection['UF_TOP_SEO'];?></p>
		</div>
	<?endif;?>

	<?if($arSubSections):?>
		<?// sections list?>
		<?$sViewElementTemplate = ($arParams["SECTION_TYPE_VIEW"] == "FROM_MODULE" ? $arTheme["SECTION_TYPE_VIEW_SERVICES"]["VALUE"] : $arParams["SECTION_TYPE_VIEW"]);?>
		
		<div class="section-wrapper-list">
			<?@include_once('page_blocks/'.$sViewElementTemplate.'.php');?>
		</div>
	<?endif;?>

	<?if (TSolution::checkAjaxRequest()):?>
		<?$APPLICATION->RestartBuffer()?>
	<?endif;?>
	
	<?if(strlen($arParams["FILTER_NAME"])):?>
		<?$GLOBALS[$arParams["FILTER_NAME"]] = array_merge((array)$GLOBALS[$arParams["FILTER_NAME"]], $arItemFilter);?>
	<?else:?>
		<?$arParams["FILTER_NAME"] = "arrFilter";?>
		<?$GLOBALS[$arParams["FILTER_NAME"]] = $arItemFilter;?>
	<?endif;?>

	<?// section elements?>
	<?$sViewElementTemplate = ($arParams["SECTION_ELEMENTS_TYPE_VIEW"] == "FROM_MODULE" ? $arTheme["ELEMENTS_PAGE_SERVICES"]["VALUE"] : $arParams["SECTION_ELEMENTS_TYPE_VIEW"]);?>

	<?@include_once('page_blocks/'.$sViewElementTemplate.'.php');?>

	<?if (TSolution::checkAjaxRequest()):?>
		<?die()?>
	<?endif;?>

	<?if (
		$arSection['DESCRIPTION'] && 
		strpos($_SERVER['REQUEST_URI'], 'PAGEN') === false &&
		$arParams['SHOW_SECTION_DESCRIPTION'] != 'N'
	):?>
		<div class="text_after_items">
			<?=$arSection['DESCRIPTION'];?>
		</div>
	<?endif;?>
<?endif;?>