<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);

global $arTheme, $APPLICATION;

$bShowLeftBlock = ($arTheme['BLOG_PAGE_LEFT_BLOCK']['VALUE'] === 'Y' && !defined('ERROR_404'));
$APPLICATION->SetPageProperty('MENU', ($bShowLeftBlock ? 'Y' : 'N' ));

// geting section items count and section [ID, NAME]
$arItemFilter = TSolution::GetCurrentSectionElementFilter($arResult["VARIABLES"], $arParams);
$arSectionFilter = TSolution::GetCurrentSectionFilter($arResult["VARIABLES"], $arParams);
$itemsCnt = TSolution\Cache::CIblockElement_GetList(array("CACHE" => array("TAG" => TSolution\Cache::GetIBlockCacheTag($arParams["IBLOCK_ID"]))), $arItemFilter, array());
$arSection = TSolution\Cache::CIblockSection_GetList(array("CACHE" => array("TAG" => TSolution\Cache::GetIBlockCacheTag($arParams["IBLOCK_ID"]), "MULTI" => "N")), $arSectionFilter, false, array('ID', 'NAME', 'DESCRIPTION', 'PICTURE', 'DETAIL_PICTURE', 'UF_TOP_SEO'), true);
?>
<?if(!$arSection && $arParams['SET_STATUS_404'] !== 'Y'):?>
	<?// get element start
	$arItemElementFilter = TSolution::GetCurrentElementFilter($arResult['VARIABLES'], $arParams);
	if ($arItemElementFilter) {
		foreach ($arItemElementFilter as $key => $value) {
			if ($key == 'SECTION_CODE') {
				$arItemElementFilter['CODE'] = $value;
				$arResult['VARIABLES']['ELEMENT_CODE'] = $value;
				unset($arItemElementFilter[$key]);
				unset($arResult['VARIABLES']['SECTION_CODE']);
			}
			if ($key == 'SECTION_ID') {
				$arItemElementFilter['ID'] = $value;
				$arResult['VARIABLES']['ELEMENT_ID'] = $value;
				unset($arItemElementFilter[$key]);
				unset($arResult['VARIABLES']['SECTION_ID']);
			}
		}
		$arItemElementFilter['SECTION_ID'] = $arResult['VARIABLES']['SECTION_ID'] = 0;
	}
	if ($arParams['CACHE_GROUPS'] == 'Y') {
		$arItemElementFilter['CHECK_PERMISSIONS'] = 'Y';
		$arItemElementFilter['GROUPS'] = $GLOBALS["USER"]->GetGroups();
	}

	$arElement = TSolution\Cache::CIblockElement_GetList(array('CACHE' => array('TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 'MULTI' => 'N')), $arItemElementFilter, false, false, array('ID', 'PREVIEW_TEXT', 'IBLOCK_SECTION_ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'));
	if ($arElement) {
		include_once('detail.php');
		return ;
	}
	// get element end?>
	<div class="alert alert-warning"><?=GetMessage("SECTION_NOTFOUND")?></div>
<?elseif (!$arSection && $arParams['SET_STATUS_404'] === 'Y'):?>
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

	<?
	// edit/add/delete buttons for edit mode
	$arSectionButtons = CIBlock::GetPanelButtons($arSection['IBLOCK_ID'], 0, $arSection['ID'], array('SESSID' => false, 'CATALOG' => true));
	$this->AddEditAction($arSection['ID'], $arSectionButtons['edit']['edit_section']['ACTION_URL'], CIBlock::GetArrayByID($arSection['IBLOCK_ID'], 'SECTION_EDIT'));
	$this->AddDeleteAction($arSection['ID'], $arSectionButtons['edit']['delete_section']['ACTION_URL'], CIBlock::GetArrayByID($arSection['IBLOCK_ID'], 'SECTION_DELETE'), array('CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
	?>
	<div class="main-section-wrapper" id="<?=$this->GetEditAreaId($arSection['ID'])?>">

		<?if($arSection['UF_TOP_SEO'] && strpos($_SERVER['REQUEST_URI'], 'PAGEN') === false):?>
			<div class="text_before_items">
				<p><?=$arSection['UF_TOP_SEO'];?></p>
			</div>
		<?endif;?>

		<?// get SECTIONS and TAG for right block?>
		<?$arSideInfo = TSolution\Functions::getSectionsWithElementCount([
			'FILTER' => $arItemFilter,
			'SECTION' => $arSection,
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
						'ALL_ARTICLES_ITEM' => [
							'TEXT' => GetMessage('ALL_ARTICLES'),
							'LINK' => $arParams['SEF_FOLDER'],
							'CURRENT' => 'Y',
							'ELEMENT_COUNT' => $allElementCount
						],
					],
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
						"FILTER_NAME" => "",
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

		<?if($arSection['DESCRIPTION'] && strpos($_SERVER['REQUEST_URI'], 'PAGEN') === false):?>
			<div class="text_after_items">
				<?=$arSection['DESCRIPTION'];?>
			</div>
		<?endif;?>
		
	</div>
<?endif;?>