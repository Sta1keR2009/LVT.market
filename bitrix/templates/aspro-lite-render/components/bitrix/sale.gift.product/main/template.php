<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */

$frame = $this->createFrame()->begin();

use \Bitrix\Main\Localization\Loc;

$injectId = 'sale_gift_product_' . rand();

$currentProductId = (int)$arResult['POTENTIAL_PRODUCT_TO_BUY']['ID'];

if (isset($arResult['REQUEST_ITEMS'])) {
	CJSCore::Init(['ajax']);

	// component parameters
	$signer = new \Bitrix\Main\Security\Sign\Signer;
	$signedParameters = $signer->sign(
		base64_encode(serialize($arResult['_ORIGINAL_PARAMS'])),
		'bx.sale.gift.product'
	);
	$signedTemplate = $signer->sign($arResult['RCM_TEMPLATE'], 'bx.sale.gift.product');

?>

	<div id="<?= $injectId ?>" class="sale_gift_product_container"></div>
	<script>
		BX.ready(function() {
			var currentProductId = <?= CUtil::JSEscape($currentProductId) ?>;
			var giftAjaxData = {
				'parameters': '<?= CUtil::JSEscape($signedParameters) ?>',
				'template': '<?= CUtil::JSEscape($signedTemplate) ?>',
				'site_id': '<?= CUtil::JSEscape(SITE_ID) ?>'
			};

			bx_sale_gift_product_load(
				'<?= CUtil::JSEscape($injectId) ?>',
				giftAjaxData
			);

			BX.addCustomEvent('onCatalogStoreProductChange', function(offerId) {
				if (currentProductId == offerId) {
					return;
				}
				currentProductId = offerId;
				bx_sale_gift_product_load(
					'<?= CUtil::JSEscape($injectId) ?>',
					giftAjaxData, {
						offerId: offerId
					}
				);
			});
		});
	</script>

<?
	$frame->end();
	return;
}


