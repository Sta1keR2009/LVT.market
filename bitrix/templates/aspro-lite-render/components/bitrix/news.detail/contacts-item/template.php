<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$bUseMap = $arParams['USE_MAP'] === 'Y' && $arResult['DISPLAY_PROPERTIES']['MAP']['VALUE'];
$typeMap = $arParams['TYPE_MAP'];
$bUseFeedback = $arParams['USE_FEEDBACK'] === 'Y';

$arResult['DISPLAY_PROPERTIES']['PHONE']['VALUE'] = (is_array($arResult['DISPLAY_PROPERTIES']['PHONE']['VALUE']) ? $arResult['DISPLAY_PROPERTIES']['PHONE']['VALUE'] : ($arResult['DISPLAY_PROPERTIES']['PHONE']['VALUE'] ? array($arResult['DISPLAY_PROPERTIES']['PHONE']['VALUE']) : array()));
$arResult['DISPLAY_PROPERTIES']['PHONE']['DESCRIPTION'] = (is_array($arResult['DISPLAY_PROPERTIES']['PHONE']['DESCRIPTION']) ? $arResult['DISPLAY_PROPERTIES']['PHONE']['DESCRIPTION'] : ($arResult['DISPLAY_PROPERTIES']['PHONE']['DESCRIPTION'] ? array($arResult['DISPLAY_PROPERTIES']['PHONE']['DESCRIPTION']) : array()));

$arResult['DISPLAY_PROPERTIES']['EMAIL']['VALUE'] = (is_array($arResult['DISPLAY_PROPERTIES']['EMAIL']['VALUE']) ? $arResult['DISPLAY_PROPERTIES']['EMAIL']['VALUE'] : ($arResult['DISPLAY_PROPERTIES']['EMAIL']['VALUE'] ? array($arResult['DISPLAY_PROPERTIES']['EMAIL']['VALUE']) : array()));

$arResult['DISPLAY_PROPERTIES']['METRO']['VALUE'] = (is_array($arResult['DISPLAY_PROPERTIES']['METRO']['VALUE']) ? $arResult['DISPLAY_PROPERTIES']['METRO']['VALUE'] : ($arResult['DISPLAY_PROPERTIES']['METRO']['VALUE'] ? array($arResult['DISPLAY_PROPERTIES']['METRO']['VALUE']) : array()));

$address = $arResult['NAME'].($arResult['PROPERTIES']['ADDRESS']['VALUE'] ? ', '.$arResult['PROPERTIES']['ADDRESS']['VALUE'] : '');

