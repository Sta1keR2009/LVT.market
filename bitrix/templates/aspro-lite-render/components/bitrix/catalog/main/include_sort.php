<?if($itemsCnt || isset($bSearchPage)):?>
	<?
	if($_SESSION[$arParams["SECTION_DISPLAY_PROPERTY"].'_'.$arParams['IBLOCK_ID']] === NULL){
		$arUserFieldViewType = CUserTypeEntity::GetList(array(), array('ENTITY_ID' => 'IBLOCK_'.$arParams['IBLOCK_ID'].'_SECTION', 'FIELD_NAME' => $arParams["SECTION_DISPLAY_PROPERTY"]))->Fetch();
		$resUserFieldViewTypeEnum = CUserFieldEnum::GetList(array(), array('USER_FIELD_ID' => $arUserFieldViewType['ID']));
		while($arUserFieldViewTypeEnum = $resUserFieldViewTypeEnum->GetNext()){
			$_SESSION[$arParams["SECTION_DISPLAY_PROPERTY"].'_'.$arParams['IBLOCK_ID']][$arUserFieldViewTypeEnum['ID']] = $arUserFieldViewTypeEnum['XML_ID'];
		}
	}

	unset($_SESSION[$arParams['IBLOCK_ID'].md5(serialize((array)$arParams['SORT_PROP']))]);

	$arDisplays = array("table", "list", "price");
	if (
		array_key_exists('display', $_REQUEST) &&
		!empty($_REQUEST['display']) &&
		(in_array(trim($_REQUEST["display"]), $arDisplays))
	) {
		setcookie('catalogViewMode', $_REQUEST['display'], 0, SITE_DIR);
		$_COOKIE['catalogViewMode'] = $_REQUEST['display'];
	}
	if (array_key_exists('sort', $_REQUEST) && !empty($_REQUEST['sort']) && !(isset($bSortRank) && $bSortRank)) {
		setcookie('catalogSort', $_REQUEST['sort'], 0, SITE_DIR);
		$_COOKIE['catalogSort'] = $_REQUEST['sort'];
	}
	if (array_key_exists('order', $_REQUEST) && !empty($_REQUEST['order'])) {
		setcookie('catalogOrder', $_REQUEST['order'], 0, SITE_DIR);
		$_COOKIE['catalogOrder'] = $_REQUEST['order'];
	}
	if (array_key_exists('show', $_REQUEST) && !empty($_REQUEST['show'])) {
		setcookie('catalogPageElementCount', $_REQUEST['show'], 0, SITE_DIR);
		$_COOKIE['catalogPageElementCount'] = $_REQUEST['show'];
	}

	if (isset($_COOKIE['catalogViewMode']) && $_COOKIE['catalogViewMode']) {
		$display = $_COOKIE['catalogViewMode'];
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

	if ($arSection["DISPLAY"] && in_array($arSection["DISPLAY"], $arDisplays)) {
		if ($arParams['SHOW_LIST_TYPE_SECTION'] != 'N') {
			if (!isset($_COOKIE['catalogViewMode'])) {
				$display = $arSection["DISPLAY"];
			}
		} else {
			$display = $arSection["DISPLAY"];
			$bForceDisplay = true;
		}
	}

	if ($display) {
		if (!in_array(trim($display), $arDisplays)) {
			$display = "table";
		}
	} else {
		$display = "table";
	}

	$arDelUrlParams = array('sort', 'order', 'control_ajax', 'ajax_get_filter', 'ajax_get', 'linerow', 'display');

    $showSmartFilter = 'N' !== $arTheme['SHOW_SMARTFILTER']['VALUE'];
    if (isset($bSearchPage)) {
        $showSmartFilter = $showSmartFilter && 'N' !== $arParams['USE_SMARTFILTER_ON_SEARCH_PAGE'];
    } else {
        $showSmartFilter = $showSmartFilter && $itemsCnt;
    }
	?>
	<!-- noindex -->
	<div class="filter-panel sort_header view_<?=$display?> flexbox flexbox--direction-row flexbox--justify-beetwen ">
		<div class="filter-panel__part-left ">
			<div class="line-block filter-panel__main-info">
				<?if($showSmartFilter):?>
					<?$bActiveFilter = TSolution\Functions::checkActiveFilterPage([
						'SEF_URL' => $arParams["SEF_URL_TEMPLATES"]['smart_filter'],
						'GLOBAL_FILTER' => $arParams['FILTER_NAME']
					]);?>
					<div class="line-block__item filter-panel__filter  <?=($arParams['FILTER_VIEW'] == "COMPACT" ? 'visible-767' : 'visible-991');?>">
						<div class="fill-theme-hover dark_link">
							<div class="bx-filter-title filter_title <?=($bActiveFilter && $bActiveFilter[1] != 'clear' ? 'active-filter' : '')?>">
								<?=TSolution::showIconSvg("icon svg-inline-catalog fill-dark-light", SITE_TEMPLATE_PATH.'/images/svg/catalog/filter_gears.svg', '', '', true, false);?>
								<span class="dotted"><?=\Bitrix\Main\Localization\Loc::getMessage("CATALOG_SMART_FILTER_TITLE");?></span>
							</div>
							<div class="controls-hr"></div>
						</div>
					</div>
				<?endif;?>

				<?
				$obSort = new TSolution\Template\Sort\Base($arParams, [
					'delUrlParams' => $arDelUrlParams,
					'availableRank' => ($arAvailableRank ?? false),
					'sortByRank' => ($bSortRank ?? false),
					'cookie' => [
						'show' => 'catalogPageElementCount',
						'sort' => 'catalogSort',
						'order' => 'catalogOrder',
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
			<?if ($arParams['FILTER_VIEW'] == "COMPACT"){
				include_once(__DIR__."/include_filter.php");
			}?>
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
	<!-- /noindex -->
<?endif;?>
