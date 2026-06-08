<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<?php
$this->setFrameMode(true);

use \Bitrix\Main\Localization\Loc;

$bHasNav = (strpos($arResult["NAV_STRING"], 'more_text_ajax') !== false);
$bMobileScrolled = $arParams['MOBILE_SCROLLED'] === true || $arParams['MOBILE_SCROLLED'] === 'Y';

$listClasses = '';
if ($bMobileScrolled) {
	$listClasses .= ' mobile-scrolled mobile-scrolled--items-2 mobile-offset';
}
if( $arParams['VIEW_TYPE'] == 'block') {
	$listClasses .= ' grid-list--items-4';
}
?>
<?php if ($arResult['SECTIONS']) : ?>
	<div class="license-list-inner license-list-inner--view-<?=$arParams['VIEW_TYPE'] ?: 'list' ?>">
		<? foreach ($arResult['SECTIONS'] as $arSection): ?>
			<?
				$panelButtons = CIBlock::GetPanelButtons($arSection['IBLOCK_ID'], 0, $arSection['ID'], ['SESSID' => false, 'CATALOG' => true]);
				$this->AddEditAction($arSection['ID'], $panelButtons['edit']['edit_section']['ACTION_URL'], CIBlock::GetArrayByID($arSection['IBLOCK_ID'], 'SECTION_EDIT'));
				$this->AddDeleteAction($arSection['ID'], $panelButtons['edit']['delete_section']['ACTION_URL'], CIBlock::GetArrayByID($arSection['IBLOCK_ID'], 'SECTION_DELETE'), ['CONFIRM' => Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]);
				$areaSectionId = $this->GetEditAreaId($arSection['ID']);
			?>
			<div class="license-list-inner__section">
				<? if ($arSection['NAME']) : ?>
					<div  id="<?= $areaSectionId ?>" class="license-list-inner__section-content">
						<? if ($arParams['SHOW_SECTION_NAME'] != 'N') : ?>
							<? if (strlen($arSection['NAME'])) : ?>
								<div class="license-list-inner__section-title switcher-title font_24 font_weight--500 font_large">
									<?= $arSection['NAME'] ?>
								</div>
							<? endif; ?>
						<? endif; ?>

						<? if ($arParams['SHOW_SECTION_PREVIEW_DESCRIPTION'] == 'Y' && strlen($arSection['DESCRIPTION']) && strpos($_SERVER['REQUEST_URI'], 'PAGEN') === false) : ?>
							<div class="license-list-inner__section-description">
								<?= $arSection['DESCRIPTION'] ?>
							</div>
						<? endif; ?>
					</div>
				<? endif; ?>

				<div class="license-list-inner__list  grid-list <?= $listClasses ?>">
					<? if ($arParams['IS_AJAX']) : ?>
						<? $APPLICATION->RestartBuffer(); ?>
					<? endif; ?>

					<? foreach ($arSection['ITEMS'] as $i => $arItem) : ?>
						<?
						$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
						$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), ['CONFIRM' => Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]);

						$previewImageSrc = isset($arItem['PREVIEW_PICTURE']) && $arItem['PREVIEW_PICTURE']['SRC']
							? $arItem['PREVIEW_PICTURE']['SRC']
							: '';
						$detailImageSrc = isset($arItem['DETAIL_PICTURE']) && $arItem['DETAIL_PICTURE']['SRC']
							? $arItem['DETAIL_PICTURE']['SRC']
							: '';
						$noImageSrc = SITE_TEMPLATE_PATH . '/images/svg/noimage_default.svg';

						if(!empty($arItem["DETAIL_PICTURE"]["ID"])) {
							$arLicenseFile = TSolution::GetFileInfo($arItem["DETAIL_PICTURE"]["ID"]);
							$LicenseFileSize = $arLicenseFile['FILE_SIZE_FORMAT'];
						}
						?>
						<div class="license-list-inner__wrapper  grid-list__item colored_theme_hover_bg-block grid-list-border-outer fill-theme-parent-all">
							<div id="<?= $this->GetEditAreaId($arItem['ID']) ?>"
								 class="license-list-inner__item height-100 outer-rounded-x shadow-hovered shadow-no-border-hovered">
								<? if ($arItem['FIELDS']['PREVIEW_PICTURE']) : ?>
									<div class="license-list-inner__image-wrapper">
										<? if($detailImageSrc && $previewImageSrc) : ?>
										<a class="license-list-inner__image fancy" href="<?= $detailImageSrc ?>"
										   	data-caption="<?= htmlspecialchars($arItem['NAME']) ?>"
											>
											<span class="license-list-inner__image-bg" style="background-image: url(<?= $previewImageSrc ?>);"></span>
										</a>
										<? elseif($previewImageSrc) : ?>
											<span class="license-list-inner__image-bg" style="background-image: url(<?= $previewImageSrc ?>);"></span>
										<? else : ?>
											<span class="license-list-inner__noimage" style="background-image: url(<?= $noImageSrc ?>);"></span>
										<? endif ?>

										<? if($detailImageSrc) : ?>
										<a class="license-list-inner__icon-preview-image license-list-inner__preview-icon1  fancy fill-theme-parent"
										   data-caption="<?= htmlspecialchars($arItem['NAME']) ?>"
										   href="<?= $detailImageSrc ?>">
										   <?=\TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/documents_icons.svg#image-preview-18-16', 'image-preview fill-theme-target fill-dark-light-block', ['WIDTH' => 18,'HEIGHT' => 18]); ?>
										</a>
										<? endif ?>
									</div>
								<? endif ?>
								<div class="license-list-inner__content-wrapper <?=($detailImageSrc ? 'license-list-inner__content--with-icon' : '')?>">
									<div class="license-list-inner__top">
										<div class="license-list-inner__name switcher-title font_weight--500">
											<?= $arItem['NAME'] ?>
										</div>
										<div class="license-list-inner__label font_13 font_short">
											<?=$LicenseFileSize?>
										</div>
										<? if($detailImageSrc) : ?>
										<a class="license-list-inner__icon-preview-image license-list-inner__preview-icon2  fancy fill-theme-parent"
										   data-caption="<?= htmlspecialchars($arItem['NAME']) ?>"
										   href="<?= $detailImageSrc ?>">
										   <?=\TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/documents_icons.svg#image-preview-18-16', 'image-preview fill-theme-target fill-dark-light-block', ['WIDTH' => 18,'HEIGHT' => 18]); ?>
										</a>
										<? endif ?>
									</div>
									<? if (strlen($arItem['FIELDS']['PREVIEW_TEXT']) && $arParams['VIEW_TYPE'] != 'block' ): ?>
										<div class="license-list-inner__bottom">
											<? // element preview text?>
											<div class="license-list-inner__description">
												<? if ($arItem['PREVIEW_TEXT_TYPE'] == 'text'): ?>
													<p><?= $arItem['FIELDS']['PREVIEW_TEXT'] ?></p>
												<? else: ?>
													<?= $arItem['FIELDS']['PREVIEW_TEXT'] ?>
												<? endif; ?>
											</div>
										</div>
									<? endif; ?>
								</div>
							</div>
						</div>
					<? endforeach ?>

					<? if ($arParams['SHOW_NAVIGATION_PAGER'] == 'Y' && $arParams['IS_AJAX']) : ?>
						<div class="bottom_nav_wrapper nav-compact">
							<div class="bottom_nav hide-600" <?= ($arParams['IS_AJAX'] ? "style='display: none; '" : ""); ?>
								 data-parent=".license-list-inner" data-append=".license-list-inner__list">
								<? if ($arParams['DISPLAY_BOTTOM_PAGER']): ?>
									<?= $arResult['NAV_STRING'] ?>
								<? endif; ?>
							</div>
						</div>
						<? die(); ?>
					<? endif; ?>

					<? if ($bMobileScrolled && $arParams['SHOW_NAVIGATION_PAGER'] == 'Y') : ?>
						<div class="bottom_nav mobile_slider <?= ($bHasNav ? '' : ' hidden-nav'); ?>"
							 data-parent=".license-list-inner"
							 data-append=".license-list-inner__list" <?= ($arParams['IS_AJAX'] ? "style='display: none; '" : ""); ?>>
							<? if ($bHasNav): ?>
								<?= $arResult['NAV_STRING'] ?>
							<? endif; ?>
						</div>
					<? endif ?>
				</div>
			</div>
		<? endforeach ?>

		<? // bottom pagination?>
		<? if ($arParams['SHOW_NAVIGATION_PAGER'] == 'Y' && $arParams['DISPLAY_BOTTOM_PAGER']): ?>
			<div class="wrap_nav bottom_nav_wrapper">
				<div class="bottom_nav_wrapper nav-compact">
					<div class="bottom_nav hide-600" <?= ($arParams['IS_AJAX'] ? "style='display: none; '" : ""); ?>
						 data-parent=".license-list-inner" data-append=".license-list-inner__list">
						<?= $arResult['NAV_STRING'] ?>

					</div>
				</div>
			</div>
		<? endif; ?>
	</div>
<?php endif //if($arResult['SECTIONS']) ?>