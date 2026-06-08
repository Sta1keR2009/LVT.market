<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);

global $arTheme;
use \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Web\Json;
\Bitrix\Main\UI\Extension::load('ajax');
\Bitrix\Main\UI\Extension::load('fx');
\Bitrix\Main\UI\Extension::load('currency');
$bOrderViewBasket = $arParams['ORDER_VIEW'];
$basketURL = (strlen(trim($arTheme['ORDER_VIEW']['DEPENDENT_PARAMS']['URL_BASKET_SECTION']['VALUE'])) ? trim($arTheme['ORDER_VIEW']['DEPENDENT_PARAMS']['URL_BASKET_SECTION']['VALUE']) : '');
$dataItem = TSolution::getDataItem($arResult);
$bOrderButton = $arResult['PROPERTIES']['FORM_ORDER']['VALUE_XML_ID'] == 'YES';
$bAskButton = $arResult['PROPERTIES']['FORM_QUESTION']['VALUE_XML_ID'] == 'YES';
$bOcbButton = $arParams['SHOW_ONE_CLINK_BUY'] != 'N';
$bGallerythumbVertical = $arParams['GALLERY_THUMB_POSITION'] === 'vertical';
$cntVisibleChars = $arParams['VISIBLE_PROP_COUNT'] >= 0 ? $arParams['VISIBLE_PROP_COUNT'] : 6;

$bShowRating = $arParams['SHOW_RATING'] == 'Y';
$bShowCompare = $arParams['DISPLAY_COMPARE'] == 'Y';
$bShowFavorit = $arParams['SHOW_FAVORITE'] == 'Y';
$bUseShare = $arParams['USE_SHARE'] == 'Y';
$bShowSendGift = $arParams['SHOW_SEND_GIFT'] === 'Y';
$bShowCheaperForm = $arParams['SHOW_CHEAPER_FORM'] === 'Y';
$bShowReview = $arParams['SHOW_REVIEW'] !== 'N';
$bPopupVideo = !!$arResult['POPUP_VIDEO'];
$bShowCalculateDelivery = $arParams["CALCULATE_DELIVERY"] === 'Y';
$bShowSKUDescription = $arParams["SHOW_SKU_DESCRIPTION"] === 'Y';

$templateData["USE_OFFERS_SELECT"] = false;

// $topGalleryClassList = " detail-gallery-big--".$arResult['GALLERY_SIZE'];
$topGalleryClassList = " detail-gallery-big--".($bGallerythumbVertical ? 'vertical' : 'horizontal');
if ($bPopupVideo) {
    $topGalleryClassList .= " detail-gallery-big--with-video";
}

$arSkuTemplateData = [];
$bSKU2 = $arParams['TYPE_SKU'] === 'TYPE_2';
$bShowSkuProps = !$bSKU2;

$arSKUSetsData = [];
if ($arResult['SKU']['SKU_GROUP']) {
    $arSKUSetsData = [
        'IBLOCK_ID' => $arResult['SKU']['CURRENT']['IBLOCK_ID'],
        'ITEMS' => $arResult['SKU']['SKU_GROUP_VALUES'],
        'CURRENT_ID' => $arResult['SKU']['CURRENT']['ID']
    ];
}

$bCrossAssociated = isset($arParams["CROSS_LINK_ITEMS"]["ASSOCIATED"]["VALUE"]) && !empty($arParams["CROSS_LINK_ITEMS"]["ASSOCIATED"]["VALUE"]);
$bCrossExpandables = isset($arParams["CROSS_LINK_ITEMS"]["EXPANDABLES"]["VALUE"]) && !empty($arParams["CROSS_LINK_ITEMS"]["EXPANDABLES"]["VALUE"]);

/*set array props for component_epilog*/
$templateData = array(
    'DETAIL_PAGE_URL' => $arResult['DETAIL_PAGE_URL'],
    'INCLUDE_FOLDER_PATH' => $arResult['INCLUDE_FOLDER_PATH'],
    'ORDER' => $bOrderViewBasket,
    'TIZERS' => array(
        'IBLOCK_ID' => $arParams['IBLOCK_TIZERS_ID'],
        'VALUE' => $arResult['TIZERS'],
    ),
    'SALE' => TSolution\Functions::getCrossLinkedItems($arResult, array('LINK_SALE'), array('LINK_GOODS', 'LINK_GOODS_FILTER'), $arParams),
    'ARTICLES' => TSolution\Functions::getCrossLinkedItems($arResult, array('LINK_ARTICLES'), array('LINK_GOODS', 'LINK_GOODS_FILTER'), $arParams),
    'SERVICES' => TSolution\Functions::getCrossLinkedItems($arResult, array('SERVICES'), array('LINK_GOODS', 'LINK_GOODS_FILTER'), $arParams),
    'FAQ' => TSolution\Functions::getCrossLinkedItems($arResult, array('LINK_FAQ')),
    'ASSOCIATED' => $arParams["USE_ASSOCIATED_CROSS"] ? [] : TSolution\Functions::getCrossLinkedItems($arResult, array('ASSOCIATED', 'ASSOCIATED_FILTER')),
    'EXPANDABLES' => $arParams["USE_EXPANDABLES_CROSS"] ? [] : TSolution\Functions::getCrossLinkedItems($arResult, array('EXPANDABLES', 'EXPANDABLES_FILTER')),
    'CATALOG_SETS' => [
        'SET_ITEMS' => $arResult['SET_ITEMS'],
        'SKU_SETS' => $arSKUSetsData,
    ],
    'POPUP_VIDEO' => $bPopupVideo,
    'RATING' => floatval($arResult['PROPERTIES']['EXTENDED_REVIEWS_RAITING'] ? $arResult['PROPERTIES']['EXTENDED_REVIEWS_RAITING']['VALUE'] : 0),
    'USE_SHARE' => $arParams['USE_SHARE'] === 'Y',
    'SHOW_REVIEW' => $bShowReview,
    'CALCULATE_DELIVERY' => $bShowCalculateDelivery,
    'BRAND' => $arResult['BRAND_ITEM'],
    'OFFERS_INFO' => [],
    'SHOW_DISCOUNT_COUNTER' => (
        $arParams["SHOW_DISCOUNT_TIME"] === "Y"
        && $arParams['SHOW_DISCOUNT_TIME_IN_LIST'] !== 'N'
    ),
    'SHOW_CHARACTERISTICS' => false,
);  
?>
<style>
.store-buy-block .counter {
    background: #163760;
}
</style>
<?if (TSolution::isSaleMode()):?>
    <div class="basket_props_block" id="bx_basket_div_<?=$arResult["ID"];?>" style="display: none;">
        <?if (!empty($arResult['PRODUCT_PROPERTIES_FILL'])):?>
            <?foreach ($arResult['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo):?>
                <input type="hidden" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']; ?>[<?=$propID;?>]" value="<?=htmlspecialcharsbx($propInfo['ID']);?>">
                <?
                if (isset($arResult['PRODUCT_PROPERTIES'][$propID])){
                    unset($arResult['PRODUCT_PROPERTIES'][$propID]);
                }
                ?>
            <?endforeach;?>
        <?endif;?>
        <?if ($arResult['PRODUCT_PROPERTIES']):?>
            <div class="wrapper">
                <?foreach($arResult['PRODUCT_PROPERTIES'] as $propID => $propInfo):?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group fill-animate">
                                <?if(
                                    'L' == $arResult['PROPERTIES'][$propID]['PROPERTY_TYPE'] &&
                                    'C' == $arResult['PROPERTIES'][$propID]['LIST_TYPE']
                                ):?>
                                    <label class="font_14"><span><?=$arResult['PROPERTIES'][$propID]['NAME']?></span></label>
                                    <?foreach($propInfo['VALUES'] as $valueID => $value):?>
                                        <div class="form-radiobox">
                                            <label class="form-radiobox__label">
                                                <input class="form-radiobox__input" type="radio" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propID?>]" value="<?=$valueID?>">
                                                <span class="bx_filter_input_checkbox">
                                                    <span><?=$value?></span>
                                                </span>
                                                <span class="form-radiobox__box"></span>
                                            </label>
                                        </div>
                                    <?endforeach;?>
                                <?else:?>
                                    <label class="font_14"><span><?=$arResult['PROPERTIES'][$propID]['NAME']?></span></label>
                                    <div class="input">
                                        <select class="form-control" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propID?>]">
                                            <?foreach($propInfo['VALUES'] as $valueID => $value):?>
                                                <option value="<?=$valueID?>" <?=($valueID == $propInfo['SELECTED'] ? '"selected"' : '')?>><?=$value?></option>
                                            <?endforeach;?>
                                        </select>
                                    </div>
                                <?endif;?>
                            </div>
                        </div>
                    </div>
                <?endforeach;?>
            </div>
        <?endif;?>
    </div>
<?endif;?>

<?// top banner?>
<?$templateData['SECTION_BNR_CONTENT'] = isset($arResult['PROPERTIES']['BNR_TOP']) && $arResult['PROPERTIES']['BNR_TOP']['VALUE_XML_ID'] == 'YES';?>
<?if($templateData['SECTION_BNR_CONTENT']):?>
    <?
    $templateData['SECTION_BNR_UNDER_HEADER'] = $arResult['PROPERTIES']['BNR_TOP_UNDER_HEADER']['VALUE_XML_ID'];
    $templateData['SECTION_BNR_COLOR'] = $arResult['PROPERTIES']['BNR_TOP_COLOR']['VALUE_XML_ID'];
    $atrTitle = $arResult['PROPERTIES']['BNR_TOP_BG']['DESCRIPTION'] ?: $arResult['PROPERTIES']['BNR_TOP_BG']['TITLE'] ?: $arResult['NAME'];
    $atrAlt = $arResult['PROPERTIES']['BNR_TOP_BG']['DESCRIPTION'] ?: $arResult['PROPERTIES']['BNR_TOP_BG']['ALT'] ?: $arResult['NAME'];

    //buttons
    $bannerButtons = [
        [
            'TITLE' => $arResult['PROPERTIES']['BUTTON1TEXT']['VALUE'] ?? '',
            'CLASS' => 'btn choise '.($arResult['PROPERTIES']['BUTTON1CLASS']['VALUE_XML_ID'] ?? 'btn-default').' '.($arResult['PROPERTIES']['BUTTON1COLOR']['VALUE_XML_ID'] ?? ''),
            'ATTR' => [
                ($arResult['PROPERTIES']['BUTTON1TARGET']['VALUE_XML_ID'] === 'scroll' || !$arResult['PROPERTIES']['BUTTON1TARGET']['VALUE_XML_ID']
                    ? 'data-block=".right_block .detail"'
                    : 'target="'.$arResult['PROPERTIES']['BUTTON1TARGET']['VALUE_XML_ID'].'"')
            ],
            'LINK' => $arResult['PROPERTIES']['BUTTON1LINK']['VALUE'],
            'TYPE' => $arResult['PROPERTIES']['BUTTON1TARGET']['VALUE_XML_ID'] === 'scroll' || !$arResult['PROPERTIES']['BUTTON1TARGET']['VALUE_XML_ID']
                ? 'anchor'
                : 'link'
        ]
    ];

    if( $arResult['PROPERTIES']['BUTTON2TEXT']['VALUE'] && $arResult['PROPERTIES']['BUTTON2LINK']['VALUE'] ){
        $bannerButtons[] = [
            'TITLE' => $arResult['PROPERTIES']['BUTTON2TEXT']['VALUE'],
            'CLASS' => 'btn choise '.($arResult['PROPERTIES']['BUTTON2CLASS']['VALUE_XML_ID'] ?? 'btn-default').' '.($arResult['PROPERTIES']['BUTTON2COLOR']['VALUE_XML_ID'] ?? ''),
            'ATTR' => [
                ($arResult['PROPERTIES']['BUTTON2TARGET']['VALUE_XML_ID'] ? 'target="'.$arResult['PROPERTIES']['BUTTON2TARGET']['VALUE_XML_ID'].'"' : '')
            ],
            'LINK' => $arResult['PROPERTIES']['BUTTON2LINK']['VALUE'],
            'TYPE' => 'link',
        ];
    }
    ?>
    <?$this->SetViewTarget('section_bnr_content');?>
        <?TSolution\Functions::showBlockHtml(array(
            'FILE' => '/images/detail_banner.php',
            'PARAMS' => array(
                'TITLE' => $arResult['NAME'],
                'COLOR' => $templateData['SECTION_BNR_COLOR'],
                'TEXT' => array(
                    'TOP' => $arResult['SECTION'] ? reset($arResult['SECTION']['PATH'])['NAME'] : '',
                    'PREVIEW' => array(
                        'TYPE' => $arResult['PREVIEW_TEXT_TYPE'],
                        'VALUE' => $arResult['PREVIEW_TEXT'],
                    )
                ),
                'PICTURES' => array(
                    'BG' => CFile::GetFileArray($arResult['PROPERTIES']['BNR_TOP_BG']['VALUE']),
                    'IMG' => CFile::GetFileArray($arResult['PROPERTIES']['BNR_TOP_IMG']['VALUE']),
                ),
                'BUTTONS' => $bannerButtons,
                'ATTR' => array(
                    'ALT' => $atrAlt,
                    'TITLE' => $atrTitle,
                ),
                'TOP_IMG' => $bTopImg
            ),
        ));?>
    <?$this->EndViewTarget();?>
<?endif;?>

<?
$article = $arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'];

//unset($arResult['OFFERS']); // get correct totalCount
$totalCount = TSolution\Product\Quantity::getTotalCount([
    'ITEM' => $arResult,
    'PARAMS' => $arParams
]);
$arStatus = TSolution\Product\Quantity::getStatus([
    'ITEM' => $arResult,
    'PARAMS' => $arParams,
    'TOTAL_COUNT' => $totalCount,
    'IS_DETAIL' => true,
]);

/* sku replace start */
$arCurrentOffer = $arResult['SKU']['CURRENT'];
$elementName = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arResult['NAME'];
$bShowSelectOffer = $arCurrentOffer && $bShowSkuProps;

