<?
if($_SESSION['UF_VIEWTYPE_BRAND_'.$arParams['IBLOCK_ID']] === NULL){
	$arUserFieldViewType = CUserTypeEntity::GetList(array(), array('ENTITY_ID' => 'IBLOCK_'.$arParams['IBLOCK_ID'].'_SECTION', 'FIELD_NAME' => $arParams["SECTION_DISPLAY_PROPERTY"]))->Fetch();
	$resUserFieldViewTypeEnum = CUserFieldEnum::GetList(array(), array('USER_FIELD_ID' => $arUserFieldViewType['ID']));
	while($arUserFieldViewTypeEnum = $resUserFieldViewTypeEnum->GetNext()){
		$_SESSION['UF_VIEWTYPE_BRAND_'.$arParams['IBLOCK_ID']][$arUserFieldViewTypeEnum['ID']] = $arUserFieldViewTypeEnum['XML_ID'];
	}
}

unset($_SESSION[$arParams['IBLOCK_ID'].md5(serialize((array)$arParams['SORT_PROP']))]);

$arDisplays = array("table", "list", "price");
if (
	array_key_exists('display', $_REQUEST) && 
	!empty($_REQUEST['display']) && 
	(in_array(trim($_REQUEST["display"]), $arDisplays))
) {
	setcookie('catalogNewsViewMode', $_REQUEST['display'], 0, SITE_DIR);
	$_COOKIE['catalogNewsViewMode'] = $_REQUEST['display'];
}
if (array_key_exists('sort', $_REQUEST) && !empty($_REQUEST['sort'])) {
	setcookie('catalogNewsSort', $_REQUEST['sort'], 0, SITE_DIR);
	$_COOKIE['catalogNewsSort'] = $_REQUEST['sort'];
}
if (array_key_exists('order', $_REQUEST) && !empty($_REQUEST['order'])) {
	setcookie('catalogNewsOrder', $_REQUEST['order'], 0, SITE_DIR);
	$_COOKIE['catalogNewsOrder'] = $_REQUEST['order'];
}
if (array_key_exists('show', $_REQUEST) && !empty($_REQUEST['show'])) {
	setcookie('catalogPageElementCount', $_REQUEST['show'], 0, SITE_DIR);
	$_COOKIE['catalogPageElementCount'] = $_REQUEST['show'];
}

if (isset($_COOKIE['catalogNewsViewMode']) && $_COOKIE['catalogNewsViewMode']) {
	$display = $_COOKIE['catalogNewsViewMode'];
} else {
	if (
		$arSection[$arParams["SECTION_DISPLAY_PROPERTY"]] && 
		isset($_SESSION[$arParams["SECTION_DISPLAY_PROPERTY"].'_'.$arParams['IBLOCK_ID']][$arSection[$arParams["SECTION_DISPLAY_PROPERTY"]]])
	) {
		$display = $_SESSION[$arParams["SECTION_DISPLAY_PROPERTY"].'_'.$arParams['IBLOCK_ID']][$arSection[$arParams["SECTION_DISPLAY_PROPERTY"]]];
	} else {
		$display = $arParams['VIEW_TYPE'];
	}
}

$bForceDisplay = false;	

if ($display) {
	if (!in_array(trim($display), $arDisplays)) {
		$display = "table";
	}
} else {
	$display = "table";
}

$arDelUrlParams = array('sort', 'order', 'control_ajax', 'ajax_get_filter', 'linerow', 'display');
?>
<!-- noindex -->
<div class="filter-panel sort_header view_<?=$display?> flexbox flexbox--direction-row flexbox--justify-beetwen ">
	<div class="filter-panel__part-left">
		<div class="line-block filter-panel__main-info">
			<?if($arTheme['SHOW_SMARTFILTER']['VALUE'] !== 'N' && $arItems):?>
				<div class="line-block__item filter-panel__filter <?=($bHideLeftBlock && !$bShowCompactHideLeft ? 'filter-panel__filter--visible' : '');?>">
					<div class="fill-theme-hover dark_link">
						<div class="bx-filter-title filter_title <?=($bActiveFilter && $bActiveFilter[1] != 'clear' ? 'active-filter' : '')?>">
							<?=TSolution::showIconSvg("icon svg-inline-catalog", SITE_TEMPLATE_PATH.'/images/svg/catalog/filter.svg', '', '', true, false);?>
							<span class="font_upper_md dotted font_bold"><?=\Bitrix\Main\Localization\Loc::getMessage("CATALOG_SMART_FILTER_TITLE");?></span>
						</div>
						<div class="controls-hr"></div>
					</div>
				</div>
			<?endif;?>

			<?
			$obSort = new TSolution\Template\Sort\Base($arParams, [
				'delUrlParams' => $arDelUrlParams,
				'cookie' => [
					'show' => 'catalogPageElementCount',
					'sort' => 'catalogNewsSort',
					'order' => 'catalogNewsOrder',
				],
			]);
			$sortHTML = $obSort->getHtml();

			$arAvailableSort = $obSort->arSort;
			$sort = $obSort->sortKeys['sort'];
			$order = $obSort->sortKeys['order'];
			$shows = $obSort->sortKeys['show'];
			$sortKey = $obSort->sortKeys['key'];
			?>
			<?=$sortHTML;?>
		</div>
		<?include_once(__DIR__."/filter.php");?>		
	</div>
	<?if (!$bForceDisplay):?>
		<div class="filter-panel__part-right">
				<div class="toggle-panel hide-600">
					<?foreach($arDisplays as $displayType):?>
						<?
						$current_url = '';
						$current_url = $APPLICATION->GetCurPageParam('display='.$displayType, $arDelUrlParams);
						$url = str_replace('+', '%2B', $current_url);
						?>
						<?if($display == $displayType):?>
							<span title="<?=\Bitrix\Main\Localization\Loc::getMessage("SECT_DISPLAY_".strtoupper($displayType))?>" class="toggle-panel__item toggle-panel__item--current"><?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/catalog/toggle_view.svg#'.$displayType, '', ['WIDTH' => '10px', 'HEIGHT' => '10px']);?></span>
						<?else:?>
							<a rel="nofollow prefetch" href="<?=$url;?>" data-url="<?=$url?>" title="<?=\Bitrix\Main\Localization\Loc::getMessage("SECT_DISPLAY_".strtoupper($displayType))?>" class="toggle-panel__item muted-use-no-hover <?=($arParams['AJAX_CONTROLS'] == 'Y' ? ' js-load-link' : '');?>"><?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/catalog/toggle_view.svg#'.$displayType, 'fill-dark-light', ['WIDTH' => '10px', 'HEIGHT' => '10px']);?></a>
						<?endif;?>
					<?endforeach;?>
				</div>
			</div>
			<?TSolution\Extensions::init('toggle_panel');?>
	<?endif;?>
</div>
<?TSolution\Extensions::init(['filter_panel', 'dropdown_select'])?>
<!-- /noindex -->
