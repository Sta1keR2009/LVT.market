<?
use \Bitrix\Main\Localization\Loc;
global $arRegion;
?>
<?//show news block?>
<?if($templateData['FILTER_URL']):?>
    <?$catalogIBlockID = $arParams['CATALOG_IBLOCK_ID'] ?? TSolution::GetFrontParametrValue('CATALOG_IBLOCK_ID');?>
    <?$filterName = 'arFilterCatalog'?>
    <?include_once('catalog/filter.php')?>

    <?
    $arFilter = [
        'IBLOCK_ID' => $catalogIBlockID,
        'ACTIVE' => 'Y'
    ];
    if ($templateData['SECTIONS']) {
        $arFilter['SECTION_ID'] = $templateData['SECTIONS'];
        $arFilter['INCLUDE_SUBSECTIONS'] = 'Y';
    }
    $iCountElement = TSolution\Cache::CIblockElement_GetList(
        [
            'CACHE' => [
                'TAG' => TSolution\Cache::GetIBlockCacheTag($catalogIBlockID),
                'MULTI' => 'N'
            ]
        ],
        array_merge($arFilter, $GLOBALS[$filterName]),
        []
    );
    ?>

    <?if($iCountElement):?>
        <?
        $arParams["PRICE_CODE"] = explode(',', TSolution::GetFrontParametrValue('PRICES_TYPE'));
        $arParams["STORES"] = explode(',', TSolution::GetFrontParametrValue('STORES'));
        if ($arRegion) {
            if ($arRegion['LIST_PRICES']) {
                $arParams["PRICE_CODE"] = array_keys($arRegion['LIST_PRICES']);
            }
            if ($arRegion['LIST_STORES']) {
                $arParams["STORES"] = $arRegion['LIST_STORES'];
            }

            $arParams['USE_REGION'] = 'Y';
        }
        ?>
        <?$GLOBALS[$filterName.'ITEMS'] = array_merge($arFilter, $GLOBALS[$filterName])?>

        <div class="detail-block ordered-block list_items">
            <?$isAjax = (\TSolution::checkAjaxRequest() ? 'Y' : 'N');?>
            <?if ($templateData['H3_GOODS']):?>
                <h3 class="switcher-title"><?=$templateData['H3_GOODS']?></h3>
            <?endif;?>

            <?if($isAjax == "N"):?>
                <?$frame = new \Bitrix\Main\Page\FrameHelper('catalog-elements-landing-block');
                $frame->begin();
                $frame->setAnimation(true);
                ?>
            <?endif;?>

            <?include_once('catalog/sort.php')?>

            <?
            //set params for props from module
            TSolution\Functions::replacePropsParams($arParams, ['PROPERTY_CODE' => 'LINKED_PROPERTY_CODE']);
            ?>

            <?if($isAjax == "Y"):?>
                <?$APPLICATION->RestartBuffer();?>
            <?endif;?>

            <?if($isAjax == "N"):?>
                <div class="ajax_load <?=$display;?>-view">
            <?endif;?>

            <?\TSolution\Functions::showBlockHtml([
                'FILE' => '/detail_list_goods.php',
                'PARAMS' => array_merge(
                    $arParams,
                    array(
                        'ORDER_VIEW' => $templateData['ORDER'],
                        'OPT_BUY' => $arParams['OPT_BUY'],
                        'SHOW_DISCOUNT' => $arParams['SHOW_DISCOUNT'],
                        'FILTER_NAME' => $filterName.'ITEMS',
                        'DISPLAY' => 'catalog_'.($display == 'price' ? 'table' : ($display == 'table' ? 'block' : $display)),
                        'AJAX' => $isAjax,
                        'LINE_TO_ROW' => $linerow,
                        'ELEMENT_IN_ROW' => 4,
                        'LINKED_CATALOG_COUNT' => $arParams['LINKED_CATALOG_COUNT'] ?? "20",
                        "ELEMENT_SORT_FIELD" => $arAvailableSort[$sortKey]["SORT"],
                        "ELEMENT_SORT_ORDER" => strtoupper($order),
                        "SHOW_PROPS_TABLE" => strtolower(\TSolution::GetFrontParametrValue('SHOW_TABLE_PROPS')),
                        "SKU_IBLOCK_ID"	=>	$arParams["SKU_IBLOCK_ID"],
                        "SKU_TREE_PROPS"	=>	$arParams["SKU_TREE_PROPS"],
                        "SKU_PROPERTY_CODE"	=>	$arParams["SKU_PROPERTY_CODE"],
                        "SKU_SORT_FIELD" => $arParams["SKU_SORT_FIELD"],
                        "SKU_SORT_ORDER" => $arParams["SKU_SORT_ORDER"],
                        "SKU_SORT_FIELD2" => $arParams["SKU_SORT_FIELD2"],
                        "SKU_SORT_ORDER2" =>$arParams["SKU_SORT_ORDER2"],
                        "ITEM_HOVER_SHADOW" =>true,
                    )
                )
            ]);?>

            <?if($isAjax == "N"):?>
                </div>
                <?$frame->end();?>
            <?else:?>
                <?die();?>
            <?endif;?>
        </div>
    <?endif;?>
<?endif;?>
