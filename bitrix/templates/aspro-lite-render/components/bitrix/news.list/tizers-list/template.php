<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<? $this->setFrameMode( true ); ?>

<?if($arResult["ITEMS"]):?>
	<?
	$templateData['ITEMS'] = true;

	$bMaxWidthWrap = (
        !isset($arParams['MAXWIDTH_WRAP']) ||
        (isset($arParams['MAXWIDTH_WRAP']) && $arParams['MAXWIDTH_WRAP'] !== "N")
    );

	$blockClasses = ' outer-rounded-x';
	$bCompact = $arParams['VIEW_TYPE'] === 'COMPACT';

	$gridClass = 'grid-list ';
	if ($bCompact) {
		$gridClass .= 'grid-list--items-1 grid-list--gap-20';
		$blockClasses .= ' tizers-list--compact';
	} else {
		$gridClass .= ' grid-list--items-'.$arParams['ITEMS_COUNT'];
		$gridClass .= \TSolution\Functions::getGridClassByCount(['992', '1200'], $arParams['ITEMS_COUNT']);
	}

	$itemClasses = '';
	$itemClasses .= ' tizers-list__item--images-position-'.$arParams['IMAGE_POSITION'];

	if($arParams['IMAGE_POSITION'] == 'TOP') {
		$itemClasses .= ' tizers-list__item--column';
	}

	$wrapperClasses = ' grid-list__item';

	$fontSize = $bCompact ? 'font_14' : 'font_16 font_15--to-600';
	?>
	<div class="tizers-list <?=$blockClasses?>">
		<?if($bMaxWidthWrap):?>
			<div class="maxwidth-theme">
		<?endif;?>
			<div class="tizers-list__items-wrapper <?=$gridClass?> <?=($arParams['MOBILE_SCROLLED'] ? 'mobile-scrolled mobile-offset mobile-scrolled--items-2' : '')?>">
				<?foreach($arResult["ITEMS"] as $arItem){
					$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
					$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
					$name = $arItem['NAME'];
					$link = $arItem["PROPERTIES"]["LINK"]["VALUE"];
					?>
					<div class="tizers-list__item-wrapper <?=$wrapperClasses?>">
						<div class="tizers-list__item color-theme-parent-all <?=$itemClasses?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
							<?

							$bNotInlineSvg = isset($arItem["PROPERTIES"]["NOT_INLINE_SVG"]) && $arItem["PROPERTIES"]["NOT_INLINE_SVG"]['VALUE'] === "Y";

							$image = ($arItem["DETAIL_PICTURE"] ? $arItem["DETAIL_PICTURE"]['ID'] : '');
							if(isset($arItem["PROPERTIES"]["TIZER_ICON"]) && $arItem["PROPERTIES"]["TIZER_ICON"]['VALUE'])
								$image = $arItem["PROPERTIES"]["TIZER_ICON"]["VALUE"];

							if($image) {
								$imagePath = CFile::GetPath($image);
							}
							?>

							<?if($image){?>
								<div class="tizers-list__item-image-wrapper tizers-list__item-image-wrapper--position-<?=$arParams['IMAGE_POSITION']?>">
									<?if($link):?>
										<a class="tizers-list__item-link flexbox" href="<?=$link?>">
									<?endif;?>
										<?if(strpos($imagePath, ".svg") !== false && !$bNotInlineSvg):?>
											<?=TSolution::showIconSvg(' fill-theme tizers-list__item-image-icon', $imagePath);?>
										<?else:?>
											<img src="<?=$imagePath?>" class="tizers-list__item-image-picture" alt="<?=($arItem["DETAIL_PICTURE"]["ALT"] ? $arItem["DETAIL_PICTURE"]["ALT"] : $arItem["NAME"])?>" title="<?=($arItem["DETAIL_PICTURE"]["TITLE"] ? $arItem["DETAIL_PICTURE"]["TITLE"] : $arItem["NAME"])?>"/>
										<?endif;?>
									<?if($link):?>
										</a>
									<?endif;?>
								</div>
							<?}?>

							<div class="tizers-list__item-text-wrapper">
								<?if($link):?>
									<a class="tizers-list__item-link dark_link tizers-list__item-name <?= $fontSize; ?><?= $bCompact ? '' : ' switcher-title'; ?> color_222 color-theme-target" href="<?=$link?>">
								<?else:?>
									<span class="tizers-list__item-name <?= $fontSize; ?><?= $bCompact ? '' : ' switcher-title'; ?> color_222">
								<?endif;?>
										<?=$name;?>
								<?if($link):?>
									</a>
								<?else:?>
									</span>
								<?endif;?>

								<?if($arItem['FIELDS']["DETAIL_TEXT"]):?>
									<span class="tizers-list__item-descr font_15 font_14--to-600"><?=$arItem["DETAIL_TEXT"];?></span>
								<?endif;?>
							</div>
						</div>
					</div>
				<?}?>
			</div>
		<?if($bMaxWidthWrap):?>
			</div>
		<?endif;?>
	</div>
<?endif;?>