if ($bShowSelectOffer) {
    $arResult['PARENT_IMG'] = '';
    if ($arResult['PREVIEW_PICTURE']) {
        $arResult['PARENT_IMG'] = $arResult['PREVIEW_PICTURE'];
    } elseif ($arResult['DETAIL_PICTURE']) {
        $arResult['PARENT_IMG'] = $arResult['DETAIL_PICTURE'];
    }

    $arResult['DETAIL_PAGE_URL'] = $arCurrentOffer['DETAIL_PAGE_URL'];

    if ($arCurrentOffer['GALLERY']) {
        $arResult['GALLERY'] = array_merge($arCurrentOffer['GALLERY'], $arResult['GALLERY']);
    }

    if ($arCurrentOffer["DISPLAY_PROPERTIES"]["CML2_ARTICLE"]["VALUE"] || $arCurrentOffer["DISPLAY_PROPERTIES"]["ARTICLE"]["VALUE"]) {
        $article = $arCurrentOffer['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] ?? $arCurrentOffer["DISPLAY_PROPERTIES"]["ARTICLE"]["VALUE"];
    }

    $arResult["DISPLAY_PROPERTIES"]["FORM_ORDER"] = $arCurrentOffer["DISPLAY_PROPERTIES"]["FORM_ORDER"];
    $arResult["DISPLAY_PROPERTIES"]["PRICE"] = $arCurrentOffer["DISPLAY_PROPERTIES"]["PRICE"];

    if($arParams["SET_SKU_TITLE"] === "Y" && isset($arCurrentOffer['NAME'])){
         $arResult["NAME"] = $elementName = $arCurrentOffer['NAME'];
    }

    $arResult['OFFER_PROP'] = TSolution::PrepareItemProps($arCurrentOffer['DISPLAY_PROPERTIES']);

    $dataItem = TSolution::getDataItem($arCurrentOffer);

    $totalCount = TSolution\Product\Quantity::getTotalCount([
        'ITEM' => $arCurrentOffer,
        'PARAMS' => $arParams
    ]);
    $arStatus = TSolution\Product\Quantity::getStatus([
        'ITEM' => $arCurrentOffer,
        'PARAMS' => $arParams,
        'TOTAL_COUNT' => $totalCount,
        'IS_DETAIL' => true,
    ]);
}

$status = $arStatus['NAME'];
$statusCode = $arStatus['CODE'];
$bShowAdditionalBlock = strlen($status) || $bShowCheaperForm || $bShowSendGift || $bShowCalculateDelivery || trim(strip_tags($arResult['INCLUDE_CONTENT']));
/* sku replace end */
?>
		<div><h1 class="font_32 switcher-title js-popup-title font_20--to-600"><?=$elementName;?></h1></div>

<?// detail description?>
<?$bSKUDescription = $bShowSKUDescription && strlen($arResult['SKU']['CURRENT']['DETAIL_TEXT']);?>
<?$templateData['DETAIL_TEXT'] = boolval(strlen($arResult['DETAIL_TEXT']) || $bSKUDescription);?>

<?$this->SetViewTarget('PRODUCT_DETAIL_TEXT_INFO');?>
    <div class="content content--max-width js-detail-description" itemprop="description">
        <?if ($templateData['DETAIL_TEXT']):?>
            <?if ($bSKUDescription):?>
                <?=$arResult['SKU']['CURRENT']['DETAIL_TEXT'];?>
            <?else:?>
                <?=$arResult['DETAIL_TEXT'];?>
            <?endif;?>
        <?endif;?>
    </div>
<?$this->EndViewTarget();?>

<?// big gallery?>
<?$templateData['BIG_GALLERY'] = boolval($arResult['BIG_GALLERY']);?>
<?if($arResult['BIG_GALLERY']):?>
    <?$this->SetViewTarget('PRODUCT_BIG_GALLERY_INFO');?>
        <?
        $arGallery = array_map(function($array){
            return [
                'src' => $array['DETAIL']['SRC'],
                'preview' => $array['PREVIEW']['src'],
                'alt' => $array['ALT'],
                'title' => $array['TITLE']
            ];
        }, $arResult['BIG_GALLERY']);
        ?>
        <?= TSolution\Functions::showGallery($arGallery, [
            'CONTAINER_CLASS' => 'gallery-detail font_24',
        ]); ?>
    <?$this->EndViewTarget();?>
<?endif;?>

<?// video?>
<?$templateData['VIDEO'] = boolval($arResult['VIDEO']);
$bOneVideo = count((array)$arResult['VIDEO']) == 1;
?>
<?if($arResult['VIDEO']):?>
    <?$this->SetViewTarget('PRODUCT_VIDEO_INFO');?>
        <?TSolution\Functions::showBlockHtml([
            'FILE' => 'video/detail_video_block.php',
            'PARAMS' => [
                'VIDEO' => $arResult['VIDEO'],
            ],
        ])?>
    <?$this->EndViewTarget();?>
<?endif;?>

<?// ask question?>
<?if($bAskButton):?>
    <?if ($bHideLeftBlock = ($arParams['LEFT_BLOCK_CATALOG_DETAIL'] === 'N')):?>
        <?$this->SetViewTarget('PRODUCT_SIDE_INFO');?>
    <?else:?>
        <?$this->SetViewTarget('under_sidebar_content');?>
    <?endif;?>
        <div class="ask-block bordered rounded-4<?=$bHideLeftBlock ? '' : ' visible-by-block-presence__condition';?>">
            <div class="ask-block__container">
                <div class="ask-block__icon">
                    <?=TSolution::showIconSvg('ask colored', SITE_TEMPLATE_PATH.'/images/svg/Question_lg.svg');?>
                </div>
                <div class="ask-block__text text-block color_666 font_14">
                    <?=$arResult['INCLUDE_ASK']?>
                </div>
                <div class="ask-block__button">
                    <div class="btn btn-default btn-transparent-bg animate-load" data-event="jqm" data-param-id="<?=TSolution::getFormID(VENDOR_PARTNER_NAME."_".VENDOR_SOLUTION_NAME."_question");?>" data-autoload-need_product="<?=TSolution::formatJsName($arResult['NAME'])?>" data-name="question">
                        <span><?=(strlen($arParams['S_ASK_QUESTION']) ? $arParams['S_ASK_QUESTION'] : Loc::getMessage('S_ASK_QUESTION'))?></span>
                    </div>
                </div>
            </div>
        </div>
    <?$this->EndViewTarget();?>
<?endif;?>

<?
/* gifts */
if ($arParams['USE_GIFTS_DETAIL'] === 'Y') {
    $templateData['GIFTS'] = [
        'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
        'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE'],
        'SUBSCRIBE_URL_TEMPLATE' => $arResult['~SUBSCRIBE_URL_TEMPLATE'],
        'POTENTIAL_PRODUCT_TO_BUY' => [
            'ID' => $arResult['ID'],
            'MODULE' => $arResult['MODULE'] ?? 'catalog',
            'PRODUCT_PROVIDER_CLASS' => $arResult['PRODUCT_PROVIDER_CLASS'] ?? 'CCatalogProductProvider',
            'QUANTITY' => $arResult['QUANTITY'] ?? '',
            'IBLOCK_ID' => $arResult['IBLOCK_ID'],

            'PRIMARY_OFFER_ID' => $arResult['OFFERS'][0]['ID'] ?? '',
            'SECTION' => [
                'ID' =>  $arResult['SECTION']['ID'] ?? '',
                'IBLOCK_ID' =>  $arResult['SECTION']['IBLOCK_ID'] ?? '',
                'LEFT_MARGIN' =>  $arResult['SECTION']['LEFT_MARGIN'] ?? '',
                'RIGHT_MARGIN' =>  $arResult['SECTION']['RIGHT_MARGIN'] ?? '',
            ],
        ]
    ];
}
?>

