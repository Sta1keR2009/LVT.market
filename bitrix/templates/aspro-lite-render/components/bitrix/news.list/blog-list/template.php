<?
use \Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();

$this->setFrameMode(true);
?>
<?if($arResult['ITEMS']):?>	
	<?
	$bMobileScrolledItems = (
		!isset($arParams['MOBILE_SCROLLED']) || 
		(isset($arParams['MOBILE_SCROLLED']) && $arParams['MOBILE_SCROLLED'])
	);

	$bMaxWidthWrap = (
        !isset($arParams['MAXWIDTH_WRAP']) ||
        (isset($arParams['MAXWIDTH_WRAP']) && $arParams['MAXWIDTH_WRAP'] !== "N")
    );

	$count1200 = $arParams['ELEMENTS_ROW'];
	$count992 = $arParams['ELEMENTS_ROW'] > 1 ? $arParams['ELEMENTS_ROW'] - 1 : $arParams['ELEMENTS_ROW'];
	$count768 = $arParams['ELEMENTS_ROW'] > 1 ? $arParams['ELEMENTS_ROW'] - 1 : $arParams['ELEMENTS_ROW'];
	$count601 = $arParams['ELEMENTS_ROW'] > 2 ? $arParams['ELEMENTS_ROW'] - 2 : $count768;

	$gridClass = 'grid-list ';
	$gridClass .= ' grid-list--items-'.$count1200.'-1200';
	$gridClass .= ' grid-list--items-'.$count992.'-992';
	$gridClass .= ' grid-list--items-'.$count768.'-768';
	$gridClass .= ' grid-list--items-'.$count601.'-601';

	if($arParams["GRID_LIST_ROW_GAP_40"]){
		$gridClass .= ' grid-list--gap-row-40';
	}

	if ($bMobileScrolledItems) {
		$gridClass .= ' mobile-scrolled mobile-scrolled--items-2 mobile-offset';
	} else {
		$gridClass .= ' grid-list--normal';
	}
	?>
	<?if (!$arParams['IS_AJAX']):?>
		<div class="blog-list blog-list--items-offset <?=$templateName?>-template">
			<?=TSolution\Functions::showTitleBlock([
				'PATH' => 'blog-list',
				'PARAMS' => $arParams
			]);?>
			<?if($bMaxWidthWrap):?>
				<div class="maxwidth-theme">
			<?endif;?>
				<div class="<?=$gridClass?>">
	<?endif;?>
			<?
			foreach($arResult['ITEMS'] as $i => $arItem):?>
				<?
				// edit/add/delete buttons for edit mode
				$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
				$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), array('CONFIRM' => Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

				// preview image
				$bImage = (isset($arItem['FIELDS']['PREVIEW_PICTURE']) && $arItem['PREVIEW_PICTURE']['SRC']);
				$nImageID = ($bImage ? (is_array($arItem['FIELDS']['PREVIEW_PICTURE']) ? $arItem['FIELDS']['PREVIEW_PICTURE']['ID'] : $arItem['FIELDS']['PREVIEW_PICTURE']) : "");
				$imageSrc = ($bImage ? CFile::getPath($nImageID) : SITE_TEMPLATE_PATH.'/images/svg/noimage_content.svg');

				// show date
				$bActiveDate = (
					$arItem['ACTIVE_FROM'] && 
					(
						in_array('DATE_ACTIVE_FROM', $arParams['FIELD_CODE']) ||
						in_array('ACTIVE_FROM', $arParams['FIELD_CODE'])
					)
				);

				$bShowSection = ($arParams['SHOW_SECTION_NAME'] == 'Y' && ($arItem['IBLOCK_SECTION_ID'] && $arResult['SECTIONS'][$arItem['IBLOCK_SECTION_ID']]));

				$bShowBottomPart = $bShowSection || $bActiveDate;
				?>
				<div class="blog-list__wrapper grid-list__item">
					<div class="blog-list__item height-100 flexbox color-theme-parent-all" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
						<?if($imageSrc):?>
							<div class="blog-list__item-image-wrapper">
								<a class="blog-list__item-link" href="<?=$arItem['DETAIL_PAGE_URL']?>">
									<span class="blog-list__item-image outer-rounded-x" style="background-image: url(<?=$imageSrc?>);"></span>
								</a>
								<?if ($arItem['VIDEO'] || $arItem['POPUP_VIDEO']):?>
									<div class="blog-list__item-sticker">
										<div class="blog-list__item__popup-video" title="<?=Loc::getMessage('HAS_VIDEO')?>"></div>
									</div>
								<?endif;?>
							</div>
						<?endif;?>

						<div class="blog-list__item-text-wrapper flex-grow-1 flexbox">
							<div class="blog-list__item-text-top-part flexbox">
								<div class="blog-list__item-title switcher-title font_weight--500 font_<?=$arParams['NAME_SIZE']?> font_16--to-600">
									<a class="dark_link color-theme-target" href="<?=$arItem['DETAIL_PAGE_URL']?>">
										<?=$arItem['NAME']?>
									</a>
								</div>	

								<?if(strlen($arItem['FIELDS']['PREVIEW_TEXT']) && $arParams['SHOW_PREVIEW']):?>
									<div class="blog-list__item-preview-wrapper">
										<div class="blog-list__item-preview color_666">
											<?=$arItem['FIELDS']['PREVIEW_TEXT'];?>
										</div>
									</div>
								<?endif;?>

								<?if($bShowBottomPart):?>
									<div class="blog-list__item-text-bottom-part">
										<?// section?>
										<?if($bShowSection):?>
											<div class="blog-list__item-section font_14"><?=$arResult['SECTIONS'][$arItem['IBLOCK_SECTION_ID']]['NAME'];?></div>
										<?endif;?>
										
										<? if ($bShowSection && $bActiveDate): ?>
											<span class="blog-list__item-text-bottom-part__separator">/</span>
										<? endif; ?>

										<?// date active period?>
										<?if($bActiveDate):?>
											<div class="blog-list__item-period font_14">
												<span class="blog-list__item-period-date"><?=$arItem['DISPLAY_ACTIVE_FROM']?></span>
											</div>
										<?endif;?>
									</div>
								<?endif;?>
							</div>
						</div>
					</div>
				</div>
			<?
			endforeach;?>

			<?if ($bMobileScrolledItems):?>
				<?if($arParams['IS_AJAX']):?>
					<div class="wrap_nav bottom_nav_wrapper">
				<?endif;?>
					<?$bHasNav = (strpos($arResult["NAV_STRING"], 'more_text_ajax') !== false);?>
					<div class="bottom_nav mobile_slider <?=($bHasNav ? '' : ' hidden-nav');?>" data-parent=".blog-list" data-append=".grid-list" <?=($arParams["IS_AJAX"] ? "style='display: none; '" : "");?>>
						<?if ($bHasNav):?>
							<?=$arResult["NAV_STRING"]?>
						<?endif;?>
					</div>

				<?if($arParams['IS_AJAX']):?>
					</div>
				<?endif;?>
			<?endif;?>

	<?if (!$arParams['IS_AJAX']):?>
				</div>
	<?endif;?>

		<?// bottom pagination?>
		<?if($arParams['IS_AJAX']):?>
			<div class="wrap_nav bottom_nav_wrapper">
		<?endif;?>

		<div class="bottom_nav_wrapper nav-compact">
			<div class="bottom_nav <?=($bMobileScrolledItems ? 'hide-600' : '');?>" <?=($arParams['IS_AJAX'] ? "style='display: none; '" : "");?> data-parent=".blog-list" data-append=".grid-list">
				<?if($arParams['DISPLAY_BOTTOM_PAGER']):?>
					<?=$arResult['NAV_STRING']?>
				<?endif;?>
			</div>
		</div>

		<?if($arParams['IS_AJAX']):?>
			</div>
		<?endif;?>

	<?if (!$arParams['IS_AJAX']):?>
		<?if($bMaxWidthWrap):?>
			</div>
		<?endif;?>
		</div>
	<?endif;?>
<?endif;?>