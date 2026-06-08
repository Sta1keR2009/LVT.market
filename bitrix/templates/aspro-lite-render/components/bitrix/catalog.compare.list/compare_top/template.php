<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<!--noindex-->
	<?$count=count($arResult);?>
	<a class=" compare-link dark_link <?=$arParams["CLASS_LINK"];?>" href="<?=$arParams["COMPARE_URL"]?>" title="<?=\Bitrix\Main\Localization\Loc::getMessage('CATALOG_COMPARE_ELEMENTS_ALL');?>">
		<span class="compare-block icon-block-with-counter <?=$arParams["CLASS_ICON"];?> fill-theme-use-svg-hover">
			<span class="js-compare-block <?=($count ? 'icon-block-with-counter--count' : '');?>">					
				<span class="icon-count icon-count--compare bg-more-theme count"><?=$count;?></span>
			</span>
			<?=\TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH."/images/svg/catalog/item_icons.svg#compare-20-16", "compare header__icon", ['WIDTH' => 20,'HEIGHT' => 16]);?>				
		</span>
		<?if ($arParams['MESSAGE']):?>
			<span class="header__icon-name title menu-light-text banner-light-text"><?=$arParams['MESSAGE'];?></span>
		<?endif;?>
	</a>
<!--/noindex-->