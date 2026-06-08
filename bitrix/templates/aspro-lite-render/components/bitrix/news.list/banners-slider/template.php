<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<?$this->setFrameMode(true);?>


<?if($arResult['ITEMS']):?>	
	<?$count = count($arResult['ITEMS']);?>
	<?
	$gallerySetting = [
		'PLUGIN_OPTIONS' => [
			// Disable preloading of all images
			'preloadImages' => false,
			'init' => false,
			'keyboard' => [
				'enabled' => true,
			],
			'loop' => false,
			'rewind' => true,
			'pagination' => [
				'enabled' => true,
				'el' => '.swiper-pagination',
			],
			'slidesPerView' => 1,
		],
	];
	?>
	<div class="swiper slider-solution banners-slider outer-rounded-x hidden" data-plugin-options='<?= \Bitrix\Main\Web\Json::encode($gallerySetting['PLUGIN_OPTIONS']); ?>'>
		<div class="swiper-wrapper">
			<?foreach($arResult['ITEMS'] as $i => $arItem):?>
				<?
					// edit/add/delete buttons for edit mode
					$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
					$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), array('CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
					
					// show preview picture?
					$bImage = (isset($arItem['FIELDS']['PREVIEW_PICTURE']) && $arItem['PREVIEW_PICTURE']['SRC']);
					$imageSrc = ($bImage ? $arItem['PREVIEW_PICTURE']['SRC'] : false);				
				?>
				<div class="swiper-slide banners-slider__item <?=$arItem['PROPERTIES']['SIZING']['VALUE_XML_ID']?> <?=$arParams['POSITION']?> <?=($arItem['PROPERTIES']['HIDDEN_SM']['VALUE_XML_ID']=='Y'?'hidden-sm':'')?> <?=($arItem['PROPERTIES']['HIDDEN_XS']['VALUE_XML_ID']=='Y'?'hidden-xs':'')?>" <?=($arItem['PROPERTIES']['BGCOLOR']['VALUE']?' style=" background:'.$arItem['PROPERTIES']['BGCOLOR']['VALUE'].';"':'')?> id="<?=$this->GetEditAreaId($arItem['ID'])?>">		
					<?if($arItem['PROPERTIES']['LINK']['VALUE']):?>
						<a href="<?=$arItem['PROPERTIES']['LINK']['VALUE']?>" <?=($arItem['PROPERTIES']['TARGET']['VALUE_XML_ID'] ? 'target="'.$arItem['PROPERTIES']['TARGET']['VALUE_XML_ID'].'"': '');?>>
					<?endif;?>
						<span class="banners-slider__image">
							<img title="<?=htmlspecialcharsbx($arItem['NAME'])?>" alt="<?=htmlspecialcharsbx($arItem['NAME'])?>" src="<?=$imageSrc?>" />
						</span>
					<?if($arItem['PROPERTIES']['LINK']['VALUE']):?>
						</a>
					<?endif;?>
				</div>
			<?endforeach;?>
		</div>
		<div class="slider-nav swiper-button-prev">
			<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/arrows.svg#left-7-12', 'stroke-dark-light', [
				'WIDTH' => 7, 
				'HEIGHT' => 12
			]); ?>
		</div>

		<div class="slider-nav swiper-button-next">
			<?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/arrows.svg#right-7-12', 'stroke-dark-light', [
				'WIDTH' => 7, 
				'HEIGHT' => 12
			]); ?>
		</div>
		<div class="swiper-pagination"></div>
		<script>showSectionBanners();</script>
	</div>
<?endif;?>