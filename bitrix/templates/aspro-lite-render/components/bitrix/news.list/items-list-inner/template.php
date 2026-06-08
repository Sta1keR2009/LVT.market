<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();
$this->setFrameMode(true);
use \Bitrix\Main\Localization\Loc;

global $arTheme;
?>
<?if($arResult['ITEMS']):?>
	<?
	$templateData['SHOW_DISCOUNT_COUNTER'] = in_array('ACTIVE_TO', $arParams['FIELD_CODE']);
	$bShowTitle = $arParams['TITLE'] && $arParams['SHOW_TITLE'];
	$bShowTitleLink = $arParams['RIGHT_TITLE'] && $arParams['RIGHT_LINK'];

	$bIcons = $arParams['IMAGES'] === 'ICONS';
	$bNarrow = $arParams['NARROW'];

	$bMobileScrolledItems = (
		!isset($arParams['MOBILE_SCROLLED']) || 
		(isset($arParams['MOBILE_SCROLLED']) && $arParams['MOBILE_SCROLLED'])
	);

	$blockClasses = 'items-list-inner--img-srl '.($arParams['ITEMS_OFFSET'] ? 'items-list-inner--items-offset' : 'items-list-inner--items-close');

	$gridClass = 'grid-list';
	if($bMobileScrolledItems){
		$gridClass .= ' mobile-scrolled mobile-scrolled--items-2 mobile-offset';
	}
	if(!$arParams['ITEMS_OFFSET']){
		$gridClass .= ' grid-list--no-gap';
	}
	if($arParams['GRID_GAP']){
		$gridClass .= ' grid-list--gap-'.$arParams['GRID_GAP'];
	}
	if($bNarrow){
		$gridClass .= ' grid-list--items-'.$arParams['ELEMENTS_ROW'];
	}
	else{
		$gridClass .= ' grid-list--wide grid-list--items-'.$arParams['ELEMENTS_ROW'].'-wide';
	}

	$itemWrapperClasses = ' grid-list__item stroke-theme-parent-all colored_theme_hover_bg-block animate-arrow-hover';
	if(!$arParams['ITEMS_OFFSET'] && $arParams['BORDER']){
		$itemWrapperClasses .= ' grid-list-border-outer';
	}

	$itemClasses = 'height-100 flexbox';
	if($arParams['ROW_VIEW']){
		if($arParams['IMAGE_POSITION'] !== 'LEFT'){
			$itemClasses .= ' flexbox--direction-row-reverse';
		}
		else{
			$itemClasses .= ' flexbox--direction-row';
		}
	}
	if($arParams['COLUMN_REVERSE']){
		$itemClasses .= ' flexbox--direction-column-reverse';
	}
	if($arParams['BORDER']){
		$itemClasses .= ' bordered';
	}
	if($arParams['ROUNDED']){
		$itemClasses .= ' outer-rounded-x';
	}
	if($arParams['ITEM_HOVER_SHADOW']){
		$itemClasses .= ' shadow-hovered shadow-no-border-hovered';
	}
	if($arParams['DARK_HOVER']){
		$itemClasses .= ' dark-block-hover';
	}
	$itemClasses .= ' items-list-inner__item--big-padding color-theme-parent-all';
	if($arParams['IMAGES'] === 'BIG_PICTURES'){
		$itemClasses .= ' flexbox--column-t767';
	}

	$qntyItems = count($arResult['ITEMS']);

	$imageWrapperClasses = 'items-list-inner__item-image-wrapper--'.($arParams['IMAGES'] === 'TRANSPARENT_PICTURES' ? 'PICTURES' : $arParams['IMAGES']).' items-list-inner__item-image-wrapper--'.$arParams['IMAGE_POSITION'];
	$imageClasses = $arParams['IMAGES'] === 'ROUND_PICTURES' ? 'rounded' : 'rounded-x';
	?>
	<?if(!$arParams['IS_AJAX']):?>
		<div class="items-list-inner <?=$blockClasses?> <?=$templateName?>-template">
			<?=TSolution\Functions::showTitleBlock([
				'PATH' => 'items-list-inner',
				'PARAMS' => $arParams,
			]);?>

		<?if($arParams['MAXWIDTH_WRAP']):?>
			<?if($bNarrow):?>
				<div class="maxwidth-theme">
			<?elseif($arParams['ITEMS_OFFSET']):?>
				<div class="maxwidth-theme maxwidth-theme--no-maxwidth">
			<?endif;?>
		<?endif;?>

		<div class="<?=$gridClass?>">
	<?endif;?>
			<?
			$bShowImage = 
				$bIcons || 
				(
					$arParams['IMAGES'] !== 'TRANSPARENT_PICTURES' &&
					in_array('PREVIEW_PICTURE', $arParams['FIELD_CODE'])
				) ||
				(
					$arParams['IMAGES'] === 'TRANSPARENT_PICTURES' &&
					in_array('TRANSPARENT_PICTURE', $arParams['PROPERTY_CODE'])
				);

			$counter = 1;
			foreach($arResult['ITEMS'] as $i => $arItem):?>
				<?
				// edit/add/delete buttons for edit mode
				$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
				$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), array('CONFIRM' => Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

				// use detail link?
				$bDetailLink = $arParams['SHOW_DETAIL_LINK'] != 'N' && (!strlen($arItem['DETAIL_TEXT']) ? ($arParams['HIDE_LINK_WHEN_NO_DETAIL'] !== 'Y' && $arParams['HIDE_LINK_WHEN_NO_DETAIL'] != 1) : true);

				// detail url
				$detailUrl = $arItem['DETAIL_PAGE_URL'];

				// preview text
				$previewText = $arItem['FIELDS']['PREVIEW_TEXT'];

				// show active date period
				$bActiveDate = (
					strlen($arItem['DISPLAY_PROPERTIES']['PERIOD']['VALUE']) || 
					(
						$arItem['DISPLAY_ACTIVE_FROM'] && 
						(
							in_array('DATE_ACTIVE_FROM', $arParams['FIELD_CODE']) ||
							in_array('ACTIVE_FROM', $arParams['FIELD_CODE'])
						)
					)
				);

				// is sale element
				$bSale = array_key_exists('SALE_NUMBER', $arItem['PROPERTIES']) && is_array($arItem['PROPERTIES']['SALE_NUMBER']) && $arItem['PROPERTIES']['SALE_NUMBER'];

				// show discount counter
				$bDiscountCounter = $bSale && $arItem['ACTIVE_TO'] && $templateData['SHOW_DISCOUNT_COUNTER'];

				// show item sections
				$bSections = $arItem['SECTIONS'] && $arParams['SHOW_SECTION'] != 'N';

				// preview image
				if($bShowImage){
					if($bIcons){
						$nImageID = $arItem['DISPLAY_PROPERTIES']['ICON']['VALUE'];
					}
					else{
						if($arParams['IMAGES'] === 'TRANSPARENT_PICTURES'){
							$nImageID = $arItem['DISPLAY_PROPERTIES']['TRANSPARENT_PICTURE']['VALUE'];
						}
						else{
							$nImageID = is_array($arItem['FIELDS']['PREVIEW_PICTURE']) ? $arItem['FIELDS']['PREVIEW_PICTURE']['ID'] : $arItem['FIELDS']['PREVIEW_PICTURE'];
						}
					}

					$imageSrc = ($nImageID ? CFile::getPath($nImageID) : SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg');
				}

				// additional class for mixitUp jquery plugin
				$mixitup_class = '';
				if (isset($arItem['SECTIONS']) && $arItem['SECTIONS']) {
					foreach ($arItem['SECTIONS'] as $id => $name) {
						$mixitup_class .= ' s-'.$id;
					}
				}

				if ($arItem['ACTIVE_FROM']) {
					if ($arDateTime = ParseDateTime($arItem['ACTIVE_FROM'], FORMAT_DATETIME)) {
						$mixitup_class .= ' d-'.$arDateTime['YYYY'];
					}
				}

				$bShowPrice = in_array('PRICE', $arParams['PROPERTY_CODE']) && strlen($arItem['DISPLAY_PROPERTIES']['PRICE']['VALUE']);
				?>
				<div class="items-list-inner__wrapper <?=$itemWrapperClasses?> <?=$mixitup_class;?>" data-ref="mixitup-target">
					<div class="items-list-inner__item <?=$itemClasses?><?=($bDetailLink ? '' : ' items-list-inner__item--cursor-initial')?><?=(!$bShowImage || !$imageSrc ? ' items-list-inner__item-without-image' : '')?><?=($bSale ? ' items-list-inner__item--sale' : '')?>" id="<?=$this->GetEditAreaId($arItem['ID'])?>">
						<?if($bShowImage && $imageSrc):?>
							<div class="items-list-inner__item-image-wrapper <?=$imageWrapperClasses?>">
								<?if($bDetailLink):?>
									<a class="items-list-inner__item-link" href="<?=$detailUrl?>">
								<?else:?>
									<span class="items-list-inner__item-link">
								<?endif;?>
									<?if($bIcons && $nImageID):?>
										<?=TSolution::showIconSvg(' fill-theme items-list-inner__item-image-icon', $imageSrc);?>
									<?else:?>
										<span class="items-list-inner__item-image <?=$imageClasses?>" style="background-image: url(<?=$imageSrc?>);"></span>
									<?endif;?>
								<?if($bDetailLink):?>
									</a>
								<?else:?>
									</span>
								<?endif;?>

								<?if($arParams['IMAGES'] !== 'BIG_PICTURES'):?>
									<?if($bDetailLink):?>
										<a class="arrow-all stroke-theme-target" href="<?=$detailUrl?>">
											<?=TSolution::showIconSvg(' arrow-all__item-arrow', SITE_TEMPLATE_PATH.'/images/svg/Arrow_map.svg');?>
											<div class="arrow-all__item-line colored_theme_hover_bg-el"></div>
										</a>
									<?endif;?>
								<?endif;?>
							</div>
						<?endif;?>

						<div class="items-list-inner__item-text-wrapper flexbox<?=($bShowPrice ? ' items-list-inner__item-text-wrapper--has-bottom-part items-list-inner__item-text-wrapper--has-bottom-part--'.$arParams['PRICE_POSITION'] : '')?>">
							<div class="items-list-inner__item-text-top-part <?=(!$arParams['NARROW'] && !($bShowImage && $imageSrc)) ? 'flex-1' : ''?>">
								<?if($arParams['IMAGE_POSITION'] === 'LEFT'):?>
									<?/*if($bDetailLink):?>
										<a class="arrow-all stroke-theme-target" href="<?=$detailUrl?>">
											<?=TSolution::showIconSvg(' arrow-all__item-arrow', SITE_TEMPLATE_PATH.'/images/svg/Arrow_map.svg');?>
											<div class="arrow-all__item-line colored_theme_hover_bg-el"></div>
										</a>
									<?endif;*/?>
								<?endif;?>

								<?if(
									$bActiveDate ||
									$bSections
								):?>
									<div class="items-list-inner__item-before-title line-block line-block--16 line-block--8-vertical line-block--flex-wrap<?=($bSections && $bActiveDate ? ' items-list-inner__item-before-title--has-bordered-section' : '')?>">
										<?// section title?>
										<?if($bSections):?>
											<div class="line-block__item">
												<div class="items-list-inner__item-section<?=($bActiveDate ? ' bordered rounded-x font_12 color_222' : ' font_13 color_999')?>"><?=implode(', ', $arItem['SECTIONS'])?></div>
											</div>
										<?endif;?>

										<?// date active period?>
										<?if($bActiveDate):?>
											<div class="line-block__item">
												<div class="items-list-inner__item-period font_13 color_999<?=($bSale && $arItem['ACTIVE_TO'] ? ' red' : '');?>">
													<?if($bSale):?>
														<?=TSolution::showIconSvg("sale", SITE_TEMPLATE_PATH.'/images/svg/Sale_discount.svg', '', '', true, false);?>
													<?endif;?>
													<?if(strlen($arItem['DISPLAY_PROPERTIES']['PERIOD']['VALUE'])):?>
														<span class="items-list-inner__item-period-date"><?=$arItem['DISPLAY_PROPERTIES']['PERIOD']['VALUE']?></span>
													<?else:?>
														<span class="items-list-inner__item-period-date items-list-inner__item-period-date--from"><?=$arItem['DISPLAY_ACTIVE_FROM']?></span>
													<?endif;?>
												</div>
											</div>
										<?endif;?>
									</div>
								<?endif;?>
									
								<?// element name?>
								<div class="items-list-inner__item-title switcher-title font_<?=$arParams['NAME_SIZE']?>">
									<?if($bDetailLink):?>
										<a class="dark_link color-theme-target" href="<?=$detailUrl?>"><?=$arItem['NAME']?></a>
									<?else:?>
										<span class="color_222"><?=$arItem['NAME']?></span>
									<?endif;?>
								</div>

								<?// element preview text?>
								<?if(
									in_array('PREVIEW_TEXT', $arParams['FIELD_CODE']) &&
									$arParams['SHOW_PREVIEW'] &&
									strlen($previewText)
								):?>
									<div class="items-list-inner__item-preview-wrapper">
										<div class="items-list-inner__item-preview font_15 color_666">
											<?=$previewText?>
										</div>
									</div>
								<?endif;?>

								<?if($arItem['DISPLAY_PROPERTIES']['SALE_NUMBER']['VALUE'] || $bDiscountCounter):?>
									<div class="items-list-inner__item-sticker items-list-inner__item-sticker--BOTTOM">
										<?if($arItem['DISPLAY_PROPERTIES']['SALE_NUMBER']['VALUE']):?>
											<div class="items-list-inner__item-sticker-value rounded-3"><?=$arItem['DISPLAY_PROPERTIES']['SALE_NUMBER']['VALUE'];?></div>
										<?endif;?>
										<?if ($bDiscountCounter):?>
											<?TSolution\Functions::showDiscountCounter(['ITEM' => $arItem]);?>
										<?endif;?>
									</div>
								<?endif;?>
							</div>

							<?if($bShowPrice):?>
								<div class="items-list-inner__item-text-bottom-part items-list-inner__item-text-bottom-part--has-price">
									<div class="items-list-inner__item-price-wrapper">
										<div class="items-list-inner__item-price font_17 color_222">
											<?=TSolution\Product\Price::show([
												'ITEM' => $arItem,
												'PARAMS' => $arParams,
												'SHOW_SCHEMA' => false,
												'PRICE_BLOCK_CLASS' => 'color_222'
											]);?>
										</div>
									</div>
								</div>
							<?endif;?>
						</div>
					</div>
				</div>
			<?
			$counter++;
			endforeach;?>

			<?if ($bMobileScrolledItems):?>
				<?if($arParams['IS_AJAX']):?>
					<div class="wrap_nav bottom_nav_wrapper">
						<script>InitScrollBar();</script>
				<?endif;?>
					<?$bHasNav = (strpos($arResult["NAV_STRING"], 'more_text_ajax') !== false);?>
					<div class="bottom_nav mobile_slider <?=($bHasNav ? '' : ' hidden-nav');?>" data-parent=".items-list-inner" data-append=".grid-list" <?=($arParams["IS_AJAX"] ? "style='display: none; '" : "");?>>
						<?if($bHasNav):?>
							<?=$arResult["NAV_STRING"]?>
						<?endif;?>
					</div>

				<?if($arParams['IS_AJAX']):?>
					</div>
				<?endif;?>
			<?endif;?>

	<?if(!$arParams['IS_AJAX']):?>
		</div>
	<?endif;?>

		<?// bottom pagination?>
		<?if($arParams['IS_AJAX']):?>
			<div class="wrap_nav bottom_nav_wrapper">
		<?endif;?>

		<div class="bottom_nav_wrapper nav-compact">
			<div class="bottom_nav <?=($bMobileScrolledItems ? 'hide-600' : '');?>" <?=($arParams['IS_AJAX'] ? "style='display: none; '" : "");?> data-parent=".items-list-inner" data-append=".grid-list">
				<?if($arParams['DISPLAY_BOTTOM_PAGER']):?>
					<?=$arResult['NAV_STRING']?>
				<?endif;?>
			</div>
		</div>

		<?if($arParams['IS_AJAX']):?>
			</div>
		<?endif;?>

	<?if(!$arParams['IS_AJAX']):?>
		<?if($arParams['MAXWIDTH_WRAP']):?>
			<?if($bNarrow):?>
				</div>
			<?elseif($arParams['ITEMS_OFFSET']):?>
				</div>
			<?endif;?>
		<?endif;?>

		</div> <?// .items-list-inner?>
	<?endif;?>
<?endif;?>