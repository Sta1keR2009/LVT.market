<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<?php
$this->setFrameMode(true);

use \Bitrix\Main\Localization\Loc;

$bHasNav = (strpos($arResult["NAV_STRING"], 'more_text_ajax') !== false);
$bMobileScrolled = $arParams['MOBILE_SCROLLED'] === true || $arParams['MOBILE_SCROLLED'] === 'Y';

$listClasses = ' grid-list--items-1';
if ($bMobileScrolled) {
	$listClasses .= ' mobile-scrolled mobile-scrolled--items-2 mobile-offset grid-list--gap-24';
}

?>
<?php if ($arResult['ITEMS']) : ?>
	<div class="review-list-inner review-list-inner--view-<?= $arParams['VIEW_TYPE'] ?: 'list' ?>">
		<div class="review-list-inner__list  grid-list <?= $listClasses ?>">
			<? if ($arParams['IS_AJAX']) : ?>
				<? $APPLICATION->RestartBuffer(); ?>
			<? endif; ?>

			<? foreach ($arResult['ITEMS'] as $i => $arItem) : ?>
				<?
				$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
				$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), ['CONFIRM' => Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]);

				$previewImageSrc = isset($arItem['PREVIEW_PICTURE']) && $arItem['PREVIEW_PICTURE']['SRC']
					? $arItem['PREVIEW_PICTURE']['SRC']
					: '';

				$bDetailLink = $arParams['SHOW_DETAIL_LINK'] != 'N' && (!strlen($arItem['DETAIL_TEXT']) ? ($arParams['HIDE_LINK_WHEN_NO_DETAIL'] !== 'Y' && $arParams['HIDE_LINK_WHEN_NO_DETAIL'] != 1) : true);

				?>
				<div class="review-list-inner__wrapper  grid-list__item stroke-theme-parent-all colored_theme_hover_bg-block animate-arrow-hover grid-list-border-outer">
					<div id="<?= $this->GetEditAreaId($arItem['ID']) ?>"
						 class="review-list-inner__item  height-100 shadow-hovered shadow-no-border-hovered rounded-x bordered">
						<div class="review-list-inner__row">
							<div class="review-list-inner__top">
								<div class="review-list-inner__line review-list-inner__line--between">
									<div class="review-list-inner__line">
										<div class="review-list-inner__image-wrapper">
											<div class="review-list-inner__image">
												<? if ($previewImageSrc) : ?>
													<span class="review-list-inner__image-bg rounded"
														  style="background-image: url(<?= $previewImageSrc ?>);"></span>
												<? else : ?>
													<span class="review-list-inner__noimage review-list-inner__image-bg rounded"
														  style="background-image: url(<?= $noImageSrc ?>);"></span>
												<? endif ?>
											</div>
										</div>
										<div class="review-list-inner__name-wrapper">
											<div class="review-list-inner__label">
												<?= implode('<span class="review-list-inner__separator">&mdash;</span>', array_filter([
													$arItem['PROPERTIES']['POST']['VALUE'] ? '<span>' . $arItem['PROPERTIES']['POST']['VALUE'] . '</span>' : null,
													$arItem['DISPLAY_ACTIVE_FROM'] ? '<span class="review-list-inner__date-active font_14 color_999">' . $arItem['DISPLAY_ACTIVE_FROM'] . '</span>' : null
												])); ?>
											</div>
											<div class="review-list-inner__name  switcher-title">
												<?= $arItem['NAME'] ?>
											</div>
										</div>
									</div>
									<div class="review-list-inner__rating-wrapper">
										<? if (isset($arItem['DISPLAY_PROPERTIES']['RATING']) && $arItem['DISPLAY_PROPERTIES']['RATING']['VALUE']): ?>
											<? $itemRating = $arItem['DISPLAY_PROPERTIES']['RATING']['VALUE']; ?>
											<div class="rating eview-list-inner__rating">
												<? for ($i = 0; $i < 5; $i++): ?>
													<div class="rating__star">
														<? $svgClass = $i < $itemRating ? ' rating__star-svg rating__star-svg--filled' : ' rating__star-svg'; ?>
														<?= TSolution::showIconSvg($svgClass, SITE_TEMPLATE_PATH . '/images/svg/rating_star_20.svg'); ?>
													</div>
												<? endfor; ?>
											</div>
										<? endif; ?>
									</div>
								</div>
							</div>

							<div class="review-list-inner__middle">
								<? // element preview text?>
								<? if (strlen($arItem['FIELDS']['PREVIEW_TEXT'])): ?>
									<div class="review-list-inner__text">
										<? if ($arItem['PREVIEW_TEXT_TYPE'] == 'text'): ?>
											<p><?= $arItem['FIELDS']['PREVIEW_TEXT'] ?></p>
										<? else: ?>
											<?= $arItem['FIELDS']['PREVIEW_TEXT'] ?>
										<? endif; ?>
									</div>
								<? endif; ?>
							</div>

							<?if(isset($arItem['DISPLAY_PROPERTIES']['DOCUMENTS']) && $arItem['DISPLAY_PROPERTIES']['DOCUMENTS']['VALUE'] || isset($arItem['DISPLAY_PROPERTIES']['VIDEO']) && $arItem['DISPLAY_PROPERTIES']['VIDEO']['VALUE']):?>
								<div class="review-list-inner__bottom">
									<?if(isset($arItem['DISPLAY_PROPERTIES']['DOCUMENTS']) && $arItem['DISPLAY_PROPERTIES']['DOCUMENTS']['VALUE']): ?>
										<div class="review-list-inner__docs-wrapper">
											<div class="review-list-inner__docs">
												<? foreach ($arItem['DISPLAY_PROPERTIES']['DOCUMENTS']['VALUE'] as $documentItem): ?>
													<div class="review-list-inner__doc-item flex-1">
														<?
														$documentFile = TSolution::GetFileInfo($documentItem);
	
														if (!$documentFile) {
															continue;
														}
	
														$isDocumentImage = false;
														if (in_array($documentFile['TYPE'], ['jpg', 'jpeg', 'bmp', 'gif', 'png'])) {
															$isDocumentImage = true;
														}
														?>
														<div class="file-type review-list-inner__doc-icon">
															<i class="file-type__icon file-type__icon--<?= $documentFile['TYPE'] ?>"></i>
														</div>
														<div class="review-list-inner__doc-info">
															<? if ($isDocumentImage) : ?>
																<a href="<?= $documentFile['SRC'] ?>"
																   class="review-list-inner__doc-name doc-list-inner__name fancy dark_link switcher-title"
																   data-caption="<?= htmlspecialchars($documentFile['DESCRIPTION']) ?>">
																	<?= $documentFile['DESCRIPTION'] ?>
																</a>
															<? else: ?>
																<a href="<?= $documentFile['SRC'] ?>" target="_blank"
																   class="review-list-inner__doc-name doc-list-inner__name switcher-title dark_link"
																   title="<?= htmlspecialchars($documentFile['DESCRIPTION']) ?>">
																	<?= $documentFile['DESCRIPTION'] ?>
																</a>
															<? endif ?>
															<div class="review-list-inner__doc-label color_999"><?= $documentFile['FILE_SIZE_FORMAT'] ?></div>
														</div>
													</div>
												<? endforeach; ?>
											</div>
										</div>
									<? endif; ?>
	
									<? if (isset($arItem['DISPLAY_PROPERTIES']['VIDEO']) && $arItem['DISPLAY_PROPERTIES']['VIDEO']['VALUE']): ?>
										<div class="review-list-inner__videos-wrapper">
											<div class="review-list-inner__videos">
												<? foreach ($arItem['DISPLAY_PROPERTIES']['VIDEO']['~VALUE'] as $video): ?>
													<div class="review-list-inner__video-item">
														<?= $video ?>
													</div>
												<? endforeach; ?>
											</div>
										</div>
									<? endif ?>
								</div>
								<? endif; ?>
							</div>
						<? if ($arItem['FIELDS']['DETAIL_TEXT']) : ?>
							<div class="review-list-inner__row review-list-inner__row--answer">
								<div class="review-list-inner__line review-list-inner__line--answer">
									<div class="review-list-inner__answer">
										<?if($arItem["DISPLAY_PROPERTIES"]["STAFF"]["VALUE"]):?>
											<div class="review-list-inner__label-answer color_999 font_13"><?=$arItem["DISPLAY_PROPERTIES"]["STAFF"]["VALUE"]?></div>
										<?endif;?>
										<? // element preview text?>
										<?if (strlen($arItem['FIELDS']['DETAIL_TEXT'])):?>
											<div class="review-list-inner__text font_15">
												<? if ($arItem['DETAIL_TEXT_TYPE'] == 'text'):?>
													<p><?=$arItem['FIELDS']['DETAIL_TEXT']?></p>
												<?else:?>
													<?=$arItem['FIELDS']['DETAIL_TEXT']?>
												<?endif;?>
											</div>
										<?endif;?>
									</div>
								</div>
							</div>
						<? endif ?>
					</div>
				</div>
			<? endforeach ?>

			<? if ($arParams['IS_AJAX']) : ?>
				<div class="bottom_nav_wrapper nav-compact">
					<div class="bottom_nav hide-600" <?= ($arParams['IS_AJAX'] ? "style='display: none; '" : ""); ?>
						 data-parent=".review-list-inner" data-append=".review-list-inner__list">
						<? if ($arParams['DISPLAY_BOTTOM_PAGER']): ?>
							<?= $arResult['NAV_STRING'] ?>
						<? endif; ?>
					</div>
				</div>
				<? die(); ?>
			<? endif; ?>

			<? if ($bMobileScrolled && $arParams['DISPLAY_BOTTOM_PAGER'] == 'Y') : ?>
				<div class="bottom_nav mobile_slider <?= ($bHasNav ? '' : ' hidden-nav'); ?>"
					 data-parent=".review-list-inner"
					 data-append=".review-list-inner__list" <?= ($arParams['IS_AJAX'] ? "style='display: none; '" : ""); ?>>
					<? if ($bHasNav): ?>
						<?= $arResult['NAV_STRING'] ?>
					<? endif; ?>
				</div>
			<? endif ?>
		</div>

		<? // bottom pagination?>
		<? if ($arParams['DISPLAY_BOTTOM_PAGER']): ?>
			<div class="wrap_nav bottom_nav_wrapper">
				<div class="bottom_nav_wrapper nav-compact">
					<div class="bottom_nav hide-600" <?= ($arParams['IS_AJAX'] ? "style='display: none; '" : ""); ?>
						 data-parent=".review-list-inner" data-append=".review-list-inner__list">
						<?= $arResult['NAV_STRING'] ?>

					</div>
				</div>
			</div>
		<? endif; ?>
	</div>
<?php endif //if($arResult['ITEMS']) ?>