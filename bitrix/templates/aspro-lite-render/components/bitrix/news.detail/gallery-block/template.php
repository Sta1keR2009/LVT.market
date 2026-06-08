<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true ) die();
$this->setFrameMode(true);
?>
<?if($arResult && $arResult['PROPERTIES']['PHOTOS']['VALUE']):?>
	<div class="gallery-item gallery-list__wrapper grid-list grid-list--items-2-1200 grid-list--items-2-992 grid-list--items-2-768 grid-list--items-2-601 grid-list--normal">
		<?foreach($arResult['PROPERTIES']['PHOTOS']['VALUE'] as $photoId):?>
			<?
			$arImage = CFile::GetFileArray($photoId);
			$imageSrc = $arImage['SRC'];
			$imageDescr = $arImage['DESCRIPTION'];
			?>
			<div  class="gallery-list__wrapper outer-rounded-x">
				<div class="gallery-list__item relative pointer  flexbox gallery-list__item--has-additional-text">
					<div class="gallery-list__item-image-wrapper relative gallery-list__item-image-wrapper">
						<a class="detail-gallery-big__link popup_link" title="<?=htmlspecialcharsbx($imageDescr)?>">
							<span class="gallery-list__item-image" style="background-image: url(<?=$imageSrc?>);"></span>
						</a>
					</div>
					<div class="gallery-list__item-text-wrapper flexbox ">
						<div class="gallery-list__item-text-cross-part fancy fancy-thumbs" data-fancybox="item_slider" href="<?=$imageSrc?>" data-big="<?=$imageSrc?>">
							<div class="cross"></div>
						</div>
					</div>
				</div>
			</div>
		<?endforeach;?>
	</div>
<?else:?>
	<div class="alert alert-warning"><?=GetMessage("ELEMENT_PROPERTY_ERROR")?></div>
<?endif;?>
