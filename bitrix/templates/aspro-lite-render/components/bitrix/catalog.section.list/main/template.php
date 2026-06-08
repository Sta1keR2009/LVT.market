<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();
$this->setFrameMode(true);
use \Bitrix\Main\Localization\Loc;

$arItems = $arResult['SECTIONS'];?>
<?if($arItems):?>
	<?
	$bIcons = $arParams['IMAGES'] === 'ICONS';
	$bNarrow = $arParams['NARROW'] === 'Y';
	$bMobileScrolled = $arParams['MOBILE_SCROLLED'] !== 'N';
	$bMobileCompact = $arParams['MOBILE_COMPACT'] === 'Y';

	$gridClass = ' grid-list--fill-bg';
	if ($bMobileScrolled) {
		$gridClass .= ' mobile-scrolled mobile-offset';
		if($bMobileCompact){
			$gridClass .= ' mobile-scrolled--items-auto mobile-scrolled--small-offset';
		} else {
			$gridClass .= ' mobile-scrolled--items-3';
		}
	} else {
		$gridClass .= ' normal';
	}

	$mainClasses = '';
	if($bMobileCompact){
		$gridClass .= ' sections-block--mobile-compact';
	}

	$arBreakPoints = ['768', '992', '1200'];
	if ($arParams['ELEMENTS_IN_ROW'] == "8") {
		array_push($arBreakPoints, '1100', '1300', '1400');
	}
	if ($arParams['ELEMENTS_IN_ROW'] == "6") {
		array_push($arBreakPoints, '1100');
	}
	if ($arParams['ELEMENTS_IN_ROW'] == "4") {
		$arBreakPoints = ['768', '1200'];
	}
	$gridClass .= \TSolution\Functions::getGridClassByCount($arBreakPoints, $arParams['ELEMENTS_IN_ROW']);

	$itemWrapperClasses = 'grid-list__item stroke-theme-parent-all colored_theme_hover_bg-block animate-arrow-hover items-'.$arParams['ELEMENTS_IN_ROW'];

	$itemClasses = 'height-100 outer-rounded-x';
	
	if ($arParams['BORDERED'] !== 'N') {
		$itemClasses .= ' bordered';
	}
	
	$itemClasses .= ' color-theme-parent-all shadow-hovered shadow-no-border-hovered';

	$imageWrapperClasses = 'sections-block__item-image-wrapper--'.$arParams['IMAGES'];?>
	
	<div class="sections-block <?=$mainClasses?> <?=$templateName?>-template">
		<?=TSolution\Functions::showTitleBlock([
			'PATH' => 'sections-list',
			'PARAMS' => $arParams
		]);?>
		<?if (!$bNarrow):?>
		<div class="maxwidth-theme">
		<?endif;?>
			<div class="grid-list <?=$gridClass;?>">
				<?$bShowImage = $bIcons ||  in_array('PICTURE', $arParams['SECTION_FIELDS']);

				foreach($arItems as $i => $arItem):?>
					<?
					// edit/add/delete buttons for edit mode
					$arSectionButtons = CIBlock::GetPanelButtons($arItem['IBLOCK_ID'], 0, $arItem['ID'], array('SESSID' => false, 'CATALOG' => true));
					$this->AddEditAction($arItem['ID'], $arSectionButtons['edit']['edit_section']['ACTION_URL'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'SECTION_EDIT'));
					$this->AddDeleteAction($arItem['ID'], $arSectionButtons['edit']['delete_section']['ACTION_URL'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'SECTION_DELETE'), array('CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

					// detail url
					$detailUrl = $arItem['SECTION_PAGE_URL'];
					if ($arParams['USE_FILTER_SECTION'] == 'Y' && $arParams['BRAND_NAME'] && $arParams['BRAND_CODE']) {
						$detailUrl .= "filter/brand-is-".$arParams['BRAND_CODE']."/apply/";
					}

					// preview image
					if ($bShowImage) {
						if ($bIcons) {
							$nImageID = $arItem['~UF_CATALOG_ICON'];
						} else {
							$nImageID = is_array($arItem['PICTURE']) ? $arItem['PICTURE']['ID'] : $arItem['~PICTURE'];
						}
						$imageSrc = ($nImageID ? CFile::ResizeImageGet($nImageID, ['width' => 120, 'height' => 120], BX_RESIZE_IMAGE_PROPORTIONAL_ALT)['src'] : SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg');
					}?>
					<div class="sections-block__wrapper <?=$itemWrapperClasses?>">
						<div class="sections-block__item <?=$itemClasses?>" id="<?=$this->GetEditAreaId($arItem['ID'])?>">
							<a class="sections-block__item-link dark_link height-100" href="<?=$detailUrl?>">
							
								<span class="sections-block__item-inner height-100">
									<?if($bShowImage && $imageSrc):?>
										<span class="sections-block__item-image-wrapper <?=$imageWrapperClasses?>">
											<?if($bIcons && $nImageID):?>
												<?$svgInline = $GLOBALS['arTheme']['USE_SVG_INLINE']['VALUE'];
												if ($arItem['~UF_SVG_INLINE']) {
													$rsSvgConfig = CUserFieldEnum::GetList(array(), array("ID" => $arItem['~UF_SVG_INLINE']));
													if ($arSvgConfig = $rsSvgConfig->Fetch()) {
														$svgInline = $arSvgConfig['XML_ID'];
													}
												}?>
												<?if ($svgInline !== 'N'):?>
													<?=TSolution::showIconSvg(' fill-theme sections-block__item-image', $imageSrc);?>
												<?else:?>
													<img src="<?=$imageSrc?>" class="sections-block__item-image" alt="<?=$arItem['NAME'];?>"/>
												<?endif;?>
											<?else:?>
												<img src="<?=$imageSrc?>" class="sections-block__item-image" alt="<?=$arItem['NAME'];?>"/>
											<?endif;?>
										</span>
									<?endif;?>
									<span class="sections-block__item-text color-theme-target font_14 font_short"><?=$arItem['NAME'];?></span>
								</span>
							</a>
						</div>
					</div>
				<?endforeach;?>
			</div>
		<?if (!$bNarrow):?>
		</div>
		<?endif;?>
	</div> <?// .sections-block?>
<?endif;?>