<div class="catalog-detail__top-info rounded-4 flexbox flexbox--direction-row flexbox--wrap-nowrap">
    <?
    // add to viewed
    TSolution\Product\Common::addViewed([
        'ITEM' => $arCurrentOffer ?: $arResult
    ]);
    ?>

    <?// meta?>
    <meta itemprop="name" content="<?=$name = strip_tags($elementName)?>" />
    <link itemprop="url" href="<?=$arResult['DETAIL_PAGE_URL']?>" />
    <meta itemprop="category" content="<?=$arResult['CATEGORY_PATH']?>" />
    <meta itemprop="description" content="<?=(strlen(strip_tags($arResult['PREVIEW_TEXT'])) ? strip_tags($arResult['PREVIEW_TEXT']) : (strlen(strip_tags($arResult['DETAIL_TEXT'])) ? strip_tags($arResult['DETAIL_TEXT']) : $name))?>" />
    <meta itemprop="sku" content="<?=$arResult['ID'];?>" />

    <?if ($arResult['SKU_CONFIG']):?><div class="js-sku-config" data-value='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arResult['SKU_CONFIG'], false, true))?>'></div><?endif;?>
    <?if ($arResult['SKU']['PROPS']):?>
        <?=TSolution\SKU::getTemplateWithJsonOffers($arResult["SKU"]["OFFERS"])?>

        <?$templateData["USE_OFFERS_SELECT"] = true;?>
    <?endif;?>

    <div class="detail-gallery-big<?= $topGalleryClassList; ?> swipeignore image-list__link">
        
            <div class="detail-gallery-big-wrapper">
                <?
                $countPhoto = count($arResult['GALLERY']);
                $arFirstPhoto = reset($arResult['GALLERY']);
                $urlFirstPhoto = $arFirstPhoto['BIG']['src'] ? $arFirstPhoto['BIG']['src'] : $arFirstPhoto['SRC'];
                ?>
                <link href="<?=$urlFirstPhoto?>" itemprop="image"/>
                <?
                $gallerySetting = [
                    'MAIN' => [
                        'SLIDE_CLASS_LIST' => 'detail-gallery-big__item detail-gallery-big__item--big swiper-slide',
                        'PLUGIN_OPTIONS' => [
                            'direction' => 'horizontal',
                            'init' => false,
                            'keyboard' => [
                                'enabled' => true,
                            ],
                            'loop' => false,
                            'pagination' => [
                                'enabled' => true,
                                'el' => '.detail-gallery-big-slider-main .swiper-pagination',
                            ],
                            'navigation' => [
                                'nextEl' => '.detail-gallery-big-slider-main .swiper-button-next',
                                'prevEl' => '.detail-gallery-big-slider-main .swiper-button-prev'
                            ],
                            'slidesPerView' => 1,
                            'thumbs' => [
                                'swiper' => '.gallery-slider-thumb',
                            ],
                            'type' => 'detail_gallery_main',
                            "preloadImages" => false,
                        ],
                    ],
                    'THUMBS' => [
                        'SLIDE_CLASS_LIST' => 'gallery__item gallery__item--thumb swiper-slide rounded-x pointer',
                        'PLUGIN_OPTIONS' => [
                            'direction' => ($bGallerythumbVertical ? 'vertical' : 'horizontal'),
                            'init' => false,
                            'loop' => false,
                            'navigation' => [
                                'nextEl' => '.gallery-slider-thumb-button--next',
                                'prevEl' => '.gallery-slider-thumb-button--prev',
                            ],
                            'pagination' => false,
                            'slidesPerView' => 'auto',
                            'type' => 'detail_gallery_thumb',
                            'watchSlidesProgress' => true,
                            "preloadImages" => false,
                        ],
                    ]
                ];
                ?>
                <div class="gallery-wrapper__aspect-ratio-container">
                    <? // thumb gallery ?>
                    <? if (isset($gallerySetting['THUMBS']) && $countPhoto || $bPopupVideo || $bShowSelectOffer): ?>
                        <div class="detail-gallery-big-slider-thumbs">
                            <? if (isset($gallerySetting['THUMBS']) && $countPhoto || $bShowSelectOffer): ?>
                            <div class="gallery-slider-thumb__container<?= $bPopupVideo ? ' gallery-slider-thumb__container--with-popup' : ''; ?>">
                                <div class="gallery-slider-thumb-button gallery-slider-thumb-button--prev slider-nav swiper-button-prev" style="display: none">
                                    <?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/arrows.svg#left-7-12', 'stroke-dark-light', [
                                        'WIDTH' => 7,
                                        'HEIGHT' => 12
                                    ]); ?>
                                </div>

                                <div class="gallery-slider-thumb js-detail-img-thumb swiper slider-solution gallery-slider-thumb__container--hide-navigation"
                                    data-size="<?= $countPhoto; ?>"
                                    data-slide-class-list="<?= $gallerySetting['THUMBS']['SLIDE_CLASS_LIST']; ?>"
                                    <? if (isset($gallerySetting['THUMBS']['PLUGIN_OPTIONS']) && count($gallerySetting['THUMBS']['PLUGIN_OPTIONS'])): ?>
                                    data-plugin-options='<?= Json::encode($gallerySetting['THUMBS']['PLUGIN_OPTIONS']); ?>'
                                    <? endif; ?>
                                >
                                    <div class="gallery__thumb-wrapper thumb swiper-wrapper" >
                                        <? if ($countPhoto > 1): ?>
                                            <? foreach ($arResult['GALLERY'] as $i => $arImage): ?>
                                                <?
                                                $alt = $arImage['ALT'];
                                                $title = $arImage['TITLE'];
                                                $url = $arImage['SMALL']['src'] ? $arImage['SMALL']['src'] : $arImage['SRC'];
                                                ?>
                                                <div id="thumb-photo-<?=$i?>" class="<?= $gallerySetting['THUMBS']['SLIDE_CLASS_LIST']; ?>">
                                                    <img class="gallery__picture rounded-x swiper-lazy" src="<?= $url; ?>" alt="<?= $alt; ?>" title="<?= $title; ?>"/>
                                                </div>
                                            <? endforeach; ?>
                                        <? endif; ?>
                                    </div>
                                </div>

                                <div class="gallery-slider-thumb-button gallery-slider-thumb-button--next slider-nav swiper-button-next" style="display: none">
                                    <?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/arrows.svg#right-7-12', 'stroke-dark-light', [
                                        'WIDTH' => 7,
                                        'HEIGHT' => 12
                                    ]); ?>
                                </div>
                            </div>
                            <? endif; ?>

                            <? if($bPopupVideo): ?>
                                <?TSolution\Functions::showBlockHtml([
                                    'FILE' => 'catalog/video_block.php',
                                    'LINK' => $arResult['POPUP_VIDEO'],
                                    'TITLE' => Loc::getMessage('VIDEO'),
                                ]);?>
                            <? endif; ?>
                        </div>
                    <? endif; ?>

                    <? // main gallery ?>
                    <div class="detail-gallery-big-slider-main">
                        <div class="detail-gallery-big-slider big js-detail-img swiper slider-solution slider-solution--show-nav-hover"
                            data-slide-class-list="<?= $gallerySetting['MAIN']['SLIDE_CLASS_LIST']; ?>"
                            <? if (isset($gallerySetting['MAIN']['PLUGIN_OPTIONS']) && count($gallerySetting['MAIN']['PLUGIN_OPTIONS'])): ?>
                            data-plugin-options='<?= \Bitrix\Main\Web\Json::encode($gallerySetting['MAIN']['PLUGIN_OPTIONS']); ?>'
                            <? endif; ?>
                        >
                            <? if ($countPhoto > 0): ?>
                                <div class="detail-gallery-big-slider__wrapper swiper-wrapper">
                                    <? foreach ($arResult['GALLERY'] as $i => $arImage): ?>
                                        <?
                                            $alt = $arImage['ALT'];
                                            $title = $arImage['TITLE'];
                                            $url = $arImage['BIG']['src'] ? $arImage['BIG']['src'] : $arImage['SRC'];
                                        ?>
                                        <div id="big-photo-<?=$i?>" class="<?= $gallerySetting['MAIN']['SLIDE_CLASS_LIST']; ?>">
                                            <a href="<?=$url?>" data-fancybox="gallery" class="detail-gallery-big__link popup_link fancy fancy-thumbs" title="<?= $title; ?>">
                                                <img class="detail-gallery-big__picture swiper-lazy" src="<?= $url; ?>" alt="<?= $alt; ?>" title="<?= $title; ?>" />
                                            </a>
                                        </div>
                                    <? endforeach; ?>
                                </div>

                                <div class="slider-nav slider-nav--prev swiper-button-prev" style="display: none">
                                    <?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/arrows.svg#left-7-12', 'stroke-dark-light', [
                                        'WIDTH' => 7,
                                        'HEIGHT' => 12
                                    ]); ?>
                                </div>

                                <div class="slider-nav slider-nav--next swiper-button-next" style="display: none">
                                    <?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/arrows.svg#right-7-12', 'stroke-dark-light', [
                                        'WIDTH' => 7,
                                        'HEIGHT' => 12
                                    ]); ?>
                                </div>
                            <? else: ?>
                                <div class="detail-gallery-big-slider__wrapper swiper-wrapper">
                                    <div class="detail-gallery-big__item detail-gallery-big__item--big detail-gallery-big__item--no-image swiper-slide">
                                        <span class="detail-gallery-big__link">
                                            <img class="detail-gallery-big__picture" src="<?=SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg'?>" />
                                        </span>
                                    </div>
                                </div>
                            <? endif; ?>
                        </div>

                        <div class="swiper-pagination swiper-pagination--bottom visible-767 swiper-pagionation-bullet--line-to-600"></div>
                    </div>
                </div>
            </div>
        

		<!-- Начало блока спецификации -->
		<div class="catalog-detail__specification-block">
		    <div class="specification-container">
		        <h3 class="specification-title">Спецификация</h3>
		        <div class="specification-files">
		            <?php if (!empty($arResult['DOCUMENTS'])): ?>
		                <?php foreach($arResult['DOCUMENTS'] as $arItem): ?>
		                    <?php
		                    $arDocFile = TSolution::GetFileInfo($arItem);
		                    $docFileDescr = $arDocFile['DESCRIPTION'] ?? '';
		                    $docFileSize = $arDocFile['FILE_SIZE_FORMAT'] ?? '';
		                    $docFileType = $arDocFile['TYPE'] ?? '';
		                    ?>
		                    <div class="specification-file">
		                        <div class="specification-file__inner">
		                            <!-- Иконка типа файла -->
		                            <div class="specification-file__icon">
		                                <?php if($docFileType == 'pdf'): ?>
						<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M31.2399 0H5.95064V5.47713H1.84106V20.013H5.94887V40H38.1591V6.47249L31.2399 0ZM23.5782 12.1279V13.3189H20.6302V16.3276H19.2072V9.26651H24.0508V10.461H20.6302V12.128L23.5782 12.1279ZM17.9972 12.8654C17.9972 13.447 17.9245 13.9473 17.7808 14.3679C17.6043 14.8803 17.3498 15.2975 17.0261 15.6143C16.7751 15.8601 16.4462 16.0453 16.029 16.1786C15.707 16.2773 15.2881 16.3292 14.7653 16.3292H12.0855V9.26643H14.6856C15.2724 9.26643 15.7225 9.30968 16.0306 9.39972C16.4426 9.52435 16.7957 9.73902 17.09 10.0506C17.386 10.3623 17.6093 10.7396 17.7668 11.1949C17.921 11.6501 17.9972 12.2023 17.9972 12.8654ZM6.93042 13.6634V16.331H5.50741V16.3292V9.26643H7.79071C8.65622 9.26643 9.22055 9.30278 9.48024 9.37372C9.88181 9.48102 10.2263 9.70957 10.4946 10.0592C10.7716 10.4175 10.9066 10.8763 10.9066 11.4337C10.9066 11.8699 10.8287 12.23 10.6712 12.526C10.5154 12.8272 10.3163 13.0557 10.0705 13.2271C9.83334 13.3915 9.58922 13.5058 9.33996 13.5577C8.99544 13.6235 8.50904 13.6633 7.86334 13.6633L6.93042 13.6634ZM35.8221 37.6527C33.7482 37.6527 10.3579 37.6527 8.28755 37.6527C8.28755 36.5915 8.28755 28.3049 8.28755 20.013H27.918V5.47713H8.28931C8.28931 3.82918 8.28931 2.68833 8.28931 2.33868C10.3563 2.33868 29.4742 2.33868 30.3207 2.33868C30.8816 2.86841 35.2058 6.91042 35.8255 7.48686C35.8221 8.43727 35.8221 35.5234 35.8221 37.6527Z" fill="#163760"/>
							<path d="M31.2399 0H5.95064V5.47713H1.84106V20.013H5.94887V40H38.1591V6.47249L31.2399 0ZM23.5782 12.1279V13.3189H20.6302V16.3276H19.2072V9.26651H24.0508V10.461H20.6302V12.128L23.5782 12.1279ZM17.9972 12.8654C17.9972 13.447 17.9245 13.9473 17.7808 14.3679C17.6043 14.8803 17.3498 15.2975 17.0261 15.6143C16.7751 15.8601 16.4462 16.0453 16.029 16.1786C15.707 16.2773 15.2881 16.3292 14.7653 16.3292H12.0855V9.26643H14.6856C15.2724 9.26643 15.7225 9.30968 16.0306 9.39972C16.4426 9.52435 16.7957 9.73902 17.09 10.0506C17.386 10.3623 17.6093 10.7396 17.7668 11.1949C17.921 11.6501 17.9972 12.2023 17.9972 12.8654ZM6.93042 13.6634V16.331H5.50741V16.3292V9.26643H7.79071C8.65622 9.26643 9.22055 9.30278 9.48024 9.37372C9.88181 9.48102 10.2263 9.70957 10.4946 10.0592C10.7716 10.4175 10.9066 10.8763 10.9066 11.4337C10.9066 11.8699 10.8287 12.23 10.6712 12.526C10.5154 12.8272 10.3163 13.0557 10.0705 13.2271C9.83334 13.3915 9.58922 13.5058 9.33996 13.5577C8.99544 13.6235 8.50904 13.6633 7.86334 13.6633L6.93042 13.6634ZM35.8221 37.6527C33.7482 37.6527 10.3579 37.6527 8.28755 37.6527C8.28755 36.5915 8.28755 28.3049 8.28755 20.013H27.918V5.47713H8.28931C8.28931 3.82918 8.28931 2.68833 8.28931 2.33868C10.3563 2.33868 29.4742 2.33868 30.3207 2.33868C30.8816 2.86841 35.2058 6.91042 35.8255 7.48686C35.8221 8.43727 35.8221 35.5234 35.8221 37.6527Z" fill="#FF0000"/>
							<path d="M15.9753 10.8625C15.8022 10.6963 15.584 10.589 15.3192 10.5319C15.115 10.4852 14.7307 10.4626 14.1524 10.4626H13.512V15.1349H14.5714C14.9678 15.1349 15.2534 15.1141 15.4317 15.0674C15.6654 15.0102 15.8559 14.9081 16.0064 14.7731C16.1622 14.6346 16.2834 14.4096 16.3821 14.0945C16.4772 13.776 16.5275 13.3432 16.5275 12.7996C16.5275 12.2595 16.479 11.8354 16.3821 11.548C16.2817 11.2538 16.1466 11.027 15.9753 10.8625Z" fill="#FF0000"/>
							<path d="M9.20674 10.8244C9.05787 10.6582 8.86575 10.5561 8.63551 10.5111C8.47108 10.4782 8.12488 10.4626 7.61592 10.4626H6.93042V12.4655H7.70252C8.26507 12.4655 8.63728 12.4309 8.82595 12.3546C9.01638 12.2819 9.16525 12.1677 9.27431 12.0101C9.38161 11.8526 9.43184 11.6708 9.43184 11.4596C9.43353 11.2053 9.36427 10.9941 9.20674 10.8244Z" fill="#FF0000"/>
						</svg>

		                                <?php else: ?>
		                                    <div class="file-icon file-icon--<?=$docFileType?>"><?=strtoupper($docFileType)?></div>
		                                <?php endif; ?>
		                            </div>
		                            <!-- Информация о файле -->
		                            <div class="specification-file__info">
		                                <a href="<?=$arDocFile['SRC']?>" target="_blank" class="specification-file__name" title="<?=htmlspecialcharsbx($docFileDescr)?>">
		                                    <?=htmlspecialcharsbx($docFileDescr)?>
		                                </a>
		                                <div class="specification-file__details">
		                                    <span class="specification-file__size"><?=$docFileSize?></span>
		                                    <span class="specification-file__type"><?=strtoupper($docFileType)?></span>
		                                </div>
		                            </div>
		                            <!-- Кнопка скачивания -->
		                            <div class="specification-file__actions">
		                                <a href="<?=$arDocFile['SRC']?>" target="_blank" class="specification-file__download" title="Скачать">
		                                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
		                                        <path d="M16.25 12.5H22.5L15 20L7.5 12.5H13.75V3.75H16.25V12.5ZM5 23.75H25V15H27.5V25C27.5 25.3315 27.3683 25.6495 27.1339 25.8839C26.8995 26.1183 26.5815 26.25 26.25 26.25H3.75C3.41848 26.25 3.10054 26.1183 2.86612 25.8839C2.6317 25.6495 2.5 25.3315 2.5 25V15H5V23.75Z" fill="#163760"/>
		                                    </svg>
		                                </a>
		                            </div>
		                        </div>
		                    </div>
		                <?php endforeach; ?>
		            <?php else: ?>
		                <!-- Блок с сообщением об отсутствии спецификации -->
		                <div class="specification-empty">
		                    <div class="specification-empty__icon">
		                        <svg width="50" height="50" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
		                            <path d="M25 5C13.9543 5 5 13.9543 5 25C5 36.0457 13.9543 45 25 45C36.0457 45 45 36.0457 45 25C45 13.9543 36.0457 5 25 5ZM25 41.6667C15.795 41.6667 8.33333 34.205 8.33333 25C8.33333 15.795 15.795 8.33333 25 8.33333C34.205 8.33333 41.6667 15.795 41.6667 25C41.6667 34.205 34.205 41.6667 25 41.6667Z" fill="#cccccc"/>
		                            <path d="M22.5 15H27.5V27.5H22.5V15ZM22.5 30H27.5V35H22.5V30Z" fill="#cccccc"/>
		                        </svg>
		                    </div>
		                    <div class="specification-empty__message">
		                        <p class="specification-empty__text">Спецификация скоро будет добавлена</p>
		                    </div>
		                </div>
		            <?php endif; ?>
		        </div>
		    </div>
		</div>
		<!-- Конец блока спецификации -->
    </div>

    <div class="catalog-detail__main">

        <?if(
            strlen($article)
            || $bShowRating
            || $bShowCompare
            || $bShowFavorit
            || $bUseShare
        ):?>
            <div class="catalog-detail__info-tc">
                <?if(
                    strlen($article)
                    || $bShowCompare
                    || $bShowFavorit
                    || $bUseShare
                    || $bShowRating
                ):?>
                    <div class="line-block line-block--20 line-block--align-normal flexbox--justify-beetwen flexbox--wrap">
                        <div class="line-block__item">
                            <?if (strlen($article)|| $bShowRating):?>
                                <div class="catalog-detail__info-tech">
                                    <div class="line-block line-block--20 flexbox--wrap js-popup-info">
                                        <?// rating?>
                                        <?if ($bShowRating):?>
                                            <div class="line-block__item font_14 color_222">
                                                <?=\TSolution\Product\Common::getRatingHtml([
                                                    'ITEM' => $arResult,
                                                    'PARAMS' => $arParams,
                                                    'SHOW_REVIEW_COUNT' => $bShowReview,
                                                    'SVG_SIZE' => [
                                                        'WIDTH' => 16,
                                                        'HEIGHT' => 16,
                                                    ],
                                                    'USE_SCHEMA' => true,
                                                ])?>
                                            </div>
                                        <?endif;?>

                                        <?// element article?>
                                        <?if(strlen($article)):?>
                                            <div class="line-block__item font_13 color_999" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
                                                <span class="article"><meta itemprop="name" content="<?=GetMessage('S_ARTICLE_FILL');?>"><?=GetMessage('S_ARTICLE')?>&nbsp;<span
                                                    class="js-replace-article"
                                                    data-value="<?=$arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE']?>"
                                                    itemprop="value"
                                                ><?=$article?></span></span>
                                            </div>
                                        <?endif;?>
                                    </div>
                                </div>
                            <?endif;?>
                        </div>

                        <? if ($bShowCompare || $bShowFavorit || $bUseShare): ?>
                            <div class="line-block__item ">
                                <div class="flexbox flexbox--row flexbox--wrap">
                                    <?if (!($bSKU2 && $arResult['HAS_SKU'])):?>
                                        <div class="js-replace-icons">
                                            <? if ($bShowFavorit): ?>
                                                <?= \TSolution\Product\Common::getActionIcon([
                                                    'ITEM' => ($arCurrentOffer ? $arCurrentOffer : $arResult),
                                                    'PARAMS' => $arParams,
                                                    'CLASS' => 'md item-action__inner--sm-to-600',
                                                ]); ?>
                                            <? endif; ?>

                                            <? if ($bShowCompare): ?>
                                                <?= \TSolution\Product\Common::getActionIcon([
                                                    'ITEM' => (($arCurrentOffer && \TSolution::isSaleMode()) ? $arCurrentOffer : $arResult),
                                                    'PARAMS' => $arParams,
                                                    'TYPE' => 'compare',
                                                    'CLASS' => 'md item-action__inner--sm-to-600',
                                                    'SVG_SIZE' => ['WIDTH' => 20,'HEIGHT' => 16],
                                                ]); ?>
                                            <? endif; ?>
                                        </div>
                                    <?endif;?>


                                    <? if ($bUseShare): ?>
                                        <? \TSolution\Functions::showShareBlock([
                                            'INNER_CLASS' => 'item-action__inner item-action__inner--md item-action__inner--sm-to-600',
                                            'CLASS' => 'item-action item-action--horizontal',
                                        ]);?>
                                    <? endif; ?>
                                </div>
                            </div>
                        <? endif; ?>
                    </div>
                <?endif;?>
            </div>
        <?endif;?>

        <div class="catalog-detail__main-parts line-block line-block--32">

            <div class="catalog-detail__main-part catalog-detail__main-part--left flex-1 line-block__item grid-list grid-list--gap-30">
				        <?//discount counter?>
        <?ob_start();?>
        <?if ($templateData['SHOW_DISCOUNT_COUNTER']):?>
            <?
            $discountDateTo = '';
            if (TSolution::isSaleMode()) {
                $arDiscount = TSolution\Product\Price::getDiscountByItemID($arResult['ID']);
                $discountDateTo = $arDiscount ? $arDiscount['ACTIVE_TO'] : '';
            } else {
                $discountDateTo = $arResult['DISPLAY_PROPERTIES']['DATE_COUNTER']['VALUE'];
            }

            if ($discountDateTo) {
                TSolution\Functions::showDiscountCounter([
                    'ICONS' => true,
                    'SHADOWED' => true,
                    'DATE' => $discountDateTo,
                    'ITEM' => $arResult,
                ]);
            }
            ?>
        <?endif;?>
        <?$itemDiscount = ob_get_clean();?>

        <? TSolution\Product\Common::showStickers([
            'TYPE' => '',
            'ITEM' => $arResult,
            'PARAMS' => $arParams,
            'WRAPPER' => 'catalog-detail__sticker-wrapper',
            'CONTENT' => $itemDiscount,
        ]); ?>
        
		
		

			 <? if ($arResult['BRAND_ITEM'] && $arResult['BRAND_ITEM']["IMAGE"]): ?>
                    <div class="grid-list__item">
                        <div class="brand-detail flexbox line-block--gap line-block--gap-12">
                            <div class="brand-detail-info" itemprop="brand" itemtype="https://schema.org/Brand" itemscope>
                                <meta itemprop="name" content="<?=$arResult["BRAND_ITEM"]["NAME"]?>" />
                                <div class="brand-detail-info__image rounded-x">
                                    <a href="<?=$arResult['BRAND_ITEM']["DETAIL_PAGE_URL"];?>">
                                        <img src="<?=$arResult['BRAND_ITEM']["IMAGE"]["src"];?>" alt="<?=$arResult['BRAND_ITEM']["NAME"];?>" title="<?=$arResult['BRAND_ITEM']["NAME"];?>" itemprop="image">
                                    </a>
                                </div>
                            </div>

                            <div class="brand-detail-info__preview line-block line-block--gap line-block--gap-8 flexbox--wrap font_14">
                                <div class="line-block__item">
                                    <a class="chip chip--transparent bordered" href="<?=$arResult['BRAND_ITEM']["DETAIL_PAGE_URL"];?>" target="_blank">
                                        <span class="chip__label"><?=GetMessage("ITEMS_BY_BRAND", array("#BRAND#" => $arResult['BRAND_ITEM']["NAME"]))?></span>
                                    </a>
                                </div>
                                <?if($arResult['SECTION']):?>
                                    <div class="line-block__item">
                                        <a class="chip chip--transparent bordered" href="<?= $arResult['BRAND_ITEM']['CATALOG_PAGE_URL'] ?>" target="_blank">
                                            <span class="chip__label"><?=GetMessage("ITEMS_BY_SECTION")?></span>
                                        </a>
                                    </div>
                                <?endif;?>
                            </div>
                        </div>
                    </div>
                <? endif; ?>
                <?if (
                    $arResult['SIZE_PATH']
                    || (
                        $bShowSkuProps
                        && $arResult['SKU']['PROPS']
                    )
                ):?>
                    <div class="grid-list__item catalog-detail__offers">
					
                        <div class="sku-props sku-props--detail"
                            data-site-id="<?=SITE_ID;?>"
                            data-item-id="<?=$arResult['ID'];?>"
                            data-iblockid="<?=$arResult['IBLOCK_ID'];?>"
                            data-offer-id="<?=$arCurrentOffer['ID'];?>"
                            data-offer-iblockid="<?=$arCurrentOffer['IBLOCK_ID'];?>"
                            data-offers-id='<?=str_replace('\'', '"', CUtil::PhpToJSObject($GLOBALS[$arParams['FILTER_NAME']]['OFFERS_ID'], false, true))?>'
                            >
                            <div class="line-block line-block--flex-wrap line-block--flex-100 line-block--32 line-block--align-flex-end">
                                <?if ($arResult['SKU']['PROPS']):?>
                                    <?=TSolution\SKU\Template::showSkuPropsHtml($arResult['SKU']['PROPS']);?>
                                <?endif;?>
                                <? // table sizes ?>
                                <? if ($arResult['SIZE_PATH']): ?>
                                    <div class="line-block__item">
                                        <div class="catalog-detail__pseudo-link <?=$bSKU2 ? '' : 'catalog-detail__pseudo-link--with-gap'?> table-sizes">
                                            <span class="font_13 fill-dark-light-block dark_link"
                                                data-event="jqm"
                                                data-param-form_id="include_block"
                                                data-param-url="<?= $arResult['SIZE_PATH']; ?>"
                                                data-param-block_title="<?= urlencode(TSolution::formatJsName(GetMessage('TABLE_SIZES')));?>"
                                                data-name="include_block"
                                                >
                                                <?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/catalog/item_icons.svg#table_sizes', '', [
                                                    'WIDTH' => 18,
                                                    'HEIGHT' => 11
                                                ]); ?>
                                                <span class="dotted"><?= GetMessage("TABLES_SIZE"); ?></span>
                                            </span>
                                        </div>
                                    </div>
                                <? endif; ?>
                            </div>
                        </div>
                    </div>
                <?endif;?>
