<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();
$this->setFrameMode(true);
use \Bitrix\Main\Localization\Loc;

$arItems = $arResult['SECTIONS'];?>
<?if($arItems):?>
	<?
	$bIcons = $arParams['IMAGES'] === 'ICONS';

	$itemWrapperClasses = 'swiper-slide';

	$itemClasses = 'height-100';
	
	$itemClasses .= ' color-theme-parent-all';

	$imageWrapperClasses = 'sections-slider__item-image-wrapper--'.$arParams['IMAGES'].' sections-slider__item-image-wrapper--fon-'.$arParams['IMAGE_ON_FON'];
	?>
	
	<div class="sections-slider <?=$templateName?>-template">
		<?=TSolution\Functions::showTitleBlock([
			'PATH' => 'sections-list',
			'PARAMS' => $arParams
		]);?>
	
		<div class="maxwidth-theme">
			<?
			$countSlides = count($arItems);
			$arOptions = [
				// Disable preloading of all images
				'preloadImages' => false,
				// Enable lazy loading
				'lazy' => false,
				'keyboard' => true,
				'init' => false,
				'countSlides' => $countSlides,
				'rewind'=> true,
				'freeMode' => ['enabled' => true, 'momentum' => true],
				'slidesPerView' => 'auto',
				'spaceBetween' => 8,
				'pagination' => false,
				// 'autoplay' => ['delay' => $slideshowSpeed,],
				'type' => 'main_sections',
				'breakpoints' => [
					601 => ['spaceBetween' => 24],
				]
			];				
			?>
			<div class="swiper-nav-offset relative">
				<div class="swiper slider-solution slider-solution--static-dots appear-block mobile-offset mobile-offset--right" data-plugin-options='<?=json_encode($arOptions)?>'>
					<div class="swiper-wrapper">
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
							<div class="sections-slider__wrapper <?=$itemWrapperClasses?>">
								<div class="sections-slider__item <?=$itemClasses?>" id="<?=$this->GetEditAreaId($arItem['ID'])?>">
									<a class="sections-slider__item-link dark_link height-100" href="<?=$detailUrl?>">
									
										<span class="sections-slider__item-inner height-100">
											<?if($bShowImage && $imageSrc):?>
												<span class="sections-slider__item-image-wrapper outer-rounded-x <?=$imageWrapperClasses?>">
													<?if($bIcons && $nImageID):?>
														<?$svgInline = $GLOBALS['arTheme']['USE_SVG_INLINE']['VALUE'];
														if ($arItem['~UF_SVG_INLINE']) {
															$rsSvgConfig = CUserFieldEnum::GetList(array(), array("ID" => $arItem['~UF_SVG_INLINE']));
															if ($arSvgConfig = $rsSvgConfig->Fetch()) {
																$svgInline = $arSvgConfig['XML_ID'];
															}
														}?>
														<?if ($svgInline !== 'N'):?>
															<?=TSolution::showIconSvg(' fill-theme sections-slider__item-image', $imageSrc);?>
														<?else:?>
															<img src="<?=$imageSrc?>" class="sections-slider__item-image" alt="<?=$arItem['NAME'];?>"/>
														<?endif;?>
													<?else:?>
														<img src="<?=$imageSrc?>" class="sections-slider__item-image" alt="<?=$arItem['NAME'];?>"/>
													<?endif;?>
												</span>
											<?endif;?>
											<span class="sections-slider__item-text color-theme-target font_14 font_short"><?=$arItem['NAME'];?></span>
										</span>
									</a>
								</div>
							</div>
						<?endforeach;?>

					</div>
				</div>
				<?if ($arOptions['countSlides'] > 1):?>
					<div class="slider-nav swiper-button-prev slider-nav--shadow"><?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#left-7-12', 'stroke-dark-light', ['WIDTH' => 7,'HEIGHT' => 12]);?></div>
					<div class="slider-nav swiper-button-next slider-nav--shadow"><?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-12', 'stroke-dark-light', ['WIDTH' => 7,'HEIGHT' => 12]);?></div>
				<?endif;?>
			</div>
		</div>
	</div> <?// .sections-slider?>
<?endif;?>