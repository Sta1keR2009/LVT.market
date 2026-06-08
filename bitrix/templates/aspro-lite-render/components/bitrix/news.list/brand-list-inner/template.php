<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<?php
$this->setFrameMode(true);

use \Bitrix\Main\Localization\Loc;

$bHasNav = (strpos($arResult["NAV_STRING"], 'more_text_ajax') !== false);
$bMobileScrolled = $arParams['MOBILE_SCROLLED'] === true || $arParams['MOBILE_SCROLLED'] === 'Y';

$listClasses = '';
if ($bMobileScrolled) {
	$listClasses .= ' mobile-scrolled mobile-scrolled--items-3 mobile-offset';
}

$listClasses .= ' grid-list--items-5';

?>
<?php if ($arResult['ITEMS']) : ?>
	<div class="brand-list-inner brand-list-inner--view-<?= $arParams['VIEW_TYPE'] ?: 'list' ?>">
		<div class="brand-list-inner__list  grid-list <?= $listClasses ?>">
			<? if ($arParams['IS_AJAX']) : ?>
				<? $APPLICATION->RestartBuffer(); ?>
			<? endif; ?>

			<? foreach ($arResult['ITEMS'] as $i => $arItem) : ?>
				<?
				$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
				$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), ['CONFIRM' => Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]);

				$previewImageId = isset($arItem['FIELDS']['PREVIEW_PICTURE']) && $arItem['FIELDS']['PREVIEW_PICTURE']
					? $arItem['FIELDS']['PREVIEW_PICTURE']['ID']
					: null;

				$previewImageSrc = '';
				if ($previewImageId) {
					$image = CFile::ResizeImageGet($previewImageId, ['width' => 150, 'height' => 90], BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true);
					$previewImageSrc = $image['src'];
				} else {
					$previewImageSrc = SITE_TEMPLATE_PATH . '/images/svg/noimage_brand.svg';
				}

				$bDetailLink = $arParams['SHOW_DETAIL_LINK'] != 'N' && (!strlen($arItem['DETAIL_TEXT']) ? ($arParams['HIDE_LINK_WHEN_NO_DETAIL'] !== 'Y' && $arParams['HIDE_LINK_WHEN_NO_DETAIL'] != 1) : true);

				?>
				<div class="brand-list-inner__wrapper  grid-list__item stroke-theme-parent-all colored_theme_hover_bg-block animate-arrow-hover grid-list-border-outer">
					<div id="<?= $this->GetEditAreaId($arItem['ID']) ?>"
						 class="brand-list-inner__item  height-100 shadow-hovered shadow-no-border-hovered outer-rounded-x">
						<? if ($arItem['FIELDS']['PREVIEW_PICTURE'] && $previewImageSrc) : ?>
							<div class="brand-list-inner__image-wrapper rounded-x">
								<? if ($bDetailLink) : ?>
									<a class="brand-list-inner__image"
									   title="<?= htmlspecialchars($arItem['NAME']) ?>"
									   href="<?= $arItem['DETAIL_PAGE_URL'] ?>">
											<span class="brand-list-inner__image-bg"
												  style="background-image: url(<?= $previewImageSrc ?>);"></span>
									</a>
								<? else : ?>
									<div class="brand-list-inner__image"
										 title="<?= htmlspecialchars($arItem['NAME']) ?>">
											<span class="brand-list-inner__image-bg"
												  style="background-image: url(<?= $previewImageSrc ?>);"></span>
									</div>
								<? endif ?>
							</div>
						<? endif ?>
						<div class="brand-list-inner__content-wrapper">
							<div class="brand-list-inner__top">
								<? if ($bDetailLink) : ?>
									<a class="brand-list-inner__name  dark_link color-theme-target <?= $arParams['VIEW_TYPE'] != 'only-logo' ? 'switcher-title' : '' ?>"
									   title="<?= htmlspecialchars($arItem['NAME']) ?>"
									   href="<?= $arItem['DETAIL_PAGE_URL'] ?>">
										<?= $arItem['NAME'] ?>
									</a>
								<? else : ?>
									<div class="brand-list-inner__name  <?= $arParams['VIEW_TYPE'] != 'only-logo' ? 'switcher-title' : '' ?>">
										<?= $arItem['NAME'] ?>
									</div>
								<? endif ?>
							</div>

							<? if ($arParams['VIEW_TYPE'] != 'only-logo') : ?>
								<div class="brand-list-inner__bottom">
									<? // element preview text?>
									<? if (strlen($arItem['FIELDS']['PREVIEW_TEXT'])): ?>
										<div class="brand-list-inner__description">
											<? if ($arItem['PREVIEW_TEXT_TYPE'] == 'text'): ?>
												<p><?= $arItem['FIELDS']['PREVIEW_TEXT'] ?></p>
											<? else: ?>
												<?= $arItem['FIELDS']['PREVIEW_TEXT'] ?>
											<? endif; ?>
										</div>
									<? endif; ?>

									<div class="brand-list-inner__properties  line-block line-block--40">
										<? if ($arItem['CONTACT_PROPERTIES']) : ?>
											<? foreach ($arItem['CONTACT_PROPERTIES'] as $property) : ?>
												<div class="brand-list-inner__property  line-block__item">
													<div class="brand-list-inner__property-label">
														<?= $property['NAME'] ?>
													</div>
													<div class="brand-list-inner__property-value">
														<? if ($property['TYPE'] == 'LINK') : ?>
															<a rel="nofollow" href="<?= $property['HREF'] ?>"
															   class="dark_link" <?= $property['ATTR'] ?>>
																<?= $property['VALUE'] ?>
															</a>
														<? else : ?>
															<?= $property['VALUE'] ?>
														<? endif ?>
													</div>
												</div>
											<? endforeach ?>
										<? endif ?>
									</div>
								</div>
							<? endif //if($arItem['FIELDS']['PREVIEW_TEXT'] || $arItem['CONTACT_PROPERTIES']) : ?>
						</div>
					</div>
				</div>
			<? endforeach ?>

			<? if ($arParams['IS_AJAX']) : ?>
				<div class="bottom_nav_wrapper nav-compact">
					<div class="bottom_nav" <?= ($arParams['IS_AJAX'] ? "style='display: none; '" : ""); ?>
						 data-parent=".brand-list-inner" data-append=".brand-list-inner__list">
						<? if ($arParams['DISPLAY_BOTTOM_PAGER']): ?>
							<?= $arResult['NAV_STRING'] ?>
						<? endif; ?>
					</div>
				</div>
				<? die(); ?>
			<? endif; ?>

			<? if ($bMobileScrolled && $arParams['DISPLAY_BOTTOM_PAGER'] == 'Y') : ?>
				<div class="bottom_nav mobile_slider <?= ($bHasNav ? '' : ' hidden-nav'); ?>"
					 data-parent=".brand-list-inner"
					 data-append=".brand-list-inner__list" <?= ($arParams['IS_AJAX'] ? "style='display: none; '" : ""); ?>>
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
					<div class="bottom_nav" <?= ($arParams['IS_AJAX'] ? "style='display: none; '" : ""); ?>
						 data-parent=".brand-list-inner" data-append=".brand-list-inner__list">
						<?= $arResult['NAV_STRING'] ?>

					</div>
				</div>
			</div>
		<? endif; ?>
	</div>
<?php endif //if($arResult['ITEMS']) ?>