<div class="grid-list__item catalog-chars">
<div class="grid-list__item catalog-chars">
    <div class="detail-block ordered-block char">
        <h3 class="switcher-title"><?=$arParams['T_CHARACTERISTICS'] ?: Loc::getMessage("T_CHARACTERISTICS")?></h3>
        <div class="props_block">
            <div class="props_block__wrapper w4">
                <?php
                // Форма с характеристиками
                if (!empty($arResult['CHARACTERISTICS']) || !empty($arResult['OFFER_PROP'])) {
                    // Получаем URL раздела для формы
                    $sectionUrl = $arResult['SECTION']['SECTION_PAGE_URL'] ?? '/catalog/';
                    ?>
                    <form action="<?=htmlspecialcharsbx($sectionUrl)?>" method="get" class="js-filter">
                        <input type="hidden" name="bxajaxid" id="bxajaxid_<?=rand(100000, 999999)?>" value="">
                        <input type="hidden" name="AJAX_CALL" value="Y">
                        
                        <div class="properties-group properties-group--table js-offers-group-wrap">
                            <div class="properties-group__group" data-group-code="no-group">
                                <div class="properties-group__items js-offers-group__items-wrap font_15">
                                    <?php
                                    // Объединяем характеристики
                                    $allChars = array_merge(
                                        (array)$arResult['CHARACTERISTICS'],
                                        (array)$arResult['OFFER_PROP']
                                    );
                                    
                                    foreach ($allChars as $charCode => $char): 
                                        if (empty($char['VALUE']) && empty($char['~VALUE'])) continue;
                                        
                                        $value = !empty($char['DISPLAY_VALUE']) ? $char['DISPLAY_VALUE'] : $char['VALUE'];
                                        $name = $char['NAME'];
                                        $filterName = 'MAX_SMART_FILTER_' . $char['ID'] . '_' . rand(1000000000, 9999999999);
                                    ?>
                                        <div class="properties-group__item" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
                                            <div class="properties-group__name-wrap">
                                                <span itemprop="name" class="properties-group__name color_666">
                                                    <?=htmlspecialcharsbx($name)?>
                                                </span>
                                            </div>
                                            
                                            <div class="properties-group__value-wrap">
                                                <div class="properties-group__value color_222" itemprop="value">
                                                    <?=htmlspecialcharsbx($value)?>
                                                </div>
                                            </div>
                                            
                                            <div class="checkbox_block">
                                                <?php
                                                // Проверяем, есть ли свойство в умном фильтре
                                                if (isset($arResult['SMART_FILTER'][$charCode])): 
                                                ?>
                                                    <input type="checkbox" 
                                                           value="Y" 
                                                           name="<?=$filterName?>"
                                                           class="char-filter-checkbox"
                                                           data-property-id="<?=$char['ID']?>"
                                                           data-property-value="<?=htmlspecialcharsbx($value)?>"
                                                           onclick="this.form.submit()">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="properties-group__groups">
                                <div class="properties-group__items js-offers-group__items-wrap font_15">
                                    <div class="properties-group__item">
                                        <div class="analog-filter-actions" style="display:flex; flex-direction:column; align-items:flex-start; gap:8px;">
                                            <span class="analog-filter-hint" style="display: none;">Выберите хотябы 1 параметр</span>
                                            <input type="submit" 
                                                   name="set_filter" 
                                                   class="btn btn-default" 
                                                   value="Подобрать похожие">
                                        </div>
							<div class="properties-group__name-wrap"></div>
                                        <div class="properties-group__value-wrap"></div>
                            
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php
                } else {
                    echo '<p>Нет характеристик для отображения</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
</div>


               

                <? //tizers ?>
                <div class="grid-list__item" data-js-block=".catalog-detail__tizers-block"></div>

                <div class="catalog-detail__info-tc"></div>
            </div>

            <div class="catalog-detail__main-part catalog-detail__main-part--right sticky-block flex-1 line-block__item grid-list grid-list--items-1 grid-list--gap-8 grid-list--fill-bg">
			  <div class="store-prices__title-new">Наличие и цены по складам:</div>
                <?
                $arPriceConfig = [
                    'PRICE_CODE' => $arParams['PRICE_CODE'],
                    'PRICE_FONT' => 24,
                    'PRICEOLD_FONT' => 16,
                ];

                $priceHtml = TSolution\Product\Price::show([
                    'ITEM' => ($arCurrentOffer ? $arCurrentOffer : $arResult),
                    'PARAMS' => $arParams,
                    'SHOW_SCHEMA' => true,
                    'BASKET' => $bOrderViewBasket,
                    'PRICE_FONT' => 24,
                    'PRICEOLD_FONT' => 16,
                    'RETURN' => true,
                ]);
                ?>

                <?ob_start();?>
                <? if ($bSKU2 && $arResult['HAS_SKU']): ?>
                    <div class="catalog-detail__cart">
                        <?= TSolution\Product\Basket::getAnchorButton([
                            'BTN_NAME' => TSolution::GetFrontParametrValue('EXPRESSION_READ_MORE_OFFERS_DEFAULT'),
                            'BLOCK' => 'sku',
                        ]); ?>
                    </div>
                <? else: ?>
                    <?
                    $arBtnConfig = [
                        'BASKET_URL' => $basketURL,
                        'BASKET' => $bOrderViewBasket,
                        'DETAIL_PAGE' => true,
                        'ORDER_BTN' => $bOrderButton,
                        'BTN_CLASS' => 'btn-lg btn-wide',
                        'BTN_CLASS_MORE' => 'bg-theme-target border-theme-target btn-wide',
                        'BTN_IN_CART_CLASS' => 'btn-lg btn-wide',
                        'BTN_CALLBACK_CLASS' => 'btn-transparent-border',
                        'BTN_OCB_CLASS' => 'btn-wide btn-transparent btn-md btn-ocb',
                        'BTN_ORDER_CLASS' => 'btn-wide btn-transparent-border btn-lg',
                        'SHOW_COUNTER' => false,
                        'ONE_CLICK_BUY' => $bOcbButton,
                        'QUESTION_BTN' => $bAskButton,
                        'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
                        'CATALOG_IBLOCK_ID' => $arResult['IBLOCK_ID'],
                        'ITEM_ID' => $arResult['ID'],
                        'IS_DETAIL' => true,
                        'SHOW_BASKET_COUNTER' => $arParams['SHOW_BASKET_COUNTER'],
                    ];

                    $arBasketConfig = TSolution\Product\Basket::getOptions(array_merge(
                        $arBtnConfig,
                        [
                            'ITEM' => ($arCurrentOffer ?: $arResult),
                            'PARAMS' => $arParams,
                            'TOTAL_COUNT' => $totalCount,
                            'IS_OFFER' => (bool)$arCurrentOffer,
                        ]
                    ));
                    ?>
                    <?=$arBasketConfig['HTML']?>
                <? endif; ?>
                <?
                $btnHtml = trim(ob_get_contents());
                ob_end_clean();
                ?>

                
                <?//sales?>
                <div class="grid-list__item" data-js-block=".catalog-detail__sale-block"></div>

				
                <div class="grid-list__item<?=(($btnHtml || $priceHtml) ? '' : ' hidden')?>">
    <div class="catalog-detail__buy-block catalog-detail__cell-block outer-rounded-x" itemprop="offers" itemscope itemtype="http://schema.org/Offer" data-id="<?=$arResult['ID']?>" data-item="<?=$dataItem;?>">
    <link itemprop="availability" href="http://schema.org/<?=$totalCount ? 'InStock' : 'OutOfStock';?>" />
    <?if ($arDiscount["ACTIVE_TO"]):?>
        <meta itemprop="priceValidUntil" content="<?=date('Y-m-d', MakeTimeStamp($arDiscount['ACTIVE_TO']));?>" />
    <?endif;?>
    <link itemprop="url" href="<?=$arResult["DETAIL_PAGE_URL"]?>" />


<?php 
// Получаем данные о складах из глобальной переменной
$storeData = $GLOBALS['STORE_DATA_FOR_PRODUCT'] ?? [];
?>

<?php if (!empty($storeData)): ?>
<div class="store-prices-block">
              
    <?php 
    // Получаем основную цену товара для JavaScript
    $basePriceValue = $arResult['PRICES']['BASE']['VALUE'] ?? 0;
    $basePriceCurrency = $arResult['PRICES']['BASE']['CURRENCY'] ?? '₽';
    
    // Если нет основной цены, используем минимальную цену
    if (!$basePriceValue && isset($minPrice['VALUE'])) {
        $basePriceValue = $minPrice['VALUE'];
        $basePriceCurrency = $minPrice['CURRENCY'];
    }
    ?>
    
    <?php foreach ($storeData as $store): ?>
<div class="store-item" style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #e8e8e8;">
    <!-- Название склада и количество -->
    <div class="store-header line-block line-block--align-center line-block--gap-12" style="margin-bottom: 10px;">
        <div class="line-block__item flex-1">
            <div class="sklad-name"><?=htmlspecialcharsbx($store['NAME'])?></div>
            <div class="about-sklad">
                <?php if ($store['ADDRESS']): ?>
                    <div class="sklad-adress"><?=htmlspecialcharsbx($store['ADDRESS'])?></div>
                <?php endif; ?>
                <div class="vnalichii">
                    В наличии: <span><?=intval($store['QUANTITY'])?> шт.</span>
                </div>
            </div>
        </div>
        <div class="line-block__item">
            <div class="srok-postavki">
                Срок поставки: <span><?=htmlspecialcharsbx($store['DELIVERY_TIME'])?></span>
            </div>
        </div>
    </div>
    
    <!-- Ценовые тиры для склада -->
<div class="buts">
    <div class="store-prices">
        <?php 
        // Фильтруем только BASE цены и сортируем по количеству
        $basePrices = array_filter($arResult['EXTENDED_PRICES'], function($price) {
            return $price['TYPE'] == 'BASE';
        });
        
        // Сортируем по количеству от меньшего к большему
        usort($basePrices, function($a, $b) {
            return $a['QUANTITY_FROM'] - $b['QUANTITY_FROM'];
        });
        
        $totalPrices = count($basePrices);
        $showArrows = $totalPrices > 3;
        $currentIndex = 0; // Начинаем с первого тира
        ?>
        
        <?php if (!empty($basePrices)): ?>
            <div class="price-tiers-wrapper" style="position: relative;">
                <div class="price-tiers" id="price-tiers-<?=$store['ID']?>">
                    <div class="price-tiers__list">
                        <?php foreach ($basePrices as $index => $price): ?>
                            <div class="price-tier <?=($index === 0 ? 'current-price' : '')?> 
                                                  <?=($index >= 3 ? 'hidden-tier' : '')?>" 
                                 style="display: <?=($index < 3 ? 'flex' : 'none')?>; justify-content: space-between; align-items: center; padding: 5px; border-bottom: 1px solid #f0f0f0;"
                                 data-store-id="<?=$store['ID']?>"
                                 data-quantity-from="<?=$price['QUANTITY_FROM']?>"
                                 data-price="<?=$price['PRICE']?>"
                                 data-tier-index="<?=$index?>">
                                <div class="price-tier__quantity font_14 color_666">
                                    от <?=intval($price['QUANTITY_FROM'])?> шт.
                                </div>
                                <div class="price-tier__value font_14 color_222 font-weight-bold">
                                    <?=CCurrencyLang::CurrencyFormat($price['PRICE'], $price['CURRENCY'], true)?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <?php if ($showArrows): ?>
                    <div class="price-tiers-navigation" style="position: absolute; right: -30px; top: 50%; transform: translateY(-50%); display: flex; flex-direction: column; gap: 5px;">
                        <button class="price-tier-nav-btn price-tier-nav-up" 
                                data-store-id="<?=$store['ID']?>" 
                                data-direction="up"
                                style="background: none; border: none; padding: 2px; cursor: pointer; opacity: <?=($currentIndex === 0 ? '0.5' : '1')?>;"
                                <?=($currentIndex === 0 ? 'disabled' : '')?>>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12.8183 8.77076C12.363 8.27641 11.637 8.27641 11.1816 8.77076L6.9567 13.3574C6.20309 14.1755 6.7229 15.6 7.77504 15.6H16.2249C17.2771 15.6 17.7969 14.1755 17.0433 13.3574L12.8183 8.77076Z" fill="#163760" fill-opacity="<?=($currentIndex === 0 ? '0.4' : '0.84')?>"/>
                            </svg>
                        </button>
                        
                        <button class="price-tier-nav-btn price-tier-nav-down" 
                                data-store-id="<?=$store['ID']?>" 
                                data-direction="down"
                                style="background: none; border: none; padding: 2px; cursor: pointer; opacity: <?=($currentIndex >= $totalPrices - 3 ? '0.5' : '1')?>;"
                                <?=($currentIndex >= $totalPrices - 3 ? 'disabled' : '')?>>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.1817 15.2292C11.637 15.7236 12.363 15.7236 12.8184 15.2292L17.0433 10.6426C17.7969 9.82448 17.2771 8.39998 16.225 8.39998H7.77506C6.72291 8.39998 6.20311 9.82448 6.95671 10.6426L11.1817 15.2292Z" fill="#163760" fill-opacity="<?=($currentIndex >= $totalPrices - 3 ? '0.4' : '0.64')?>"/>
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

        <!-- Блок с количеством -->
        <div class="store-buy-block" style="margin-top: 0px;">
            <!-- Input для количества -->
            <div class="buds">
                <div class="line-block__item">
                    <div class="store-quantity-input">
                        <input type="number" 
                               id="store-quantity-<?=$store['ID']?>" 
                               value="" 
			       placeholder="0"
                               min="0" 
                               max="<?=$store['QUANTITY']?>"
                               class="form-control store-quantity-input-field" 
                               style="width: 80px; display: inline-block;" 
                               data-store-id="<?=$store['ID']?>"
                               onchange="updateStoreTotal(<?=$store['ID']?>)"
                               oninput="updateStoreTotal(<?=$store['ID']?>)">
                    </div>
                </div> 
            </div>
            <div class="line-block line-block--align-center line-block--gap-12">
                <!-- Информация о цене и итоге -->
                <div class="line-block__item flex-1">
                    <div class="store-total-block line-block line-block--align-baseline font_14" id="store-total-<?=$store['ID']?>">
                        <div class="line-block__item"><span id="store-total-qty-<?=$store['ID']?>">0</span> шт.</div>
                        <div class="line-block__item">по: <strong id="store-unit-price-<?=$store['ID']?>">0 ₽</strong></div>
                        <div class="line-block__item">итого: <strong id="store-total-sum-<?=$store['ID']?>">0 ₽</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Блок "В корзине" (скрыт по умолчанию) -->
    <div class="store-in-cart-block" id="store-in-cart-<?=$store['ID']?>" style="display: none; text-align: center; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ccc;">
        <div class="store-in-cart-content" style="display: inline-flex; align-items: center; gap: 8px; color: #2CBE15;">
            <div class="store-in-cart-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.5 12L10.5 15L16.5 9M7.8 21H16.2C17.8802 21 18.7202 21 19.362 20.673C19.9265 20.3854 20.3854 19.9265 20.673 19.362C21 18.7202 21 17.8802 21 16.2V7.8C21 6.11984 21 5.27976 20.673 4.63803C20.3854 4.07354 19.9265 3.6146 19.362 3.32698C18.7202 3 17.8802 3 16.2 3H7.8C6.11984 3 5.27976 3 4.63803 3.32698C4.07354 3.6146 3.6146 4.07354 3.32698 4.63803C3 5.27976 3 6.11984 3 7.8V16.2C3 17.8802 3 18.7202 3.32698 19.362C3.6146 19.9265 4.07354 20.3854 4.63803 20.673C5.27976 21 6.11984 21 7.8 21Z" stroke="#2CBE15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="store-in-cart-text font_14 font-weight-bold">
                В корзине: <span id="store-cart-quantity-<?=$store['ID']?>">0</span> шт.
            </div>
        </div>
    </div>
</div>
    <?php endforeach; ?>
    
    <!-- Блок с общим итогом -->
    <div class="total" style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
        <div class="total-card" style=" dispay:flex; text-align: left; align-items: center; max-width: 457px;">
            <p style="font-size: 16px; margin: 0; color: #333;">
                Итого: <span id="total-quantity" style="font-weight: bold; color: #163760;">0</span><span style="color: #163760;"> шт.</span> 
                на сумму  <span id="total-price" style="font-weight: bold; color: #163760;"> 0 ₽</span>
            </p>
        </div>
        <div class="store-add-to-cart-button">
            <button class="btn btn-default btn-lg btn-wide js-add-all-stores-to-cart"
                    data-product-id="<?=$arResult['ID']?>">
                В корзину
            </button>
        </div>		
    </div>
</div>

<style>
.current-price {
    
}

.store-quantity-input-field {
    padding: 5px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
}

.store-quantity-input-field:focus {
    border-color: #163760;
    outline: none;
}

.price-tier.current-price .price-tier__value {
    color: #fff;
    font-weight: bold;
}

.price-tier.current-price .price-tier__quantity {
    color: #fff;
    font-weight: bold;
}

.store-add-to-cart-button {
    text-align: center;
    padding: 15px 0;
/*    border-top: 1px solid #e8e8e8; */
}

.total-card {
    text-align: center;
    font-size: 18px;
    font-weight: bold;
}

/* Стили для блока "В корзине" */
.store-in-cart-block {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.btn-remove-from-cart:hover {
    background: #d9534f !important;
    color: white !important;
}

/* Адаптивные стили для ценовых тиров */
.price-tiers-wrapper {
    position: relative;
    margin-bottom: 12px;
    width: 100%;
    overflow: hidden; /* Ограничиваем область видимости */
}

.price-tiers__list {
    display: flex;
    gap: 8px;
    transition: transform 0.3s ease;
    width: max-content; /* Ширина по содержимому */
}


/* Кнопки навигации - позиционируем по бокам */
.price-tiers-navigation {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
    gap: 8px;
    z-index: 2;
}

.price-tier-nav-btn {
    background: white;
    border: 1px solid #e8e8e8;
    border-radius: 50%;
    padding: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.price-tier-nav-up {
    left: -35px;
}

.price-tier-nav-down {
    right: -35px;
}

.price-tier-nav-btn:hover {
    border-color: #163760;
    background: #163760;
}

.price-tier-nav-btn:hover svg path {
    fill: white;
    fill-opacity: 1;
}

.price-tier-nav-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

/* ========== АДАПТИВНЫЕ МЕДИА-ЗАПРОСЫ ========== */

/* Для экранов меньше 1490px - уменьшаем ширину тира */
@media (max-width: 1490px) {
    .price-tier {
/*        width: 160px; Уменьшаем ширину на средних экранах */
        padding: 9px 12px;
        min-height: 56px;
	min-width: 83px;
    }

.store-prices {
	width: 56%;
}
.price-tiers__list{
	gap: 0px;
}

.sklad-name {
	font-size: 16px;
}

.about-sklad {
	font-size: 13px;
}    

.srok-postavki{
	font-size: 13px;
}
.store-buy-block .line-block{
	font-size: 12px;
}
    .price-tier__quantity.font_14.color_666 {
        font-size: 13px;
    }
    
    .price-tier__value.font_14.color_222.font-weight-bold {
        font-size: 15px;
    }
    
    .price-tier-nav-up {
        left: -30px;
    }
    
    .price-tier-nav-down {
        right: -30px;
    }
}

@media (max-width: 1440px) {
.store-prices { 
        width: 59%;
	}
}

@media (max-width: 1370px) {
.store-prices {  
        width: 61%;
        }
}

@media (max-width: 1330px) {
.store-prices {  
        width: 63%;
        }
}

@media (max-width: 1300px) {
.store-prices {  
        width: 65%;
        }
}

@media (max-width: 1250px) {
.store-prices {  
        width: 67%;
        }
}

/* Для экранов меньше 1200px */
@media (max-width: 1200px) {
    .price-tier {
/*        width: 150px; */
        padding: 8px 10px;
        min-height: 52px;
    }

.catalog-detail__main{
	width: 76%;
}
    
    .price-tier__quantity.font_14.color_666 {
        font-size: 12px;
    }
    
    .price-tier__value.font_14.color_222.font-weight-bold {
        font-size: 14px;
    }
}

/* Для экранов меньше 768px */
@media (max-width: 768px) {
    .price-tier {
        width: 140px;
        padding: 7px 9px;
        min-height: 48px;
    }
    
    .price-tier__quantity.font_14.color_666 {
        font-size: 11px;
    }
    
    .price-tier__value.font_14.color_222.font-weight-bold {
        font-size: 13px;
    }
    
    .price-tier-nav-btn {
        width: 24px;
        height: 24px;
    }
    
    .price-tier-nav-up {
        left: -25px;
    }
    
    .price-tier-nav-down {
        right: -25px;
    }
}

/* Для экранов меньше 480px */
@media (max-width: 480px) {
    .price-tier {
        width: 130px;
        padding: 6px 8px;
        min-height: 44px;
    }
    
    .price-tier__quantity.font_14.color_666 {
        font-size: 10px;
    }
    
    .price-tier__value.font_14.color_222.font-weight-bold {
        font-size: 12px;
    }
    
    .price-tier-nav-btn {
        width: 22px;
        height: 22px;
    }
    
    .price-tier-nav-up {
        left: -20px;
    }
    
    .price-tier-nav-down {
        right: -20px;
    }
}

/* Для экранов меньше 360px */
@media (max-width: 360px) {
    .price-tier {
        width: 120px;
        padding: 5px 7px;
    }
    
    .price-tier-nav-btn {
        width: 20px;
        height: 20px;
    }
}

</style>

<script>
// Данные о ценах для JavaScript
var storePriceData = {
    productId: <?=$arResult['ID']?>,
    baseProductId: <?=$arResult['ID']?>,
    currency: '<?=$arResult['PRICES']['BASE']['CURRENCY']?>',
    basePrice: <?=$arResult['PRICES']['BASE']['VALUE']?>,
    extendedPrices: <?=json_encode(array_values(array_filter($arResult['EXTENDED_PRICES'], function($price) {
        return $price['TYPE'] == 'BASE';
    }))) ?>,
    stores: {
        <?php foreach ($storeData as $store): ?>
        <?=$store['ID']?>: {
            name: '<?=htmlspecialcharsbx($store['NAME'])?>',
            quantity: <?=$store['QUANTITY']?>,
            maxQuantity: <?=$store['QUANTITY']?>,
            deliveryTime: '<?=htmlspecialcharsbx($store['DELIVERY_TIME'])?>',
            cartId: 'product_<?=$arResult['ID']?>_store_<?=$store['ID']?>'
        },
        <?php endforeach; ?>
    }
};

// Объект для хранения количества в корзине по складам
var storeCartQuantities = {};
var totalQuantity = 0;
var totalPrice = 0;

// Храним текущие позиции прокрутки для каждого склада
var priceTierPositions = {};

// ================== ФУНКЦИИ ДЛЯ НАВИГАЦИИ ЦЕНОВЫХ ТИРОВ ==================

// Функция для прокрутки ценовых тиров
function scrollPriceTiers(storeId, direction) {
    // Инициализируем позицию если её нет
    if (typeof priceTierPositions[storeId] === 'undefined') {
        priceTierPositions[storeId] = 0;
    }
    
    var currentPosition = priceTierPositions[storeId];
    var allTiers = document.querySelectorAll('.price-tier[data-store-id="' + storeId + '"]');
    var totalTiers = allTiers.length;
    
    if (totalTiers <= 3) return; // Если тиров 3 или меньше, не нужно навигации
    
    // Определяем новую позицию
    if (direction === 'up' && currentPosition > 0) {
        priceTierPositions[storeId] = currentPosition - 1;
    } else if (direction === 'down' && currentPosition < totalTiers - 3) {
        priceTierPositions[storeId] = currentPosition + 1;
    } else {
        return; // Не выходим за границы
    }
    
    // Обновляем отображение
    updatePriceTiersDisplay(storeId);
}

// Функция для обновления отображения ценовых тиров
function updatePriceTiersDisplay(storeId) {
    var currentPosition = priceTierPositions[storeId] || 0;
    var allTiers = document.querySelectorAll('.price-tier[data-store-id="' + storeId + '"]');
    var totalTiers = allTiers.length;
    
    if (totalTiers <= 3) {
        // Если тиров 3 или меньше, показываем все
        allTiers.forEach(function(tier) {
            tier.style.display = 'flex';
        });
        return;
    }
    
    // Скрываем все тиры
    allTiers.forEach(function(tier) {
        tier.style.display = 'none';
    });
    
    // Показываем только 3 тира начиная с currentPosition
    for (var i = 0; i < 3; i++) {
        var index = currentPosition + i;
        if (index < totalTiers) {
            var tier = allTiers[index];
            if (tier) {
                tier.style.display = 'flex';
            }
        }
    }
    
    // Обновляем состояние кнопок навигации
    updateNavigationButtons(storeId, currentPosition, totalTiers);
}

// Функция для обновления состояния кнопок навигации
function updateNavigationButtons(storeId, currentPosition, totalTiers) {
    var upBtn = document.querySelector('.price-tier-nav-up[data-store-id="' + storeId + '"]');
    var downBtn = document.querySelector('.price-tier-nav-down[data-store-id="' + storeId + '"]');
    
    if (upBtn) {
        var upOpacity = currentPosition === 0 ? '0.4' : '0.84';
        upBtn.style.opacity = currentPosition === 0 ? '0.5' : '1';
        upBtn.disabled = currentPosition === 0;
        
        // Обновляем fill-opacity SVG
        var upPath = upBtn.querySelector('path');
        if (upPath) {
            upPath.setAttribute('fill-opacity', upOpacity);
        }
    }
    
    if (downBtn) {
        var downOpacity = currentPosition >= totalTiers - 3 ? '0.4' : '0.64';
        downBtn.style.opacity = currentPosition >= totalTiers - 3 ? '0.5' : '1';
        downBtn.disabled = currentPosition >= totalTiers - 3;
        
        // Обновляем fill-opacity SVG
        var downPath = downBtn.querySelector('path');
        if (downPath) {
            downPath.setAttribute('fill-opacity', downOpacity);
        }
    }
}

// Инициализация навигации ценовых тиров
function initPriceTiersNavigation() {
    // Инициализируем позиции для всех складов
    <?php foreach ($storeData as $store): ?>
    priceTierPositions[<?=$store['ID']?>] = 0;
    <?php endforeach; ?>
    
    // Обновляем отображение для всех складов
    <?php foreach ($storeData as $store): ?>
    <?php 
    $basePrices = array_filter($arResult['EXTENDED_PRICES'], function($price) {
        return $price['TYPE'] == 'BASE';
    });
    if (count($basePrices) > 3): ?>
    updatePriceTiersDisplay(<?=$store['ID']?>);
    <?php endif; ?>
    <?php endforeach; ?>
}

// Обновление подсветки текущего ценового тира с учетом прокрутки
function updatePriceTierHighlightWithScroll(storeId, quantity) {
    // Сначала обновляем стандартную подсветку
    updatePriceTierHighlight(storeId, quantity);
    
    // Проверяем, нужно ли прокручивать к текущему тиру
    if (quantity > 0) {
        var currentPosition = priceTierPositions[storeId] || 0;
        var allTiers = document.querySelectorAll('.price-tier[data-store-id="' + storeId + '"]');
        var currentTier = document.querySelector('.price-tier.current-price[data-store-id="' + storeId + '"]');
        
        // Если текущий тир не виден, прокручиваем к нему
        if (currentTier && allTiers.length > 3) {
            var tierIndex = parseInt(currentTier.getAttribute('data-tier-index')) || 0;
            
            // Если тир вне видимой области, прокручиваем
            if (tierIndex < currentPosition || tierIndex >= currentPosition + 3) {
                // Находим оптимальную позицию для показа тира
                if (tierIndex <= allTiers.length - 3) {
                    priceTierPositions[storeId] = tierIndex;
                } else {
                    // Если тир в конце, показываем последние 3
                    priceTierPositions[storeId] = Math.max(0, allTiers.length - 3);
                }
                updatePriceTiersDisplay(storeId);
            }
        }
    }
}

// ================== ОСНОВНЫЕ ФУНКЦИИ ==================

// Функция для показа уведомления о превышении остатков
function showStockExceededNotification(storeId, maxQuantity) {
    var notificationId = 'stock-exceeded-notification-' + storeId;
    var existingNotification = document.getElementById(notificationId);
    
    if (!existingNotification) {
        // Создаем элемент уведомления
        var notification = document.createElement('div');
        notification.id = notificationId;
        notification.className = 'stock-exceeded-notification';
        notification.style.cssText = 'color: #d9534f; padding: 5px 0; font-size: 13px; font-weight: bold; text-align: center;';
        notification.innerHTML = 'Максимально возможное кол-во для заказа ' + maxQuantity + ' шт.';
        
        // Вставляем уведомление после блока "В корзине"
        var cartBlock = document.getElementById('store-in-cart-' + storeId);
        if (cartBlock && cartBlock.parentNode) {
            cartBlock.parentNode.insertBefore(notification, cartBlock.nextSibling);
        }
    }
}

// Функция для скрытия уведомления о превышении остатков
function hideStockExceededNotification(storeId) {
    var notificationId = 'stock-exceeded-notification-' + storeId;
    var notification = document.getElementById(notificationId);
    if (notification) {
        notification.remove();
    }
}

function getUnitPriceForQuantity(quantity, storeId) {
    // Используем BASE цены из extendedPrices
    if (storePriceData.extendedPrices && Array.isArray(storePriceData.extendedPrices)) {
        // Сортируем цены по количеству от большего к меньшему
        var sortedPrices = storePriceData.extendedPrices.slice().sort(function(a, b) {
            var aQty = parseInt(a.QUANTITY_FROM) || 1;
            var bQty = parseInt(b.QUANTITY_FROM) || 1;
            return bQty - aQty;
        });
        
        // Ищем подходящий ценовый тир
        for (var i = 0; i < sortedPrices.length; i++) {
            var priceTier = sortedPrices[i];
            var tierQuantity = parseInt(priceTier.QUANTITY_FROM) || 1;
            if (quantity >= tierQuantity) {
                return parseFloat(priceTier.PRICE);
            }
        }
    }
    
    // Если не нашли подходящий тир, возвращаем базовую цену
    return storePriceData.basePrice;
}

function updatePriceTierHighlight(storeId, quantity) {
    // Убираем класс current-price у всех тиров этого склада
    var allTiers = document.querySelectorAll('.price-tier[data-store-id="' + storeId + '"]');
    allTiers.forEach(function(tier) {
        tier.classList.remove('current-price');
    });
    
    if (quantity === 0) return;
    
    // Находим подходящий тир и добавляем класс
    if (storePriceData.extendedPrices && Array.isArray(storePriceData.extendedPrices)) {
        // Сортируем цены по количеству от большего к меньшему
        var sortedPrices = storePriceData.extendedPrices.slice().sort(function(a, b) {
            var aQty = parseInt(a.QUANTITY_FROM) || 1;
            var bQty = parseInt(b.QUANTITY_FROM) || 1;
            return bQty - aQty;
        });
        
        // Ищем подходящий тир
        var selectedTierQuantity = 1;
        for (var i = 0; i < sortedPrices.length; i++) {
            var priceTier = sortedPrices[i];
            var tierQuantity = parseInt(priceTier.QUANTITY_FROM) || 1;
            if (quantity >= tierQuantity) {
                selectedTierQuantity = tierQuantity;
                break;
            }
        }
        
        // Добавляем класс current-price к нужному тиру
        var selectedTier = document.querySelector('.price-tier[data-store-id="' + storeId + '"][data-quantity-from="' + selectedTierQuantity + '"]');
        if (selectedTier) {
            selectedTier.classList.add('current-price');
        }
    }
}

//function formatCurrency(amount) {
    // Простое форматирование валюты
//    return amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$& ') + ' ' + storePriceData.currency;
//}

function formatCurrency(amount) {
    // Округляем до целого числа
    var roundedAmount = Math.round(amount);
    
    // Форматируем с пробелами тысяч и без десятичных
    return roundedAmount.toLocaleString('ru-RU', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }) + ' ₽ ';
}

// Функция для обновления общего итога
function updateTotalSummary() {
    totalQuantity = 0;
    totalPrice = 0;
    
    // Считаем общее количество и цену
    for (var storeId in storePriceData.stores) {
        var input = document.getElementById('store-quantity-' + storeId);
        var quantity = parseInt(input.value) || 0;
        
        if (quantity > 0) {
            var unitPrice = getUnitPriceForQuantity(quantity, storeId);
            totalQuantity += quantity;
            totalPrice += unitPrice * quantity;
        }
    }
    
    // Обновляем отображение
    document.getElementById('total-quantity').textContent = totalQuantity;
    document.getElementById('total-price').textContent = formatCurrency(totalPrice);
}

// Функция для отображения блока "В корзине"
function showInCartBlock(storeId, quantity, skipNotificationCheck) {
    var block = document.getElementById('store-in-cart-' + storeId);
    var quantitySpan = document.getElementById('store-cart-quantity-' + storeId);
    
    if (quantity > 0) {
        quantitySpan.textContent = quantity;
        block.style.display = 'block';
        
        // Убираем уведомление о превышении остатков, если количество допустимое
        var maxQuantity = storePriceData.stores[storeId].maxQuantity || 0;
        if (quantity <= maxQuantity) {
            hideStockExceededNotification(storeId);
        }
        
        // Обновляем значение в инпуте (синхронизируем)
        var input = document.getElementById('store-quantity-' + storeId);
        if (input) {
            input.disabled = false;
            input.style.opacity = '1';
            input.style.backgroundColor = '';
        }
        
        // Обновляем расчеты цены
        var unitPrice = getUnitPriceForQuantity(quantity, storeId);
        var totalPriceForStore = unitPrice * quantity;
        
        document.getElementById('store-total-qty-' + storeId).textContent = quantity;
        document.getElementById('store-unit-price-' + storeId).textContent = formatCurrency(unitPrice);
        document.getElementById('store-total-sum-' + storeId).textContent = formatCurrency(totalPriceForStore);
        
        // Обновляем подсветку с учетом прокрутки
        updatePriceTierHighlightWithScroll(storeId, quantity);
        
    } else {
        block.style.display = 'none';
        
        // Убираем уведомление о превышении остатков
        hideStockExceededNotification(storeId);
        
        // Обновляем значение в инпуте
        var input = document.getElementById('store-quantity-' + storeId);
        if (input) {
            input.disabled = false;
            input.style.opacity = '1';
            input.style.backgroundColor = '';
        }
        
        // Обновляем расчеты для нулевого количества
        document.getElementById('store-total-qty-' + storeId).textContent = '0';
        document.getElementById('store-unit-price-' + storeId).textContent = '0 ₽';
        document.getElementById('store-total-sum-' + storeId).textContent = '0 ₽';
        
        // Убираем подсветку у всех тиров
        var allTiers = document.querySelectorAll('.price-tier[data-store-id="' + storeId + '"]');
        allTiers.forEach(function(tier) {
            tier.classList.remove('current-price');
        });
    }
    
    // Обновляем общий итог
    updateTotalSummary();
}

// Обновленная функция updateStoreTotal
function updateStoreTotal(storeId) {
    var input = document.getElementById('store-quantity-' + storeId);
    var quantity = parseInt(input.value) || 0;
    
    // Получаем максимальное количество на складе
    var maxQuantity = storePriceData.stores[storeId].maxQuantity || 0;
    
    // Проверяем, не превышает ли количество остаток на складе
    if (quantity > maxQuantity) {
        // Показываем уведомление красным текстом
        showStockExceededNotification(storeId, maxQuantity);
        
        // Скрываем блок "В корзине" если количество превышает допустимое
        var block = document.getElementById('store-in-cart-' + storeId);
        if (block) {
            block.style.display = 'none';
        }
    } else {
        // Скрываем уведомление, если оно было показано (только когда количество стало допустимым)
        hideStockExceededNotification(storeId);
    }
    
    // Если количество 0, скрываем детали
    if (quantity === 0) {
        document.getElementById('store-total-qty-' + storeId).textContent = '0';
        document.getElementById('store-unit-price-' + storeId).textContent = '0 ₽';
        document.getElementById('store-total-sum-' + storeId).textContent = '0 ₽';
        
        // Убираем подсветку у всех тиров
        var allTiers = document.querySelectorAll('.price-tier[data-store-id="' + storeId + '"]');
        allTiers.forEach(function(tier) {
            tier.classList.remove('current-price');
        });
    } else {
        // Получаем цену за единицу в зависимости от количества
        var unitPrice = getUnitPriceForQuantity(quantity, storeId);
        var totalPriceForStore = unitPrice * quantity;
        
        // Обновляем отображение
        document.getElementById('store-total-qty-' + storeId).textContent = quantity;
        document.getElementById('store-unit-price-' + storeId).textContent = formatCurrency(unitPrice);
        document.getElementById('store-total-sum-' + storeId).textContent = formatCurrency(totalPriceForStore);
        
        // Обновляем подсветку ценового тира
        updatePriceTierHighlightWithScroll(storeId, quantity);
    }
    
    // ОБНОВЛЯЕМ КОРЗИНУ АВТОМАТИЧЕСКИ при изменении количества (только если количество допустимое)
    var currentCartQuantity = storeCartQuantities[storeId] || 0;
    if (quantity !== currentCartQuantity && quantity <= maxQuantity) {
        // Обновляем локальное состояние
        storeCartQuantities[storeId] = quantity;
        
        // Показываем блок "В корзине" (только если количество допустимое)
        showInCartBlock(storeId, quantity, true);
        
        // Отправляем запрос на обновление корзины (с задержкой для debounce)
        if (window.updateCartTimeout) {
            clearTimeout(window.updateCartTimeout);
        }
        
        window.updateCartTimeout = setTimeout(function() {
            updateCartFromInputs();
        }, 1000);
    } else if (quantity === 0 && currentCartQuantity > 0) {
        // Если количество стало 0, а в корзине было что-то
        storeCartQuantities[storeId] = 0;
        showInCartBlock(storeId, 0, true);
        
        if (window.updateCartTimeout) {
            clearTimeout(window.updateCartTimeout);
        }
        
        window.updateCartTimeout = setTimeout(function() {
            updateCartFromInputs();
        }, 1000);
    }
    
    // Обновляем общий итог
    updateTotalSummary();
}

// Новая функция для обновления корзины на основе текущих значений в инпутах
function updateCartFromInputs() {
    var storeQuantities = {};
    var hasQuantity = false;
    
    // Собираем количество по всем складам (только допустимые значения)
    for (var storeId in storePriceData.stores) {
        var input = document.getElementById('store-quantity-' + storeId);
        var quantity = parseInt(input.value) || 0;
        var maxQuantity = storePriceData.stores[storeId].maxQuantity || 0;
        
        // Добавляем в корзину только если количество допустимое
        if (quantity > 0 && quantity <= maxQuantity) {
            storeQuantities[storeId] = quantity;
            hasQuantity = true;
        }
    }
    
    // Отправляем запрос на обновление корзины (только допустимые значения)
    updateStoresInCart(storeQuantities, null, '', false);
}

// Обновленная функция updateStoresInCart
function updateStoresInCart(storeQuantities, button, originalText, showSuccessMessage) {
    var formData = new FormData();
    formData.append('action', 'update_stores_in_cart');
    formData.append('product_id', storePriceData.productId);
    formData.append('store_quantities', JSON.stringify(storeQuantities));
    formData.append('sessid', BX.bitrix_sessid());
    
    fetch('/bitrix/templates/aspro-lite/ajax/cart_store.php', {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.status === 'success') {
            // Успешное обновление
            if (showSuccessMessage && button) {
                button.innerHTML = 'Обновлено в корзине!';
                
                setTimeout(function() {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 2000);
            }
            
            // Обновляем локальное состояние
            if (data.updated_quantities) {
                storeCartQuantities = data.updated_quantities;
                
                // Синхронизируем инпуты с данными из корзины (только допустимые значения)
                for (var storeId in storeCartQuantities) {
                    var quantity = storeCartQuantities[storeId] || 0;
                    var maxQuantity = storePriceData.stores[storeId].maxQuantity || 0;
                    var input = document.getElementById('store-quantity-' + storeId);
                    
                    if (input && quantity <= maxQuantity) {
                        // Обновляем значение в инпуте
                        input.value = quantity;
                        // Показываем блок "В корзине"
                        showInCartBlock(storeId, quantity, true);
                    }
                }
                
                // Для складов, которых нет в обновленных данных, устанавливаем 0
                for (var storeId in storePriceData.stores) {
                    if (!storeCartQuantities.hasOwnProperty(storeId)) {
                        var input = document.getElementById('store-quantity-' + storeId);
                        if (input) {
                            // НЕ сбрасываем значение в инпуте, только скрываем блок "В корзине"
                            showInCartBlock(storeId, 0, true);
                        }
                    }
                }
            }
            
            // Обновляем корзину если доступно
            if (typeof BX !== 'undefined') {
                BX.onCustomEvent('OnBasketChange');
            }
            
        } else {
            throw new Error(data.message || 'Unknown error');
        }
    })
    .catch(function(error) {
        console.error('Ошибка обновления корзины:', error);
        if (button) {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    });
}

// Функция для удаления товара из корзины
function removeFromCart(storeId) {
    var input = document.getElementById('store-quantity-' + storeId);
    if (input) {
        input.value = 0;
        updateStoreTotal(storeId);
    }
}

// Функция для добавления всех выбранных товаров в корзину
function addAllStoresToCart(button) {
    var storeQuantities = {};
    var hasQuantity = false;
    var hasExceededStock = false;
    var exceededStores = [];
    
    // Собираем количество по всем складам и проверяем остатки
    for (var storeId in storePriceData.stores) {
        var input = document.getElementById('store-quantity-' + storeId);
        var quantity = parseInt(input.value) || 0;
        var maxQuantity = storePriceData.stores[storeId].maxQuantity || 0;
        
        if (quantity > 0) {
            if (quantity > maxQuantity) {
                hasExceededStock = true;
                exceededStores.push(storePriceData.stores[storeId].name);
                // Показываем уведомление для пользователя
                showStockExceededNotification(storeId, maxQuantity);
            } else {
                storeQuantities[storeId] = quantity;
                hasQuantity = true;
            }
        }
    }
    
    if (!hasQuantity && !hasExceededStock) {
        alert('Пожалуйста, укажите количество хотя бы для одного склада');
        return;
    }
    
    if (hasExceededStock) {
        var message = 'Исправьте количество для складов, где оно превышает доступный остаток:\n';
        exceededStores.forEach(function(storeName) {
            message += '• ' + storeName + '\n';
        });
        alert(message);
        return;
    }
    
    // Показываем загрузку
    button.disabled = true;
    var originalText = button.innerHTML;
    button.innerHTML = 'Добавляем...';
    
    // Отправляем все склады одним запросом
    updateStoresInCart(storeQuantities, button, originalText, true);
}

// Функция для загрузки состояния корзины
function loadCartState() {
    var formData = new FormData();
    formData.append('action', 'get_cart_state');
    formData.append('product_id', storePriceData.productId);
    formData.append('sessid', BX.bitrix_sessid());
    
    fetch('/bitrix/templates/aspro-lite/ajax/cart_store.php', {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.status === 'success') {
            // Обновляем количество в инпутах из корзины
            storeCartQuantities = data.store_quantities || {};
            
            // Заполняем только те склады, где есть товары в корзине
            var hasItemsInCart = false;
            for (var storeId in storeCartQuantities) {
                var quantity = storeCartQuantities[storeId] || 0;
                if (quantity > 0) {
                    var input = document.getElementById('store-quantity-' + storeId);
                    if (input) {
                        input.value = quantity;
                        // Показываем блок "В корзине"
                        showInCartBlock(storeId, quantity, true);
                        // Вызываем updateStoreTotal для проверки остатков
                        updateStoreTotal(storeId);
                        hasItemsInCart = true;
                    }
                }
            }
            
            // Если нет товаров в корзине, оставляем все 0
            if (!hasItemsInCart) {
                for (var storeId in storePriceData.stores) {
                    var input = document.getElementById('store-quantity-' + storeId);
                    if (input) {
                        // НЕ сбрасываем значение в инпуте, только скрываем блок "В корзине"
                        showInCartBlock(storeId, 0, true);
                        updateStoreTotal(storeId);
                    }
                }
            }
            
            // Обновляем общий итог
            updateTotalSummary();
        }
    })
    .catch(function(error) {
        console.error('Ошибка загрузки состояния корзины:', error);
    });
}

// ================== ИНИЦИАЛИЗАЦИЯ ==================

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Инициализируем все блоки с количеством 0
    <?php foreach ($storeData as $store): ?>
    updateStoreTotal(<?=$store['ID']?>);
    <?php endforeach; ?>
    
    // Инициализируем навигацию ценовых тиров
    initPriceTiersNavigation();
    
    // Вешаем обработчики на кнопки навигации ценовых тиров
    document.querySelectorAll('.price-tier-nav-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var storeId = this.getAttribute('data-store-id');
            var direction = this.getAttribute('data-direction');
            if (storeId && direction) {
                scrollPriceTiers(storeId, direction);
            }
        });
    });
    
    // Вешаем обработчик на кнопку добавления в корзину
    var addButton = document.querySelector('.js-add-all-stores-to-cart');
    if (addButton) {
        addButton.addEventListener('click', function() {
            addAllStoresToCart(this);
        });
    }
    
    // Вешаем обработчики на кнопки удаления
    document.querySelectorAll('.btn-remove-from-cart').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var storeId = this.getAttribute('data-store-id');
            removeFromCart(storeId);
        });
    });
    
    // Загружаем состояние корзины при загрузке страницы
    setTimeout(loadCartState, 500);
});

