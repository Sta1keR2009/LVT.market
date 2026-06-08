<?php
use \Bitrix\Main\Localization\Loc;
global $arRegion;
?>
<? if($arParams['SHOW_LINK_GOODS'] == 'Y' &&
    $arParams['LINK_GOODS_IBLOCK_ID'] > 0 &&
    in_array('catalog', $GLOBALS["SHOW_TYPE_ITEMS"])
):?>
    <?
    $catalogIBlockID = $arParams['LINK_GOODS_IBLOCK_ID'] ?? TSolution::GetFrontParametrValue('CATALOG_IBLOCK_ID');
    $bCheckAjaxBlock = TSolution::checkRequestBlock("goods-list-inner");
    $isAjax = (TSolution::checkAjaxRequest() && $bCheckAjaxBlock ) ? 'Y' : 'N';

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


    <div class="main-wrapper">
        <div class="detail-block ordered-block">
            <div class="js-load-wrapper ajax-pagination-wrapper <?=$APPLICATION->ShowViewContent("section_additional_class");?>" data-class="goods-list-inner">

                <?
                $GLOBALS['arrGoodsFilter'] = array(
                    'PROPERTY_' . $arParams['LINK_GOODS_PROP_CODE'] => $arResult['ID'],
                    'SECTION_GLOBAL_ACTIVE' => 'Y',
                    'ACTIVE' => 'Y',
                    'IBLOCK_ID' => $catalogIBlockID,
                );

                $arItems = TSolution\Cache::CIblockElement_GetList(
                    [
                        'CACHE' => [
                            'TAG' => TSolution\Cache::GetIBlockCacheTag($catalogIBlockID),
                            'MULTI' => 'Y'
                        ]
                    ],
                    $GLOBALS['arrGoodsFilter'],
                    false,
                    false,
                    array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID")
                );

                $arAllSections = $arSectionsID = $arItemsID = array();

                if($arItems) {

                    $setionIDRequest = (isset($_GET["section_id"]) && $_GET["section_id"] ? $_GET["section_id"] : 0);

                    foreach($arItems as $arItem)
                    {
                        $arItemsID[$arItem["ID"]] = $arItem["ID"];
                        if($arItem["IBLOCK_SECTION_ID"])
                        {
                            if(is_array($arItem["IBLOCK_SECTION_ID"]))
                            {
                                foreach($arItem["IBLOCK_SECTION_ID"] as $id)
                                {
                                    $arAllSections[$id]["COUNT"]++;
                                    $arAllSections[$id]["ITEMS"][$arItem["ID"]] = $arItem["ID"];
                                }
                            }
                            else
                            {
                                $arAllSections[$arItem["IBLOCK_SECTION_ID"]]["COUNT"]++;
                                $arAllSections[$arItem["IBLOCK_SECTION_ID"]]["ITEMS"][$arItem["ID"]] = $arItem["ID"];
                            }
                        }
                    }

                    $arSectionsID = array_keys($arAllSections);

                    $GLOBALS['preFilterBrand'] = array(
                        'ID' => $arItemsID,
                    );

                    include_once('catalog/sort.php');
                    ?>

                    <?
                    //set params for props from module
                    TSolution\Functions::replacePropsParams($arParams, ['PROPERTY_CODE' => 'LINKED_PROPERTY_CODE']);
                    ?>

                    <div class="inner_wrapper relative">
                        <?if ($isAjax === 'Y'):?>
                            <?$APPLICATION->RestartBuffer();?>
                        <?else:?>
                            <div class="ajax_load">
                        <?endif;?>
                        <?TSolution\Functions::showBlockHtml([
                            'FILE' => '/detail_list_goods.php',
                            'PARAMS' => array_merge(
                                $arParams,
                                array(
                                    'ORDER_VIEW' => $bOrderViewBasket,
                                    'CHECK_REQUEST_BLOCK' => $bCheckAjaxBlock,
                                    'SHOW_PROPS_TABLE' => strtolower(TSolution::GetFrontParametrValue('SHOW_TABLE_PROPS')),
                                    'AJAX' => $isAjax,
                                    'FILTER_NAME' => 'arrGoodsFilter',
                                    'DISPLAY' => 'catalog_'.($display == 'price' ? 'table' : ($display == 'table' ? 'block' : $display)),
                                    'LINE_TO_ROW' => $linerow,
                                    'ELEMENT_IN_ROW' => $APPLICATION->GetProperty('MENU') === 'Y' ? 4 : 5,
                                    "ELEMENT_SORT_FIELD" => $arAvailableSort[$sortKey]["SORT"],
                                    "ELEMENT_SORT_ORDER" => strtoupper($order),
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
                        <?if ($isAjax === 'Y'):?>
                            <?die();?>
                        <?else:?>
                            </div>
                        <?endif;?>
                    </div>

                <?
                }
                ?>
            </div>
        </div>
    </div>

<? endif; ?>
