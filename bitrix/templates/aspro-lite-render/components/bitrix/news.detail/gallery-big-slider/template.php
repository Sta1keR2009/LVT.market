<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);
use \Bitrix\Main\Localization\Loc;
?>
<?php if ($arResult && $arResult['PROPERTIES']['PHOTOS']['VALUE']): ?>
    <?php
    $countPhoto = count($arResult['PROPERTIES']['PHOTOS']['VALUE']);
    $arOptionsGalleryBig = [
        'navigation' => [
            'nextEl' => '.swiper-button-next',
            'prevEl' => '.swiper-button-prev',
        ],
        'spaceBetween' => 15,
        'pagination' => [
            'el' => '.swiper-pagination',
            'clickable' => true,
        ],
        'thumbs' => [
            'swiper' => '#gallery-thumbs',
        ],
    ];

    $arOptionsGallerySmall = [
        'spaceBetween' => 30,
        'slidesPerView' => "auto",
        'freeMode' => true,
        'watchSlidesProgress' => true,
        'type' => 'gallery-thumbs',
        'navigation' => [
         'nextEl' => '.swiper-button-next-thumbs',
         'prevEl' => '.swiper-button-prev-thumbs',
        ],
        'breakpoints' => [
			601 => [
				'slidesPerView' => 4
			],
			992 => [
				'slidesPerView' => 5,
				'freeMode' => false,
			],
			1251 => [
				'slidesPerView' => 7,
				'freeMode' => false,
			],
		],
    ];?>
    <div class="gallery-item <?= $templateName ?>-template bordered outer-rounded-x">
        <?php if ($arParams['NARROW']): ?>
            <div class="maxwidth-theme">
        <?php endif; ?>

        <div class="gallery-big">
            <div id="gallery" class="swiper slider-solution gallery-big__swiper" data-plugin-options='<?=json_encode($arOptionsGalleryBig)?>'>
                <div class="swiper-wrapper">
                    <?php foreach ($arResult['PROPERTIES']['PHOTOS']['VALUE'] as $photoId): ?>
                        <?php
                        $arImage = CFile::GetFileArray($photoId);
                        $imageSrc = $arImage['SRC'];
                        $imageDescr = $arImage['DESCRIPTION'];
                        ?>
                        <div class="swiper-slide swiper-autoheight">
                            <a class="gallery-big__link detail-gallery-big__link popup_link fancy fancy-thumbs" data-fancybox="gallery_big_view" href="<?= $imageSrc ?>" title="<?= htmlspecialcharsbx($imageDescr) ?>">
                                <img class="img-responsive rounded-4" src="<?= $imageSrc ?>" alt="<?= htmlspecialcharsbx($imageDescr)?>">
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($countPhoto > 1): ?>
                    <div class="slider-nav swiper-button-prev slider-nav--shadow"><?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#left-7-12', 'stroke-dark-light', ['WIDTH' => 7,'HEIGHT' => 12]);?></div>
                    <div class="slider-nav swiper-button-next slider-nav--shadow"><?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-12', 'stroke-dark-light', ['WIDTH' => 7,'HEIGHT' => 12]);?></div>

                    <div class="gallery-count-info font_13 color_999 text-center hide-600">
						<span class="gallery-count-info__js-text">1</span>/<span><?=$countPhoto;?></span>
					</div>
                    <div class="swiper-pagination swiper-pagination--bottom visible-600 swiper-pagionation-bullet--line-to-600 static"></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($countPhoto > 1): ?>
            <div class="gallery-thumbs relative hide-600">
                <div id="gallery-thumbs" class="swiper slider-solution gallery-thumbs__swiper" data-plugin-options='<?=json_encode($arOptionsGallerySmall)?>' >
                    <div class="swiper-wrapper">
                        <?php foreach ($arResult['PROPERTIES']['PHOTOS']['VALUE'] as $photoId): ?>
                            <?php
                            $arImage = CFile::GetFileArray($photoId);
                            $imageSrc = $arImage['SRC'];
                            $imageDescr = $arImage['DESCRIPTION'];
                            ?>
                            <div class="swiper-slide pointer">
                                <img class="gallery-thumbs__picture rounded-4" src="<?= $imageSrc ?>" alt="<?= htmlspecialcharsbx($imageDescr) ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="slider-nav swiper-button-prev swiper-button-prev-thumbs slider-nav--shadow"><?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#left-7-12', 'stroke-dark-light', ['WIDTH' => 7,'HEIGHT' => 12]);?></div>
                <div class="slider-nav swiper-button-next swiper-button-next-thumbs slider-nav--shadow"><?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-12', 'stroke-dark-light', ['WIDTH' => 7,'HEIGHT' => 12]);?></div>
            </div>
        <?php endif; ?>

        <?php if ($arParams['NARROW']): ?>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <div class="alert alert-warning"><?= GetMessage("ELEMENT_PROPERTY_ERROR") ?></div>
<?php endif; ?>
