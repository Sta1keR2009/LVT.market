<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

$templateData = [
	'CURRENCIES' => CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true)
];
$curJsId = $this->randString();
?>
<div class="ordered-block">
	<div class="ordered-block__title font_20">
		<?= $arParams["TITLE"] ?? GetMessage("CATALOG_SET_BUY_SET"); ?>
	</div>

	<div id="bx-set-const-<?= $curJsId ?>" class="bx-set-constructor">
		<div class="set-constructor__container">
			<div class="set-constructor__cell-container grid-list__item bordered outer-rounded-x flexbox flexbox--justify-center" data-area="product">
				<div class="set-constructor__cell bx-original-item-container line-block line-block--gap">
					<? $arResult["ELEMENT"]["SET_CONSTRUCTOR"] = "Y"; ?>

					<div class="line-block__item">
						<div class="set-constructor__product-image">
							<?= TSolution\Product\Image::showImg([
								'ITEM' => $arResult['ELEMENT'],
								'PARAMS' => $arParams,
								'WRAP_LINK' => false,
								'ADDITIONAL_IMG_CLASS' => 'rounded-x'
							]); ?>
						</div>
					</div>

					<div class="set-constructor__product-text line-block__item color_222">
						<span class="bx-added-item-new-price font_15 font_weight--500">
							<?= $arResult["ELEMENT"]["PRICE_PRINT_DISCOUNT_VALUE"]; ?> x <?= $arResult["ELEMENT"]["BASKET_QUANTITY"]; ?>
							<?= $arResult["ELEMENT"]["MEASURE"]["SYMBOL_RUS"]; ?>
						</span>
						<? if (!($arResult["ELEMENT"]["PRICE_VALUE"] == $arResult["ELEMENT"]["PRICE_DISCOUNT_VALUE"])): ?>
							<span class="bx-catalog-set-item-price-old font_14 muted">
								<strong><?= $arResult["ELEMENT"]["PRICE_PRINT_VALUE"]; ?></strong>
							</span>
						<? endif; ?>
						<div class="title-set font_15"><?= $arResult["ELEMENT"]["NAME"]; ?></div>
					</div>
				</div>
			</div>

			<div class="grid-list__item flexbox flexbox--justify-center flexbox--align-center" data-area="plus">
				<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/catalog/item_action_icons.svg#plus-12-12', 'fill-use-999', [
					'WIDTH' => 12,
					'HEIGHT' => 12
				]); ?>
			</div>

			<div class="grid-list__item flexbox flexbox--justify-center" data-area="kit">
				<div data-role="set-items" class="set-constructor__items grid-list grid-list--items-1 grid-list--no-gap bordered outer-rounded-x scrollbar">
					<? foreach ($arResult["SET_ITEMS"] as $type => $items): ?>
						<? foreach ($items as $key => $arItem): ?>
							<div class="set-constructor__item grid-list__item" data-active="<?= $type === 'DEFAULT' ? 'true': 'false'; ?>" data-id="<?= $arItem["ID"]; ?>" data-img="<?= $arItem["DETAIL_PICTURE"]["src"]; ?>" data-url="<?= $arItem["DETAIL_PAGE_URL"]; ?>" data-name="<?= $arItem["NAME"]; ?>" data-price="<?= $arItem["PRICE_DISCOUNT_VALUE"]; ?>" data-print-price="<?= $arItem["PRICE_PRINT_DISCOUNT_VALUE"]; ?>" data-old-price="<?= $arItem["PRICE_VALUE"]; ?>" data-print-old-price="<?= $arItem["PRICE_PRINT_VALUE"]; ?>" data-diff-price="<?= $arItem["PRICE_DISCOUNT_DIFFERENCE_VALUE"]; ?>" data-measure="<?= $arItem["MEASURE"]["SYMBOL_RUS"]; ?>" data-quantity="<?= $arItem["BASKET_QUANTITY"]; ?>">
								<div class="set-constructor__item-inner bordered line-block line-block--gap line-block--gap-20">
									<div class="set-constructor__item-block line-block__item">
										<div class="set-constructor__item-image">
											<? $arItem["SET_CONSTRUCTOR_ITEM"] = "Y"; ?>
											<?= TSolution\Product\Image::showImg([
												'ITEM' => $arItem,
												'PARAMS' => $arParams,
												'WRAP_LINK' => false
											]); ?>
										</div>
									</div>

									<div class="line-block__item line-block line-block--gap flex-1 flexbox--justify-beetwen flexbox--wrap">
										<div class="set-constructor__item-block set-constructor__item-block--name line-block__item">
											<a class="font_15 font_weight--500 dark_link" href="<?= $arItem["DETAIL_PAGE_URL"]; ?>"><?= $arItem["NAME"]; ?></a>
										</div>

										<div class="set-constructor__item-block set-constructor__item-block--price line-block__item">
											<div class="bx-added-item-table-cell-price">
												<div class="bx-added-item-new-price font_15 font_weight--500 color_222">
													<?= $arItem["PRICE_PRINT_DISCOUNT_VALUE"]; ?> x <?= $arItem["BASKET_QUANTITY"]; ?>
													<?= $arItem["MEASURE"]["SYMBOL_RUS"]; ?>
												</div>

												<? if ($arItem["PRICE_VALUE"] != $arItem["PRICE_DISCOUNT_VALUE"]): ?>
													<div class="bx-added-item-old-price price__old-val font_12">
														<?= $arItem["PRICE_PRINT_VALUE"]; ?>
													</div>
												<? endif; ?>
											</div>
										</div>
									</div>

									<div class="set-constructor__item-block line-block__item">
										<div class="bx-added-item-table-cell-action">
											<? if ($type === 'DEFAULT'): ?>
												<div class="pointer" data-role="set-delete-btn">
													<?= TSolution::showSpriteIconSvg(
														SITE_TEMPLATE_PATH . '/images/svg/header_icons.svg#close-16-16',
														'fill-use-999',
														[
															'WIDTH' => 13,
															'HEIGHT' => 13
														]
													); ?>
												</div>
											<? elseif ($arItem['CAN_BUY']): ?>
												<div class="pointer" data-role="set-add-btn">
													<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/catalog/item_action_icons.svg#plus-12-12', 'fill-dark-light', [
														'WIDTH' => 13,
														'HEIGHT' => 13
													]); ?>
												</div>
											<? else: ?>
												<span class="bx-catalog-set-item-notavailable"><?= GetMessage('CATALOG_SET_MESS_NOT_AVAILABLE'); ?></span>
											<? endif; ?>
										</div>
									</div>
								</div>
							</div>
						<? endforeach; ?>
					<? endforeach; ?>
				</div>
			</div>

			<div class="grid-list__item flexbox flexbox--justify-center flexbox--align-center" data-area="equal">
				<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/catalog/item_action_icons.svg#equal-12-8', 'fill-use-999', [
					'WIDTH' => 12,
					'HEIGHT' => 8
				]); ?>
			</div>

			<div class="set-constructor__cell-container grid-list__item bordered outer-rounded-x flexbox flexbox--justify-center" data-area="result">
				<div class="result">
					<div class="set-constructor__result-items line-block line-block--gap line-block--gap-16 flexbox--justify-beetwen flexbox--wrap">
						<div class="line-block__item set-constructor__result-price">
							<div class="bx-constructor-result-btn-container">
								<span class="bx-constructor-result-price font_24 font_weight--500 color_222" data-role="set-price">
									<?= $arResult["SET_ITEMS"]["PRICE"]; ?>
								</span>
							</div>

							<div class="bx-constructor-result-table-value price__old-val font_14<?= !$arResult['SHOW_DEFAULT_SET_DISCOUNT'] ? ' hidden': ''; ?>" data-role="set-old-price">
								<?= $arResult["SET_ITEMS"]["OLD_PRICE"]; ?>
							</div>
						</div>

						<?if ($arParams['ORDER_VIEW']):?>
							<div class="line-block__item">
								<div class="bx-constructor-result-btn-container">
									<div class="btn btn-default btn-sm<?= $arResult["ELEMENT"]["CAN_BUY"] ? '': ' hidden'; ?>" data-role="set-buy-btn">
										<?= GetMessage("CATALOG_SET_BUY") ?>
									</div>
								</div>
							</div>
						<?endif;?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?
	$arJsParams = [
		"numSliderItems" => count($arResult["SET_ITEMS"]["OTHER"]),
		"numSetItems" => count($arResult["SET_ITEMS"]["DEFAULT"]),
		"jsId" => $curJsId,
		"parentContId" => "bx-set-const-" . $curJsId,
		"ajaxPath" => $this->GetFolder() . '/ajax.php',
		"canBuy" => $arResult["ELEMENT"]["CAN_BUY"],
		"currency" => $arResult["ELEMENT"]["PRICE_CURRENCY"],
		"mainElementPrice" => $arResult["ELEMENT"]["PRICE_DISCOUNT_VALUE"],
		"mainElementOldPrice" => $arResult["ELEMENT"]["PRICE_VALUE"],
		"mainElementDiffPrice" => $arResult["ELEMENT"]["PRICE_DISCOUNT_DIFFERENCE_VALUE"],
		"mainElementBasketQuantity" => $arResult["ELEMENT"]["BASKET_QUANTITY"],
		"lid" => SITE_ID,
		"iblockId" => $arParams["IBLOCK_ID"],
		"basketUrl" => $arParams["BASKET_URL"],
		"setIds" => $arResult["DEFAULT_SET_IDS"],
		"offersCartProps" => $arParams["OFFERS_CART_PROPERTIES"],
		"itemsRatio" => $arResult["BASKET_QUANTITY"],
		"noFotoSrc" => SITE_TEMPLATE_PATH . '/images/svg/noimage_product.svg',
		"messages" => [
			"EMPTY_SET" => GetMessage('CT_BCE_CATALOG_MESS_EMPTY_SET'),
			"ADD_BUTTON" => GetMessage("CATALOG_SET_BUTTON_ADD")
		]
	];
	?>
	<script type="text/javascript">
		BX.ready(function() {
			new BX.Catalog.SetConstructor(<?= CUtil::PhpToJSObject($arJsParams, false, true, true) ?>);
		});
	</script>
</div>