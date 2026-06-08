<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();?>
<?$this->setFrameMode(true);?>
<?use \Bitrix\Main\Localization\Loc;?>
<?$bImage = strlen($arResult['FIELDS']['PREVIEW_PICTURE']['SRC']);
$arImage = ($bImage ? CFile::ResizeImageGet($arResult['FIELDS']['PREVIEW_PICTURE']['ID'], array('width' => 70, 'height' => 10000), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true) : array());
$imageSrc = ($bImage ? $arImage['src'] : '');
if(!$imageSrc && strlen($arResult['FIELDS']['DETAIL_PICTURE']['SRC'])){
	$bImage = strlen($arResult['FIELDS']['DETAIL_PICTURE']['SRC']);
	$arImage = ($bImage ? CFile::ResizeImageGet($arResult['FIELDS']['DETAIL_PICTURE']['ID'], array('width' => 90, 'height' => 10000), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true) : array());
	$imageSrc = ($bImage ? $arImage['src'] : '');
	$bLogo = ($imageSrc ? true : false);
}

?>
<div class="review-detail">
	<div class="review-detail__item <?=($bLogo ? ' wlogo' : '')?>">
		<div class="review-detail__item-header">
			<div class="flexbox flexbox--direction-row-reverse">
				<?if($imageSrc):?>
					<div class="review-detail__item-image">
						<img class="img-responsive<?=(!$bLogo ? ' rounded' : '')?>" src="<?=$imageSrc?>" alt="<?=($bImage ? $arResult['PREVIEW_PICTURE']['ALT'] : $arResult['NAME'])?>" title="<?=($bImage ? $arResult['PREVIEW_PICTURE']['TITLE'] : $arResult['NAME'])?>" />
					</div>
				<?endif;?>
				<div class="review-detail__item-info flex-grow-1">
					<div class="review-detail__item-top-info muted">
						<?if(isset($arResult['DISPLAY_PROPERTIES']['POST']) && strlen($arResult['DISPLAY_PROPERTIES']['POST']['VALUE'])):?>
							<span class="font_13"><?=$arResult['DISPLAY_PROPERTIES']['POST']['VALUE']?></span>
						<?endif?>
						<?if(isset($arResult['DISPLAY_ACTIVE_FROM']) && $arResult['DISPLAY_ACTIVE_FROM'] && isset($arResult['DISPLAY_PROPERTIES']['POST']) && strlen($arResult['DISPLAY_PROPERTIES']['POST']['VALUE'])):?>
							<span class="review-detail__item-separator">&ndash;</span>
						<?endif;?>
						<?if(isset($arResult['DISPLAY_ACTIVE_FROM']) && $arResult['DISPLAY_ACTIVE_FROM']):?>
							<span class="review-detail__item-date font_13"><?=$arResult['DISPLAY_ACTIVE_FROM']?></span>
						<?endif;?>
					</div>
					<div class="review-detail__item-title switcher-title color_222 font_18"><?=$arResult['NAME'];?></div>
					<?if(in_array('RATING', $arParams['PROPERTY_CODE'])):?>
						<?$ratingValue = ($arResult['DISPLAY_PROPERTIES']['RATING']['VALUE'] ? $arResult['DISPLAY_PROPERTIES']['RATING']['VALUE'] : 0);?>
						<div class="votes_block votes_block--inline">
							<div class="ratings">
								<div class="inner_rating">
									<?for($i=1;$i<=5;$i++):?>
										<div class="item-rating <?=(round($ratingValue) >= $i ? "filed" : "");?>"><?=TSolution::showIconSvg("star", SITE_TEMPLATE_PATH."/images/svg/star_sm.svg");?></div>
									<?endfor;?>
								</div>
							</div>
						</div>
					<?endif;?>
				</div>
			</div>
		</div>
		<div class="review-detail__item-bottom">
			<?if($arResult['PREVIEW_TEXT'] && strlen($arResult['PREVIEW_TEXT'])):?>
				<div class="review-detail__item-text"><?=$arResult['PREVIEW_TEXT'];?></div>
			<?endif;?>
			<div class="review-detail__item-close">
				<span class="btn btn-md btn-default btn-transparent-border jqmClose"><?=Loc::getMessage('CLOSE_POPUP');?></span>
			</div>
		</div>
	</div>
</div>