<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

$this->setFrameMode(true);

use \Bitrix\Main\Localization\Loc;

$templateData = array_filter([
	'ELEMENT_CODE' => $arResult['CODE'],
	'DOCUMENTS' => $arResult['DISPLAY_PROPERTIES']['DOCUMENTS']['VALUE'],
	'SERVICES' => [
		'IBLOCK_ID' => $arResult['DISPLAY_PROPERTIES']['LINK_SERVICES']['LINK_IBLOCK_ID'],
		'VALUE' => $arResult['DISPLAY_PROPERTIES']['LINK_SERVICES']['VALUE'],
	],
]);

$propsPartners = (isset($arResult['IMAGE']) && $arResult['IMAGE']) || (isset($arResult['CONTACT_PROPERTIES']) && $arResult['CONTACT_PROPERTIES']);

if($arResult['FIELDS']['DETAIL_PICTURE']) {
	// single detail image
	$templateData['BANNER_TOP_ON_HEAD'] = isset($arResult['PROPERTIES']['PHOTOPOS']) && $arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'] == 'TOP_ON_HEAD';

	$atrTitle = (strlen($arResult['DETAIL_PICTURE']['DESCRIPTION']) ? $arResult['DETAIL_PICTURE']['DESCRIPTION'] : (strlen($arResult['DETAIL_PICTURE']['TITLE']) ? $arResult['DETAIL_PICTURE']['TITLE'] : $arResult['NAME']));
	$atrAlt = (strlen($arResult['DETAIL_PICTURE']['DESCRIPTION']) ? $arResult['DETAIL_PICTURE']['DESCRIPTION'] : (strlen($arResult['DETAIL_PICTURE']['ALT']) ? $arResult['DETAIL_PICTURE']['ALT'] : $arResult['NAME']));

	$bTopImg = (strpos($arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'], 'TOP') !== false);
	$templateData['IMG_TOP_SIDE'] = isset($arResult['PROPERTIES']['PHOTOPOS']) && $arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'] == 'TOP_SIDE';
	?>
	<?if (!$templateData['IMG_TOP_SIDE']):?>
		<?if ($bTopImg):?>
			<?if ($templateData['BANNER_TOP_ON_HEAD']):?>
				<?$this->SetViewTarget('side-over-title');?>
			<?else:?>
				<?$this->SetViewTarget('top_section_filter_content');?>
			<?endif;?>
		<?endif;?>

		<?\TSolution\Functions::showBlockHtml([
			'FILE' => '/images/detail_single.php',
			'PARAMS' => [
				'TYPE' => $arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'],
				'URL' => $arResult['DETAIL_PICTURE']['SRC'],
				'ALT' => $atrAlt,
				'TITLE' => $atrTitle,
				'TOP_IMG' => $bTopImg
			],
		])?>

		<?if ($bTopImg):?>
			<?$this->EndViewTarget();?>
		<?endif;?>
	<?endif;?>
<?}?>

<div class="partner-detail">
	<?if ($propsPartners) { ?>
		<div class="partner-detail__card bordered outer-rounded-x">
			<div class="partner-detail__card-image">
				<div class="partner-detail__image-wrapper rounded-x">
					<div class="partner-detail__image">
					<span class="partner-detail__image-bg" title="<?= htmlspecialchars($arResult['IMAGE']['TITLE']) ?>"
						style="background-image: url(<?= $arResult['IMAGE']['PREVIEW_SRC'] ?>);"></span>
					</div>
				</div>
			</div>
			<div class="partner-detail__card-info">
				<div class="partner-detail__content<?=$propsPartners ? "" : " marginone" ?>">
					<? if ($arResult['FIELDS']['PREVIEW_TEXT'] || $arResult['FIELDS']['DETAIL_TEXT']): ?>
						<div class="partner-detail__text-wrapper">
							<div class="partner-detail__text linecamp-4">
								<? if($arResult['FIELDS']['PREVIEW_TEXT']) : ?>
								<div class="partner-detail__text-preview">
									<?if($arResult['PREVIEW_TEXT_TYPE'] == 'text'):?>
										<p><?= $arResult['FIELDS']['PREVIEW_TEXT']?></p>
									<?else:?>
										<?= $arResult['FIELDS']['PREVIEW_TEXT']?>
									<?endif;?>
								</div>
								<?endif?>
								<?if($arResult['DETAIL_TEXT_TYPE'] == 'text'):?>
									<p><?= $arResult['FIELDS']['DETAIL_TEXT']?></p>
								<?else:?>
									<?= $arResult['FIELDS']['DETAIL_TEXT']?>
								<?endif;?>
							</div>
						</div>
					<? endif; ?>
				</div>
				<div class="partner-detail__more-detail-text-link active hide">
					<span class="choise dotted dark_link"><?=Loc::getMessage('MORE_DETAIL_TEXT');?></span>
				</div>
				<? if ($arResult['CONTACT_PROPERTIES']) : ?>
					<div class="partner-detail__properties line-block--6">
						<? foreach ($arResult['CONTACT_PROPERTIES'] as $property) : ?>
							<div class="partner-detail__property line-block__item">
								<div class="partner-detail__property-value">
									<? if ($property['TYPE'] == 'LINK') : ?>
										<a rel="nofollow" target="_blank" href="<?= $property['HREF'] ?>"
										class="dark_link rounded-x">
											<?= $property['VALUE'] ?>
										</a>
									<? else : ?>
										<?= $property['VALUE'] ?>
									<? endif ?>
								</div>
							</div>
						<? endforeach; ?>
					</div>
				<? endif ?>
			</div>
		</div>
	<?}?>
</div>
<script>
	BX.message({
		MORE_DETAIL_TEXT: '<? echo GetMessage("MORE_DETAIL_TEXT"); ?>',
		HIDE_DETAIL_TEXT: '<? echo GetMessage("HIDE_DETAIL_TEXT"); ?>',
	});
</script>
