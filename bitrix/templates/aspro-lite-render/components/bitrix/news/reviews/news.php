<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Bitrix\Main\Localization\Loc;

$this->setFrameMode(true);

Loc::loadMessages(__FILE__);

$arItemFilter = TSolution::GetIBlockAllElementsFilter($arParams);
$itemsCnt = TSolution\Cache::CIblockElement_GetList(['CACHE' => ['TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID'])]], $arItemFilter, []);
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
	<?endif;?>
<?$this->endViewTarget()?>

<?php include('include_top_block.php'); ?>
<?
if($arParams["USE_SHARE"] || $arParams["USE_RSS"]) {
	$arExtensions[] = 'item_action';
	\TSolution\Extensions::init($arExtensions);
}
?>
<? if (!$itemsCnt): ?>
	<div class="alert alert-warning"><?= Loc::getMessage('REVIEWS__SECTION_EMPTY') ?></div>
<? else : ?>
	<?global $arTheme;?>
	<?TSolution::CheckComponentTemplatePageBlocksParams($arParams, __DIR__);?>
	<?// section elements?>
	<?$sViewElementsTemplate = ($arParams["SECTION_ELEMENTS_TYPE_VIEW"] == "FROM_MODULE" ? $arTheme["REVIEWS_PAGE"]["VALUE"] : $arParams["SECTION_ELEMENTS_TYPE_VIEW"]);?>
	<?@include_once('page_blocks/'.$sViewElementsTemplate.'.php');?>
<? endif ?>

