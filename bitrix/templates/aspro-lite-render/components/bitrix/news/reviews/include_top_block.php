<?php

use \Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Application;

	$file = SITE_DIR . 'include/reviews.php';
	$fileDocumentRoot = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'] . $file);

	$hasReviewFile = false;

	if(file_exists($fileDocumentRoot)) {
		$hasReviewFile = trim(file_get_contents($fileDocumentRoot)) != '';
	}

?>

<div class="reviews-info bordered rounded-x">
	<div class="reviews-info__top">
		<div class="reviews-info__line">
			<? if($hasReviewFile) : ?>
			<div class="reviews-info__col">
				<div class="reviews-info__text">
					<? $APPLICATION->IncludeFile(SITE_DIR . 'include/reviews.php', [], [
							'MODE' => 'text',
							'NAME' => Loc::getMessage('REVIEWS__DESCRIPTION'),
							'TEMPLATE' => 'include_area.php',
						]
					); ?>
				</div>
			</div>
			<? endif; ?>
			<div class="reviews-info__col">
				<div class="reviews-info__btn-wrapper order-info-btns <?= !$hasReviewFile ? 'reviews-info__btn-wrapper--singleton' : '' ?>">
					<div>
						<div class="btn btn-default btn-lg min_width--300 animate-load"
							 data-event="jqm"
							 data-name="feedback"
							 data-param-id="<?= TSolution::getFormID("aspro_lite_feedback"); ?>"
						>
							<?= $arParams['S_FEEDBACK'] ?: Loc::getMessage('REVIEWS__BTN__SEND') ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