// Слушаем изменения в корзине
if (typeof BX !== 'undefined') {
    BX.addCustomEvent('OnBasketChange', function() {
        setTimeout(loadCartState, 100);
    });
}

// CSS стили для навигации (добавьте в стили)
// ================== ФУНКЦИЯ ДЛЯ ВАЛИДАЦИИ И ПОДСВЕТКИ ==================

// Функция для проверки валидации
function validateInputs(showMessage = true, highlightEmpty = false) {
    var hasQuantity = false;
    var hasErrors = false;
    var emptyInputs = [];
    var exceededInputs = [];
    
    // Проверяем все склады
    for (var storeId in storePriceData.stores) {
        var input = document.getElementById('store-quantity-' + storeId);
        var quantity = parseInt(input.value) || 0;
        var maxQuantity = storePriceData.stores[storeId].maxQuantity || 0;
        
        if (quantity > 0) {
            hasQuantity = true;
            
            // Проверяем превышение остатков
            if (quantity > maxQuantity) {
                hasErrors = true;
                exceededInputs.push(storeId);
            } 
		else {
                highlightInputError(storeId, 'none');
            }
        } else {
            // Если quantity = 0, проверяем нужно ли подсвечивать пустые
            if (highlightEmpty && showMessage) {
                emptyInputs.push(storeId);
                highlightInputError(storeId, 'empty');
            } else {
                highlightInputError(storeId, 'none');
            }
        }
    }
    
    // Если нужно показать сообщение об ошибке (при нажатии кнопки)
    if (showMessage) {
        if (!hasQuantity) {
            // Подсвечиваем ВСЕ инпуты красным
            for (var storeId in storePriceData.stores) {
                highlightInputError(storeId, 'empty');
            }
            showGeneralError();
            return false;
        }
        
        if (hasErrors) {
            showGeneralErrorWithSpecifics();
            return false;
        }
        
        // Если все хорошо, скрываем возможное предыдущее сообщение
        hideGeneralError();
    }
    
    return hasQuantity && !hasErrors;
}