if (!empty($arResult['ITEMS'])) {
	$bHasBottomPager = $arParams["DISPLAY_BOTTOM_PAGER"] == "Y" && $arResult["NAV_STRING"];
	$bUseSchema = !(isset($arParams["NO_USE_SHCEMA_ORG"]) && $arParams["NO_USE_SHCEMA_ORG"] == "Y");
	$bAjax = $arParams["AJAX_REQUEST"] == "Y";
	$bMobileScrolledItems = $arParams['MOBILE_SCROLLED'];
	$bSlider = $arParams['SLIDER'] === true || $arParams['SLIDER'] === 'Y';

	$bShowCompare = $arParams['DISPLAY_COMPARE'] == 'Y';
	$bShowFavorit = $arParams['SHOW_FAVORITE'] == 'Y';
	$bShowRating = $arParams['SHOW_RATING'] == 'Y';

	$elementInRow = $arParams['ELEMENT_IN_ROW'];

	$bOrderViewBasket = $arParams['ORDER_VIEW'];
	$basketURL = (strlen(trim($arTheme['ORDER_VIEW']['DEPENDENT_PARAMS']['URL_BASKET_SECTION']['VALUE'])) ? trim($arTheme['ORDER_VIEW']['DEPENDENT_PARAMS']['URL_BASKET_SECTION']['VALUE']) : '');

	$bUseSelectOffer = false;

	if ($bSlider) {
		$bDots1200 = $arParams['DOTS_1200'] === 'Y' ? 1 : 0;
		if ($arParams['ITEM_1200']) {
			$items1200 = intval($arParams['ITEM_1200']);
		} else {
			$items1200 = $arParams['ELEMENT_IN_ROW'] ? $arParams['ELEMENT_IN_ROW'] : 1;
		}

		if ($arParams['ITEM_768']) {
			$items768 = intval($arParams['ITEM_768']);
		} else {
			$items768 =
				$arParams['ELEMENT_IN_ROW'] > 1 ? 2 : 1;
		}

		$items992 = intval($arParams['ITEM_992']);

		if ($arParams['ITEM_380']) {
			$items380 = intval($arParams['ITEM_380']);
		} else {
			$items380 = 1;
		}

		if ($arParams['ITEM_0']) {
			$items0 = intval($arParams['ITEM_0']);
		} else {
			$items0 = 1;
		}

		$sliderClasses = ' swiper slider-solution mobile-offset mobile-offset--right';
		$sliderWrapperClasses = ' swiper-wrapper mobile-scrolled--items-3';
		$elementSliderClasses = ' swiper-slide swiper-slide--height-auto';
	} else {
		if ($bMobileScrolledItems) {
			$gridClass .= ' mobile-scrolled mobile-scrolled--items-3 mobile-offset';
		} else {
			$gridClass .= ' grid-list--compact';
		}

		$gridClass .= ' grid-list--items-' . $elementInRow . '-1200 grid-list--items-' . ($elementInRow - 1) . '-992 grid-list--items-' . ($elementInRow - 2) . '-768 grid-list--items-2-601';
	}

	$itemClass = ' outer-rounded-x bg-theme-parent-hover border-theme-parent-hover color-theme-parent-all js-popup-block';
	if (!$bSlider)
		$itemClass .= ' shadow-hovered shadow-hovered-f600 shadow-no-border-hovered';
	if ($arParams['BORDERED'] !== 'N') {
		$itemClass .= ' bordered';
	}

	$templateData = array(
		'TEMPLATE_CLASS' => 'bx_' . $arParams['TEMPLATE_THEME'],
		'SHOW_DISCOUNT_COUNTER' => (
			$arParams["SHOW_DISCOUNT_TIME"] === "Y" 
			&& $arParams['SHOW_DISCOUNT_TIME_IN_LIST'] !== 'N'
		),
	);
	$arParams['IS_GIFT'] = 'Y';

	$arSkuTemplate = array();
	if (!empty($arResult['SKU_PROPS'])) {
		$arSkuTemplate = TSolution\SKU\Template::showSkuPropsHtml($arResult['SKU_PROPS']);
	} ?>
	<script type="text/javascript">
		BX.message({
			CVP_MESS_BTN_BUY: '<?= ('' != $arParams['MESS_BTN_BUY'] ? CUtil::JSEscape($arParams['MESS_BTN_BUY']) : GetMessageJS('CVP_TPL_MESS_BTN_BUY_GIFT')); ?>',
			CVP_MESS_BTN_ADD_TO_BASKET: '<?= ('' != $arParams['MESS_BTN_ADD_TO_BASKET'] ? CUtil::JSEscape($arParams['MESS_BTN_ADD_TO_BASKET']) : GetMessageJS('CVP_TPL_MESS_BTN_ADD_TO_BASKET')); ?>',

			CVP_MESS_BTN_DETAIL: '<?= ('' != $arParams['MESS_BTN_DETAIL'] ? CUtil::JSEscape($arParams['MESS_BTN_DETAIL']) : GetMessageJS('CVP_TPL_MESS_BTN_DETAIL')); ?>',

			CVP_MESS_NOT_AVAILABLE: '<?= ('' != $arParams['MESS_BTN_DETAIL'] ? CUtil::JSEscape($arParams['MESS_BTN_DETAIL']) : GetMessageJS('CVP_TPL_MESS_BTN_DETAIL')); ?>',
			CVP_BTN_MESSAGE_BASKET_REDIRECT: '<?= GetMessageJS('CVP_CATALOG_BTN_MESSAGE_BASKET_REDIRECT'); ?>',
			CVP_BASKET_URL: '<?= $arParams["BASKET_URL"]; ?>',
			CVP_ADD_TO_BASKET_OK: '<?= GetMessageJS('CVP_ADD_TO_BASKET_OK'); ?>',
			CVP_TITLE_ERROR: '<?= GetMessageJS('CVP_CATALOG_TITLE_ERROR') ?>',
			CVP_TITLE_BASKET_PROPS: '<?= GetMessageJS('CVP_CATALOG_TITLE_BASKET_PROPS') ?>',
			CVP_TITLE_SUCCESSFUL: '<?= GetMessageJS('CVP_ADD_TO_BASKET_OK'); ?>',
			CVP_BASKET_UNKNOWN_ERROR: '<?= GetMessageJS('CVP_CATALOG_BASKET_UNKNOWN_ERROR') ?>',
			CVP_BTN_MESSAGE_SEND_PROPS: '<?= GetMessageJS('CVP_CATALOG_BTN_MESSAGE_SEND_PROPS'); ?>',
			CVP_BTN_MESSAGE_CLOSE: '<?= GetMessageJS('CVP_CATALOG_BTN_MESSAGE_CLOSE') ?>'
		});
	</script>
	<div class="bx_item_list_you_looked_horizontal detail <?= $templateData['TEMPLATE_CLASS']; ?>">
		<div class="common_product wrapper_block s_<?= $injectId; ?> <?= ($arParams["SHOW_UNABLE_SKU_PROPS"] != "N" ? "show_un_props" : "unshow_un_props"); ?>">
			<? if (empty($arParams['HIDE_BLOCK_TITLE']) || $arParams['HIDE_BLOCK_TITLE'] == 'N'): ?>
				<h3 class="switcher-title"><?= ($arParams['BLOCK_TITLE'] ? htmlspecialcharsbx($arParams['BLOCK_TITLE']) : GetMessage('SGP_TPL_BLOCK_TITLE_DEFAULT')) ?></h3>
			<? endif; ?>

			<div class="catalog-block<?= $bSlider ? ' relative swiper-nav-offset' : ''; ?>">
				<? if ($bSlider): ?>
					<?
					$sliderClasses = ' swiper slider-solution slider-solution--hide-before-loaded mobile-offset mobile-offset--right';
					$sliderWrapperClasses = ' swiper-wrapper mobile-scrolled--items-3';
					$elementSliderClasses = ' swiper-slide swiper-slide--height-auto';
					?>
					<?
					$sliderOptions = [
						'loop' => false,
						'autoplay' => false,
						'spaceBetween' => intval($arParams['GRID_GAP'] ?: 32),
						'slidesPerView' => 'auto',
						'freeMode' => true,
						'breakpoints' => [
							600 => [
								'freeMode' => false,
								'slidesPerView' => 2,
							],
							768 => [
								'freeMode' => false,
								'slidesPerView' => 3,
							],
							992 => [
								'freeMode' => false,
								'slidesPerView' => 4,
							],
							1200 => [
								'freeMode' => false,
								'slidesPerView' => 5,
							],
						],
					];
					?>
					<div class="content_inner catalog_block js_append ajax_load <?= $sliderClasses ?>" data-plugin-options='<?= \Bitrix\Main\Web\Json::encode($sliderOptions); ?>'>
				<? else: ?>
					<div class="content_inner js_append ajax_load block grid-list grid-list--fill-bg <?=$gridClass?>">
				<? endif; ?>
					<? if ($sliderWrapperClasses): ?>
						<div class="<?= $sliderWrapperClasses; ?>">
					<? endif; ?>
						<?
						$elementEdit = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_EDIT');
						$elementDelete = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_DELETE');
						$elementDeleteParams = array('CONFIRM' => GetMessage('CVP_TPL_ELEMENT_DELETE_CONFIRM'));
						?>
						<? foreach ($arResult['ITEMS'] as $key => $arItem): ?>
							<?
							$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
							$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));

							$item_id = $arItem["ID"];
							$arItem["strMainID"] = $this->GetEditAreaId($arItem['ID']);
							$strObName = 'ob' . preg_replace("/[^a-zA-Z0-9_]/", "x", $arItem["strMainID"]);

							$elementName = ((isset($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) && $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arItem['NAME']);
							// use order button?
							$bOrderButton = ($arItem["PROPERTIES"]["FORM_ORDER"]["VALUE_XML_ID"] == "YES");
							$dataItem = ($bOrderViewBasket ? TSolution::getDataItem($arItem) : false);

							$article = $arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'];

							//unset($arItem['OFFERS']); // get correct totalCount
							$totalCount = TSolution\Product\Quantity::getTotalCount([
								'ITEM' => $arItem,
								'PARAMS' => $arParams
							]);
							$arStatus = TSolution\Product\Quantity::getStatus([
								'ITEM' => $arItem,
								'PARAMS' => $arParams,
								'TOTAL_COUNT' => $totalCount
							]);
							/* sku replace start */
							$arCurrentOffer = $arItem['SKU']['CURRENT'];

							if ($arCurrentOffer) {
								$arItem['PARENT_IMG'] = '';
								if ($arItem['PREVIEW_PICTURE']) {
									$arItem['PARENT_IMG'] = $arItem['PREVIEW_PICTURE'];
								} elseif ($arItem['DETAIL_PICTURE']) {
									$arItem['PARENT_IMG'] = $arItem['DETAIL_PICTURE'];
								}

								$oid = \Bitrix\Main\Config\Option::get(VENDOR_MODULE_ID, 'CATALOG_OID', 'oid');
								if ($oid) {
									$arItem['DETAIL_PAGE_URL'] .= '?' . $oid . '=' . $arCurrentOffer['ID'];
									$arCurrentOffer['DETAIL_PAGE_URL'] = $arItem['DETAIL_PAGE_URL'];
								}
								if ($arParams['SHOW_GALLERY'] === 'Y') {
									$arOfferGallery = TSolution\Functions::getSliderForItem([
										'TYPE' => 'catalog_block',
										'PROP_CODE' => $arParams['OFFER_ADD_PICT_PROP'],
										'ITEM' => $arCurrentOffer,
										'PARAMS' => $arParams,
									]);

									if ($arOfferGallery) {
										$arItem['GALLERY'] = array_merge($arOfferGallery, $arItem['GALLERY']);
										array_splice($arItem['GALLERY'], $arParams['MAX_GALLERY_ITEMS']);
									}
								} else {
									if ($arCurrentOffer['PREVIEW_PICTURE'] || $arCurrentOffer['DETAIL_PICTURE']) {
										if ($arCurrentOffer['PREVIEW_PICTURE']) {
											$arItem['PREVIEW_PICTURE'] = $arCurrentOffer['PREVIEW_PICTURE'];
										} elseif ($arCurrentOffer['DETAIL_PICTURE']) {
											$arItem['PREVIEW_PICTURE'] = $arCurrentOffer['DETAIL_PICTURE'];
										}
									}
									if (!$arCurrentOffer['PREVIEW_PICTURE'] && !$arCurrentOffer['DETAIL_PICTURE']) {
										if ($arItem['PREVIEW_PICTURE']) {
											$arCurrentOffer['PREVIEW_PICTURE'] = $arItem['PREVIEW_PICTURE'];
										} elseif ($arItem['DETAIL_PICTURE']) {
											$arCurrentOffer['PREVIEW_PICTURE'] = $arItem['DETAIL_PICTURE'];
										}
									}
								}

								if ($arCurrentOffer["DISPLAY_PROPERTIES"]["CML2_ARTICLE"]["VALUE"] || $arCurrentOffer["DISPLAY_PROPERTIES"]["ARTICLE"]["VALUE"]) {
									$article = $arCurrentOffer['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] ?? $arCurrentOffer["DISPLAY_PROPERTIES"]["ARTICLE"]["VALUE"];
								}

								$arItem["DISPLAY_PROPERTIES"]["FORM_ORDER"] = $arCurrentOffer["DISPLAY_PROPERTIES"]["FORM_ORDER"];
								$arItem["DISPLAY_PROPERTIES"]["PRICE"] = $arCurrentOffer["DISPLAY_PROPERTIES"]["PRICE"];
								$arItem["NAME"] = $arCurrentOffer["NAME"];
								$elementName = $arCurrentOffer["NAME"];
								$arItem['OFFER_PROP'] = TSolution::PrepareItemProps($arCurrentOffer['DISPLAY_PROPERTIES']);

								$dataItem = ($bOrderViewBasket ? TSolution::getDataItem($arCurrentOffer) : false);

								$totalCount = TSolution\Product\Quantity::getTotalCount([
									'ITEM' => $arCurrentOffer,
									'PARAMS' => $arParams
								]);
								$arStatus = TSolution\Product\Quantity::getStatus([
									'ITEM' => $arCurrentOffer,
									'PARAMS' => $arParams,
									'TOTAL_COUNT' => $totalCount
								]);
							}
							$bOrderButton = ($arItem["DISPLAY_PROPERTIES"]["FORM_ORDER"]["VALUE_XML_ID"] == "YES");

							$status = $arStatus['NAME'];
							$statusCode = $arStatus['CODE'];
							/* sku replace end */ ?>
							<? ob_start(); ?>
							<? if ($templateData['SHOW_DISCOUNT_COUNTER']): ?>
								<?
								$discountDateTo = '';
								if (TSolution\Product\Price::$catalogInclude) {
									$arDiscount = TSolution\Product\Price::getDiscountByItemID($arItem['ID']);
									$discountDateTo = $arDiscount ? $arDiscount['ACTIVE_TO'] : '';
								} else {
									$discountDateTo = $arItem['DISPLAY_PROPERTIES']['DATE_COUNTER']['VALUE'];
								}
								?>
								<? if ($discountDateTo): ?>
									<? TSolution\Functions::showDiscountCounter([
										'ICONS' => true,
										'SHADOWED' => true,
										'DATE' => $discountDateTo,
										'ITEM' => $arItem
									]); ?>
								<? endif; ?>
							<? endif; ?>
							<? $itemDiscount = ob_get_clean(); ?>

							<?
							$arTransferItem = ($arCurrentOffer ? $arCurrentOffer : $arItem);

							// Set price for gift
							if ($arTransferItem['PRICES']) {
								foreach ($arTransferItem['PRICES'] as $priceCode => $value) {
									$arTransferItem["PRICES"][$priceCode]["DISCOUNT_VALUE"] = $arTransferItem["PRICES"][$priceCode]["DISCOUNT_DIFF"];
									$arTransferItem["PRICES"][$priceCode]["PRINT_DISCOUNT_VALUE"] = $arTransferItem["PRICES"][$priceCode]["PRINT_DISCOUNT_DIFF"];
									$arTransferItem["PRICES"][$priceCode]["DISCOUNT_DIFF_PERCENT"] = 100;
								}
							}
							?>

							<div class="catalog-block__wrapper<?= $elementSliderClasses; ?> grid-list__item grid-list-border-outer <?= ($arCurrentOffer ? 'has-offers' : ''); ?>" data-hovered="false">
								<? if (TSolution::isSaleMode()): ?>
									<div class="basket_props_block" id="bx_basket_div_<?= $arItem["ID"]; ?>_<?= $arParams["FILTER_HIT_PROP"] ?>" style="display: none;">
										<? if (!empty($arItem['PRODUCT_PROPERTIES_FILL'])): ?>
											<? foreach ($arItem['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo): ?>
												<input type="hidden" name="<?= $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<?= $propID; ?>]" value="<?= htmlspecialcharsbx($propInfo['ID']); ?>">
												<?
												if (isset($arItem['PRODUCT_PROPERTIES'][$propID])) {
													unset($arItem['PRODUCT_PROPERTIES'][$propID]);
												}
												?>
											<? endforeach; ?>
										<? endif; ?>

										<? if ($arItem['PRODUCT_PROPERTIES']): ?>
											<div class="wrapper">
												<? foreach ($arItem['PRODUCT_PROPERTIES'] as $propID => $propInfo): ?>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group fill-animate">
																<? if (
																	'L' == $arItem['PROPERTIES'][$propID]['PROPERTY_TYPE'] &&
																	'C' == $arItem['PROPERTIES'][$propID]['LIST_TYPE']
																): ?>
																	<label class="font_14"><span><?=$arItem['PROPERTIES'][$propID]['NAME']?></span></label>
																	<?foreach($propInfo['VALUES'] as $valueID => $value):?>
																		<div class="form-radiobox">
																			<label class="form-radiobox__label">
																				<input class="form-radiobox__input" type="radio" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propID?>]" value="<?=$valueID?>">
																				<span class="bx_filter_input_checkbox">
																					<span><?=$value?></span>
																				</span>
																				<span class="form-radiobox__box"></span>
																			</label>
																		</div>
																	<?endforeach;?>
																<? else: ?>
																	<label class="font_14"><span><?= $arItem['PROPERTIES'][$propID]['NAME'] ?></span></label>
																	<div class="input">
																		<select class="form-control" name="<?= $arParams['PRODUCT_PROPS_VARIABLE'] ?>[<?= $propID ?>]">
																			<? foreach ($propInfo['VALUES'] as $valueID => $value): ?>
																				<option value="<?= $valueID ?>" <?= ($valueID == $propInfo['SELECTED'] ? '"selected"' : '') ?>><?= $value ?></option>
																			<? endforeach; ?>
																		</select>
																	</div>
																<? endif; ?>
															</div>
														</div>
													</div>
												<? endforeach; ?>
											</div>
										<? endif; ?>
									</div>
								<? endif; ?>

								<div class="catalog-block__item <?= $itemClass ?>" id="<?= $arItem["strMainID"] ?>">
									<?if ($arItem['SKU']['PROPS']):?>
										<template class="offers-template-json">
											<?=TSolution\SKU::getOfferTreeJson($arItem["SKU"]["OFFERS"])?>
										</template>
										<?$bUseSelectOffer = true;?>
									<?endif;?>
									<div class="catalog-block__inner flexbox height-100" <? if ($bUseSchema): ?>itemprop="itemListElement" itemscope="" itemtype="http://schema.org/Product" <? endif; ?>>
										<? if ($bUseSchema): ?>
											<meta itemprop="description" content="<?= htmlspecialcharsbx(strip_tags($arItem['PREVIEW_TEXT'] ?: $arItem['NAME'])) ?>" />
										<? endif; ?>
										<? $arImgConfig = [
											'TYPE' => 'catalog_block',
											'ADDITIONAL_IMG_CLASS' => 'js-replace-img',
											'ADDITIONAL_WRAPPER_CLASS' => ($arParams['IMG_CORNER'] == 'Y' ? 'catalog-block__item--img-corner' : ''),
										]; ?>
										<div class="js-config-img" data-img-config='<?= str_replace('\'', '"', CUtil::PhpToJSObject($arImgConfig, false, true)) ?>'></div>
										<? $priceHtml = $discountHtml = ''; ?>
										<? if (TSolution\Product\Price::check($arTransferItem)) {
											$arPrices = TSolution\Product\Price::show([
												'ITEM' => $arTransferItem,
												'PARAMS' => $arParams,
												'SHOW_SCHEMA' => $bUseSchema,
												'BASKET' => $bOrderViewBasket,
												'RETURN' => true,
												'APART_ECONOMY' => true,
												'PRICE_FONT' => '18 font_14--to-600',
											]);
											if ($arPrices['PRICES']) {
												$priceHtml = $arPrices['PRICES'];
											}
											if ($arPrices['ECONOMY']) {
												$discountHtml = $arPrices['ECONOMY'];
											}
										} ?>

										<?= TSolution\Product\Image::showImage(
											array_merge(
												[
													'ITEM' => $arItem,
													'PARAMS' => $arParams,
													'CONTENT_TOP' => $itemDiscount,
													'CONTENT_BOTTOM' => $discountHtml,
												],
												$arImgConfig
											)
										) ?>

										<? if ($bUseSchema): ?>
											<meta itemprop="name" content="<?= $arItem['NAME'] ?>">
											<link itemprop="url" href="<?= $arItem['DETAIL_PAGE_URL'] ?>">
										<? endif; ?>

										<div class="catalog-block__info flex-1 flexbox flexbox--justify-beetwen" data-id="<?= ($arCurrentOffer ? $arCurrentOffer['ID'] : $arItem['ID']) ?>" <?= ($bOrderViewBasket ? ' data-item="' . $dataItem . '"' : '') ?> <? if ($bUseSchema): ?> itemprop="offers" itemscope itemtype="http://schema.org/Offer" <? endif; ?>>
											<div class="catalog-block__info-top">
												<div class="catalog-block__info-inner">
													<? // element price
													?>
													<? $arPriceConfig = [
														'PRICE_CODE' => $arParams['PRICE_CODE'],
														'PARAMS' => [
															'SHOW_DISCOUNT_PERCENT' => 'N', // hide discount in js_item_detail.php
															'IS_GIFT' => 'Y',
														],
													]; ?>
													<div class="js-popup-price" data-price-config='<?= str_replace('\'', '"', CUtil::PhpToJSObject($arPriceConfig, false, true)) ?>'>
														<? if ($priceHtml): ?>
															<?= $priceHtml; ?>
														<? endif; ?>
													</div>
													<? // element title
													?>
													<div class="catalog-block__info-title height-auto-t600 linecamp-3 height-auto-t600 font_15 font_14--to-600">
														<? if ($bUseSchema): ?>
															<link itemprop="url" href="<?= $arItem['DETAIL_PAGE_URL'] ?>">
														<? endif; ?>
														<a href="<?= $arItem["DETAIL_PAGE_URL"] ?>" class="dark_link switcher-title js-popup-title color-theme-target"><span><?= $elementName; ?></span></a>
													</div>
													<? if ($bShowRating || strlen($status) || strlen($article)): ?>
														<div class="catalog-block__info-tech">
															<div class="line-block line-block--12 flexbox--wrap js-popup-info">
																<? // rating
																?>
																<? if ($bShowRating): ?>
																	<div class="line-block__item font_13 font_12--to-600">
																		<?= \TSolution\Product\Common::getRatingHtml([
																			'ITEM' => $arItem,
																			'PARAMS' => $arParams,
																		]) ?>
																	</div>
																<? endif; ?>
																<? // status
																?>
																<? if ((strlen($status) && !isset($arParams['HIDE_STATUS_BUTTON']))): ?>
																	<div class="line-block__item font_13 font_12--to-600">
																		<? if ($bUseSchema): ?>
																			<?= TSolution\Functions::showSchemaAvailabilityMeta($statusCode); ?>
																		<? endif; ?>
																		<span class="status-icon <?= $statusCode ?> js-replace-status" data-state="<?= $statusCode ?>" data-code="<?= $arItem['DISPLAY_PROPERTIES']['STATUS']['VALUE_XML_ID'] ?>" data-value="<?= $arItem['DISPLAY_PROPERTIES']['STATUS']['VALUE'] ?>"><?= $status ?></span>
																	</div>
																<? endif; ?>
																<? // article
																?>
																<? if (strlen($article)): ?>
																	<div class="line-block__item font_13 font_12--to-600 color_999">
																		<span class="article"><?= GetMessage('S_ARTICLE') ?>&nbsp;<span class="js-replace-article" data-value="<?= $arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] ?>"><?= $article ?></span></span>
																	</div>
																<? endif; ?>
															</div>
														</div>
													<? endif; ?>
												</div>
											</div>
										</div>

										<? // element btns
										?>
										<? $arBtnConfig = [
											'BASKET_URL' => $basketURL,
											'BASKET' => $bOrderViewBasket,
											'ORDER_BTN' => $bOrderButton,
											'BTN_CLASS' => 'btn-sm btn-wide',
											'BTN_CLASS_MORE' => 'btn-sm bg-theme-target border-theme-target',
											'BTN_IN_CART_CLASS' => 'btn-sm',
											'BTN_ORDER_CLASS' => 'btn-sm btn-wide btn-transparent-border',
											'SHOW_COUNTER' => false,
											'CATALOG_IBLOCK_ID' => $arItem['IBLOCK_ID'],
											'ITEM_ID' => $arItem['ID'],
										];
										if ($arParams['TYPE_SKU'] === 'TYPE_2' && $arItem['OFFERS']) {
											$arBtnConfig['SHOW_MORE'] = true;
											$arItem['CAN_BUY'] = 'N';
											$totalCount = 0;
										} ?>
										<?
										$arBasketConfig = TSolution\Product\Basket::getOptions(array_merge(
											$arBtnConfig,
											[
												'ITEM' => $arTransferItem,
												'IS_OFFER' => (bool)$arCurrentOffer,
												'PARAMS' => $arParams,
												'TOTAL_COUNT' => $totalCount
											]
										)); ?>
										<? if (
											($bShowCompare
												|| $bShowFavorit
												|| ($arCurrentOffer
													|| (!$arCurrentOffer && $arBasketConfig['HTML'])
												)
												|| $arItem['SKU']['PROPS'])
											&& !isset($arParams['HIDE_BUY_BUTTON'])
										): ?>
											<div class="catalog-block__info-bottom <?= ($arCurrentOffer ? 'catalog-block__info-bottom--with-sku' : ''); ?>">
												<div class="line-block line-block--8 line-block--8-vertical flexbox--wrap flexbox--justify-center">
													<div class="line-block__item js-btn-state-wrapper flex-1 <?= ($arCurrentOffer ? 'hide-600' : ''); ?> <?= (!$arBasketConfig['HTML'] ? ' hidden' : ''); ?>">
														<div class="js-replace-btns js-config-btns" data-btn-config='<?= str_replace('\'', '"', CUtil::PhpToJSObject($arBtnConfig, false, true)) ?>'>
															<?= $arBasketConfig['HTML'] ?>
														</div>
													</div>
													<? if ($arCurrentOffer): ?>
														<div class="visible-600 line-block__item flex-1">
															<?= TSolution\Product\Basket::getMoreButton([
																'ITEM' => $arCurrentOffer,
																'BTN_CLASS_MORE' => ''
															]); ?>
														</div>
													<? endif; ?>
													<? if ($bShowCompare || $bShowFavorit): ?>
														<div class="line-block__item js-replace-icons">
															<? if ($bShowFavorit): ?>
																<?= \TSolution\Product\Common::getActionIcon([
																	'ITEM' => $arItem,
																	'PARAMS' => $arParams,
																	'CLASS' => 'md',
																]) ?>
															<? endif; ?>
															<? if ($bShowCompare): ?>
																<?= \TSolution\Product\Common::getActionIcon([
																	'ITEM' => (($arCurrentOffer && \TSolution::isSaleMode()) ? $arCurrentOffer : $arItem),
																	'PARAMS' => $arParams,
																	'TYPE' => 'compare',
																	'CLASS' => 'md',
																	'SVG_SIZE' => ['WIDTH' => 20, 'HEIGHT' => 16],
																]) ?>
															<? endif; ?>
														</div>
													<? endif; ?>
												</div>
												<? if ($arItem['SKU']['PROPS']): ?>
													<div class="catalog-block__offers hide-600">
														<div class="sku-props sku-props--block" data-site-id="<?= SITE_ID; ?>" data-item-id="<?= $arItem['ID']; ?>" data-iblockid="<?= $arItem['IBLOCK_ID']; ?>" data-offer-id="<?= $arCurrentOffer['ID']; ?>" data-offer-iblockid="<?= $arCurrentOffer['IBLOCK_ID']; ?>">
															<div class="line-block line-block--flex-wrap line-block--40 line-block--align-flex-end line-block--flex-100">
																<?= TSolution\SKU\Template::showSkuPropsHtml($arItem['SKU']['PROPS']) ?>
															</div>
														</div>
													</div>
												<? endif; ?>
											</div>
										<? endif; ?>
									</div>
								</div>
							</div>
						<? endforeach; ?>
					<? if ($sliderWrapperClasses): ?>
						</div>
					<? endif; ?>
				</div>
				<?if($bUseSelectOffer):?>
					<script>typeof useOfferSelect === 'function' && useOfferSelect()</script>
				<?endif;?>
				<? if ($bSlider): ?>
					<div class="slider-nav slider-nav--no-auto-hide swiper-button-prev hide-600">
						<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/arrows.svg#left-7-12', 'stroke-dark-light', [
							'WIDTH' => 7,
							'HEIGHT' => 12
						]); ?>
					</div>

					<div class="slider-nav slider-nav--no-auto-hide swiper-button-next hide-600">
						<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/arrows.svg#right-7-12', 'stroke-dark-light', [
							'WIDTH' => 7,
							'HEIGHT' => 12
						]); ?>
					</div>
				<? endif; ?>
			</div>
		</div>
	</div>
<? } ?>
<? $frame->beginStub(); ?>
<? $frame->end(); ?>