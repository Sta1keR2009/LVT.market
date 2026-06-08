<?
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/header/settings.php');

global $arTheme;

$headerHeight = '99px';

/* set classes for header parts */
$headerInnerClasses = '';
$headerInnerClasses .= ' header__inner--paddings';

$mainPartClasses = '';

$innerClasses = 'relative part-with-search__inner';
?>

<header class="header header_simple header_simple_1 <?=($arRegion ? 'header--with_regions' : '')?> <?=TSolution::ShowPageProps('HEADER_COLOR')?>">
	<div class="header__inner <?=$headerInnerClasses?>">
		<div class="header__main-part  <?=$mainPartClasses?>">

			<?if($bNarrowHeader):?>
				<div class="maxwidth-theme">
			<?endif;?>

				<div class="header__main-inner relative">
					<?if($arRegion):?>
						<?//regions?>
						<div class="header__main-item icon-block--with_icon">
							<?
							$arRegionsParams = array();
							if($bAjax) {
								$arRegionsParams['POPUP'] = 'N';
							}
							TSolution::ShowListRegions($arRegionsParams);?>
						</div>
					<?endif;?>

					<div class="header__main-item header__main-item--centered">
						<div class="line-block line-block--40">
							<?//show logo?>
							<div class="logo <?=htmlspecialcharsbx($logoClass)?> line-block__item no-shrinked">
								<?TSolution::ShowBufferedLogo();?>
							</div>
						</div>
					</div>

					<?//show phone and callback?>
					<?
					$blockOptions = array(
						'PARAM_NAME' => 'HEADER_TOGGLE_PHONE',
						'BLOCK_TYPE' => 'PHONE',
						'IS_AJAX' => $bAjax,
						'AJAX_BLOCK' => $ajaxBlock,
						'VISIBLE' => $bShowPhone && $bPhone,
						'WRAPPER' => 'header__main-item no-shrinked',
						'CALLBACK' => false,
					);
					?>
					<?=\TSolution\Functions::showHeaderBlock($blockOptions);?>
				</div>

			<?if($bNarrowHeader):?>
				</div>
			<?endif;?>
		</div>

		<?if(
			$bAjax &&
			(
				preg_match('/^HEADER_/', $ajaxBlock) &&
				!preg_match('/^HEADER_FIXED_/', $ajaxBlock)
			)
		) {
			$APPLICATION->restartBuffer();
			die();
		}?>
	</div>
</header>
<?
\TSolution::$arCssVariables['--header-height'] = $headerHeight;
?>
