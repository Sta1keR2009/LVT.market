<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

$frame = $this->createFrame()->begin();

$bUseMap = TSolution::GetFrontParametrValue('CONTACTS_USE_MAP', SITE_ID) != 'N';
$typeMap = TSolution::GetFrontParametrValue('CONTACTS_TYPE_MAP', SITE_ID);

$mapLAT = $mapLON = $cntMapShops = $cntEmptyShops = 0;
$arPlacemarks = [];

$title = $arParams['MAIN_TITLE'] ?: GetMessage('MAIN_TITLE_DEFAULT');

$htmlContent = $htmlDetailsContent = '';
?>
<?ob_start(); ?>
<?if ($arResult['STORES']):?>
    <?php
    // get shops
    $arShops = [];
    $dbRes = CIBlock::GetList(
        [],
        [
            'CODE' => 'aspro_lite_shops',
            'ACTIVE' => 'Y',
            'SITE_ID' => SITE_ID,
        ]
    );
    if ($arShopsIblock = $dbRes->Fetch()) {
        $dbRes = CIBlockElement::GetList(
            [],
            [
                'ACTIVE' => 'Y',
                'IBLOCK_ID' => $arShopsIblock['ID'],
            ],
            false,
            false,
            [
                'ID',
                'DETAIL_PAGE_URL',
                'PROPERTY_LINK_STORE',
            ]
        );
        while ($arShop = $dbRes->GetNext()) {
            if(is_array($arShop['PROPERTY_LINK_STORE_VALUE'])) {
                foreach($arShop['PROPERTY_LINK_STORE_VALUE'] as $idStore) {
                    $arShops[$idStore] = $arShop;
                }
            } else {
                $arShops[$arShop['PROPERTY_LINK_STORE_VALUE']] = $arShop;
            }
        }
    }
    foreach ($arResult['STORES'] as $pid => $arStore) {
        $amount = (isset($arStore['REAL_AMOUNT']) ? $arStore['REAL_AMOUNT'] : $arStore['AMOUNT']);

        if (
            $amount <= 0
            && $arParams['SHOW_EMPTY_STORE'] == 'N'
        ) {
            ++$cntEmptyShops;
            continue;
        }
    }
