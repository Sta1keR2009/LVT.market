<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();?>
<?$this->setFrameMode(true);?>
<?use \Bitrix\Main\Localization\Loc;?>

<div class="company-item <?=$templateName?>-template">
	<?
	$bRegion = (is_array($arParams['REGION']) &&  $arParams['REGION']);
	$bWideBlock = false;
	$bImageOffset = $arParams['IMAGE_OFFSET'] === 'Y';
	$bBordered = $arParams['BORDERED'] === 'Y';
	$bShowImage = $arParams['IMAGE'] === 'Y';

	$bHasUrl = (isset($arResult['DISPLAY_PROPERTIES']['URL']) && strlen($arResult['DISPLAY_PROPERTIES']['URL']['VALUE']));
	$bShowBtn = (isset($arResult['DISPLAY_PROPERTIES']['MORE_BUTTON_TITLE']) && strlen($arResult['DISPLAY_PROPERTIES']['MORE_BUTTON_TITLE']['VALUE']) && $bHasUrl);
	$arParams['TITLE'] = ($arResult['DISPLAY_PROPERTIES']['COMPANY_NAME']['VALUE'] ? $arResult['DISPLAY_PROPERTIES']['COMPANY_NAME']['VALUE'] : $arResult['NAME']);
	if ($bRegion && $arParams['REGION']['PROPERTY_COMPANY_TITLE_VALUE']) {
		$arParams['TITLE'] =  $arParams['REGION']['PROPERTY_COMPANY_TITLE_VALUE'];
	}

	$mainText = $dopText = '';
	if ($arResult['FIELDS']['PREVIEW_TEXT']) {
		$mainText = ($bRegion && $arParams['~REGION']['DETAIL_TEXT'] ? $arParams['~REGION']['DETAIL_TEXT'] : $arResult['PREVIEW_TEXT']);
	}
	if ($arResult['FIELDS']['DETAIL_TEXT'] && $arParams['SHOW_ADDITIONAL_TEXT'] == 'Y') {
		$dopText = ($bRegion && $arParams['~REGION']['PREVIEW_TEXT'] ? $arParams['~REGION']['PREVIEW_TEXT'] : $arResult['DETAIL_TEXT']);
	}

	if($arParams['IMAGE'] !== "N") {
		$bImage = strlen($arResult['PREVIEW_PICTURE']['SRC']);
		$arImage = ($bImage ? CFile::ResizeImageGet($arResult['PREVIEW_PICTURE']['ID'], array('width' => 2000, 'height' => 2000), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true) : array());
		$imageSrc = ($bImage ? $arImage['src'] : '');
	}

	$wrapperClass = 'company-front-wrapper outer-rounded-x relative';

	if($bShowImage) {
		$wrapperClass .= ' company-image-type';
	}

	if($bBordered){
		$wrapperClass .= ' bordered';
	}
	?>

	<div class="company-item__wrapper company-item--<?=$arParams['TYPE_BLOCK'];?>">
		<?ob_start();?>
			<?if ($bShowBtn):?>
				<div class="index-block__btn">
					<a href="<?=str_replace('//', '/', SITE_DIR.'/'.$arResult['DISPLAY_PROPERTIES']['URL']['VALUE'])?>" class="btn btn-default btn-elg">
						<?=$arResult['DISPLAY_PROPERTIES']['MORE_BUTTON_TITLE']['VALUE']?>
					</a>
				</div>
			<?endif;?>
		<?$buttonHtml = ob_get_clean();?>

		<?ob_start();?>
			<?if ($mainText):?>
				<div class="company-item__text index-block__preview"><?=$mainText;?></div>
			<?endif;?>
		<?$mainTextHtml = ob_get_clean();?>
		
		<?ob_start();?>
			<div class="company-item__picture rounded-x" style="background-image: url(<?=$imageSrc?>)">
				<?if($bVideo):?>
					<div class="video-block">
						<div class="video-block__play bg-theme-after">
							<div class="video-block__fancy fancy" rel="nofollow">
								<?if($videoPlayer == 'HTML5'):?>
									<video class="company-item__video" muted playsinline controls loop><source  class="video-content" src="#" data-src="<?=$videoPlayerSrc;?>" type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"' /></video>
								<?else:?>
									<iframe class="company-item__video-iframe" data-src="<?=$videoPlayerSrc;?>"></iframe>
								<?endif;?>
							</div>
						</div>
					</div>
				<?endif;?>
			</div>
		<?$pictureHtml = ob_get_clean();?>
		<?if (!$bWideBlock):?>
		<div class="maxwidth-theme">
		<?endif;?>
			<div class="flexbox flexbox--direction-row <?=$wrapperClass?>">
				<div class="company-item__heading flex-1 relative">
					<?if ($bWideBlock):?>
						<div class="maxwidth-theme--half">
					<?endif;?>
						<div class="company-item__title sticky-block">
							<?=TSolution\Functions::showTitleInLeftBlock([
								'PARAMS' => $arParams,
							]);?>
								<?if($mainText && $imageSrc):?>
									<?=$mainTextHtml;?>
								<?endif;?>
						</div>
					<?if ($bWideBlock):?>
						</div>
					<?endif;?>
				</div>
				<div class="company-item__right flex-1">
					<?if (!$bWideBlock):?>
					<div class="sticky-block company-item__info">
					<?endif;?>
						<?if ($imageSrc):?>								
							<div class="<?=($bWideBlock ? 'sticky-block' : '');?> company-item__picture-wrapper">
								<?=$pictureHtml;?>
							</div>
						<?elseif(!$imageSrc && $mainText):?>
							<?=$mainTextHtml;?>
						<?endif;?>
					<?if (!$bWideBlock):?>
					</div>
					<?endif;?>
				</div>
				<?=$buttonHtml;?>
			</div>
		<?if (!$bWideBlock):?>
		</div>
		<?endif;?>	
	</div>
</div>