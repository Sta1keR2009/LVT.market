<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();?>
<?$this->setFrameMode(true);?>
<?use \Bitrix\Main\Localization\Loc;?>
<?if($arResult['ITEMS']):?>	
	<?
	$templateData['ITEMS'] = true;

	$qntyItems = count($arResult['ITEMS']);
	$bMobileScrolledItems = (
		!isset($arParams['MOBILE_SCROLLED']) || 
		(isset($arParams['MOBILE_SCROLLED']) && $arParams['MOBILE_SCROLLED'])
	);
	$bMaxWidthWrap = (
		!isset($arParams['MAXWIDTH_WRAP']) ||
		(isset($arParams['MAXWIDTH_WRAP']) && $arParams['MAXWIDTH_WRAP'])
	);
	
	global $arTheme;

	$blockClasses = '';
	if (!$arParams['ITEMS_OFFSET']) {
		$blockClasses .= ' news-list--items-close';
	}
	if ($arParams['ITEMS_OFFSET']) {
		$blockClasses .= ' news-list--items-offset';
	}

	$gridClass = 'grid-list';

	if ($bMobileScrolledItems) {
		$gridClass .= ' mobile-scrolled mobile-scrolled--items-2 mobile-offset';
	} else {
		$gridClass .= ' grid-list--normal';
	}

	if (!$arParams['ITEMS_OFFSET']) {
		$gridClass .= ' grid-list--no-gap';
		if ($arParams['TEXT_POSITION'] == 'BOTTOM_RELATIVE' && !$arParams['ITEM_PADDING']) {
			$gridClass .= ' grid-list--gap-row';
		}
	}
	if ($arParams['NO_GRID']) {
		$gridClass .= ' grid-list--no-grid';
	}
	if ($arParams['WIDE_FIRST']) {
		$gridClass .= ' grid-list--wide-first';
	}
	if ($arParams['ITEMS_TEMPLATE']) {
		$gridClass .= ' grid-list--items-'.$arParams['ITEMS_TEMPLATE'];
	}

	if (!$arParams['NARROW']) {
		$gridClass .= ' grid-list--items-'.$arParams['ELEMENTS_ROW'].'-wide';
	} else {
		$gridClass .= ' grid-list--items-'.$arParams['ELEMENTS_ROW'];
	}

	$itemWrapperClasses = ' grid-list__item stroke-theme-hover colored_theme_hover_bg-block animate-arrow-hover';
	if (!$arParams['ITEMS_OFFSET'] && $arParams['BORDER']) {
		$itemWrapperClasses .= ' grid-list-border-outer';
	}
	if ($arParams['NO_GRID']) {
		$itemWrapperClasses .= ' item-w'.floor(100/$arParams['ELEMENTS_ROW']);
	}

	$itemClasses = 'height-100 flexbox';
	if ($arParams['ROW_VIEW']) {
		$itemClasses .= ' flexbox--direction-row-reverse';
	}
	if ($arParams['COLUMN_REVERSE']) {
		$itemClasses .= ' flexbox--direction-column-reverse';
	}	
	if ($arParams['ROUNDED'] && $arParams['ITEMS_OFFSET']) {
		$itemClasses .= ' rounded-4';
	}
	if ($arParams['TEXT_POSITION'] == 'LEFT') {
		$itemClasses .= ' news-list__item--big-padding';
	}
	if ($arParams['ITEM_HOVER_SHADOW']) {
		$itemClasses .= ' shadow-hovered shadow-no-border-hovered';
	}
	
	if ($arParams['ITEM_PADDING']) {
		$itemClasses .= ' news-list__item--padding';
	}

	$imageClasses = '';
	if ($arParams['ROUNDED'] && $arParams['ITEMS_OFFSET']) {
		$imageClasses .= ' rounded-4';
	}
	if ($arParams['ABSOLUTE_IMAGE']) {
		$imageClasses .= ' news-list__item-image--absolute';
	}
	?>
	<?if (!$arParams['IS_AJAX']):?>
	
		<div class="news-list <?=$blockClasses?> <?=$templateName?>-template">
			<?=TSolution\Functions::showTitleBlock([
				'PATH' => 'news-list',
				'PARAMS' => $arParams
			]);?>

		<?if($bMaxWidthWrap):?>
			<?if($arParams['NARROW']):?>
					<div class="maxwidth-theme">
			<?elseif ($arParams['ITEMS_OFFSET']):?>
				<div class="maxwidth-theme maxwidth-theme--no-maxwidth">
			<?endif;?>
		<?endif;?>

	
		<div class="<?=$gridClass?>">
	<?endif;?>
			<?
			$counter = 1;
			foreach($arResult['ITEMS'] as $i => $arItem):?>
				<?
				// edit/add/delete buttons for edit mode
				$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
				$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), array('CONFIRM' => Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

				// preview image
				$bImage = (isset($arItem['FIELDS']['PREVIEW_PICTURE']) && $arItem['PREVIEW_PICTURE']['SRC']);
				$nImageID = ($bImage ? (is_array($arItem['FIELDS']['PREVIEW_PICTURE']) ? $arItem['FIELDS']['PREVIEW_PICTURE']['ID'] : $arItem['FIELDS']['PREVIEW_PICTURE']) : "");
				$imageSrc = ($bImage ? CFile::getPath($nImageID) : SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg');

				// show date
				$bActiveDate = (
					strlen($arItem['DISPLAY_PROPERTIES']['PERIOD']['VALUE']) || 
					$arItem['ACTIVE_FROM'] && in_array('ACTIVE_FROM', $arParams['FIELD_CODE'])
				);

				$bShowSection = ($arParams['SHOW_SECTION_NAME'] == 'Y' && ($arItem['IBLOCK_SECTION_ID'] && $arResult['SECTIONS'][$arItem['IBLOCK_SECTION_ID']]));

				$imagePosition = $arParams['IMAGE_POSITION'];
				$textPosition = $arParams['TEXT_POSITION'];
				$textPadding = $arParams['TEXT_PADDING'];
				$bFonImg = false;
				$bordered = true;
				
				$itemWrapperClassesExt = $itemClassesExt = '';				

				if ($arParams['WIDE_FIRST'] && $counter === 1 && !$arParams['IS_AJAX']) {
					$itemWrapperClassesExt .= ($arParams['ELEMENTS_ROW'] == 4 ? ' item-w50' : ' item-w66');
					
					if ($arParams['IMAGE_POSITION_FIRST']) {
						$imagePosition = $arParams['IMAGE_POSITION_FIRST'];
					}
					if ($arParams['TEXT_POSITION_FIRST']) {
						$textPosition = $arParams['TEXT_POSITION_FIRST'];
					}
					if ($arParams['DARK_HOVER'] && $arParams['IMAGE_POSITION_FIRST'] === 'BG') {
						$itemClassesExt .= ' dark-block-hover';
					}
					
					$bFonImg = $arParams['IMAGE_POSITION_FIRST'] === 'BG';
					$textPadding = $bordered = false;
				}

				if ($arParams['BORDER'] && $bordered) {
					$itemClassesExt .= ' bordered';
				}
				?>

				<div class="news-list__wrapper <?=$itemWrapperClasses;?> <?=$itemWrapperClassesExt;?> color-theme-parent-all">
					<div class="news-list__item <?=$itemClasses?> <?=$itemClassesExt?> <?=($arItem['DISPLAY_PROPERTIES']['SALE_NUMBER']['VALUE'] ? 'news-list__item--with-discount' : '');?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
						<?if($imageSrc && $arParams['SHOW_IMAGE']):?>
							<div class="news-list__item-image-wrapper <?='news-list__item-image-wrapper--'.$imagePosition?>">
								<a class="news-list__item-link" href="<?=$arItem['DETAIL_PAGE_URL']?>">
									<span class="news-list__item-image<?=$imageClasses?>" style="background-image: url(<?=$imageSrc?>);"></span>
								</a>
								<?if($bShowSection):?>
									<div class="news-list__item-sticker sticker">
										<div class="sticker__item font_13"><?=$arResult['SECTIONS'][$arItem['IBLOCK_SECTION_ID']]['NAME'];?></div>
									</div>
								<?endif;?>
							</div>
						<?endif;?>

						<?if($bFonImg):?>
							<a class="news-list__item-link news-list__item-link--absolute" href="<?=$arItem['DETAIL_PAGE_URL']?>"></a>
						<?endif;?>

						<div class="news-list__item-text-wrapper news-list__item-text-wrapper--<?=$textPosition?> flex-grow-1<?=($textPadding ? ' news-list__item-text-wrapper--with-padding' : '')?>">
							<div class="news-list__item-text-top-part<?=($bFonImg || ($arParams['SHOW_IMAGE'] && !$textPadding) ? '' : ' flexbox flexbox--justify-beetwen flexbox--direction-column-reverse');?>">

								<?// date active period?>
								<?if($bActiveDate):?>
									<?$periodClass = (!$arParams['SHOW_IMAGE'] ? 'news-list__item-period--mt-29' : 'news-list__item-period--mb-9')?>
									<div class="news-list__item-period<?=($bFonImg ? ' news-list__item-period--FON' : ' color_999 '.$periodClass);?> font_13">
										<?if(strlen($arItem['DISPLAY_PROPERTIES']['PERIOD']['VALUE'])):?>
											<span class="news-list__item-period-date"><?=$arItem['DISPLAY_PROPERTIES']['PERIOD']['VALUE']?></span>
										<?else:?>
											<span class="news-list__item-period-date"><?=$arItem['DISPLAY_ACTIVE_FROM']?></span>
										<?endif;?>
										<?if (!$arParams['SHOW_IMAGE']):?>
											<div class="arrow-all">
												<?=TSolution::showIconSvg(' arrow-all__item-arrow', SITE_TEMPLATE_PATH.'/images/svg/Arrow_map.svg');?>
												<div class="arrow-all__item-line colored_theme_hover_bg-el"></div>
											</div>
										<?endif;?>
									</div>
								<?endif;?>

								<div class="news-list__item-title switcher-title font_<?=$arParams['NAME_SIZE']?>">
									<a class="dark_link color-theme-target" href="<?=$arItem['DETAIL_PAGE_URL']?>">
										<?=$arItem['NAME'];?>
									</a>
								</div>	

								<?if(strlen($arItem['FIELDS']['PREVIEW_TEXT']) && $arParams['SHOW_PREVIEW']):?>
									<div class="news-list__item-preview-wrapper">
										<div class="news-list__item-preview color_666">
											<?=$arItem['FIELDS']['PREVIEW_TEXT'];?>
										</div>
									</div>
								<?endif;?>

							</div>

						</div>

					</div>
				</div>
			<?
			$counter++;
			endforeach;?>

			<?if ($bMobileScrolledItems):?>
				<?if($arParams['IS_AJAX']):?>
					<div class="wrap_nav bottom_nav_wrapper">
				<?endif;?>
					<?$bHasNav = (strpos($arResult["NAV_STRING"], 'more_text_ajax') !== false);?>
					<div class="bottom_nav mobile_slider <?=($bHasNav ? '' : ' hidden-nav');?>" data-parent=".news-list" data-append=".grid-list" <?=($arParams["IS_AJAX"] ? "style='display: none; '" : "");?>>
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
			<div class="bottom_nav <?=($bMobileScrolledItems ? 'hide-600' : '');?>" <?=($arParams['IS_AJAX'] ? "style='display: none; '" : "");?> data-parent=".news-list" data-append=".grid-list">
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
			<?if($arParams['NARROW']):?>
				</div>
			<?elseif ($arParams['ITEMS_OFFSET']):?>
				</div>
			<?endif;?>
		<?endif;?>
	</div>
	<?endif;?>
<?endif;?>