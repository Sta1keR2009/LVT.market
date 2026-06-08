<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$this->setFrameMode(true);
$colmd = 12;
$colsm = 12;
?>
<?if($arResult):?>
	<?
	global $arTheme;
	$compactFooterMobile = $arTheme['COMPACT_FOOTER_MOBILE']['VALUE'];
	if(!function_exists("ShowSubItems2")){
		function ShowSubItems2($arItem, $indexSection){
			?>
			<?if($arItem["CHILD"]):?>
				<?$noMoreSubMenuOnThisDepth = false;
				$count = count($arItem["CHILD"]);?>
				<?$lastIndex = count($arItem["CHILD"]) - 1;?>
				<?foreach($arItem["CHILD"] as $i => $arSubItem):?>
					<?if(!$i):?>
						<div id="<?=$indexSection?>" class="wrap wrap_menu_compact_mobile">
					<?endif;?>
						<?$bLink = strlen($arSubItem['LINK']);?>
						<div class="item-link item-link <?=$i == 0 ? 'item-link--first' : ''?> <?=$i == $lastIndex ? 'item-link--last' : ''?>">
							<div class="item<?=($arSubItem["SELECTED"] ? " active" : "")?>">
								<div class="title font_short">
									<?if($bLink):?>
										<a href="<?=$arSubItem['LINK']?>"><?=$arSubItem['TEXT']?></a>
									<?else:?>
										<span><?=$arSubItem['TEXT']?></span>
									<?endif;?>
								</div>
							</div>
						</div>

						<?$noMoreSubMenuOnThisDepth |= TSolution::isChildsSelected($arSubItem["CHILD"]);?>
					<?if($i && $i === $lastIndex || $count == 1):?>
						</div>
					<?endif;?>
				<?endforeach;?>

			<?endif;?>
			<?
		}
	}
	?>
	<?$indexSection = $arParams['ROOT_MENU_TYPE'];?>
	<div class="bottom-menu">
		<div class="items">
			<?$lastIndex = count($arResult) - 1;?>
			<?$arParams['BOLD_ITEMS'] = false;?>
			<?foreach($arResult as $i => $arItem):?>

				<?if($i === 1):?>
					<div id="<?=$indexSection?>" class="wrap wrap_menu_compact_mobile">
				<?endif;?>
					<?$bLink = strlen($arItem['LINK']);?>
					<div class="item-link <?=$arParams['BOLD_ITEMS'] ? '' : 'accordion-close'?> <?=$bMenuToRow ? 'line-block__item' : ''?> <?=($arItem["CHILD"] || count($arResult) > 1) ? "items-child" : "";?> item-link" data-parent="#<?=$indexSection?>" data-target="#<?=$indexSection?>">
						<div class="item<?=($arItem["SELECTED"] ? " active" : "")?>">
							<div class="<?=$bLink ? 'title ' : '';?> font_weight--600 font_short">
								<?if($bLink):?>
									<a class="dark_link" href="<?=$arItem['LINK']?>"><?=$arItem['TEXT']?></a>
								<?else:?>
									<span><?=$arItem['TEXT']?></span>
								<?endif;?>
							</div>
						</div>

						<?if( ($arItem["CHILD"] || $i < 1) && !$arParams['BOLD_ITEMS'] ):?>
							<span class="item-link-arrow">
								<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH."/images/svg/arrows.svg#down-7-5", "", ['WIDTH' => 7,'HEIGHT' => 5]);?>
							</span>
						<?endif;?>
					</div>
				<?if($i && $i === $lastIndex):?>
					</div>
				<?endif;?>
				<?ShowSubItems2($arItem, $indexSection);?>
			<?endforeach;?>
		</div>
	</div>
<?endif;?>