$arPhotos = array();
$imageID = ($arResult['FIELDS']['DETAIL_PICTURE']['ID'] ? $arResult['FIELDS']['DETAIL_PICTURE']['ID'] : false);
if($imageID){
    $arImage = CFile::ResizeImageGet($imageID, array('width' => 600, 'height' => 600), BX_RESIZE_IMAGE_PROPORTIONAL);
    $arPhotos[] = array(
        'ID' => $arResult['FIELDS']['DETAIL_PICTURE']['ID'],
        'ORIGINAL' => ($arResult['FIELDS']['DETAIL_PICTURE']['SRC'] ? $arResult['FIELDS']['DETAIL_PICTURE']['SRC'] : $arImage),
        'PREVIEW' => $arImage,
        'DESCRIPTION' => (strlen($arResult['FIELDS']['DETAIL_PICTURE']['DESCRIPTION']) ? $arResult['FIELDS']['DETAIL_PICTURE']['DESCRIPTION'] : $address),
    );
}
if(is_array($arResult['DISPLAY_PROPERTIES']['MORE_PHOTOS']['VALUE'])) {
    foreach($arResult['DISPLAY_PROPERTIES']['MORE_PHOTOS']['VALUE'] as $i => $photoID){
        $arPhotos[] = array(
            'ID' => $photoID,
            'ORIGINAL' => CFile::GetPath($photoID),
            'PREVIEW' => CFile::ResizeImageGet($photoID, array('width' => 600, 'height' => 600), BX_RESIZE_IMAGE_PROPORTIONAL),
            'DESCRIPTION' => $arResult['DISPLAY_PROPERTIES']['MORE_PHOTOS']['DESCRIPTION'][$i],
        );
    }
}
?>
<?if($bUseMap):?>
    <?
    $mapLAT = $mapLON = $iCountShops = 0;
    $arPlacemarks = array();

    if($arResult['DISPLAY_PROPERTIES']['MAP']['VALUE']){
        $arCoords = explode(',', $arResult['DISPLAY_PROPERTIES']['MAP']['VALUE']);
        $mapLAT = floatval($arCoords[0]);
        $mapLON = floatval($arCoords[1]);

        $phones = '';
        $arResult['DISPLAY_PROPERTIES']['PHONE']['VALUE'] = (is_array($arResult['DISPLAY_PROPERTIES']['PHONE']['VALUE']) ? $arResult['DISPLAY_PROPERTIES']['PHONE']['VALUE'] : ($arResult['DISPLAY_PROPERTIES']['PHONE']['VALUE'] ? array($arResult['DISPLAY_PROPERTIES']['PHONE']['VALUE']) : array()));
        foreach ($arResult['DISPLAY_PROPERTIES']['PHONE']['VALUE'] as $phone) {
            $phones .= '<div class="value"><a class="dark_link" rel= "nofollow" href="tel:'.str_replace(array(' ', ',', '-', '(', ')'), '', $phone).'">'.$phone.'</a></div>';
        }

        $emails = '';
        $arResult['DISPLAY_PROPERTIES']['EMAIL']['VALUE'] = (is_array($arResult['DISPLAY_PROPERTIES']['EMAIL']['VALUE']) ? $arResult['DISPLAY_PROPERTIES']['EMAIL']['VALUE'] : ($arResult['DISPLAY_PROPERTIES']['EMAIL']['VALUE'] ? array($arResult['DISPLAY_PROPERTIES']['EMAIL']['VALUE']) : array()));
        foreach ($arResult['DISPLAY_PROPERTIES']['EMAIL']['VALUE'] as $email) {
            $emails .= '<a class="dark_link" href="mailto:' .$email. '">' .$email . '</a><br>';
        }

        $metrolist = '';
        $arResult['DISPLAY_PROPERTIES']['METRO']['VALUE'] = (is_array($arResult['DISPLAY_PROPERTIES']['METRO']['VALUE']) ? $arResult['DISPLAY_PROPERTIES']['METRO']['VALUE'] : ($arResult['DISPLAY_PROPERTIES']['METRO']['VALUE'] ? array($arResult['DISPLAY_PROPERTIES']['METRO']['VALUE']) : array()));
        foreach ($arResult['DISPLAY_PROPERTIES']['METRO']['VALUE'] as $metro) {
            $metrolist .= '<div class="metro"><i></i>'. $metro . '</div>';
        }

        $popupOptions = [
            'ITEM' => [
                'NAME' => $address,
                'EMAIL' => $arResult['DISPLAY_PROPERTIES']['EMAIL']['VALUE'],
                'EMAIL_HTML' => $emails,
                'PHONE' => $arResult['DISPLAY_PROPERTIES']['PHONE']['VALUE'],
                'PHONE_HTML' => $phones,
                'METRO' => $arResult['DISPLAY_PROPERTIES']['METRO']['VALUE'],
                'METRO_HTML' => $metrolist,
                'SCHEDULE' => $arResult['DISPLAY_PROPERTIES']['SCHEDULE']['DISPLAY_VALUE'],
                'DISPLAY_PROPERTIES' => [
                    'METRO' => [
                        'NAME' => Loc::getMessage('MYMS_TPL_METRO'),
                    ],
                    'SCHEDULE' => [
                        'NAME' => Loc::getMessage('MYMS_TPL_SCHEDULE'),
                    ],
                    'PHONE' => [
                        'NAME' =>  Loc::getMessage('MYMS_TPL_PHONE'),
                    ],
                    'EMAIL' => [
                        'NAME' => Loc::getMessage('MYMS_TPL_EMAIL'),
                    ]
                ]
            ],
            'PARAMS' => [
                'TITLE' => '',
                'BTN_CLASS' => 'btn btn-transparent',
            ],
            'SHOW_QUESTION_BTN' => 'Y',
            'SHOW_SOCIAL' => 'N',
            'SHOW_CLOSE' => 'N',
            'SHOW_TITLE' => 'N',
        ];

        $arPlacemarks[] = array(
            "LAT" => floatval($arCoords[0]),
            "LON" => floatval($arCoords[1]),
            "TEXT" => TSolution\Functions::getItemMapHtml($popupOptions),
        );
    }
    ?>
