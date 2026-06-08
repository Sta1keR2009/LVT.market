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
if ($arParams['VIEW_TYPE'] == 'list') {
	$listClasses .= ' grid-list--items-1';
}
if ($arParams['ITEMS_OFFSET'] == 'Y') {
	$listClasses .= ' grid-list--gap-32';
} else {
	$listClasses .= ' grid-list--no-gap';
}
?>
<?php if ($arResult['ITEMS']) : ?>
	<div class="history-list-inner history-list-inner--view-<?= $arParams['VIEW_TYPE'] ?: 'list' ?>">
		<div class="history-list-inner__list  grid-list <?= $listClasses ?>">
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
				?>
				<div class="history-list-inner__wrapper  grid-list__item stroke-theme-parent-all colored_theme_hover_bg-block animate-arrow-hover grid-list-border-outer">
					<div id="<?= $this->GetEditAreaId($arItem['ID']) ?>"
						 class="history-list-inner__item rounded-4 height-100">
						<div class="history-list-inner__content-wrapper">
							<div class="history-list-inner__year-wrapper">
								<div class="history-list-inner__year  switcher-title">
									<?= $arItem['NAME'] ?>
								</div>
								<? if( $arItem['PERIOD'] ): ?>
									<div class="history-list-inner__period">
										<span class="history-list-inner__period-item"><?= $arItem['PERIOD']; ?></span>
									</div>
								<? endif; ?>
							</div>
							<div class="history-list-inner__text-wrapper">
								<? if ($arItem['FIELDS']['PREVIEW_TEXT'] && $arItem['FIELDS']['DETAIL_TEXT']) : ?>
									<div class="history-list-inner__preview-text">
										<? if ($arItem['PREVIEW_TEXT_TYPE'] == 'text'): ?>
											<p><?= $arItem['FIELDS']['PREVIEW_TEXT'] ?></p>
										<? else: ?>
											<?= $arItem['FIELDS']['PREVIEW_TEXT'] ?>
										<? endif; ?>
									</div>
									<div class="history-list-inner__detail-text">
										<? if ($arItem['DETAIL_TEXT_TYPE'] == 'text'): ?>
											<p><?= $arItem['FIELDS']['DETAIL_TEXT'] ?></p>
										<? else: ?>
											<?= $arItem['FIELDS']['DETAIL_TEXT'] ?>
										<? endif; ?>
									</div>
								<? else : ?>
									<? if (strlen($arItem['FIELDS']['PREVIEW_TEXT'])): ?>
										<div class="history-list-inner__detail-text">
											<? if ($arItem['PREVIEW_TEXT_TYPE'] == 'text'): ?>
												<p><?= $arItem['FIELDS']['PREVIEW_TEXT'] ?></p>
											<? else: ?>
												<?= $arItem['FIELDS']['PREVIEW_TEXT'] ?>
											<? endif; ?>
										</div>
									<? endif; ?>
								<? endif ?>
							</div>
						</div>
						<? if ($previewImageSrc) : ?>
							<div class="history-list-inner__image-wrapper ">
								<div class="history-list-inner__image">
									<img class="rounded-4" src="<?= $previewImageSrc ?>" title="<?= htmlspecialchars($arItem['NAME']) ?>" alt="<?= htmlspecialchars($arItem['NAME']) ?>">
								</div>
							</div>
						<? endif ?>
					</div>
				</div>
			<? endforeach ?>

			<? if ($arParams['IS_AJAX']) : ?>
				<div class="bottom_nav_wrapper nav-compact">
					<div class="bottom_nav hide-600" <?= ($arParams['IS_AJAX'] ? "style='display: none; '" : ""); ?>
						 data-parent=".history-list-inner" data-append=".history-list-inner__list">
						<? if ($arParams['DISPLAY_BOTTOM_PAGER']): ?>
							<?= $arResult['NAV_STRING'] ?>
						<? endif; ?>
					</div>
				</div>
				<? die(); ?>
			<? endif; ?>

			<? if ($bMobileScrolled && $arParams['DISPLAY_BOTTOM_PAGER'] == 'Y') : ?>
				<div class="bottom_nav mobile_slider <?= ($bHasNav ? '' : ' hidden-nav'); ?>"
					 data-parent=".history-list-inner"
					 data-append=".history-list-inner__list" <?= ($arParams['IS_AJAX'] ? "style='display: none; '" : ""); ?>>
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
						 data-parent=".history-list-inner" data-append=".history-list-inner__list">
						<?= $arResult['NAV_STRING'] ?>

					</div>
				</div>
			</div>
		<? endif; ?>
	</div>
<?php endif //if($arResult['ITEMS']) ?>