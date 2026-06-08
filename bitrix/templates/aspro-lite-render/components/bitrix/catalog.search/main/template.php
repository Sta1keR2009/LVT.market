<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$isAjax="N";?>
<?if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest"  && isset($_GET["ajax_get"]) && $_GET["ajax_get"] == "Y" || (isset($_GET["ajax_basket"]) && $_GET["ajax_basket"]=="Y") || isset($_GET["control_ajax"])){
	$isAjax="Y";
}?>
<?if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest" && isset($_GET["ajax_get_filter"]) && $_GET["ajax_get_filter"] == "Y"  && !isset($_GET["control_ajax"])){
	$isAjaxFilter="Y";
}?>
<?
global $arTheme, $arRegion, $searchQuery;
$catalogIBlockID = $arParams["IBLOCK_ID"];
$arParams["AJAX_FILTER_CATALOG"] = "N";

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsPartnumberSearchHelper.php';
// Partnumber mode is fully disabled: only site search flow is allowed.
$bLvtPartnumberSearch = false;
?>

<?$APPLICATION->SetPageProperty("MENU", 'N');?>
<?$APPLICATION->AddViewContent('right_block_class', 'catalog_page search_page');?>

<?if($arParams["FILTER_NAME"] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"])){
	$arParams["FILTER_NAME"] = "searchFilter";
}


$bShowFilter = ($arTheme["SEARCH_VIEW_TYPE"]["VALUE"] == "with_filter");
$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.history.js');

// bitrix:search.page arrFILTER
$arSearchPageFilter = array(
	'arrFILTER' => array('iblock_'.$arParams['IBLOCK_TYPE']),
	'arrFILTER_iblock_'.$arParams['IBLOCK_TYPE'] => array($arParams['IBLOCK_ID']),
);

$arSKU = array();
if(TSolution::isSaleMode() && $arParams['IBLOCK_ID']){
	$arSKU = CCatalogSKU::GetInfoByProductIBlock($arParams['IBLOCK_ID']);
	if($arSKU['IBLOCK_ID']){
		$dbRes = CIBlock::GetByID($arSKU['IBLOCK_ID']);
		if($arSkuIblock = $dbRes ->Fetch()){
			$arSearchPageFilter['arrFILTER'][] = 'iblock_'.$arSkuIblock['IBLOCK_TYPE_ID'];
			$arSearchPageFilter['arrFILTER'] = array_unique($arSearchPageFilter['arrFILTER']);
			if(!$arSearchPageFilter['arrFILTER_iblock_'.$arSkuIblock['IBLOCK_TYPE_ID']]){
				$arSearchPageFilter['arrFILTER_iblock_'.$arSkuIblock['IBLOCK_TYPE_ID']] = array();
			}
			$arSearchPageFilter['arrFILTER_iblock_'.$arSkuIblock['IBLOCK_TYPE_ID']][] = $arSKU['IBLOCK_ID'];
		}
	}
}

// Витрина E-каталог (ИБ 41): при поиске из основного каталога (ИБ 11) расширяем фильтр модуля поиска
$lvtEcatalogIblockId = 41;
$lvtMainCatalogIblockId = 11;
if ((int) $arParams['IBLOCK_ID'] === $lvtMainCatalogIblockId) {
	$lvtTypeFilterKey = 'arrFILTER_iblock_' . $arParams['IBLOCK_TYPE'];
	if (!empty($arSearchPageFilter[$lvtTypeFilterKey]) && is_array($arSearchPageFilter[$lvtTypeFilterKey])) {
		if (!in_array($lvtEcatalogIblockId, array_map('intval', $arSearchPageFilter[$lvtTypeFilterKey]), true)) {
			$arSearchPageFilter[$lvtTypeFilterKey][] = $lvtEcatalogIblockId;
		}
	}
}

// show bitrix.search_page content
$APPLICATION->ShowViewContent('comp_search_page');

?>

