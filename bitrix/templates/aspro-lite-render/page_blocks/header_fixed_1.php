<?
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/header/settings.php');

global $arTheme;

$headerInnerClasses = '';
if(!$bNarrowHeader) {
	$headerInnerClasses .= ' header__inner--paddings';
}
$innerClasses = 'relative';

?>
<div class="header header--fixed-1 <?=$bNarrowHeader ? 'header--narrow' : ''?>">
	<div class="header__inner header__inner--shadow-fixed <?=$headerInnerClasses?>">
		<?if($ajaxBlock == "HEADER_FIXED_MAIN_PART" && $bAjax) {
			$APPLICATION->restartBuffer();
		}?>

		<div class="header__main-part  <?=htmlspecialcharsbx($mainPartClasses)?>"  data-ajax-load-block="HEADER_FIXED_MAIN_PART">

			<?if($bNarrowHeader):?>
				<div class="maxwidth-theme">
			<?endif;?>

			<div class="header__main-inner <?=$innerClasses?>">

				<div class="header__main-item">
					<div class="line-block line-block--40">
						<?//show logo?>
						<div class="logo <?=htmlspecialcharsbx($logoClass)?> line-block__item no-shrinked">
							<?TSolution::ShowBufferedFixedLogo();?>
						</div>
					</div>
				</div>

				<div class="header__main-item header__burger menu-dropdown-offset">
					<!-- noindex -->
					<nav class="mega-menu">
						<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
							array(
								"COMPONENT_TEMPLATE" => ".default",
								"PATH" => SITE_DIR."include/header/menu.only_catalog.php",
								"AREA_FILE_SHOW" => "file",
								"AREA_FILE_SUFFIX" => "",
								"AREA_FILE_RECURSIVE" => "Y",
								"EDIT_TEMPLATE" => "include_area.php",
								"LARGE_CATALOG_BUTTON" => "Y",
								"USE_NLO_MENU" => "Y",
								"NLO_MENU_CODE" => "menu-fixed",

							),
							false, array("HIDE_ICONS" => "Y")
						);?>
					</nav>
					<!-- /noindex -->
				</div>

				<?
				$blockOptions = array(
					'PARAM_NAME' => 'HEADER_FIXED_TOGGLE_SEARCH',
					'BLOCK_TYPE' => 'SEARCH',
					'IS_AJAX' => $bAjax,
					'AJAX_BLOCK' => $ajaxBlock,
					'VISIBLE' => true,
					'WRAPPER' => 'header__main-item flex-1 header__search',
					'TYPE' => 'LINE',
					'POSTFIX_ID' => '_fixed'
				);
				?>
				<?=\TSolution\Functions::showHeaderBlock($blockOptions);?>

				<div class="header__main-item <?=!$bCabinet && !$bCompare && !$bFavorite && !$bOrder ? 'hidden' : ''?>">
					<div class="line-block line-block--40">
						<?$blockOptions = array(
							'PARAM_NAME' => 'HEADER_FIXED_TOGGLE_CABINET',
							'BLOCK_TYPE' => 'CABINET',
							'IS_AJAX' => $bAjax,
							'AJAX_BLOCK' => $ajaxBlock,
							'VISIBLE' => $bCabinet,
							'WRAPPER' => 'line-block__item',
							'CABINET_PARAMS' => array(
								'TEXT_LOGIN' => 'login',
								'TEXT_NO_LOGIN' => GetMessage("LOGIN"),
								'CLASS_LINK' => 'fill-dark-light-block color-theme-hover banner-light-icon-fill banner-light-text flexbox flexbox--direction-column flexbox--align-center',
							),
						);?>
						<?=\TSolution\Functions::showHeaderBlock($blockOptions);?>

						<?$blockOptions = array(
							'PARAM_NAME' => 'HEADER_FIXED_TOGGLE_COMPARE',
							'BLOCK_TYPE' => 'COMPARE',
							'IS_AJAX' => $bAjax,
							'AJAX_BLOCK' => $ajaxBlock,
							'VISIBLE' => $bCompare,
							'WRAPPER' => 'line-block__item',
							'MESSAGE' => GetMessage("S_COMPARE"),
							'CLASS_LINK' => 'light-opacity-hover fill-theme-hover fill-dark-light-block color-theme-hover banner-light-icon-fill flexbox flexbox--direction-column flexbox--align-center',
							'CLASS_ICON' => 'menu-light-icon-fill ',
						);?>
						<?=\TSolution\Functions::showHeaderBlock($blockOptions);?>

						<?$blockOptions = array(
							'PARAM_NAME' => 'HEADER_FIXED_TOGGLE_FAVORITE',
							'BLOCK_TYPE' => 'FAVORITE',
							'IS_AJAX' => $bAjax,
							'AJAX_BLOCK' => $ajaxBlock,
							'VISIBLE' => $bFavorite,
							'WRAPPER' => 'line-block__item',
							'MESSAGE' => GetMessage("S_FAVORITE"),
							'CLASS_LINK' => 'light-opacity-hover color-theme-hover fill-theme-hover fill-dark-light-block color-theme-hover banner-light-icon-fill banner-light-text flexbox flexbox--direction-column flexbox--align-center no-shrinked',
							'CLASS_ICON' => 'menu-light-icon-fill fill-use-888',
						);?>
						<?=\TSolution\Functions::showHeaderBlock($blockOptions);?>

						<?$blockOptions = array(
							'PARAM_NAME' => 'HEADER_FIXED_TOGGLE_BASKET',
							'BLOCK_TYPE' => 'BASKET',
							'IS_AJAX' => $bAjax,
							'AJAX_BLOCK' => $ajaxBlock,
							'VISIBLE' => $bOrder && !TSolution::IsBasketPage() && !TSolution::IsOrderPage(),
							'WRAPPER' => 'line-block__item',
							'MESSAGE' => GetMessage("BASKET"),
							'CLASS_LINK' => 'flexbox flexbox--direction-column flexbox--align-center fill-dark-light-block no-shrinked'
						);?>
						<?=\TSolution\Functions::showHeaderBlock($blockOptions);?>
					</div>
				</div>

			</div>

			<?if($bNarrowHeader):?>
				</div>
			<?endif;?>
		</div>

		<?if($ajaxBlock == "HEADER_FIXED_MAIN_PART" && $bAjax) {
			die();
		}?>
	</div>
</div>