// Функция для подсветки инпута с ошибкой
function highlightInputError(storeId, errorType) {
    var input = document.getElementById('store-quantity-' + storeId);
    if (!input) return;
    
    // Сначала убираем все классы ошибок
    input.classList.remove('error-empty', 'error-exceeded');
    
    switch(errorType) {
        case 'empty':
            input.classList.add('error-empty');
            input.style.borderColor = '#dc3545';
            input.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
            input.style.backgroundColor = '#fff5f5';
            break;
        case 'exceeded':
            input.classList.add('error-exceeded');
            input.style.borderColor = '#ffc107';
            input.style.boxShadow = '0 0 0 0.2rem rgba(255, 193, 7, 0.25)';
            input.style.backgroundColor = '#fff9e6';
            break;
        case 'none':
        default:
            input.style.borderColor = '#ddd';
            input.style.boxShadow = 'none';
            input.style.backgroundColor = '';
            break;
    }
}

// Функция для сброса всех ошибок
function resetAllErrors() {
    for (var storeId in storePriceData.stores) {
        highlightInputError(storeId, 'none');
    }
    hideGeneralError();
}

// Функция для показа общего сообщения об ошибке (только при нажатии кнопки)
function showGeneralError() {
    // Удаляем предыдущее сообщение если есть
    hideGeneralError();
    
    var errorDiv = document.createElement('div');
    errorDiv.id = 'general-quantity-error';
    errorDiv.style.cssText = 'color: #dc3545; padding: 10px 15px; margin: 15px 0; font-size: 14px; font-weight: bold; text-align: center;';
    errorDiv.innerHTML = 'Пожалуйста, укажите количество хотя бы для одного склада';
    
    // Вставляем сообщение над блоком с итогом
    var totalBlock = document.querySelector('.total');
    if (totalBlock && totalBlock.parentNode) {
        totalBlock.parentNode.insertBefore(errorDiv, totalBlock);
    }
    
    // Автоматически скрываем сообщение через 5 секунд
    setTimeout(hideGeneralError, 5000);
}


