<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
?>

<div class="bx-sbb-empty-cart-container">
	<div class="bx-sbb-empty-cart-image"></div>

	<div class="bx-sbb-empty-cart-text">
		<?=Loc::getMessage("SBB_EMPTY_BASKET_TITLE")?>
	</div>

	<div class="bx-sbb-empty-cart-desc mt mt--16">
		<div class="flexbox">
			<div class="mb mb--24">
				<?=Loc::getMessage('SBB_EMPTY_BASKET_HINT')?>
			</div>

			<div class="line-block line-block--gap line-block--gap-8 line-block--wrap line-block--justify-center">
				<a href="<?=TSolution::getFrontParametrValue('CATALOG_PAGE_URL');?>" class="btn btn-default">
					<?=Loc::getMessage('SBB_EMPTY_BASKET_HINT_CATALOG');?>
				</a>
				<a href="<?=SITE_DIR;?>" class="btn btn-transparent">
					<?=Loc::getMessage('SBB_EMPTY_BASKET_HINT_MAINPAGE');?>
				</a>
			</div>
		</div>
	</div>
</div>