<?endif;?>
<div class="contacts-detail shop-detail" itemscope itemtype="http://schema.org/Organization">
	<?//hidden text for validate microdata?>
	<div class="hidden">
		<span itemprop="name"><?=$address?></span>
	</div>

	<div class="contacts__row">
		<div class="contacts__col">
			<div class="contacts__content-wrapper">
                <?/*<div class="contacts-detail__property contacts-detail__property--top">
                    <div class="contact-property contact-property--address">
                        <div class="contact-property__label font_13 color_999"><?=Loc::getMessage('T_CONTACTS_ADDRESS')?></div>
                        <div itemprop="address" class="contact-property__value color_222 font_28"><?=$address?></div>
                    </div>
                </div>
                */?>

                <?//gallery?>
                <?if($arPhotos):?>
                    <?
                    $countSlides = count($arPhotos);
                    $arOptions = [
                        'preloadImages' => false,
                        'lazy' => false,
                        'keyboard' => true,
                        'init' => false,
                        'loop' => false,
                        'countSlides' => $countSlides,
                        'slidesPerView' => 1,
                        'spaceBetween' => 10,
                        'pagination' => [
                            'enabled' => true,
                            'el' => ".contacts-detail__image__pagination",
                        ],
                        'type' => 'contacts_gallery',
                    ];
                    ?>
                    <!-- noindex-->
                        <div class="contacts-detail__image contacts-detail__image--gallery outer-rounded-x swipeignore">                   
                            <div class="text-center gallery-big swiper-nav-offset">
                                <div class="swiper slider-solution outer-rounded-x" data-plugin-options='<?=json_encode($arOptions)?>'>
                                    <div class="swiper-wrapper">
                                        <?foreach($arPhotos as $i => $arPhoto):?>
                                            <div class="swiper-slide">
                                                <a href="<?=$arPhoto['ORIGINAL']?>" class="fancy" data-fancybox="item_slider" target="_blank" title="<?=$arPhoto['DESCRIPTION']?>">
                                                    <div style="background-image:url('<?=$arPhoto['PREVIEW']['src']?>')"></div>
                                                </a>
                                            </div>
                                        <?endforeach;?>
                                    </div>
                                </div>
                                <?if ($arOptions['countSlides'] > 1):?>
                                    <div class="slider-nav swiper-button-next">
                                        <?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/arrows.svg#right-7-12', 'stroke-dark-light', [
                                            'WIDTH' => 7, 
                                            'HEIGHT' => 12
                                        ]); ?>
                                    </div>
                                    <div class="slider-nav swiper-button-prev">
                                        <?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/arrows.svg#left-7-12', 'stroke-dark-light', [
                                            'WIDTH' => 7, 
                                            'HEIGHT' => 12
                                        ]); ?>
                                    </div>
                                    <div class="contacts-detail__image__pagination"></div>
                                <?endif;?>
                            </div>
                        </div>
                    <!-- /noindex-->
                <?endif;?>
                
				<div class="contacts-detail__info">
					<div class="contacts-detail__properties">
                        <?if(
                            $arResult['DISPLAY_PROPERTIES']['METRO']['VALUE'] ||
                            $arResult['DISPLAY_PROPERTIES']['SCHEDULE']['VALUE'] ||
                            $address
                        ):?>
                            <div class="contacts__col">
                                <?if($address):?>
                                    <div class="contacts-detail__property">
                                        <div class="contact-property contact-property--address">
                                            <div class="contact-property__label font_13 color_999"><?=Loc::getMessage('T_CONTACTS_ADDRESS')?></div>
                                            <div  itemprop="address" class="contact-property__value color_222"><?=$address?></div>
                                        </div>
                                    </div>
                                <?endif;?>

                                <?if($arResult['DISPLAY_PROPERTIES']['METRO']['VALUE']):?>
                                    <div class="contacts-detail__property">
                                        <div class="contact-property contact-property--metro">
                                            <div class="contact-property__label font_13 color_999"><?=Loc::getMessage('T_CONTACTS_METRO')?></div>
                                            <?foreach($arResult['DISPLAY_PROPERTIES']['METRO']['VALUE'] as $i => $metro):?>
                                                <div class="contact-property__value color_222"><?=$metro?></div>
                                            <?endforeach;?>
                                        </div>
                                    </div>
                                <?endif;?>

                                <?if($arResult['DISPLAY_PROPERTIES']['SCHEDULE']['VALUE']):?>
                                    <div class="contacts-detail__property">
                                        <div class="contact-property contact-property--schedule">
                                            <div class="contact-property__label font_13 color_999"><?=Loc::getMessage('T_CONTACTS_SCHEDULE')?></div>
                                            <div class="contact-property__value color_222"><?=$arResult['DISPLAY_PROPERTIES']['SCHEDULE']['DISPLAY_VALUE']?></div>
                                        </div>
                                    </div>
                                <?endif;?>
                            </div>
                        <?endif;?>

                        <?if(
                            $arResult['DISPLAY_PROPERTIES']['PHONE']['VALUE'] ||
                            $arResult['DISPLAY_PROPERTIES']['EMAIL']['VALUE']
                        ):?>
                            <div class="contacts__col">
                                <?if($arResult['DISPLAY_PROPERTIES']['PHONE']['VALUE']):?>
                                    <div class="contacts-detail__property">
                                        <div class="contact-property contact-property--phones">
                                            <div class="contact-property__label font_13 color_999"><?=Loc::getMessage('T_CONTACTS_PHONE')?></div>
                                            <?foreach($arResult['DISPLAY_PROPERTIES']['PHONE']['VALUE'] as $i => $phone):?>
                                                <div class="contact-property__value dark_link">
                                                    <a title="<?=htmlspecialcharsbx($arResult['DISPLAY_PROPERTIES']['PHONE']['DESCRIPTION'][$i])?>" href="<?='tel:'.str_replace(array(' ', '-', '(', ')'), '', $phone)?>"><?=$phone?></a>
                                                </div>
                                            <?endforeach;?>
                                        </div>
                                    </div>
                                <?endif;?>

                                <?if($arResult['DISPLAY_PROPERTIES']['EMAIL']['VALUE']):?>
                                    <div class="contacts-detail__property">
                                        <div class="contact-property contact-property--email">
                                            <div class="contact-property__label font_13 color_999"><?=Loc::getMessage('T_CONTACTS_EMAIL')?></div>
                                            <?foreach($arResult['DISPLAY_PROPERTIES']['EMAIL']['VALUE'] as $i => $email):?>
                                                <div class="contact-property__value dark_link">
                                                    <a href="mailto:<?=$email?>"><?=$email?></a>
                                                </div>
                                            <?endforeach;?>
                                        </div>
                                    </div>
                                <?endif;?>
                            </div>
                        <?endif;?>
					</div>
				</div>
				<div class="contacts-detail__social">
					<?$APPLICATION->IncludeComponent(
						"aspro:social.info.lite",
						".default",
						array(
							"CACHE_TYPE" => "A",
							"CACHE_TIME" => "3600000",
							"CACHE_GROUPS" => "N",
							"COMPONENT_TEMPLATE" => ".default",
							'SVG' => false,
							'IMAGES' => true,
							'ICONS' => true,
							'SIZE' => 'large',
							'HIDE_MORE' => false,
						),
						false
					);?>
				</div>
				<div class="contacts-detail__description" itemprop="description">
                    <?if(strlen($arResult['FIELDS']['DETAIL_TEXT'])):?>
                        <div itemprop="description" class="contact-property contact-property--decription">
                            <div class="contact-property__text font_large color_666"><?=$arResult['FIELDS']['DETAIL_TEXT']?></div>
                        </div>
                    <?endif;?>

					<?if($bUseFeedback):?>
						<div class="contacts-detail__btn-wrapper">
						<span>
							<span class="btn btn-default btn-transparent-border bg-theme-target border-theme-target animate-load" data-event="jqm" data-param-id="aspro_lite_question" data-name="question"><?=Loc::getMessage('T_CONTACTS_QUESTION1')?></span>
						</span>
						</div>
					<?endif;?>
				</div>
			</div>
		</div>
        <?if($bUseMap):?>
            <div class="contacts__map-wrapper">
                <div class="sticky-block contacts_map-sticky outer-rounded-x bordered">
                    <?if($typeMap == 'GOOGLE'):?>
                        <?$APPLICATION->IncludeComponent(
                            "bitrix:map.google.view",
                            "map",
                            array(
                                "API_KEY" => \Bitrix\Main\Config\Option::get('fileman', 'google_map_api_key', ''),
                                "INIT_MAP_TYPE" => "ROADMAP",
                                "COMPONENT_TEMPLATE" => "map",
                                "COMPOSITE_FRAME_MODE" => "A",
                                "COMPOSITE_FRAME_TYPE" => "AUTO",
                                "CONTROLS" => array(
                                    0 => "SMALL_ZOOM_CONTROL",
                                    1 => "TYPECONTROL",
                                ),
                                "OPTIONS" => array(
                                    0 => "ENABLE_DBLCLICK_ZOOM",
                                    1 => "ENABLE_DRAGGING",
                                ),
                                "MAP_DATA" => serialize(array("google_lat" => $mapLAT, "google_lon" => $mapLON, "google_scale" => 17, "PLACEMARKS" => $arPlacemarks)),
                                "MAP_HEIGHT" => "550px",
                                "MAP_WIDTH" => "100%",
                                "MAP_ID" => "",
                                "ZOOM_BLOCK" => array(
                                    "POSITION" => "right center",
                                )
                            ),
                            false
                        );?>
                    <?else:?>
                        <?$APPLICATION->IncludeComponent(
                            "bitrix:map.yandex.view",
                            "map",
                            array(
                                "API_KEY" => \Bitrix\Main\Config\Option::get('fileman', 'yandex_map_api_key', ''),
                                "INIT_MAP_TYPE" => "MAP",
                                "COMPONENT_TEMPLATE" => "map",
                                "COMPOSITE_FRAME_MODE" => "A",
                                "COMPOSITE_FRAME_TYPE" => "AUTO",
                                "CONTROLS" => array(
                                    0 => "ZOOM",
                                    1 => "SMALLZOOM",
                                    2 => "TYPECONTROL",
                                ),
                                "OPTIONS" => array(
                                    0 => "ENABLE_DBLCLICK_ZOOM",
                                    1 => "ENABLE_DRAGGING",
                                ),
                                "MAP_DATA" => serialize(array("yandex_lat" => $mapLAT, "yandex_lon" => $mapLON, "yandex_scale" => 17, "PLACEMARKS" => $arPlacemarks)),
                                "MAP_HEIGHT" => "550px",
                                "MAP_WIDTH" => "100%",
                                "MAP_ID" => "",
                                "ZOOM_BLOCK" => array(
                                    "POSITION" => "right center",
                                )
                            ),
                            false
                        );?>
                    <?endif;?>
                </div>
            </div>
        <?endif;?>
	</div>
</div>