// Функция для скрытия общего сообщения об ошибке
function hideGeneralError() {
    var existingError = document.getElementById('general-quantity-error');
    if (existingError) {
        existingError.remove();
    }
}

// ================== ОБНОВЛЕННАЯ ФУНКЦИЯ addAllStoresToCart ==================

// Обновленная функция для добавления всех выбранных товаров в корзину
function addAllStoresToCart(button) {
    // Сначала выполняем валидацию с показом сообщений и подсветкой пустых
    if (!validateInputs(true, true)) {
        return false;
    }
    
    // Если валидация прошла, собираем данные
    var storeQuantities = {};
    for (var storeId in storePriceData.stores) {
        var input = document.getElementById('store-quantity-' + storeId);
        var quantity = parseInt(input.value) || 0;
        var maxQuantity = storePriceData.stores[storeId].maxQuantity || 0;
        
        if (quantity > 0 && quantity <= maxQuantity) {
            storeQuantities[storeId] = quantity;
        }
    }
    
    // Проверяем, есть ли что добавлять (после валидации это должно быть true)
    if (Object.keys(storeQuantities).length === 0) {
        return false;
    }
    
    // Показываем загрузку
    button.disabled = true;
    var originalText = button.innerHTML;
    button.innerHTML = 'Добавляем...';
    
    // Отправляем все склады одним запросом
    updateStoresInCart(storeQuantities, button, originalText, true);
    
    return true;
}

// ================== ОБНОВЛЕННАЯ ФУНКЦИЯ updateStoreTotal ==================

// Обновленная функция updateStoreTotal (БЕЗ показа общего сообщения)
function updateStoreTotal(storeId) {
    var input = document.getElementById('store-quantity-' + storeId);
    var quantity = parseInt(input.value) || 0;
    
    // Получаем максимальное количество на складе
    var maxQuantity = storePriceData.stores[storeId].maxQuantity || 0;
    
    // Проверяем, не превышает ли количество остаток на складе
    if (quantity > maxQuantity) {
        // Показываем уведомление красным текстом
        showStockExceededNotification(storeId, maxQuantity);
        
        // Скрываем блок "В корзине" если количество превышает допустимое
        var block = document.getElementById('store-in-cart-' + storeId);
        if (block) {
            block.style.display = 'none';
        }
    } else {
        // Скрываем уведомление, если оно было показано
        hideStockExceededNotification(storeId);
        
        // Если количество нормальное, убираем подсветку ошибки
        if (quantity > 0) {
            highlightInputError(storeId, 'none');
        } else {
            highlightInputError(storeId, 'none');
        }
    }
    
    // Обновляем количество даже если есть ошибка (для отображения)
    updateStoreDisplay(storeId, quantity, maxQuantity);
    
    // Проверяем валидацию (НО НЕ ПОКАЗЫВАЕМ СООБЩЕНИЕ - только подсветку превышений)
    validateInputs(false, false);
    
    // Автоматическое обновление корзины (только если количество допустимое)
    updateCartAutomatically(storeId, quantity, maxQuantity);
    
    // Обновляем общий итог
    updateTotalSummary();
}

// Вспомогательная функция для обновления отображения
function updateStoreDisplay(storeId, quantity, maxQuantity) {
    if (quantity === 0) {
        document.getElementById('store-total-qty-' + storeId).textContent = '0';
        document.getElementById('store-unit-price-' + storeId).textContent = '0 ₽';
        document.getElementById('store-total-sum-' + storeId).textContent = '0 ₽';
        
        // Убираем подсветку у всех тиров
        var allTiers = document.querySelectorAll('.price-tier[data-store-id="' + storeId + '"]');
        allTiers.forEach(function(tier) {
            tier.classList.remove('current-price');
        });
    } else {
        // Получаем цену за единицу в зависимости от количества
        var unitPrice = getUnitPriceForQuantity(quantity, storeId);
        var totalPriceForStore = unitPrice * quantity;
        
        // Обновляем отображение
        document.getElementById('store-total-qty-' + storeId).textContent = quantity;
        document.getElementById('store-unit-price-' + storeId).textContent = formatCurrency(unitPrice);
        document.getElementById('store-total-sum-' + storeId).textContent = formatCurrency(totalPriceForStore);
        
        // Обновляем подсветку ценового тира
        updatePriceTierHighlightWithScroll(storeId, quantity);
    }
}

