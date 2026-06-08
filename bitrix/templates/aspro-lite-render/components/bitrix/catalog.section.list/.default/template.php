<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();
$this->setFrameMode(true);
use \Bitrix\Main\Localization\Loc;

$arItems = $arResult['SECTIONS'];?>
<?if($arItems):?>
	<?
	$bIcons = $arParams['IMAGES'] === 'ICONS';
	$bNarrow = $arParams['NARROW'] === 'Y';
	$bMobileScrolled = $arParams['MOBILE_SCROLLED'] === 'Y';

	$listWrapClasses = '';
	if ($bMobileScrolled) {
		$listWrapClasses .= ' mobile-scrolled mobile-scrolled--items-auto mobile-scrolled--small-offset mobile-offset';
	}

	$itemWrapperClasses = 'line-block__item stroke-theme-parent-all colored_theme_hover_bg-block animate-arrow-hover';

	$itemClasses = 'height-100 outer-rounded-x';
	
	if ($arParams['BORDERED'] !== 'N') {
		$itemClasses .= ' bordered';
	}
	
	$itemClasses .= ' shadow-hovered shadow-no-border-hovered color-theme-parent-all';

	$imageWrapperClasses = 'sections-list__item-image-wrapper--'.$arParams['IMAGES'];?>
	
	<div class="sections-list <?=$templateName?>-template">
		<?=TSolution\Functions::showTitleBlock([
			'PATH' => 'sections-list',
			'PARAMS' => $arParams
		]);?>
	
		<?if (!$bNarrow):?>
		<div class="maxwidth-theme">
		<?endif;?>
			<div class="line-block line-block--align-normal line-block--flex-wrap line-block--gap line-block--gap-12 line-block--gap-8-to-600 <?=$listWrapClasses?>">
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
						$imageSrc = ($nImageID ? CFile::ResizeImageGet($nImageID, ['width' => 40, 'height' => 40], BX_RESIZE_IMAGE_PROPORTIONAL_ALT)['src'] : SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg');
					}?>
					<div class="sections-list__wrapper <?=$itemWrapperClasses?>">
						<div class="sections-list__item <?=$itemClasses?>" id="<?=$this->GetEditAreaId($arItem['ID'])?>">
							<a class="sections-list__item-link dark_link height-100" href="<?=$detailUrl?>">
							
								<span class="sections-list__item-inner flexbox flexbox--direction-row flexbox--align-center height-100">
									<?if($bShowImage && $imageSrc):?>
										<span class="sections-list__item-image-wrapper <?=$imageWrapperClasses?>">
											<?if($bIcons && $nImageID):?>
												<?$svgInline = $GLOBALS['arTheme']['USE_SVG_INLINE']['VALUE'];
												if ($arItem['~UF_SVG_INLINE']) {
													$rsSvgConfig = CUserFieldEnum::GetList(array(), array("ID" => $arItem['~UF_SVG_INLINE']));
													if ($arSvgConfig = $rsSvgConfig->Fetch()) {
														$svgInline = $arSvgConfig['XML_ID'];
													}
												}?>
												<?if ($svgInline !== 'N'):?>
													<?=TSolution::showIconSvg(' fill-theme sections-list__item-image', $imageSrc);?>
												<?else:?>
													<img src="<?=$imageSrc?>" class="sections-list__item-image" alt="<?=$arItem['NAME'];?>"/>
												<?endif;?>
											<?else:?>
												<img src="<?=$imageSrc?>" class="sections-list__item-image" alt="<?=$arItem['NAME'];?>"/>
											<?endif;?>
										</span>
									<?endif;?>
									<span class="sections-list__item-text color-theme-target font_short"><?=$arItem['NAME'];?></span>
								</span>
							</a>
						</div>
					</div>
				<?endforeach;?>
				<?if ($arParams['RIGHT_LINK']):?>
					<div class="sections-list__wrapper <?=$itemWrapperClasses?>">
						<div class="sections-list__item <?=$itemClasses?>">
							<a class="sections-list__item-link dark_link bg-theme-parent-hover" href="<?=$arParams['RIGHT_LINK'];?>">
								<span class="sections-list__item-inner flexbox flexbox--direction-row flexbox--align-center">
									<span class="sections-list__item-image-wrapper sections-list__item-image-wrapper--PICTURES sections-list__item-image-wrapper--LINK rounded-x bg-theme-target">
										<?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-12', 'arrow stroke-dark-light', ['WIDTH' => 7,'HEIGHT' => 12]);?>
									</span>
									<span class="sections-list__item-text color-theme-target font_short linecamp-12"><?=$arParams['RIGHT_TITLE'] ?: Loc::getMessage('ALL_CATALOG');?></span>
								</span>
							</a>
						</div>
					</div>
				<?endif;?>
			</div>
		<?if (!$bNarrow):?>
		</div>
		<?endif;?>
	</div> <?// .sections-list?>
<?endif;?>