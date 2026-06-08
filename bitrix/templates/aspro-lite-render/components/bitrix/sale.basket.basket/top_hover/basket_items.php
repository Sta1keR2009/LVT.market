<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
//echo ShowError($arResult["ERROR_MESSAGE"]);

if (!include_once($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/vendor/php/solution.php')) {
	return false;
}

$bDelayColumn  = false;
$bDeleteColumn = false;
$bWeightColumn = false;
$bPropsColumn  = false;
$rowCols = 0;

$showOldPrice = \Bitrix\Main\Config\Option::get("aspro.lite", "SERVICES_SHOW_OLD_PRICE", 'Y') === 'Y';

$bHasServices = false;


if ($normalCount > 0) :
	global $arBasketItems; ?>

	<?
	foreach ($arResult["GRID"]["HEADERS"] as $id => $arHeader) {
		if ($arHeader["id"] == "DELETE") {
			$bDeleteColumn = true;
		}
		if ($arHeader["id"] == "TYPE") {
			$bTypeColumn = true;
		}
		if ($arHeader["id"] == "QUANTITY") {
			$bQuantityColumn = true;
		}
		if ($arHeader["id"] == "DISCOUNT") {
			$bDiscountColumn = true;
		}
	}
	?>
	<? foreach ($arResult["GRID"]["HEADERS"] as $id => $arHeader) :
		if (in_array($arHeader["id"], array("TYPE", "DISCOUNT"))) {
			continue;
		} // some header columns are shown differently
		elseif ($arHeader["id"] == "PROPS") {
			$bPropsColumn = true;
			continue;
		} elseif ($arHeader["id"] == "DELAY") {
			$bDelayColumn = true;
			continue;
		} elseif ($arHeader["id"] == "WEIGHT") {
			$bWeightColumn = true;
		} elseif ($arHeader["id"] == "DELETE") {
			continue;
		} ?>
	<? endforeach; ?>

	<div class="basket_wrap">
		<div class="items_wrap cart dropdown dropdown--relative dropdown-product">
			<? if (isset($arResult["ITEMS_IBLOCK_ID"])) { ?>
				<div class="iblockid" data-iblockid="<?= $arResult["ITEMS_IBLOCK_ID"]; ?>"></div>
			<? } ?>
			<div class="items scrollbar dropdown-product__items">
				<? foreach ($arResult["GRID"]["ROWS"] as $k => $arItem) :
					
					$isServices = false;
					if ($arItem["PROPS"]) {
						$arPropsByCode = array_column($arItem["PROPS"], NULL, "CODE");
						$isServices = isset($arPropsByCode["ASPRO_BUY_PRODUCT_ID"]) && $arPropsByCode["ASPRO_BUY_PRODUCT_ID"]["VALUE"] > 0;
						$idParentProduct = $arPropsByCode["ASPRO_BUY_PRODUCT_ID"]["VALUE"];
					}

					if ($isServices)
						$bHasServices = true;

					$currency = $arItem["CURRENCY"];
					if ($arItem["DELAY"] == "N" && $arItem["CAN_BUY"] == "Y") :
						$arBasketItems[] = $arItem["PRODUCT_ID"]; ?>
						<div class="dropdown-product__item <?= ($isServices ? 'hidden' : '') ?>" data-id="<?= $arItem["ID"] ?>" product-id="<?= $arItem["PRODUCT_ID"] ?>" data-iblockid="<?= $arItem["IBLOCK_ID"] ?>" <? if ($arItem["QUANTITY"] > $arItem["AVAILABLE_QUANTITY"]) : ?>data-error="no_amounth" <? endif; ?> <? if ($isServices) : ?>data-parent_product_id="<?= $idParentProduct ?>" <? endif; ?>>
							<div class="line-block line-block--20 line-block--align-normal">
								<div class="line-block__item">
									<div class="dropdown-product__item-image">
										<? if (strlen($arItem["PREVIEW_PICTURE"]["SRC"]) > 0) { ?>
											<? if (strlen($arItem["DETAIL_PAGE_URL"]) > 0) : ?><a href="<?= $arItem["DETAIL_PAGE_URL"] ?>" class="thumb"><? endif; ?>
												<img src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>" alt="<?= (is_array($arItem["PREVIEW_PICTURE"]["ALT"]) ? $arItem["PREVIEW_PICTURE"]["ALT"] : $arItem["NAME"]); ?>" title="<?= (is_array($arItem["PREVIEW_PICTURE"]["TITLE"]) ? $arItem["PREVIEW_PICTURE"]["TITLE"] : $arItem["NAME"]); ?>" />
												<? if (strlen($arItem["DETAIL_PAGE_URL"]) > 0) : ?></a><? endif; ?>
										<? } elseif (strlen($arItem["DETAIL_PICTURE"]["SRC"]) > 0) { ?>
											<? if (strlen($arItem["DETAIL_PAGE_URL"]) > 0) : ?><a href="<?= $arItem["DETAIL_PAGE_URL"] ?>" class="thumb"><? endif; ?>
												<img src="<?= $arItem["DETAIL_PICTURE"]["SRC"] ?>" alt="<?= (is_array($arItem["DETAIL_PICTURE"]["ALT"]) ? $arItem["DETAIL_PICTURE"]["ALT"] : $arItem["NAME"]); ?>" title="<?= (is_array($arItem["DETAIL_PICTURE"]["TITLE"]) ? $arItem["DETAIL_PICTURE"]["TITLE"] : $arItem["NAME"]); ?>" />
												<? if (strlen($arItem["DETAIL_PAGE_URL"]) > 0) : ?></a><? endif; ?>
										<? } else { ?>
											<? if (strlen($arItem["DETAIL_PAGE_URL"]) > 0) : ?><a href="<?= $arItem["DETAIL_PAGE_URL"] ?>" class="thumb"><? endif; ?>
												<img src="<?= SITE_TEMPLATE_PATH ?>/images/svg/noimage_product.svg" alt="<?= $arItem["NAME"] ?>" title="<?= $arItem["NAME"] ?>" width="72" height="72" />
												<? if (strlen($arItem["DETAIL_PAGE_URL"]) > 0) : ?></a><? endif; ?>
										<? } ?>
										<? if (0 && !empty($arItem["BRAND"])) : ?><div class="ordercart_brand"><img src="<?= $arItem["BRAND"] ?>" /></div><? endif; ?>
									</div>
								</div>
								<div class="line-block__item flex-1">
									<div class="dropdown-product__item-info">
										<?if($arParams["SHOW_DISCOUNT_PERCENT"] == "Y" && doubleval($arItem["DISCOUNT_PRICE_PERCENT"]) > 0):?>
											<div class="dropdown-product__item-discount">
												<div class="stickers-basket sticker sticker--upper sticker--static">
													<div>
														<div class="stickers-basket--item sticker__item sticker__item--sale font_12">
															-<?=$arItem["DISCOUNT_PRICE_PERCENT_FORMATED"]?>
														</div>
													</div>
												</div>
											</div>
										<?endif;?>
										<div class="dropdown-product__item-prices">
											<div class="dropdown-product__item-cost">
												<span class="price font_weight--500"><?= $arItem["PRICE_FORMATED"] ?></span>
												<?if($arItem['QUANTITY'] > 1):?>
													<span class="muted font_14"><?= ' x ' . $arItem['QUANTITY'] . ' ' . $arItem['MEASURE_TEXT']; ?></span>
												<?endif;?>
												<input type="hidden" name="item_price_<?= $arItem["ID"] ?>" value="<?= $arItem["PRICE"] ?>" />
												<input type="hidden" name="item_summ_<?= $arItem["ID"] ?>" value="<?= $arItem["PRICE"] * $arItem["QUANTITY"] ?>" />
											</div>
										</div>
										<div class="font_14 dropdown-product__item-title">
											<? if (strlen($arItem["DETAIL_PAGE_URL"]) > 0) : ?><a class="dark_link" href="<?= $arItem["DETAIL_PAGE_URL"] ?>"><? endif; ?><?= $arItem["NAME"] ?><? if (strlen($arItem["DETAIL_PAGE_URL"]) > 0) : ?></a><? endif; ?>
										</div>																				
										
										<? $bLinlServices = isset($arItem["LINK_SERVICES"]) && is_array($arItem["LINK_SERVICES"]) && count($arItem["LINK_SERVICES"]) > 0; ?>
										<? if ($bLinlServices) : ?>
											<div class="services_top_hover_wrap">
												<? foreach ($arItem["LINK_SERVICES"] as $arService) : ?>
													<div class="services_top_hover_item">
														<div class="services_top_hover_item_info">
															<div class="services_top_hover_item_info_inner">
																<span class="services_top_hover_item_title"><?= $arService["NAME"] ?></span>
																<span class="services_top_hover_item_x"> x </span>
																<span class="services_top_hover_item_quantity"><?= $arService["QUANTITY"] ?></span>
															</div>
														</div>
														<div class="services_top_hover_item_price">
															<span class="price font-bold"><?= $arService["SUM_FORMATED"] ?></span>
															<? if ($showOldPrice && $arService["NEED_SHOW_OLD_SUM"]) : ?>
																<span class="price_discount"><?= $arService["SUM_FULL_PRICE_FORMATED"] ?></span>
															<? endif; ?>
														</div>
													</div>
												<? endforeach; ?>
											</div>
										<? endif; ?>
										

										<? if ($bDeleteColumn) : ?>
											<div class="dropdown-product__item-remove remove fill-dark-light-block fill-theme-use-svg-hover" title="<?=GetMessage('T_BUTTON_REMOVE_ITEM')?>">
												<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH."/images/svg/header_icons.svg#remove-16-16", "remove ", ['WIDTH' => 16,'HEIGHT' => 16]);?>
											</div>
										<? endif; ?>
									</div>
								</div>
							</div>
						</div>
					<? endif; ?>
				<? endforeach; ?>
			</div>
		</div>

		<div class="foot dropdown dropdown--relative dropdown-product-foot">
			<div class="line-block line-block--24 flexbox--justify-beetwen cart-total">
				<div class="line-block__item cart-total__text font_14">
					<?=GetMessage('SALE_TOTAL');?>
				</div>
				<div class="line-block__item cart-total__value">
					<span class="font_16 font_weight--500"><?=$arResult["allSum_FORMATED"]?></span>
					<? if ($bHasServices) : ?>
						<div class="services_include"><span class="font_xs darken"><?= GetMessage("INCLUDE_SERVICES") ?></span></div>
					<? endif; ?>
				</div>
			</div>
			<? if ($arError["ERROR"]) : ?>
				<div class="top_error_block rounded-x flexbox flexbox--row flexbox--justify-beetwen flexbox--align-start colored_theme_bg_opacity">
					<span class="top_error_text font_13 color_555">
						<?= $arError["TEXT"]; ?>
					</span>
					<?=TSolution::showIconSvg('warning fill-theme-svg no-shrinked top_error_icon', SITE_TEMPLATE_PATH.'/images/svg/warning.svg');?>
				</div>
			<? endif; ?>
			<div class="buttons">
				<div class="wrap_button ">
					<a href="<?= $arParams["PATH_TO_BASKET"] ?>" class="btn <?=$arError["ERROR"] ? 'btn-transparent-border' : 'btn-default'?> btn-wide"><span><?= GetMessage("GO_TO_BASKET") ?></span></a>
				</div>
			</div>
		</div>
		
	</div>
<?/* else : ?>
	<div class="basket_empty height-100 dropdown dropdown--relative dropdown-product-empty">
		<div class="wrap">
			<?=TSolution::showIconSvg('basket', SITE_TEMPLATE_PATH."/images/svg/basket.svg");?>
			<h4 class="font_18"><?=GetMessage('T_BASKET_EMPTY_TITLE');?></h4>
			<div class="description color_666"><?=GetMessage('T_BASKET_EMPTY_DESCRIPTION');?></div>
			<div class="button">
				<a class="btn btn-default btn-transparent-border" href="<?=$catalogUrl?>">
					<?=GetMessage('T_BASKET_BUTTON_CATALOG');?>
				</a>
			</div>
		</div>
	</div>
*/?>
<? endif; ?>
