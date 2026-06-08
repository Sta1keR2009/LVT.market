<?$bTab = isset($tabCode) && $tabCode === 'kit';?>
<?if (TSolution::isSaleMode()):?>
    <?ob_start();?>
    <?if ($templateData['CATALOG_SETS']['SKU_SETS']):?>
        <?foreach($templateData['CATALOG_SETS']['SKU_SETS']['ITEMS'] as $skuID):?>
            <span data-sku_block_id="<?=$skuID;?>" <?=$skuID !== $templateData['CATALOG_SETS']['SKU_SETS']['CURRENT_ID'] ? "class='hidden'" : '';?>>
                <?$APPLICATION->IncludeComponent('bitrix:catalog.set.constructor', 'main',
                    [
                        'IBLOCK_ID' => $templateData['CATALOG_SETS']['SKU_SETS']['IBLOCK_ID'],
                        'ELEMENT_ID' => $skuID,
                        'PRICE_CODE' => $arParams['PRICE_CODE'],
                        'BASKET_URL' => $arParams['BASKET_URL'],
                        'OFFERS_CART_PROPERTIES' => $arParams['OFFERS_CART_PROPERTIES'],
                        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                        'CACHE_TIME' => $arParams['CACHE_TIME'],
                        'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                        'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
                        'BUNDLE_ITEMS_COUNT' => $arParams['BUNDLE_ITEMS_COUNT'],
                        'SHOW_MEASURE' => $arParams['SHOW_MEASURE'],
                        'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
                        'TITLE' => $arParams['T_NABOR'],
                        'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                        'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                        'ORDER_VIEW' => $arParams['ORDER_VIEW'],
                    ], $component, ['HIDE_ICONS' => 'Y']
                );?>
            </span>
        <?endforeach;?>
    <?else:?>
        <?$APPLICATION->IncludeComponent('bitrix:catalog.set.constructor', 'main',
            [
                'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                'ELEMENT_ID' => $arResult['ID'],
                'PRICE_CODE' => $arParams['PRICE_CODE'],
                'BASKET_URL' => $arParams['BASKET_URL'],
                'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                'CACHE_TIME' => $arParams['CACHE_TIME'],
                'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
                'BUNDLE_ITEMS_COUNT' => $arParams['BUNDLE_ITEMS_COUNT'],
                'SHOW_MEASURE' => $arParams['SHOW_MEASURE'],
                'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
                'TITLE' => $arParams['T_NABOR'],
                'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                'ORDER_VIEW' => $arParams['ORDER_VIEW'],
            ], $component, ['HIDE_ICONS' => 'Y']
        );?>
    <?endif;?>
    <?$html_kit = trim(ob_get_clean());?>
    <?if ($html_kit):?>
        <?if ($bTab):?>
            <?if (!isset($arTabs[$tabCode])):?>
                <?$arTabs[$tabCode] = ['classList' => []];?>
            <?else:?>
                <div class="tab-pane ordered-block--hide-icons<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList'], leadingDelimiter: true);?>" id="kit">
                    <?=$html_kit;?>
                </div>
            <?endif;?>
        <?else:?>
            <div class="detail-block ordered-block ordered-block--hide-icons kit">
                <h3 class="switcher-title"><?=$arParams['T_NABOR'];?></h3>
                <?=$html_kit;?>
            </div>
        <?endif;?>
    <?endif;?>
<?endif;?>