// Вспомогательная функция для автоматического обновления корзины
function updateCartAutomatically(storeId, quantity, maxQuantity) {
    var currentCartQuantity = storeCartQuantities[storeId] || 0;
    
    // Обновляем корзину только если количество допустимое
    if (quantity !== currentCartQuantity && quantity <= maxQuantity) {
        // Обновляем локальное состояние
        storeCartQuantities[storeId] = quantity;
        
        // Показываем блок "В корзине" (только если количество допустимое)
        showInCartBlock(storeId, quantity, true);
        
        // Отправляем запрос на обновление корзины (с задержкой для debounce)
        if (window.updateCartTimeout) {
            clearTimeout(window.updateCartTimeout);
        }
        
        window.updateCartTimeout = setTimeout(function() {
            updateCartFromInputs();
        }, 1000);
    } else if (quantity === 0 && currentCartQuantity > 0) {
        // Если количество стало 0, а в корзине было что-то
        storeCartQuantities[storeId] = 0;
        showInCartBlock(storeId, 0, true);
        
        if (window.updateCartTimeout) {
            clearTimeout(window.updateCartTimeout);
        }
        
        window.updateCartTimeout = setTimeout(function() {
            updateCartFromInputs();
        }, 1000);
    }
}

// ================== ОБНОВЛЕННАЯ ИНИЦИАЛИЗАЦИЯ ==================

// Обновленная инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Инициализируем все блоки с количеством 0
    <?php foreach ($storeData as $store): ?>
    updateStoreTotal(<?=$store['ID']?>);
    <?php endforeach; ?>
    
    // Инициализируем навигацию ценовых тиров
    initPriceTiersNavigation();
    
    // Вешаем обработчики на кнопки навигации ценовых тиров
    document.querySelectorAll('.price-tier-nav-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var storeId = this.getAttribute('data-store-id');
            var direction = this.getAttribute('data-direction');
            if (storeId && direction) {
                scrollPriceTiers(storeId, direction);
            }
        });
    });
    
    // Вешаем обработчик на кнопку добавления в корзину
    var addButton = document.querySelector('.js-add-all-stores-to-cart');
    if (addButton) {
        addButton.addEventListener('click', function() {
            addAllStoresToCart(this);
        });
    }
    
    // Вешаем обработчики на кнопки удаления
    document.querySelectorAll('.btn-remove-from-cart').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var storeId = this.getAttribute('data-store-id');
            removeFromCart(storeId);
        });
    });
    
    // Вешаем обработчики на изменение инпутов для мгновенной валидации
    document.querySelectorAll('.store-quantity-input-field').forEach(function(input) {
        input.addEventListener('input', function() {
            var storeId = this.getAttribute('data-store-id');
            // Скрываем общее сообщение при начале ввода
            hideGeneralError();
            // Сбрасываем подсветку ошибок для этого инпута
            highlightInputError(storeId, 'none');
            updateStoreTotal(storeId);
        });
        
        input.addEventListener('focus', function() {
            // Скрываем общее сообщение при фокусе на инпут
            hideGeneralError();
            var storeId = this.getAttribute('data-store-id');
            // Сбрасываем подсветку ошибок для этого инпута
            highlightInputError(storeId, 'none');
        });
        
        input.addEventListener('blur', function() {
            var storeId = this.getAttribute('data-store-id');
            updateStoreTotal(storeId);
        });
    });
    
    // Загружаем состояние корзины при загрузке страницы
    setTimeout(loadCartState, 500);
});

// Обновленный CSS для ошибок
var errorStyles = document.createElement('style');
errorStyles.textContent = `
.store-quantity-input-field.error-empty {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    background-color: #fff5f5 !important;
    animation: pulse-red 0.5s ease;
}

.store-quantity-input-field.error-exceeded {
    border-color: #ffc107 !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
    background-color: #fff9e6 !important;
}

#general-quantity-error {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes pulse-red {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}

.total {
    margin-top: 20px !important;
}
`;
document.head.appendChild(errorStyles);

</script>

<?php endif; ?>

    <?// Блок с кнопками корзины (скрыт, т.к. используем кнопки складов) ?>
    <? if ($bSKU2 && $arResult['HAS_SKU']) :?>
        <?=$btnHtml?>
    <? else: ?>
        <div class="catalog-detail__cart js-replace-btns js-config-btns hidden" data-btn-config='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arBtnConfig, false, true))?>'>
            <?=$btnHtml?>
        </div>
    <? endif; ?>

    <?// Блок с общей стоимостью?>
    <div id="js-price-data" class="js-price-data"
         data-currency="<?=$arResult['PRICES']['BASE']['CURRENCY']?>"
         data-base-price="<?=$arResult['PRICES']['BASE']['VALUE']?>"
         data-offer-id="<?=$currentOfferId?>"
         data-tiers='<?=\Bitrix\Main\Web\Json::encode(array_values(array_filter($arResult['EXTENDED_PRICES'], function($price) {
             return $price['TYPE'] == 'BASE';
         })), JSON_UNESCAPED_UNICODE)?>'
         data-offer-tiers='<?=\Bitrix\Main\Web\Json::encode($offerTiersMap, JSON_UNESCAPED_UNICODE)?>'>
    </div>
    
    <div id="js-total-block" class="product-total line-block line-block--align-baseline font_16"
         style="display:none;">
        <div class="line-block__item"><span id="js-total-qty">1</span> шт.</div>
        <div class="line-block__item">по: <strong id="js-za-odin"></strong></div>
        <div class="line-block__item">итого: <strong id="js-total-sum"></strong></div>
    </div>

    <?// Схема для микроразметки?>
    <?
    $offersScheme = new TSolution\Scheme\Offers([
        'ITEM' => $arResult,
        'DISCOUNT' => $discountDateTo,
    ]);
    $offersScheme->show();
    ?>
</div>

<style>
.current-price {
/*    background-color: #163760BD; */
 
}

price-tier__quantity font_14 color_666{
	color: #222222A3;
}
.current-price .price-tier__quantity.font_14.color_666{
	color: #222222A3;
	font-weight: 400;
}
.current-price .price-tier__value.font_14.color_222.font-weight-bold{
	background: #163760BD;
	color: #FFFFFF;
}

.store-quantity-input-field {
    padding: 5px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
}

.store-quantity-input-field:focus {
    border-color: #163760;
    outline: none;
}

.price-tier.current-price .price-tier__value {
    color: #fff;
    font-weight: bold;
}

.price-tier.current-price .price-tier__quantity {
    color: #fff;
    font-weight: bold;
}

.store-add-to-cart-button {
    text-align: center;
    padding: 15px 0;
/*    border-top: 1px solid #e8e8e8; */
}

.total-card {
    text-align: center;
    font-size: 18px;
    font-weight: bold;
}
</style>

                </div>
            </div>
        </div>

		<?if($bShowAdditionalBlock):?>
                    <div class="grid-list__item">
                        <div class="catalog-detail__forms catalog-detail__cell-block grid-list grid-list--items-1 outer-rounded-x shadow font_14">
                            <?// Блок с ценой?>
                            <div class="grid-list__item">
                                <div class="product-price">
								<div class="price color_222">
                                                                <div class="line-block line-block--6 line-block--align-baseline line-block--flex-wrap">
                            <div class="line-block__item">
                                        <div class="price__new">
                        <span class="price__new-val font_18 font_14--to-600">
                                                                                                 <?php
                                    // Базовая розничная цена
                                    $basePrice = $arResult['PRICES']['BASE']['VALUE']; // 5000
                                    
                                    // Проверяем наличие тиров цен (только для базового типа)
                                    $hasTierPrices = false;
                                    if (isset($arResult['EXTENDED_PRICES'])) {
                                        foreach ($arResult['EXTENDED_PRICES'] as $price) {
                                            if ($price['TYPE'] == 'BASE' && $price['QUANTITY_FROM'] > 1) {
                                                $hasTierPrices = true;
                                                break;
                                            }
                                        }
                                    }
                                    
                                    // Форматируем вывод
                                    if ($hasTierPrices) {
                                        echo 'от ' . number_format($basePrice, 0, '', ' ') . ' ₽';
                                    } else {
                                        echo '' . number_format($basePrice, 0, '', ' ') . ' ₽';
                                    }
                                    ?>
								                                                </span>
                    </div>
                                            </div>
                                                                                    </div>
                                    </div>

                                </div>
                            </div>

                            <?// status?>
                            <? if (strlen($status)): ?>
                                <div class="grid-list__item">
                                    <?if ($bUseSchema):?>
                                        <?= TSolution\Functions::showSchemaAvailabilityMeta($statusCode); ?>
                                    <?endif;?>
                                    <?= TSolution\Product\Common::showModalBlock([
                                        'NAME' => $status,
                                        'NAME_CLASS' => "js-replace-status status-icon ".($bSKU2 && $arResult['HAS_SKU'] ? "" : $statusCode),
                                        'SVG_PATH' => '/catalog/item_status_icons.svg#'.$statusCode.'_lg',
                                        'SVG_SIZE' => ['WIDTH' => 16, 'HEIGHT' => 16],
                                        'USE_SIZE_IN_PATH' => false,
                                        'ICON_CLASS' => 'status__svg-icon '.$statusCode,
                                        'WRAPPER' => 'status-container color_222 ' . $statusCode,
                                        'DATA_ATTRS' => [
                                            'state' => $statusCode,
                                            'code' => $arResult['DISPLAY_PROPERTIES']['STATUS']['VALUE_XML_ID'],
                                            'value' => $arResult['DISPLAY_PROPERTIES']['STATUS']['VALUE'],
                                        ]
                                    ]); ?>
                                </div>
                            <? endif; ?>
                            <?// calculate delivery?>
                            <? if (
                                $bShowCalculateDelivery &&
                                !($bSKU2 && $arResult['HAS_SKU'])
                            ): ?>
                                <div class="grid-list__item">
                                <?
                                $arConfig = [
                                    'NAME' => $arParams['EXPRESSION_FOR_CALCULATE_DELIVERY'],
                                    'SVG_NAME' => 'delivery',
                                    'SVG_SIZE' => ['WIDTH' => 16, 'HEIGHT' => 15],
                                    'SVG_PATH' => '/catalog/item_order_icons.svg#delivery',
                                    'WRAPPER' => 'stroke-dark-light-block dark_link animate-load',
                                    'DATA_ATTRS' => [
                                        'event' => 'jqm',
                                        'param-form_id' => 'delivery',
                                        'name' => 'delivery',
                                        'param-product_id' => $arCurrentSKU ? $arCurrentSKU['ID'] : $arResult['ID'],
                                    ]
                                ];

                                if ($arParams['USE_REGION'] === 'Y' && $arParams['STORES'] && is_array($arParams['STORES'])) {
                                    $arConfig['DATA_ATTRS']['param-region_stores_id'] = implode(',', $arParams['STORES']);
                                }
                                ?>
                                <?= TSolution\Product\Common::showModalBlock($arConfig); ?>
                                <? unset($arConfig); ?>
                                </div>
                            <? endif; ?>

                            <?// found cheaper?>
                            <? if ($bShowCheaperForm): ?>
                                <div class="grid-list__item">
                                    <?= TSolution\Product\Common::showModalBlock([
                                        'NAME' => $arParams['CHEAPER_FORM_NAME'],
                                        'SVG_NAME' => 'valet',
                                        'SVG_PATH' => '/catalog/item_order_icons.svg#valet',
                                        'SVG_SIZE' => ['WIDTH' => 16, 'HEIGHT' => 16],
                                        'WRAPPER' => 'stroke-dark-light-block dark_link animate-load',
                                        'DATA_ATTRS' => [
                                            'event' => "jqm",
                                            'param-id' => TSolution::partnerName . '_' . TSolution::solutionName . '_cheaper',
                                            'name' => 'cheaper',
                                            'autoload-product_name' => TSolution::formatJsName($arCurrentSKU ? $arCurrentSKU['NAME'] : $arResult['NAME']),
                                            'autoload-product_id' => $arCurrentSKU ? $arCurrentSKU['ID'] : $arResult['ID'],
                                        ]
                                    ]); ?>
                                </div>
                            <? endif; ?>

                            <?// send gift?>
                            <? if ($bShowSendGift): ?>
                                <div class="grid-list__item">
                                    <?= TSolution\Product\Common::showModalBlock([
                                        'NAME' => $arParams['SEND_GIFT_FORM_NAME'],
                                        'SVG_NAME' => 'gift',
                                        'WRAPPER' => 'stroke-dark-light-block dark_link animate-load',
                                        'SVG_SIZE' => ['WIDTH' => 16, 'HEIGHT' => 17],
                                        'SVG_PATH' => '/catalog/item_order_icons.svg#gift',
                                        'DATA_ATTRS' => [
                                            'event' => "jqm",
                                            'param-id' => TSolution::partnerName . '_' . TSolution::solutionName . '_send_gift',
                                            'name' => 'send_gift',
                                            'autoload-product_name' => TSolution::formatJsName($arResult["NAME"]),
                                            'autoload-product_link' => (CMain::IsHTTPS() ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . $APPLICATION->GetCurPage(),
                                            'autoload-product_id' => $arResult["ID"]
                                        ]
                                    ]); ?>
                                </div>
                            <? endif; ?>

                            <? if (trim(strip_tags($arResult['INCLUDE_CONTENT']))): ?>
                                <div class="grid-list__item">
                                    <?= TSolution\Product\Common::showModalBlock([
                                        'SVG_NAME' => 'gift',
                                        'SVG_PATH' => '/catalog/item_order_icons.svg#attention-16-16',
                                        'USE_SIZE_IN_PATH' => false,
                                        'SVG_SIZE' => ['WIDTH' => 17, 'HEIGHT' => 16],
                                        'TEXT' => $arResult['INCLUDE_CONTENT'],
                                        'WRAPPER' => 'fill-dark-light',
                                    ]); ?>
                                </div>
                            <? endif; ?>
                        </div>
                    </div>
                <? endif; ?>

    </div>
</div>

<?// props content?>
<?if ($templateData['SHOW_CHARACTERISTICS']):?>
    <?$this->SetViewTarget('PRODUCT_PROPS_INFO');?>
    <?TSolution\Functions::showBlockHtml([
        'FILE' => '/chars.php',
        'PARENT_COMPONENT' => $this->getComponent(),
        'PARAMS' => [
			'SECTION_PAGE_URL' => $arResult['SECTION']['SECTION_PAGE_URL'],
            'SMART_FILTER' => $arResult['SMART_FILTER'],
            'GRUPPER_PROPS' => $arParams['GRUPPER_PROPS'],
            'IBLOCK_ID' => $arResult['IBLOCK_ID'],
            'IBLOCK_TYPE' => $arResult['IBLOCK_TYPE'],
            'CHARACTERISTICS' => $arResult['CHARACTERISTICS'],
            'SKU_IBLOCK_ID' => $arParams['SKU_IBLOCK_ID'],
            'OFFER_PROP' => $arResult['OFFER_PROP'],
            'SHOW_HINTS' => $arParams['SHOW_HINTS'],
            'PROPERTIES_DISPLAY_TYPE' => $arParams['PROPERTIES_DISPLAY_TYPE'],
        ],
    ]);?>
    <?$this->EndViewTarget();?>
<?endif;?>
