<?
use \Bitrix\Main\Localization\Loc;

$bTab = isset($tabCode) && $tabCode === 'goods';
?>
<?//show goods block?>
<?if($templateData['GOODS']['VALUE']):?>
    <?if(!isset($html_goods)):?>
        <?$GLOBALS['arrGoodsFilter'] = array('ID' => $templateData['GOODS']['VALUE']);?>
        <?
        $bCheckAjaxBlock = TSolution::checkRequestBlock("goods-list-inner");
        $isAjax = (TSolution::checkAjaxRequest() && $bCheckAjaxBlock ) ? 'Y' : 'N';
        ?>
        <?
        //set params for props from module
        TSolution\Functions::replacePropsParams($arParams, ['PROPERTY_CODE' => 'LINKED_PROPERTY_CODE']);
        ?>
        <?ob_start();?>
            <?TSolution\Functions::showBlockHtml([
                'FILE' => '/detail_linked_goods.php',
                'PARAMS' => array_merge(
                    $arParams,
                    array(
                        'ORDER_VIEW' => $bOrderViewBasket,
                        'CHECK_REQUEST_BLOCK' => $bCheckAjaxBlock,
					    'IS_AJAX' => $isAjax,
                        'ITEM_1200' => 4,
                    )
                )
            ]);?>
        <?$html_goods = trim(ob_get_clean());?>
    <?endif;?>

    <?if($html_goods && strpos($html_goods, 'error') === false):?>
        <?if (
            isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest" && 
            isset($_GET["ajax_get_filter"]) && $_GET["ajax_get_filter"] == "Y"
            ) {
            $isAjaxFilter="Y";
        }
        if (isset($isAjaxFilter) && $isAjaxFilter == "Y") {
            $isAjax="N";
        }
        ?>
        <?if($bTab):?>
            <?if(!isset($bShow_goods)):?>
                <?$bShow_goods = true;?>
            <?else:?>
                <div class="tab-pane <?=(!($iTab++) ? 'active' : '')?>" id="goods">
                    <h3 class="switcher-title"><?=$arParams["T_GOODS"]?></h3>
                    <div class="ajax-pagination-wrapper" data-class="goods-list-inner">
                        <div class="inner_wrapper relative">
                            <?if ($isAjax === 'Y'):?>
                                <?$APPLICATION->RestartBuffer();?>
                            <?endif;?>
                            <?=$html_goods?>
                            <?if ($isAjax === 'Y'):?>
                                <?die();?>
                            <?endif;?>
                        </div>
                    </div>
                </div>
            <?endif;?>
        <?else:?>
            <div class="detail-block ordered-block goods">
                <h3 class="switcher-title"><?=$arParams["T_GOODS"]?></h3>
                
                <div class="ajax-pagination-wrapper" data-class="goods-list-inner">
                    <div class="inner_wrapper relative">
                        <?if ($isAjax === 'Y'):?>
                            <?$APPLICATION->RestartBuffer();?>
                        <?endif;?>
                        <?=$html_goods?>
                        <?if ($isAjax === 'Y'):?>
                            <?die();?>
                        <?endif;?>
                    </div>
                </div>
            </div>
        <?endif;?>
    <?endif;?>
<?endif;?>