<?
// include bitrix.search_page (только режим «Поиск по сайту»)
ob_start();
include 'include_search_page.php';
$searchPageContent = ob_get_clean();

if (!strlen($searchQuery)) {
	$searchQuery = (string) ($_GET['q'] ?? '');
}
?>
<div class="top-content-block <?=$APPLICATION->ShowViewContent('top_class');?>">
	<?=$searchPageContent;?>

	<?if($arLanding &&strlen($arLanding['PROPERTY_H3_GOODS_VALUE'])):?>
		<h4 class="search-title"><?=$arLanding['PROPERTY_H3_GOODS_VALUE']?></h4>
	<?endif;?>

	<?$APPLICATION->ShowViewContent('top_content');?><?$APPLICATION->ShowViewContent('top_content2');?>
	<hr>
</div>

<div class="main-wrapper flexbox flexbox--direction-row">
	<div class="section-content-wrapper <?/*=($bShowLeftBlock ? 'with-leftblock' : '');*/?> flex-1">
		<?
		if($arRegion)
		{
			if($arRegion['LIST_PRICES'])
			{
				if(reset($arRegion['LIST_PRICES']) != 'component')
					$arParams['PRICE_CODE'] = array_keys($arRegion['LIST_PRICES']);
			}
			if($arRegion['LIST_STORES'])
			{
				if(reset($arRegion['LIST_STORES']) != 'component')
					$arParams['STORES'] = $arRegion['LIST_STORES'];
			}
		}

		if($arParams['LIST_PRICES'])
		{
			foreach($arParams['LIST_PRICES'] as $key => $price)
			{
				if(!$price)
					unset($arParams['LIST_PRICES'][$key]);
			}
		}

		if($arParams['STORES'])
		{
			foreach($arParams['STORES'] as $key => $store)
			{
				if(!$store)
					unset($arParams['STORES'][$key]);
			}
		}
		if (is_array($arElements) && !empty($arElements))
		{
			if($arSKU)
			{
				foreach($arElements as $key => $value)
				{
					$arTmp = CIBlockElement::GetProperty($arSKU['IBLOCK_ID'], $value, array("sort" => "asc"), Array("ID"=>$arSKU['SKU_PROPERTY_ID']))->Fetch();
					if($arTmp['VALUE'])
						$arElements[$arTmp['VALUE']] = $arTmp['VALUE'];
				}
			}
			$arrFilter = ($GLOBALS[$arParams["FILTER_NAME"]] ? $GLOBALS[$arParams["FILTER_NAME"]] : []);

			$GLOBALS[$arParams["FILTER_NAME"]] = array(
				"=ID" => $arElements,
				'SECTION_GLOBAL_ACTIVE' => 'Y',
				'IBLOCK_ID' => $catalogIBlockID,
			) + $arrFilter;

			if($arParams['HIDE_NOT_AVAILABLE'] === 'Y'){
				$GLOBALS[$arParams["FILTER_NAME"]]['CATALOG_AVAILABLE'] = 'Y';
			}

			if($arRegion)
			{
				if($arRegion['LIST_STORES'] && $arParams["HIDE_NOT_AVAILABLE"] == "Y")
				{
					if($arParams['STORES']){
						if(TSolution::checkVersionModule('18.6.200', 'iblock')){
							$arStoresFilter = array(
								'STORE_NUMBER' => $arParams['STORES'],
								'>STORE_AMOUNT' => 0,
							);
						}
						else{
							if(count($arParams['STORES']) > 1){
								$arStoresFilter = array('LOGIC' => 'OR');
								foreach($arParams['STORES'] as $storeID)
								{
									$arStoresFilter[] = array(">CATALOG_STORE_AMOUNT_".$storeID => 0);
								}
							}
							else{
								foreach($arParams['STORES'] as $storeID)
								{
									$arStoresFilter = array(">CATALOG_STORE_AMOUNT_".$storeID => 0);
								}
							}
						}

						$arTmpFilter = array('!TYPE' => array('2', '3'));
						if($arStoresFilter){
							if(!TSolution::checkVersionModule('18.6.200', 'iblock') && count($arStoresFilter) > 1){
								$arTmpFilter[] = $arStoresFilter;
							}
							else{
								$arTmpFilter = array_merge($arTmpFilter, $arStoresFilter);
							}

							$GLOBALS[$arParams["FILTER_NAME"]][] = array(
								'LOGIC' => 'OR',
								array('TYPE' => array('2', '3')),
								$arTmpFilter,
							);
						}
					}
				}

				$GLOBALS[$arParams["FILTER_NAME"]] = TSolution::makeElementFilterInRegion($GLOBALS[$arParams["FILTER_NAME"]]);
			}

			$arItems = TSolution\Cache::CIBLockElement_GetList(
				array(
					'CACHE' => array(
						'MULTI' => 'Y',
						'TAG' => TSolution\Cache::GetIBlockCacheTag($catalogIBlockID),
					)
				),
				$GLOBALS[$arParams["FILTER_NAME"]],
				false,
				false,
				array(
					'ID',
					'IBLOCK_ID',
					'IBLOCK_SECTION_ID',
				)
			);

			$arAllSections = $arSectionsID = $arItemsID = array();

			if($arItems){
				TSolution\Extensions::init(['filter_panel', 'dropdown_select', 'smart_filter']);
				
				// sort
				ob_start();
				include_once 'include_sort.php';
				$htmlSort = ob_get_clean();
				
				$listElementsTemplate = 'catalog_'.($display == 'price' ? 'table' : ($display == 'table' ? 'block' : $display));

				if($sort === 'RANK'){
					$sectionSort = 'ID';
					$sectionSortOrder = TSolution\Search\Common::SortBySearchOrder($arElements, $arItems);
				}
			}
			?>
			
			<?$bContolAjax = (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest" && isset($_GET["control_ajax"]) && $_GET["control_ajax"] == "Y" );?>

			<?if (
				isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && 
				strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest" && 
				isset($_GET["ajax_get_filter"]) && $_GET["ajax_get_filter"] == "Y"
				) {
				$isAjaxFilter="Y";
			}
			if (isset($isAjaxFilter) && $isAjaxFilter == "Y") {
				$isAjax="N";
			}
			?>

			<?$APPLICATION->ShowViewContent('search_content');?>
			<div class="js_wrapper_items<?=($arTheme["LAZYLOAD_BLOCK_CATALOG"]["VALUE"] == "Y" ? ' with-load-block' : '')?>" >
				<div class="js-load-wrapper <?=$APPLICATION->ShowViewContent("section_additional_class");?>">

					<?if($bContolAjax):?>
						<?$APPLICATION->RestartBuffer();?>
					<?endif;?>

					<?// sort?>
					<?=$htmlSort?>

					<?unset($_GET['q']);?>

					<?=$htmlFilter?>

					<div class="inner_wrapper relative">
					<?if($isAjax === "Y"):?>
						<?$APPLICATION->RestartBuffer();?>
					<?endif;?>

					<?if ($isAjax == "N"):?>
						<div class="ajax_load <?=$display;?>-view">
					<?endif;?>
							<?
							//$arTransferParams = array();
							?>
							<?$show = $arParams["PAGE_ELEMENT_COUNT"];
							$linerow = ($arParams['LINE_ELEMENT_COUNT'] == '5' ? 5 : 4);
							?>
								<?
								if($arItems){
								?>
									<?// section elements?>
									<?$upperDisplay = $display ? strtoupper($display): 'TABLE';?>
									<?$sViewElementsTemplate = ($arParams["ELEMENTS_".$upperDisplay."_TYPE_VIEW"] == "FROM_MODULE" ? $arTheme["ELEMENTS_".$upperDisplay."_TYPE_VIEW"]["VALUE"] : $arParams["ELEMENTS_".$upperDisplay."_TYPE_VIEW"]);?>
									<?@include_once( $_SERVER["DOCUMENT_ROOT"].$arParams["CATALOG_TEMPLATE_PATH"].'/page_blocks/'.$sViewElementsTemplate.'.php');
									?>
								<?}?>							
						</div>
					<?if($isAjax === "Y"):?>
						<?die();?>
					<?endif;?>

					<?if(isset($isAjaxFilter) && $isAjaxFilter):?>
						<script type="text/javascript">
							BX.removeCustomEvent("onAjaxSuccessFilter", function tt(e){});
							BX.addCustomEvent("onAjaxSuccessFilter", function tt(e){
								var arAjaxPageData = <?=CUtil::PhpToJSObject($arAdditionalData);?>;
								if ($('.element-count-wrapper .element-count').length) {
									$('.element-count-wrapper .element-count').text($('.js_append').closest('.catalog-items').find('.bottom_nav').attr('data-all_count'));
								}
							});
						</script>
					<?endif;?>

					</div> <?// .<div class="inner_wrapper">?>

					<?if($bContolAjax):?>
						<?die();?>
					<?endif;?>
				</div>
			</div>
			
		<?}else{
			$lvtQtrim = trim((string) $searchQuery);
			if (!strlen($searchQuery)) {
				echo '<div class="alert alert-info">'.GetMessage("CT_BCSE_EMPTY_QUERY")."</div>";
			} elseif (mb_strlen($lvtQtrim) >= 3) {
				$APPLICATION->SetAdditionalCSS('/local/css/lvt_mouser_search.css');
				echo '<div class="alert alert-warning">'.GetMessage("CT_BCSE_NOT_FOUND")."</div>";
				$lvtLazyUrl = '/local/api/mouser_search_lazy.php?q=' . rawurlencode($lvtQtrim) . '&catalog_iblock=' . (int) $catalogIBlockID;
				$lvtLazyEsc = htmlspecialchars($lvtLazyUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
				echo '<div id="lvt-mouser-lazy-result" data-url="' . $lvtLazyEsc . '">';
				echo '<div class="alert alert-info lvt-mouser-lazy-loading"><span class="lvt-mouser-lazy-spinner" aria-hidden="true"></span><span>Загрузка данных Mouser...</span></div>';
				echo '</div>';
				?>
				<style>
					.lvt-mouser-lazy-loading{display:flex;align-items:center;gap:10px;}
					.lvt-mouser-lazy-spinner{
						width:18px;height:18px;border:2px solid rgba(19,138,229,.24);border-top-color:#138ae5;border-radius:50%;
						animation:lvtMouserSpin .85s linear infinite;
					}
					@keyframes lvtMouserSpin{to{transform:rotate(360deg);}}
				</style>
				<script>
				(function () {
					var box = document.getElementById('lvt-mouser-lazy-result');
					if (!box) return;
					var url = box.getAttribute('data-url');
					if (!url) return;
					fetch(url, {credentials: 'same-origin'})
						.then(function (r) { return r.json(); })
						.then(function (payload) {
							if (payload && payload.redirect) {
								window.location.href = payload.redirect;
								return;
							}
							if (payload && payload.html) {
								box.innerHTML = payload.html;
								return;
							}
							box.innerHTML = '<div class="alert alert-danger">' + (payload && payload.message ? payload.message : 'Не удалось получить данные Mouser.') + '</div>';
						})
						.catch(function () {
							box.innerHTML = '<div class="alert alert-danger">Ошибка загрузки данных Mouser.</div>';
						});
				})();
				</script>
				<?
			} else {
				echo '<div class="alert alert-danger">'.GetMessage("CT_BCSE_NOT_FOUND")."</div>";
			}

			$APPLICATION->AddViewContent('top_class', 'emptys');
		}
		?>

	</div>
	
</div>