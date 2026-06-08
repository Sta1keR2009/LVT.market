<?php
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

$this->setFrameMode(true);

$bMobileScrolledItems = $arParams['MOBILE_SCROLLED'] === 'Y';

$elementInRow = $arParams['ELEMENT_IN_ROW'];
$bItemsTypeAlbums = $arParams['ITEMS_TYPE'] != 'PHOTOS';
?>
<?if($arResult['ITEMS']):?>
	<?$bMobileScrolledItems = (
        !isset($arParams['MOBILE_SCROLLED'])
        || (isset($arParams['MOBILE_SCROLLED']) && $arParams['MOBILE_SCROLLED'] != 'N')
    );

    $count1200 = $arParams['ELEMENT_IN_ROW'];
    $count992 = $arParams['ELEMENT_IN_ROW'] > 1 ? $arParams['ELEMENT_IN_ROW'] - 1 : $arParams['ELEMENT_IN_ROW'];
    $count768 = $arParams['ELEMENT_IN_ROW'] > 1 ? $arParams['ELEMENT_IN_ROW'] - 1 : $arParams['ELEMENT_IN_ROW'];
    $count601 = 2;

    $gridClass = 'grid-list ';
    $gridClass .= ' grid-list--items-'.$count1200.'-1200';
    $gridClass .= ' grid-list--items-'.$count992.'-992';
    $gridClass .= ' grid-list--items-'.$count768.'-768';
    $gridClass .= ' grid-list--items-'.$count601.'-601';

    if ($bMobileScrolledItems) {
        $gridClass .= ' mobile-scrolled mobile-scrolled--items-2 mobile-offset';
    } else {
        $gridClass .= ' grid-list--normal';
    }?>

	<?if (!$arParams['IS_AJAX']):?>
		<div class="gallery-list gallery-list--items-offset <?=$templateName;?>-template">
			<?=TSolution\Functions::showTitleBlock([
			    'PATH' => 'gallery-list',
			    'PARAMS' => $arParams,
			]);?>

		    <?if($arParams['MAXWIDTH_WRAP'] != 'N'):?>
				<div class="maxwidth-theme">
			<?endif; ?>

			<div class="<?=$gridClass; ?>">
	<?endif;?>
	<?$counter = 1;

    foreach($arResult['ITEMS'] as $i => $arItem):?>
        <?if($bItemsTypeAlbums) {
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_EDIT'));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem['IBLOCK_ID'], 'ELEMENT_DELETE'), ['CONFIRM' => Loc::getMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')]);
        }

        // detail url
        $detailUrl = $arItem['DETAIL_PAGE_URL'];

        // photos
        $arPhotos = (array_key_exists('PHOTOS', (array) $arItem['PROPERTIES']) && $arItem['PROPERTIES']['PHOTOS']['VALUE']) ? (array) $arItem['PROPERTIES']['PHOTOS']['VALUE'] : (array) $arItem['PROPERTY_PHOTOS_VALUE'];

        // preview image
        $nImageID = is_array($arItem['FIELDS']['PREVIEW_PICTURE']) ? $arItem['FIELDS']['PREVIEW_PICTURE']['ID'] : $arItem['FIELDS']['PREVIEW_PICTURE'];
        if (!$nImageID && $arPhotos) {
            $nImageID = reset($arPhotos);
        }

        $imageSrc = ($nImageID ? CFile::getPath($nImageID) : SITE_TEMPLATE_PATH.'/images/noimage.png');
        $imageDescrPhoto = is_array($arItem['FIELDS']['PREVIEW_PICTURE']) ? $arItem['FIELDS']['PREVIEW_PICTURE']['DESCRIPTION'] : '';
        $imageDescrAlbum = $arItem['PROPERTIES']['PHOTOS']['DESCRIPTION'][0];?>
			<div class="gallery-list__wrapper grid-list__item outer-rounded-x">
				<div class="gallery-list__item flexbox gallery-list__item--has-additional-text relative pointer hover_zoom" <?=$bItemsTypeAlbums ? 'id="'.$this->GetEditAreaId($arItem['ID']).'"' : '';?>>
					<?if($imageSrc):?>
						<div class="gallery-list__item-image-wrapper">
                            <a class="gallery-list__item-link" href="<?=$detailUrl; ?>">
								<span class="gallery-list__item-image shine" style="background-image: url(<?=$imageSrc; ?>);"></span>
							</a>
						</div>
					<?endif;?>

					<?if($bItemsTypeAlbums):?>
                        <a class="gallery-list__item-link" href="<?=$detailUrl;?>">
                            <div class="gallery-list__item-additional-text-wrapper">
							    <div class="gallery-list__item-additional-text-top-part">
                                    <div class="gallery-list__item-photos-count font_14 color_light--opacity mb mb--2"><?=Aspro\Functions\CAsproLite::declOfNum(
                                        count($arPhotos),
                                        [
                                            Loc::getMessage('PHOTOS_COUNT_1'),
                                            Loc::getMessage('PHOTOS_COUNT_2'),
                                            Loc::getMessage('PHOTOS_COUNT_5'),
                                        ]
                                    );?>
                                    </div>

                                    <div class="gallery-list__item-title color_light font_18 font_16--to-600 linecamp-2">
									    <span><?=$arItem['NAME']; ?></span>
								    </div>
							    </div>
						    </div>
                        </a>
					<?endif; ?>

					<?if(!$bItemsTypeAlbums):?>
						<div class="gallery-list__item-text-wrapper flexbox">
							<div class="gallery-list__item-text-cross-part " data-fancybox="gallery_item" href="<?=$imageSrc; ?>" title="<?=Loc::getMessage('INCREASE')?>">
								<div class="cross"></div>
							</div>

							<div class="gallery-list__item-text-top-part">
								<a class="gallery-list__item-link gallery-list__item-link--absolute" href="<?=$detailUrl;?>"></a>
							    <div class="gallery-list__item-title">
								    <a class="linecamp-2 color_light font_16 font_14--to-600" href="<?=$detailUrl; ?>"><?=Loc::getMessage('ALBUM_LINK', ['#NAME#' => $arItem['NAME']]); ?></a>
							    </div>
						    </div>
					    </div>
					<?endif; ?>
				</div>
			</div>
		<?++$counter;?>
    <?endforeach;?>

	<?if ($bMobileScrolledItems):?>
		<?if($arParams['IS_AJAX']):?>
			<div class="wrap_nav bottom_nav_wrapper">
			    <script>initCountdown();</script>
		<?endif;?>

        <?$bHasNav = (strpos($arResult['NAV_STRING'], 'more_text_ajax') !== false); ?>
		    <div class="bottom_nav mobile_slider <?= $bHasNav ? '' : ' hidden-nav'; ?>" data-parent=".gallery-list" data-append=".grid-list" <?= $arParams['IS_AJAX'] ? "style='display: none; '" : ''; ?>>
				<?if ($bHasNav):?>
					<?=$arResult['NAV_STRING']; ?>
				<?endif; ?>
			</div>

		<?if($arParams['IS_AJAX']):?>
			</div>
		<?endif; ?>
	<?endif; ?>

	<?if (!$arParams['IS_AJAX']):?>
		</div>
	<?endif; ?>

	<?// bottom pagination?>
	<?if($arParams['IS_AJAX']):?>
		<div class="wrap_nav bottom_nav_wrapper">
	<?endif; ?>

	<div class="bottom_nav_wrapper nav-compact">
		<div class="bottom_nav <?= $bMobileScrolledItems ? 'hide-600' : ''; ?>" <?= $arParams['IS_AJAX'] ? "style='display: none; '" : ''; ?> data-parent=".gallery-list" data-append=".grid-list">
			<?if($arParams['DISPLAY_BOTTOM_PAGER']):?>
				<?=$arResult['NAV_STRING']; ?>
			<?endif; ?>
		</div>
	</div>

	<?if($arParams['IS_AJAX']):?>
		</div>
	<?endif; ?>

	<?if (!$arParams['IS_AJAX']):?>
		</div>
	<?endif;?>
<?endif;?>

<?if($arParams['MAXWIDTH_WRAP'] == 'Y'):?>
    </div>
<?endif; ?>
