<?
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/header/settings.php');

// sites list
$arShowSites = TSolution\Functions::getShowSites();
$countSites = count($arShowSites);

global $arTheme, $arRegion;
?>
<?if($ajaxBlock === 'MOBILE_MENU_MAIN_PART' && $bAjax){
	$APPLICATION->restartBuffer();
}?>
<div class="mobilemenu mobilemenu_1" data-ajax-load-block="MOBILE_MENU_MAIN_PART">
	<?// close icon?>
	<span class="mobilemenu__close fill-dark-light fill-theme-hover" title="<?=\Bitrix\Main\Localization\Loc::getMessage('CLOSE_BLOCK');?>">
		<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/header_icons.svg#close-16-16', '', ['WIDTH' => 16, 'HEIGHT' => 16]); ?>
	</span>

	<div class="mobilemenu__inner">
		<div class="mobilemenu__item">
			<?// logo?>
			<div class="logo no-shrinked <?=htmlspecialcharsbx($logoClass)?>">
				<?TSolution::ShowBufferedMobileLogo();?>
			</div>
		</div>

		<?// top items?>
		<?if(
			(boolval($arRegion) && $bShowRegionUpMobileMenu) ||
			($bCabinet && $bShowCabinetUpMobileMenu) ||
			($bCompare && $bShowCompareUpMobileMenu) ||
			($bShowCartMobileMenu && $bShowCartUpMobileMenu) ||
			($bFavorite && $bShowFavoriteUpMobileMenu)
		):?>
			<div class="mobilemenu__item">
				<?/*=TSolution\Functions::showMobileMenuBlock(
					array(
						'PARAM_NAME' => 'MOBILE_MENU_TOGGLE_LANG',
						'BLOCK_TYPE' => 'LANG',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => $bShowLangMobileMenu && $bShowLangUpMobileMenu && $countSites > 1,
						'WRAPPER' => '',
						'SITE_SELECTOR_NAME' => $siteSelectorName,
						'TEMPLATE' => 'mobile',
						'SITE_LIST' => $arShowSites,
					)
				);*/?>

				<?// regions?>
				<?=TSolution\Functions::showMobileMenuBlock(
					array(
						'PARAM_NAME' => 'MOBILE_MENU_TOGGLE_REGION',
						'BLOCK_TYPE' => 'REGION',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => boolval($arRegion) && $bShowRegionUpMobileMenu,
						'WRAPPER' => '',
					)
				);?>

				<?// cabinet?>
				<?=TSolution\Functions::showMobileMenuBlock(
					array(
						'PARAM_NAME' => 'MOBILE_MENU_TOGGLE_PERSONAL',
						'BLOCK_TYPE' => 'CABINET',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => $bCabinet && $bShowCabinetUpMobileMenu,
						'WRAPPER' => '',
					)
				);?>

				<?// compare?>
				<?=TSolution\Functions::showMobileMenuBlock(
					array(
						'PARAM_NAME' => 'MOBILE_MENU_TOGGLE_COMPARE',
						'BLOCK_TYPE' => 'COMPARE',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => $bCompare && $bShowCompareUpMobileMenu,
						'WRAPPER' => '',
						'WITH_ICON' => true,
					)
				);?>

				<?// favorite?>
				<?=TSolution\Functions::showMobileMenuBlock(
					array(
						'PARAM_NAME' => 'MOBILE_MENU_TOGGLE_FAVORITE',
						'BLOCK_TYPE' => 'FAVORITE',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => $bFavorite && $bShowFavoriteUpMobileMenu,
						'WRAPPER' => '',
						'WITH_ICON' => true,
					)
				);?>

				<?// cart?>
				<?=TSolution\Functions::showMobileMenuBlock(
					array(
						'PARAM_NAME' => 'MOBILE_MENU_TOGGLE_CART',
						'BLOCK_TYPE' => 'BASKET',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => $bShowCartMobileMenu && $bShowCartUpMobileMenu && !TSolution::IsBasketPage() && !TSolution::IsOrderPage(),
						'WRAPPER' => '',
						'WITH_ICON' => true,
					)
				);?>
			</div>
			<div class="mobilemenu__separator"></div>
		<?endif;?>

		<div class="mobilemenu__item">
			<?if(TSolution::nlo('menu-mobile', 'class="loadings" style="height:47px;"')):?>
			<!-- noindex -->
			<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
				array(
					"COMPONENT_TEMPLATE" => ".default",
					"PATH" => SITE_DIR."include/header/menu_mobile.php",
					"AREA_FILE_SHOW" => "file",
					"AREA_FILE_SUFFIX" => "",
					"AREA_FILE_RECURSIVE" => "Y",
					"EDIT_TEMPLATE" => "include_area.php"
				),
				false, array("HIDE_ICONS" => "Y")
			);?>
			<!-- /noindex -->
			<?endif;?>
			<?TSolution::nlo('menu-mobile');?>
		</div>

		<div class="mobilemenu__separator"></div>
		<?if(
			(boolval($arRegion) && !$bShowRegionUpMobileMenu) ||
			($bCabinet && !$bShowCabinetUpMobileMenu) ||
			($bCompare && !$bShowCompareUpMobileMenu) ||
			($bShowCartMobileMenu && !$bShowCartUpMobileMenu) ||
			($bFavorite && !$bShowFavoriteUpMobileMenu)
		):?>
			<div class="mobilemenu__item">
				<?/*=TSolution\Functions::showMobileMenuBlock(
					array(
						'PARAM_NAME' => 'MOBILE_MENU_TOGGLE_LANG',
						'BLOCK_TYPE' => 'LANG',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => $bShowLangMobileMenu && !$bShowLangUpMobileMenu && $countSites > 1,
						'WRAPPER' => '',
						'SITE_SELECTOR_NAME' => $siteSelectorName,
						'TEMPLATE' => 'mobile',
						'SITE_LIST' => $arShowSites,
					)
				);*/?>

				<?// regions?>
				<?=TSolution\Functions::showMobileMenuBlock(
					array(
						'PARAM_NAME' => 'MOBILE_MENU_TOGGLE_REGION',
						'BLOCK_TYPE' => 'REGION',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => boolval($arRegion) && !$bShowRegionUpMobileMenu,
						'WRAPPER' => '',
					)
				);?>

				<?// cabinet?>
				<?=TSolution\Functions::showMobileMenuBlock(
					array(
						'PARAM_NAME' => 'MOBILE_MENU_TOGGLE_PERSONAL',
						'BLOCK_TYPE' => 'CABINET',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => $bCabinet && !$bShowCabinetUpMobileMenu,
						'WRAPPER' => '',
					)
				);?>

				<?// compare?>
				<?=TSolution\Functions::showMobileMenuBlock(
					array(
						'PARAM_NAME' => 'MOBILE_MENU_TOGGLE_COMPARE',
						'BLOCK_TYPE' => 'COMPARE',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => $bCompare && !$bShowCompareUpMobileMenu,
						'WRAPPER' => '',
					)
				);?>

				<?// favorite?>
				<?=TSolution\Functions::showMobileMenuBlock(
					array(
						'PARAM_NAME' => 'MOBILE_MENU_TOGGLE_FAVORITE',
						'BLOCK_TYPE' => 'FAVORITE',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => $bFavorite && !$bShowFavoriteUpMobileMenu,
						'WRAPPER' => '',
					)
				);?>

				<?// cart?>
				<?=TSolution\Functions::showMobileMenuBlock(
					array(
						'PARAM_NAME' => 'MOBILE_MENU_TOGGLE_CART',
						'BLOCK_TYPE' => 'BASKET',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => $bShowCartMobileMenu && !$bShowCartUpMobileMenu && !TSolution::IsBasketPage() && !TSolution::IsOrderPage(),
						'WRAPPER' => '',
					)
				);?>
			</div>
			<div class="mobilemenu__separator"></div>
		<?endif;?>

		<?// top items?>
		<?if(
			$bShowPhoneMobileMenu ||
			$bShowAddressMobileMenu ||
			$bShowEmailMobileMenu ||
			$bShowScheduleMobileMenu ||
			$bShowSocialMobileMenu
		):?>
			<div class="mobilemenu__item mobilemenu__footer">
				<?=TSolution\Functions::showMobileMenuBlock(
					array(
						'PARAM_NAME' => 'MOBILE_MENU_TOGGLE_CONTACTS',
						'BLOCK_TYPE' => 'CONTACTS',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => $bShowPhoneMobileMenu || $bShowAddressMobileMenu || $bShowEmailMobileMenu || $bShowScheduleMobileMenu,
						'WRAPPER' => '',
						'PHONES' => $bShowPhoneMobileMenu,
						'CALLBACK' => $bShowCallbackMobileMenu,
						'ADDRESS' => $bShowAddressMobileMenu,
						'EMAIL' => $bShowEmailMobileMenu,
						'SCHEDULE' => $bShowScheduleMobileMenu,
					)
				);?>

				<?// social?>
				<?=TSolution\Functions::showMobileMenuBlock(
					array(
						'PARAM_NAME' => 'MOBILE_MENU_TOGGLE_SOCIAL',
						'BLOCK_TYPE' => 'SOCIAL',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => $bShowSocialMobileMenu,
						'WRAPPER' => '',
						'HIDE_MORE' => false,
					)
				);?>
			</div>
		<?endif;?>
	</div>
</div>
<?if($ajaxBlock === 'MOBILE_MENU_MAIN_PART' && $bAjax){
	die();
}?>
