<a href="#" class="jqmClose top-close stroke-theme-hover"><?= TSolution::showIconSvg('', SITE_TEMPLATE_PATH . '/images/svg/Close.svg') ?></a>
<? if ($arResult): ?>
	<?
	// preview image
	$bImage = (isset($arResult['FIELDS']['PREVIEW_PICTURE']) && $arResult['PREVIEW_PICTURE']['SRC']) || (isset($arResult['FIELDS']['DETAIL_PICTURE']) && $arResult['DETAIL_PICTURE']['SRC']);
	$nImageID = ($bImage ? ($arResult['PREVIEW_PICTURE']['ID'] ?: $arResult['DETAIL_PICTURE']['ID']) : '');
	$imageSrc = $bImage ? ($arResult['PREVIEW_PICTURE']['SRC'] ?:  $arResult['DETAIL_PICTURE']['SRC']) : '';

	// discount value
	$bSaleNumber = strlen($arResult['DISPLAY_PROPERTIES']['SALE_NUMBER']['VALUE']);

	$templateData['SHOW_DISCOUNT_COUNTER'] = in_array('ACTIVE_TO', $arParams['FIELD_CODE']);

	// dicount counter
	$bDiscountCounter = $arResult['ACTIVE_TO'] && $templateData['SHOW_DISCOUNT_COUNTER'];
	?>
	<div class="popup-sale form popup">
		<div class="popup-sale__item">
			<div class="form-header">
				<div class="popup-sale__item-title color_222 font_24 font_bold"><?= $arResult["NAME"] ?></div>
			</div>
			<div class="popup-sale__item-text form-body color_666">
				<? // preview or detail image?>
				<? if ($imageSrc): ?>
					<div class="popup-sale__item-image-wrapper">
						<a class="popup-sale__item-link" href="<?= $arResult['DETAIL_PAGE_URL'] ?>">
							<span class="popup-sale__item-image rounded-4" style="background-image: url(<?= $imageSrc ?>);"></span>
						</a>
					</div>
				<? endif; ?>

				<? if ($bSaleNumber || $bDiscountCounter): ?>
					<div class="popup-sale__item-info sticker sticker--static font_12 color_999">
						<? if ($bSaleNumber): ?>
							<div>
								<div class="sticker__item sticker__item--stock"><?= $arResult['DISPLAY_PROPERTIES']['SALE_NUMBER']['VALUE'] ?></div>
							</div>
						<? endif; ?>

						<? if ($bDiscountCounter): ?>
							<div>
								<? TSolution\Functions::showDiscountCounter([
									'ICONS' => true,
									'ITEM' => $arResult,
									'SHADOWED' => true,
								]); ?>
							</div>
						<? endif; ?>
					</div>
				<? endif; ?>

				<? $obParser = new CTextParser; ?>
				<?= $obParser->html_cut($arResult["DETAIL_TEXT"], 500); ?>

				<div class="popup-sale__item-btn">
					<a class="btn btn-default btn-lg btn-transparent-border" href="<?= $arResult["DETAIL_PAGE_URL"] ?>"><?= \Bitrix\Main\Localization\Loc::getMessage("MORE_TEXT_LINK") ?></a>
				</div>
			</div>
			<script>
				initCountdown();
			</script>
		</div>
	</div>
<? endif; ?>