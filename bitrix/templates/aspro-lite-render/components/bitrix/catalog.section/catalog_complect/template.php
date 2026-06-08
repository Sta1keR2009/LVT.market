<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

$this->setFrameMode(true);
?>
<?if($arResult['ITEMS']):?>
    <!-- items-container -->
    <?php
    $templateData['ITEMS'] = true;

    TSolution\Product\Price::checkCatalogModule(); // check included catalog module (\TSolution\Product\Price::$catalogInclude)
    $elementInRow = $arParams['ELEMENT_IN_ROW'];

    $bOrderViewBasket = $arParams['ORDER_VIEW'];

    $gridClass .= ' mobile-scrolled mobile-scrolled--items-3 mobile-offset';
    $gridClass .= ' grid-list--items-'.$elementInRow.'-1200 grid-list--items-'.($elementInRow - 1).'-992 grid-list--items-'.($elementInRow - 2).'-768 grid-list--items-2-601';

    $itemClass = ' outer-rounded-x bg-theme-parent-hover border-theme-parent-hover color-theme-parent-all js-popup-block';
    $itemClass .= ' shadow-hovered shadow-hovered-f600 shadow-no-border-hovered';
    $itemClass .= ' bordered';

    ?>

    <div class="catalog-items catalog_complect__template">
        <div class="fast_view_params" data-params="<?=urlencode(serialize($arTransferParams)); ?>"></div>
        <?if ($arResult['SKU_CONFIG']):?><div class="js-sku-config" data-value='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arResult['SKU_CONFIG'], false, true)); ?>'></div><?endif; ?>
        <div class="catalog-block">
            <div class="js_append ajax_load block grid-list grid-list--fill-bg <?=$gridClass; ?>">

        <?foreach($arResult['ITEMS'] as $arItem) {
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_EDIT'));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_DELETE'), ['CONFIRM' => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')]);

            $item_id = $arItem['ID'];
            $arItem['strMainID'] = $this->GetEditAreaId($arItem['ID']);

            $elementName = ((isset($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) && $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arItem['NAME']);
            $dataItem = TSolution::getDataItem($arItem);

            // unset($arItem['OFFERS']); // get correct totalCount
            $totalCount = TSolution\Product\Quantity::getTotalCount([
                'ITEM' => $arItem,
                'PARAMS' => $arParams,
            ]);

            /* sku replace start */
            $arCurrentOffer = $arItem['SKU']['CURRENT'];

            if ($arCurrentOffer) {
                $arItem['PARENT_IMG'] = '';
                if ($arItem['PREVIEW_PICTURE']) {
                    $arItem['PARENT_IMG'] = $arItem['PREVIEW_PICTURE'];
                } elseif ($arItem['DETAIL_PICTURE']) {
                    $arItem['PARENT_IMG'] = $arItem['DETAIL_PICTURE'];
                }

                $oid = Bitrix\Main\Config\Option::get(VENDOR_MODULE_ID, 'CATALOG_OID', 'oid');
                if ($oid) {
                    $arItem['DETAIL_PAGE_URL'] .= '?'.$oid.'='.$arCurrentOffer['ID'];
                    $arCurrentOffer['DETAIL_PAGE_URL'] = $arItem['DETAIL_PAGE_URL'];
                }

                if ($arCurrentOffer['PREVIEW_PICTURE'] || $arCurrentOffer['DETAIL_PICTURE']) {
                    if ($arCurrentOffer['PREVIEW_PICTURE']) {
                        $arItem['PREVIEW_PICTURE'] = $arCurrentOffer['PREVIEW_PICTURE'];
                    } elseif ($arCurrentOffer['DETAIL_PICTURE']) {
                        $arItem['PREVIEW_PICTURE'] = $arCurrentOffer['DETAIL_PICTURE'];
                    }
                }
                if (!$arCurrentOffer['PREVIEW_PICTURE'] && !$arCurrentOffer['DETAIL_PICTURE']) {
                    if ($arItem['PREVIEW_PICTURE']) {
                        $arCurrentOffer['PREVIEW_PICTURE'] = $arItem['PREVIEW_PICTURE'];
                    } elseif ($arItem['DETAIL_PICTURE']) {
                        $arCurrentOffer['PREVIEW_PICTURE'] = $arItem['DETAIL_PICTURE'];
                    }
                }

                $arItem['NAME'] = $arCurrentOffer['NAME'];
                $elementName = $arCurrentOffer['NAME'];

                $dataItem = TSolution::getDataItem($arCurrentOffer);

                $totalCount = TSolution\Product\Quantity::getTotalCount([
                    'ITEM' => $arCurrentOffer,
                    'PARAMS' => $arParams,
                ]);
            }
            /* sku replace end */?>
            <?ob_start(); ?>
            <?$itemDiscount = ob_get_clean(); ?>

            <div class="catalog-block__wrapper<?= $elementSliderClasses; ?> grid-list__item grid-list-border-outer <?= $arCurrentOffer ? 'has-offers' : ''; ?>"<?= $arItem['BIG_DATA'] ? ' data-bigdata data-rcm="'.$arItem['RCM_ID'].'"' : ''; ?> data-hovered="false">
                <div class="catalog-block__item <?=$itemClass; ?>" id="<?=$arItem['strMainID']; ?>">
                    <div class="catalog-block__inner flexbox height-100">
                        <?$arImgConfig = [
                            'TYPE' => 'catalog_block',
                            'ADDITIONAL_IMG_CLASS' => 'js-replace-img',
                        ]; ?>
                        <div class="js-config-img" data-img-config='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arImgConfig, false, true)); ?>'></div>

                        <?$priceHtml = $discountHtml = ''; ?>
                        <?php
                        $arPrices = TSolution\Product\Price::show([
                            'ITEM' => ($arCurrentOffer ? $arCurrentOffer : $arItem),
                            'PARAMS' => $arParams,
                            'BASKET' => $bOrderViewBasket,
                            'RETURN' => true,
                            'APART_ECONOMY' => true,
                            'PRICE_FONT' => '18 font_14--to-600',
                        ]);
                        if ($arPrices['PRICES']) {
                            $priceHtml = $arPrices['PRICES'];
                        }?>
                        <?=TSolution\Product\Image::showImage(
                            array_merge(
                                [
                                    'ITEM' => $arItem,
                                    'PARAMS' => $arParams,
                                    'CONTENT_TOP' => $itemDiscount,
                                    'CONTENT_BOTTOM' => $discountHtml,
                                ],
                                $arImgConfig
                            )
                        ); ?>
                        <?if(isset($arItem['QUANTITY_COMPLECT']['SHOW_QUANTITY']) && $arItem['QUANTITY_COMPLECT']['SHOW_QUANTITY'] === 'Y'):?>
                            <div class="quantity font_sxs">x<?=$arItem['QUANTITY_COMPLECT']['QUANTITY']; ?></div>
                        <?endif; ?>
                        <div
                            class="catalog-block__info flex-1 flexbox flexbox--justify-beetwen"
                            data-id="<?= $arCurrentOffer ? $arCurrentOffer['ID'] : $arItem['ID']; ?>"
                            data-item="<?=$dataItem; ?>"
                        >
                            <div class="catalog-block__info-top">
                                <div class="catalog-block__info-inner">
                                    <?// element price?>
                                    <?$arPriceConfig = [
                                        'PRICE_CODE' => $arParams['PRICE_CODE'],
                                        'PRICE_FONT' => '18 font_14--to-600',
                                        'PARAMS' => [
                                            'SHOW_DISCOUNT_PERCENT' => 'N', // hide discount in js_item_detail.php
                                        ],
                                    ]; ?>
                                    <div class="js-popup-price" data-price-config='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arPriceConfig, false, true)); ?>'>
                                        <?if ($priceHtml):?>
                                            <?=$priceHtml; ?>
                                        <?endif; ?>
                                    </div>
                                    <?// element title?>
                                    <div class="catalog-block__info-title linecamp-3 height-auto-t600 font_15 font_14--to-600">
                                        <a href="<?=$arItem['DETAIL_PAGE_URL']; ?>" class="dark_link switcher-title js-popup-title color-theme-target"><span><?=$elementName; ?></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <?}?>
        </div> <?// .js_append ajax_load block grid-list?>

        <div class="bottom_nav_wrapper nav-compact">
            <div class="bottom_nav <?= $bMobileScrolledItems ? 'hide-600' : ''; ?>" style= "display: none;" data-count="<?=$arResult['NAV_RESULT']->NavRecordCount; ?>" data-parent=".catalog-block" data-append=".grid-list">
                <?if($arParams['DISPLAY_BOTTOM_PAGER']):?>
                    <?=$arResult['NAV_STRING']; ?>
                <?endif; ?>
            </div>
        </div>
        </div> <?// .catalog-block?>
    </div> <?// .catalog-items?>
<?endif; ?>
