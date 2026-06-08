<?
use CLite as Solution,
	Aspro\Lite\Functions\Extensions;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);

Extensions::init('searchtitle');

$INPUT_ID = trim($arParams["~INPUT_ID"]);
if(strlen($INPUT_ID) <= 0)
	$INPUT_ID = "title-search-input";
$INPUT_ID = CUtil::JSEscape($INPUT_ID);
$CONTAINER_ID = trim($arParams["~CONTAINER_ID"]);
if(strlen($CONTAINER_ID) <= 0)
	$CONTAINER_ID = "title-search";
$CONTAINER_ID = CUtil::JSEscape($CONTAINER_ID);

global $isFixedTopSearch;
$INPUT_ID_TMP = $INPUT_ID;
if(isset($isFixedTopSearch) && $isFixedTopSearch)
{
	$CONTAINER_ID .= 'tf';
	$INPUT_ID .= 'tf';
	$isFixedTopSearch = false;
}

?>
<?if($arParams["SHOW_INPUT"] !== "N"):?>
	<div class="search-wrapper relative">
		<div id="<?=$CONTAINER_ID?>">
			<form action="<?=$arResult["FORM_ACTION"]?>" class="search search--hastype lvt-header-search-form">
				<button class="search-input-close btn-close fill-dark-light-block" type="button">
					<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH."/images/svg/header_icons.svg#close-16-16", "clear ", ['WIDTH' => 16,'HEIGHT' => 16]);?>
				</button>
				<div class="search-input-div">
					<input class="search-input font_16 banner-light-text form-control js-lvt-header-search-q" id="<?=$INPUT_ID?>" type="text" name="q" value="" placeholder="<?=GetMessage("CT_BST_SEARCH_BUTTON")?>" size="40" maxlength="80" autocomplete="off" data-placeholder-site="<?=htmlspecialcharsbx(GetMessage("CT_BST_SEARCH_BUTTON"))?>" />
				</div>
				
				<div class="search-button-div">
					<button class="btn btn--no-rippple btn-clear-search fill-dark-light-block banner-light-icon-fill light-opacity-hover" type="reset" name="rs">
						<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH."/images/svg/header_icons.svg#close-9-9", "clear ", ['WIDTH' => 9,'HEIGHT' => 9]);?>
					</button>

					<div class="dropdown-select lvt-search-mode-dropdown">
						<input type="hidden" name="type" value="catalog" />

						<div class="dropdown-select__title font_14 font_large fill-dark-light banner-light-text">
							<span class="js-lvt-search-mode-label">Поиск по сайту</span>
						</div>
					</div>

					<button class="btn btn-search btn--no-rippple fill-dark-light-block banner-light-icon-fill light-opacity-hover" type="submit" name="s" value="<?=GetMessage("CT_BST_SEARCH_BUTTON")?>">
						<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH."/images/svg/header_icons.svg#search-18-18", "search ", ['WIDTH' => 18,'HEIGHT' => 18]);?>
					</button>
				</div>
			</form>
		</div>
	</div>
<?endif;?>

<script type="text/javascript">
	var jsControl = new JCTitleSearch2({
		//'WAIT_IMAGE': '/bitrix/themes/.default/images/wait.gif',
		'AJAX_PAGE' : '<?=CUtil::JSEscape(POST_FORM_ACTION_URI)?>',
		'CONTAINER_ID': '<?=$CONTAINER_ID?>',
		'INPUT_ID': '<?=$INPUT_ID?>',
		'INPUT_ID_TMP': '<?=$INPUT_ID?>',
		'MIN_QUERY_LEN': 2
	});
</script>