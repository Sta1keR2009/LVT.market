<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<?php
$this->setFrameMode(true);

use \Bitrix\Main\Localization\Loc;

$bHasNav = (strpos($arResult["NAV_STRING"], 'more_text_ajax') !== false);
?>
<?// intro text?>
<?ob_start();?>
<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "page",
		"AREA_FILE_SUFFIX" => "inc",
		"EDIT_TEMPLATE" => ""
	)
);?>
<?$html = ob_get_contents();
ob_end_clean();?>
<?if($html):?>
	<div class="text_before_items">
		<?$APPLICATION->IncludeComponent(
			"bitrix:main.include",
			"",
			Array(
				"AREA_FILE_SHOW" => "page",
				"AREA_FILE_SUFFIX" => "inc",
				"EDIT_TEMPLATE" => ""
			)
		);?>
	</div>
<?endif;?>

<?
$listClasses = 'grid-list--items-1';
if ($arParams['ITEMS_OFFSET'] == 'Y') {
	$listClasses .= ' grid-list--gap-32';
} else {
	$listClasses .= ' grid-list--no-gap';
}

?>
<?php if ($arResult['SECTIONS']) : ?>
	<div class="tabs arrow_scroll swipeignore">
		<ul class="nav nav-tabs">
			<? $i = 0 ?>
			<? foreach ($arResult['SECTIONS'] as $arSection): ?>
				<li class="nav__item <?= $i == 0 ? 'active' : '' ?>">
					<a href="#faq-section-<?= $arSection['ID'] ?>" data-toggle="tab"><?= $arSection['NAME'] ?></a>
				</li>
				<? $i++ ?>
			<? endforeach; ?>
		</ul>
	</div>

	<div class="tab-content">
		<? $i = 0 ?>
		<? foreach ($arResult['SECTIONS'] as $arSection): ?>
			<div class="tab-pane <?= $i == 0 ? 'active' : '' ?>" id="faq-section-<?= $arSection['ID'] ?>">
				<div class="faq-accordion__list  accordion-type-1 grid-list <?= $listClasses ?>">
					<? if ($arParams['IS_AJAX']) : ?>
						<? $APPLICATION->RestartBuffer(); ?>
					<? endif; ?>

					<? foreach ($arSection['ITEMS'] as $i => $arItem) : ?>
						<?
						$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
						$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), ['CONFIRM' => Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]);
						$areaElementId = $this->GetEditAreaId($arItem['ID']);
						$hasAnswerText = isset($arItem['FIELDS']['PREVIEW_TEXT']) && $arItem['FIELDS']['PREVIEW_TEXT'];
						?>
						<div class="grid-list__item item-accordion-wrapper <?= $hasAnswerText ? 'shadow-hovered shadow-no-border-hovered' : '' ?>  faq-accordion__wrapper">
							<div id="<?= $areaElementId ?>" class="faq-accordion__item">
								<div class="accordion-head accordion-close stroke-theme-hover" <?= $hasAnswerText ? 'data-toggle="collapse"' : '' ?>
									 data-parent="#accordion-<?= $areaElementId ?>"
									 href="#accordion-<?= $areaElementId ?>">
									<div class="faq-accordion__head">
										<span class="faq-accordion__name switcher-title font_weight--500 font_18"><?= $arItem['NAME'] ?></span>
									</div>

									<? if ($hasAnswerText) : ?>
										<i class="svg inline  svg-inline-right-arrow" aria-hidden="true">
											<svg width="16" height="16" viewBox="0 0 16 16" fill="none"
												 xmlns="http://www.w3.org/2000/svg">
												<path d="M8 1V15M1 8H15" stroke="#999999" stroke-width="2"
													  stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
										</i>
									<? endif ?>
								</div>
								<div id="accordion-<?= $areaElementId ?>" class="panel-collapse collapse">
									<div class="accordion-body color_666">
										<div class="faq-accordion__preview-text  accordion-preview">
											<? if ($hasAnswerText) : ?>
												<div class="faq-accordion__detail-text">
													<? if ($arItem['PREVIEW_TEXT_TYPE'] == 'text') : ?>
														<p><?= $arItem['FIELDS']['PREVIEW_TEXT'] ?></p>
													<? else: ?>
														<?= $arItem['FIELDS']['PREVIEW_TEXT'] ?>
													<? endif; ?>

												</div>
												<? if ($arItem['DISPLAY_PROPERTIES']['LINK_BUTTON']) : ?>
													<div class="faq-accordion__btn-wrapper">
														<a href="<?= $arItem['DISPLAY_PROPERTIES']['LINK_BUTTON']['VALUE'] ?>" class="btn btn-default btn-lg has-ripple">
															<span><?= $arItem['DISPLAY_PROPERTIES']['TITLE_BUTTON']['VALUE'] ?: Loc::getMessage('FAI__BTN__LINK') ?></span>
														</a>
													</div>
												<? endif ?>
											<? endif; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					<? endforeach ?>
				</div>
			</div>
			<? $i++ ?>
		<? endforeach ?>
	</div>
<?php endif //if($arResult['SECTIONS']) ?>