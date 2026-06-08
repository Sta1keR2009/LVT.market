<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();
$this->setFrameMode(true);
use \Bitrix\Main\Localization\Loc;

$bItemsTypeElements = $arParams['ITEMS_TYPE'] !== 'SECTIONS';
$arItems = $bItemsTypeElements ? $arResult['ITEMS'] : $arResult['SECTIONS'];
?>
<?if($arItems):?>
	<?
	$bShowLeftBlock = $arParams['HIDE_LEFT_TEXT_BLOCK'] === 'N';
	$bNarrow = $arParams['NARROW'];

	$blockClasses = 'services-list--items-offset';
	
	$gridClass = 'grid-list';
	if($arParams['MOBILE_SCROLLED']){
		$gridClass .= ' mobile-scrolled mobile-scrolled--items-2 mobile-offset';
	}

	if($bNarrow){
		$gridClass .= ' grid-list--items-'.$arParams['ELEMENTS_ROW'];
		$gridClass .= \TSolution\Functions::getGridClassByCount(['992', '1200'], $arParams['ELEMENTS_ROW']);
	}

	$itemWrapperClasses = ' grid-list__item stroke-theme-parent-all colored_theme_hover_bg-block animate-arrow-hover color-theme-parent-all';
	if(!$arParams['ITEMS_OFFSET'] && $arParams['BORDER']){
		$itemWrapperClasses .= ' grid-list-border-outer';
	}

	$itemClasses = 'height-100 flexbox bordered outer-rounded-x shadow-hovered shadow-no-border-hovered ';
	
	if(!$bItemsTypeElements){
		$itemClasses .= ' services-list__item--section';
	}

	if (!$arParams['MOBILE_SCROLLED']) {
		$itemClasses .= ' services-list__item--no-scrolled';
	}
	?>
	<?if(!$arParams['IS_AJAX']):?>
		<div class="services-list <?=$blockClasses?> <?=$templateName?>-template">
			<div class="<?=$gridClass?>">
	<?endif;?>
			<?
			$bShowImage = in_array('PREVIEW_PICTURE', $arParams['FIELD_CODE']);

			$counter = 1;
			foreach($arItems as $i => $arItem):?>
				<?
				// edit/add/delete buttons for edit mode
				if($bItemsTypeElements){
					$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
					$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), array('CONFIRM' => Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
				}
				else{
					// edit/add/delete buttons for edit mode
					$arSectionButtons = CIBlock::GetPanelButtons($arItem['IBLOCK_ID'], 0, $arItem['ID'], array('SESSID' => false, 'CATALOG' => true));
					$this->AddEditAction($arItem['ID'], $arSectionButtons['edit']['edit_section']['ACTION_URL'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'SECTION_EDIT'));
					$this->AddDeleteAction($arItem['ID'], $arSectionButtons['edit']['delete_section']['ACTION_URL'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'SECTION_DELETE'), array('CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
				}

				// use detail link?
				$bDetailLink = $arParams['SHOW_DETAIL_LINK'] != 'N' && (!strlen($arItem['DETAIL_TEXT']) ? ($arParams['HIDE_LINK_WHEN_NO_DETAIL'] !== 'Y' && $arParams['HIDE_LINK_WHEN_NO_DETAIL'] != 1) : true);

				// detail url
				$detailUrl = $bItemsTypeElements ? $arItem['DETAIL_PAGE_URL'] : $arItem['SECTION_PAGE_URL'];

				// preview text
				$previewText = $bItemsTypeElements ? $arItem['FIELDS']['PREVIEW_TEXT'] : $arItem['~UF_TOP_SEO'];
				$htmlPreviewText = '';

				// preview image
				if($bShowImage){
					if($bItemsTypeElements){
						$nImageID = is_array($arItem['FIELDS']['PREVIEW_PICTURE']) ? $arItem['FIELDS']['PREVIEW_PICTURE']['ID'] : $arItem['FIELDS']['PREVIEW_PICTURE'];	
					}
					else{
						$nImageID = is_array($arItem['PICTURE']) ? $arItem['PICTURE']['ID'] : $arItem['~PICTURE'];	
					}
					$imageSrc = ($nImageID ? CFile::getPath($nImageID) : SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg');
				}

				$bShowPrice = $bItemsTypeElements && in_array('PRICE', $arParams['PROPERTY_CODE']) && strlen($arItem['DISPLAY_PROPERTIES']['PRICE']['VALUE']);
				$bShowBottom = ($bItemsTypeElements && $bShowPrice);
				$bOrderButton = $arItem["PROPERTIES"]["FORM_ORDER"]["VALUE_XML_ID"] === "YES";
				?>
				<div class="services-list__wrapper <?=$itemWrapperClasses?>">
					<div class="services-list__item <?=$itemClasses?> <?=($bDetailLink ? '' : 'services-list__item--cursor-initial')?>" id="<?=$this->GetEditAreaId($arItem['ID'])?>">
						<?if($bShowPrice){
							$priceHtml = $discountHtml = '';
							$arPrices = TSolution\Product\Price::show([
								'ITEM' =>$arItem,
								'PARAMS' => $arParams,
								'SHOW_SCHEMA' => true,
								'BASKET' => $bOrderViewBasket,
								'PRICE_FONT' => 18,
								'PRICEOLD_FONT' => 12,
								'PRICE_BLOCK_CLASS' => "line-block__item no-shrinked",
								'RETURN' => true,
								'APART_ECONOMY' => true,
								'SERVICES_PAGE' => true,
							]);
							if ($arPrices['PRICES']) {
								$priceHtml = $arPrices['PRICES'];
							}
							if ($arPrices['ECONOMY']) {
								$discountHtml = $arPrices['ECONOMY'];
							}
						}?>
					
						<?if($bShowImage && $imageSrc):?>
							<div class="services-list__item-image-wrapper <?=$imageWrapperClasses?>">
								<?if($bDetailLink):?>
									<a class="services-list__item-link services-list__item-link--absolute" href="<?=$detailUrl?>">
								<?endif;?>
									<?if($bIcons && $nImageID):?>
										<?=TSolution::showIconSvg(' fill-theme services-list__item-image-icon', $imageSrc);?>
									<?else:?>
										<span class="services-list__item-image" style="background-image: url(<?=$imageSrc?>);"></span>
									<?endif;?>
								<?if($bDetailLink):?>
									</a>
								<?else:?>
									</span>
								<?endif;?>
								<?=$discountHtml?>
							</div>
						<?endif;?>

						<div class="services-list__item-text-wrapper flexbox <?=($bShowBottom ? 'services-list__item-text-wrapper--has-bottom-part' : '')?>">

							<div class="services-list__item-text-top-part flexbox height-100">
								<?if($bItemsTypeElements && $arItem['SECTIONS'] && $arParams['SHOW_SECTION'] != 'N'):?>
									<div class="services-list__item-section font_13 color_999"><?=implode(', ', $arItem['SECTIONS'])?></div>
								<?endif;?>
								<div class="services-list__item-title switcher-title">
									<?if($bDetailLink):?>
										<a class="dark_link color-theme-target" href="<?=$detailUrl?>"><?=$arItem['NAME']?></a>
									<?else:?>
										<span class="color_222"><?=$arItem['NAME']?></span>
									<?endif;?>
								</div>

								<?if($bShowBottom):?>
									<div class="services-list__item-text-bottom-part flexbox <?=($bShowPrice ? 'services-list__item-text-bottom-part--has-price' : '')?>">
										<div class="services-list__item-price-wrapper">
											<div class="services-list__item-price font_17 color_222">
												<?=$priceHtml?>
											</div>
										</div>
									</div>
								<?endif;?>
								
								<?if(
									in_array('PREVIEW_TEXT', $arParams['FIELD_CODE']) &&
									$arParams['SHOW_PREVIEW'] &&
									strlen($previewText)
								):?>
									<?ob_start()?>
										<div class="services-list__item-preview-wrapper">
											<div class="services-list__item-preview font_14 color_666">
												<?=$previewText?>
											</div>
										</div>
									<?$htmlPreviewText = ob_get_clean()?>
									<?if($bItemsTypeElements):?>
										<?//=$htmlPreviewText?>
									<?endif;?>
								<?endif;?>
								<?if($bItemsTypeElements):?>
									<div class="services-list__item-btns line-block__item">
											<div class="line-block line-block--align-normal line-block--12">
												<?if ($bOrderButton):
													$arBtnConfig = [
														'BTN_CLASS' => 'btn-sm btn-wide',
														'ITEM' => $arItem,
														'BTN_ORDER_CLASS' => 'btn-sm btn-wide',
														'ORDER_FORM_ID' => $arParams['FORM_ID_ORDER_SERVISE'] ? $arParams['FORM_ID_ORDER_SERVISE'] : "aspro_lite_order_services",
														'CONFIG' => array("EXPRESSION_ORDER_BUTTON" => $arParams["S_ORDER_SERVISE"] ? $arParams["S_ORDER_SERVISE"] : Loc::getMessage("S_ORDER_SERVISE"))
													];	
													$arBasketConfig = TSolution\Product\Basket::getOrderButton($arBtnConfig);
													?>
													<div class="line-block__item">
														<?=$arBasketConfig?>
													</div>
												<?else:?>
													<div class="line-block__item">
														<?if($bDetailLink):?>
															<a class="btn btn-default btn-sm btn-wide" href="<?=$detailUrl?>"><?=Loc::getMessage('MORE')?></a>
														<?endif;?>
													</div>
												<?endif;?>
											</div>
										</div>
								<?endif;?>

								<?if(!$bItemsTypeElements):?>
									<?=$htmlPreviewText?>
								<?endif;?>

							</div>
						</div>
					</div>
				</div>
			<?
			$counter++;
			endforeach;?>

			<?if($arParams['IS_AJAX']):?>
				<div class="wrap_nav bottom_nav_wrapper">
			<?endif;?>
				<?$bHasNav = (strpos($arResult["NAV_STRING"], 'more_text_ajax') !== false);?>
				<div class="bottom_nav mobile_slider <?=($bHasNav ? '' : ' hidden-nav');?>" data-parent=".services-list" data-append=".grid-list" <?=($arParams["IS_AJAX"] ? "style='display: none; '" : "");?>>
					<?if($bHasNav):?>
						<?=$arResult["NAV_STRING"]?>
					<?endif;?>
				</div>

			<?if($arParams['IS_AJAX']):?>
				</div>
			<?endif;?>

	<?if(!$arParams['IS_AJAX']):?>
		</div>
	<?endif;?>

		<?// bottom pagination?>
		<?if($arParams['IS_AJAX']):?>
			<div class="wrap_nav bottom_nav_wrapper">
		<?endif;?>

		<div class="bottom_nav_wrapper nav-compact">
			<div class="bottom_nav hide-600" <?=($arParams['IS_AJAX'] ? "style='display: none; '" : "");?> data-parent=".services-list" data-append=".grid-list">
				<?if($arParams['DISPLAY_BOTTOM_PAGER']):?>
					<?=$arResult['NAV_STRING']?>
				<?endif;?>
			</div>
		</div>

		<?if($arParams['IS_AJAX']):?>
			</div>
		<?endif;?>
	<?if(!$arParams['IS_AJAX']):?>
		</div> <?// .services-list?>
	<?endif;?>
<?endif;?>