?>

    <?if($cntEmptyShops == count($arResult['STORES'])):?>
        <?ShowError(GetMessage('NO_STORES')); ?>
    <?endif; ?>

    <div class="stores-list<?= $arParams['SHOW_GENERAL_STORE_INFORMATION'] === 'Y' ? ' stores-list--general' : ''; ?>">
        <div class="stores-list__items__wrapper">
            <div class="stores-list__items bordered">
                <div class="stores-list__items__inner scrollbar">
                    <?foreach ($arResult['STORES'] as $pid => $arStore):?>
                        <?php
                        $amount = (isset($arStore['REAL_AMOUNT']) ? $arStore['REAL_AMOUNT'] : $arStore['AMOUNT']);
                        $amount = TSolution\Product\Quantity::getPositiveAmount($amount);

                        if($arParams['SHOW_EMPTY_STORE'] === 'N' && intval($amount) <= 0) {
                            continue;
                        }

                        $arStatus = TSolution\Product\Quantity::getStatus([
                            'ITEM' => [
                                'ID' => $arParams['ELEMENT_ID'],
                            ],
                            'PARAMS' => [
                                'SHOW_AMOUNT' => true,
                            ],
                            'TOTAL_COUNT' => $amount,
                        ]);
                        $status = $arStatus['NAME'];
                        $statusCode = $arStatus['CODE'];

                        if (
                            $arParams['USE_MIN_AMOUNT'] == 'Y'
                            && intval($arParams['MIN_AMOUNT']) > 0 && intval($amount) > 0
                        ) {
                            $status = $amount > intval($arParams['MIN_AMOUNT']) ? GetMessage('S_MANY') : GetMessage('S_FEW');
                        }

                        if ($arParams['SHOW_GENERAL_STORE_INFORMATION'] === 'Y') {
                            $arStore['ADDRESS'] = GetMessage('S_GENERAL');
                        } elseif (isset($arStore['TITLE'])) {
                            if (
                                $arParams['FIELDS']
                                && (
                                    in_array('TITLE', $arParams['FIELDS'])
                                    || in_array('ADDRESS', $arParams['FIELDS'])
                                )
                            ) {
                                $setTitle = in_array('TITLE', $arParams['FIELDS']) && strlen($arStore['TITLE']);
                                $setAddress = in_array('ADDRESS', $arParams['FIELDS']) && strlen($arStore['ADDRESS']);
                                $storeName = ($setTitle ? $arStore['TITLE'] : '');
                                $storeName .= $setTitle && $setAddress ? ', ' : '';
                                $storeName .= ($setAddress ? $arStore['ADDRESS'] : '');
                            } else {
                                $storeName = $arStore['TITLE'].(strlen($arStore['ADDRESS']) && strlen($arStore['TITLE']) ? ', ' : '').$arStore['ADDRESS'];
                            }

                            $arStore['ADDRESS'] = $storeName;
                        }

                        $phones = '';
                        $arStore['PHONE'] = (is_array($arStore['PHONE']) ? $arStore['PHONE'] : ($arStore['PHONE'] ? [$arStore['PHONE']] : []));
                        foreach ($arStore['PHONE'] as $phone) {
                            $phones .= '<div class="value"><a class="dark_link" rel= "nofollow" href="tel:'.str_replace([' ', ',', '-', '(', ')'], '', $phone).'">'.$phone.'</a></div>';
                        }

                        $emails = '';
                        $arStore['EMAIL'] = (is_array($arStore['EMAIL']) ? $arStore['EMAIL'] : ($arStore['EMAIL'] ? [$arStore['EMAIL']] : []));
                        foreach ($arStore['EMAIL'] as $email) {
                            $emails .= '<a class="dark_link" rel= "nofollow" href="mailto:'.$email.'">'.$email.'</a><br>';
                        }

                        $metrolist = '';
                        $arStore['METRO'] = (is_array($arStore['METRO']) ? $arStore['METRO'] : ($arStore['METRO'] ? [$arStore['METRO']] : []));
                        foreach ($arStore['METRO'] as $metro) {
                            $metrolist .= '<div class="metro"><i></i>'.$metro.'</div>';
                        }

                        if (TSolution::GetFrontParametrValue('STORES_SOURCE', SITE_ID) == 'IBLOCK') {
                            $arStore['URL'] = $arShops[$arStore['ID']]['DETAIL_PAGE_URL'];
                        }

                        if (
                            $bUseMap
                            && $bHasCoords = ($arStore['GPS_N'] && $arStore['GPS_S'])
                        ) {
                            $mapLAT += $arStore['GPS_N'];
                            $mapLON += $arStore['GPS_S'];

                            $popupOptions = [
                                'ITEM' => [
                                    'NAME' => $arStore['ADDRESS'],
                                    'URL' => $arStore['URL'],
                                    'EMAIL' => $arStore['EMAIL'],
                                    'EMAIL_HTML' => $emails,
                                    'PHONE' => $arStore['PHONE'],
                                    'PHONE_HTML' => $phones,
                                    'METRO' => $arStore['METRO'],
                                    'METRO_HTML' => $metrolist,
                                    'SCHEDULE' => $arStore['SCHEDULE'],
                                    'DISPLAY_PROPERTIES' => [
                                        'METRO' => [
                                            'NAME' => GetMessage('MYMS_TPL_METRO'),
                                        ],
                                        'SCHEDULE' => [
                                            'NAME' => GetMessage('MYMS_TPL_SCHEDULE'),
                                        ],
                                        'PHONE' => [
                                            'NAME' => GetMessage('MYMS_TPL_PHONE'),
                                        ],
                                        'EMAIL' => [
                                            'NAME' => GetMessage('MYMS_TPL_EMAIL'),
                                        ],
                                    ],
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

                            $arPlacemarks[] = [
                                'LAT' => $arStore['GPS_N'],
                                'LON' => $arStore['GPS_S'],
                                'TEXT' => TSolution\Functions::getItemMapHtml($popupOptions),
                            ];

                            ++$cntMapShops;
                        }
                        ?>
                        <div class="stores-list__item color-theme-parent-all<?= $bHasCoords ? ' show_on_map' : ''; ?>" data-coordinates="<?= $bHasCoords ? $arStore['GPS_N'].','.$arStore['GPS_S'] : ''; ?>">
                            <div class="stores-list__item__inner" >
                                <div class="stores-list__item__line line-block line-block--24 line-block--align-flex-start flexbox--justify-beetwen">
                                    <div class="line-block__item">
                                        <?if ($arStore['URL']):?>
                                            <a class="stores-list__item-title dark_link switcher-title color-theme-target font_16" href="<?=$arStore['URL']; ?>"><?=$arStore['ADDRESS']; ?></a>
                                        <?else:?>
                                            <div class="stores-list__item-title dark_link switcher-title color-theme-target font_16"><?=$arStore['ADDRESS']; ?></div>
                                        <?endif; ?>
                                    </div>
                                    <div class="line-block__item">
                                        <span class="status-icon font_14 <?=$statusCode; ?>"><?=$status; ?></span>
                                    </div>
                                </div>

                                <?if (
                                    strlen($arStore['SCHEDULE'])
                                    || $arStore['PHONE']
                                    || $arStore['EMAIL']
                                    || $arStore['METRO']
                                ):?>
                                    <div>
                                        <div class="line-block line-block--4-vertical line-block--align-normal flexbox--direction-column">
                                            <?if (
                                                strlen($arStore['SCHEDULE'])
                                                || $arStore['PHONE']
                                            ):?>
                                                <div class="line-block__item">
                                                    <div class="stores-list__item__line line-block line-block--6 line-block--6-vertical line-block--align-flex-start line-block--flex-wrap">
                                                        <?if (strlen($arStore['SCHEDULE'])):?>
                                                            <div class="line-block__item">
                                                                <div class="stores-list__item-schedule rounded-x font_13 color_222"><?=$arStore['SCHEDULE']; ?></div>
                                                            </div>
                                                        <?endif; ?>
                                                        <?if ($arStore['PHONE']):?>
                                                            <?foreach($arStore['PHONE'] as $phone):?>
                                                                <div class="line-block__item">
                                                                    <div class="stores-list__item-phone rounded-x font_13 color_222">
                                                                        <a class="dark_link" rel="nofollow" href="tel:<?=str_replace([' ', ',', '-', '(', ')'], '', $phone); ?>"><?=$phone; ?></a>
                                                                    </div>
                                                                </div>
                                                            <?endforeach; ?>
                                                        <?endif; ?>
                                                    </div>
                                                </div>
                                            <?endif; ?>

                                            <?if (
                                                $arStore['EMAIL']
                                                || $arStore['METRO']
                                            ):?>
                                                <div class="line-block__item">
                                                    <div class="stores-list__item__line line-block line-block--6 line-block--6-vertical line-block--align-flex-start line-block--flex-wrap">
                                                        <?if ($arStore['EMAIL']):?>
                                                            <div class="line-block__item">
                                                                <div class="stores-list__item-email rounded-x font_13 color_222">
                                                                    <a class="dark_link" rel="nofollow" href="mailto:<?=$arStore['EMAIL'][0]; ?>"><?=$arStore['EMAIL'][0]; ?>
                                                                </a></div>
                                                            </div>
                                                        <?endif; ?>
                                                        <?if ($arStore['METRO']):?>
                                                            <div class="line-block__item">
                                                                <div class="stores-list__item-metro rounded-x color_222"><?=TSolution::showSpriteIconSvg(
                                                                    SITE_TEMPLATE_PATH.'/images/svg/map_icons.svg#metro-14-10',
                                                                    '',
                                                                    [
                                                                        'WIDTH' => 14,
                                                                        'HEIGHT' => 10,
                                                                    ]
                                                                ); ?><span class="font_12"><?=$arStore['METRO'][0]; ?></span></div>
                                                            </div>
                                                        <?endif; ?>
                                                    </div>
                                                </div>
                                            <?endif; ?>
                                        </div>
                                    </div>
                                <?endif; ?>
                            </div>
                        </div>

                        <?if($arParams['SHOW_GENERAL_STORE_INFORMATION'] !== 'Y'):?>
                        <?ob_start(); ?>
                        <div class="stores-list__detail">
                            <div class="stores-list__detail__inner">
                                <span class="stores-list__detail-close fill-theme-hover fill-use-svg-999"><?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/header_icons.svg#close-14-14', '', ['WIDTH' => 14, 'HEIGHT' => 14]); ?></span>

                                <div class="stores-list__detail-title">
                                    <span class="color_222 font_20"><?=$arStore['ADDRESS']; ?></span>
                                </div>

                                <div class="status-icon font_14 <?=$statusCode; ?>"><?=$status; ?></div>

                                <?if (strlen($arStore['SCHEDULE'])):?>
                                    <div class="stores-list__detail-subtitle font_14 color_999"><?=GetMessage('S_SCHEDULE'); ?></div>
                                    <div class="stores-list__detail-schedule color_222"><?=$arStore['SCHEDULE']; ?></div>
                                <?endif; ?>

                                <?if ($arStore['PHONE']):?>
                                    <div class="stores-list__detail-subtitle font_14 color_999"><?=GetMessage('S_PHONE'); ?></div>
                                    <div class="stores-list__detail-phone color_222">
                                        <?foreach($arStore['PHONE'] as $i => $phone):?>
                                            <?if ($i):?>
                                                <br />
                                            <?endif; ?>
                                            <a class="dark_link" rel="nofollow" href="tel:<?=str_replace([' ', ',', '-', '(', ')'], '', $phone); ?>"><?=$phone; ?></a>
                                        <?endforeach; ?>
                                    </div>
                                <?endif; ?>

                                <?if ($arStore['EMAIL']):?>
                                    <div class="stores-list__detail-subtitle font_14 color_999"><?=GetMessage('S_EMAIL'); ?></div>
                                    <div class="stores-list__detail-email color_222">
                                        <a class="dark_link" rel="nofollow" href="mailto:<?=$arStore['EMAIL'][0]; ?>"><?=$arStore['EMAIL'][0]; ?></a>
                                    </div>
                                <?endif; ?>

                                <?if ($arStore['METRO']):?>
                                    <div class="stores-list__detail-subtitle font_14 color_999"><?=GetMessage('S_METRO'); ?></div>
                                    <div class="stores-list__detail-metro color_222"><?=$arStore['METRO'][0]; ?></div>
                                <?endif; ?>

                                <?if ($arStore['URL']):?>
                                    <div class="stores-list__detail-buttons">
                                        <a class="btn btn-transparent" href="<?=$arStore['URL']; ?>"><?=GetMessage('S_GOTO_STORE_DETAIL'); ?></a>
                                    </div>
                                <?endif; ?>
                            </div>
                        </div>
                        <?php
                        $htmlDetailsContent .= ob_get_contents();
                            ob_end_clean();
                            ?>
                        <?endif; ?>
                    <?endforeach; ?>
                </div>
            </div>
        </div>

        <?if($arParams['SHOW_GENERAL_STORE_INFORMATION'] !== 'Y'):?>
            <div class="stores-list__details__wrapper">
                <div class="stores-list__details bordered">
                    <div class="stores-list__details__inner scrollbar">
                        <?=$htmlDetailsContent; ?>
                    </div>
                </div>
            </div>
        <?endif; ?>

        <?if ($cntMapShops):?>
            <?php
            $mapLAT = floatval($mapLAT / $cntMapShops);
            $mapLON = floatval($mapLON / $cntMapShops);
            ?>
            <div class="stores-list__map__wrapper">
                <div class="stores-list__map bordered">
                    <div class="stores-list__map__inner">
                        <?if($typeMap == 'GOOGLE'):?>
                            <?$APPLICATION->IncludeComponent(
                                'bitrix:map.google.view',
                                'map',
                                [
                                    'API_KEY' => Bitrix\Main\Config\Option::get('fileman', 'google_map_api_key', ''),
                                    'INIT_MAP_TYPE' => 'ROADMAP',
                                    'COMPONENT_TEMPLATE' => 'map',
                                    'COMPOSITE_FRAME_MODE' => 'A',
                                    'COMPOSITE_FRAME_TYPE' => 'AUTO',
                                    'CONTROLS' => [
                                        0 => 'SMALL_ZOOM_CONTROL',
                                        1 => 'TYPECONTROL',
                                    ],
                                    'OPTIONS' => [
                                        0 => 'ENABLE_DBLCLICK_ZOOM',
                                        1 => 'ENABLE_DRAGGING',
                                    ],
                                    'MAP_DATA' => serialize(['google_lat' => $mapLAT, 'google_lon' => $mapLON, 'google_scale' => 17, 'PLACEMARKS' => $arPlacemarks]),
                                    'MAP_WIDTH' => '100%',
                                    'MAP_HEIGHT' => '500',
                                    'MAP_ID' => '',
                                    'ZOOM_BLOCK' => [
                                        'POSITION' => 'right center',
                                    ],
                                ],
                                false
                            ); ?>
                        <?else:?>
                            <?$APPLICATION->IncludeComponent(
                                'bitrix:map.yandex.view',
                                'map',
                                [
                                    'API_KEY' => Bitrix\Main\Config\Option::get('fileman', 'yandex_map_api_key', ''),
                                    'INIT_MAP_TYPE' => 'MAP',
                                    'COMPONENT_TEMPLATE' => 'map',
                                    'COMPOSITE_FRAME_MODE' => 'A',
                                    'COMPOSITE_FRAME_TYPE' => 'AUTO',
                                    'CONTROLS' => [
                                        0 => 'ZOOM',
                                        1 => 'SMALLZOOM',
                                        2 => 'TYPECONTROL',
                                    ],
                                    'OPTIONS' => [
                                        0 => 'ENABLE_DBLCLICK_ZOOM',
                                        1 => 'ENABLE_DRAGGING',
                                    ],
                                    'MAP_DATA' => serialize(['yandex_lat' => $mapLAT, 'yandex_lon' => $mapLON, 'yandex_scale' => 17, 'PLACEMARKS' => $arPlacemarks]),
                                    'MAP_WIDTH' => '100%',
                                    'MAP_HEIGHT' => '100%',
                                    'MAP_ID' => '',
                                    'ZOOM_BLOCK' => [
                                        'POSITION' => 'right center',
                                    ],
                                ],
                                false
                            ); ?>
                        <?endif; ?>
                    </div>
                </div>
            </div>
        <?endif; ?>
    </div>
<?else:?>
    <?ShowError(GetMessage('NO_STORES')); ?>
<?endif; ?>
<?php
$htmlContent = ob_get_contents();
ob_end_clean();
?>
<div class="form popup stores_block_wrap<?= $cntMapShops ? ' has_map' : ''; ?>">
    <div class="form-header">
        <div class="text">
            <div class="title switcher-title font_24 color_222"><?=$title; ?></div>
        </div>
    </div>

    <div class="form-body">
        <?if (strlen($arResult['ERROR_MESSAGE'])):?>
            <?ShowError($arResult['ERROR_MESSAGE']); ?>
        <?endif; ?>

        <?=$htmlContent; ?>
    </div>
</div>
