<?php
global $arCrossItems;

$bTab = isset($tabCode) && $tabCode === 'associated';

if ($arParams['USE_ASSOCIATED_CROSS'] && isset($arCrossItems['ASSOCIATED'])) {
    $templateData['ASSOCIATED']['VALUE'] = $arCrossItems['ASSOCIATED'];
}
$filterName = 'arrAssociatedFilter';
?>
<?// show goods block?>
<?if ($templateData['ASSOCIATED']['VALUE']):?>
    <?if (!isset($html_goods)):?>
        <?php
        $GLOBALS[$filterName] = ['ID' => $templateData['ASSOCIATED']['VALUE']];
        $bCheckAjaxBlock = TSolution::checkRequestBlock('associated-list-inner');
        $isAjax = (TSolution::checkAjaxRequest() && $bCheckAjaxBlock) ? 'Y' : 'N';
        ?>
        <?ob_start();?>
            <?TSolution\Functions::showBlockHtml([
                'FILE' => '/detail_linked_goods.php',
                'PARAMS' => array_merge(
                    $arParams,
                    [
                        'ORDER_VIEW' => $bOrderViewBasket,
                        'CHECK_REQUEST_BLOCK' => $bCheckAjaxBlock,
                        'IS_AJAX' => $isAjax,
                        'ITEM_1200' => 5,
                        'ELEMENT_IN_ROW' => 5,
                        'FILTER_NAME' => $filterName,
                    ]
                ),
            ]);?>
        <?$html_associated = trim(ob_get_clean());?>
    <?endif;?>

    <?if ($html_associated && strpos($html_associated, 'error') === false):?>
        <?if ($bTab):?>
            <?if (!isset($arTabs[$tabCode])):?>
                <?$arTabs[$tabCode] = ['classList' => []];?>
            <?else:?>
                <div class="tab-pane<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList'], leadingDelimiter: true);?>" id="associated">
                    <div class="ajax-pagination-wrapper" data-class="associated-list-inner">
                        <?if ($isAjax === 'Y'):?>
                            <?$APPLICATION->RestartBuffer();?>
                        <?endif;?>
                            <?=$html_associated;?>
                        <?if ($isAjax === 'Y'):?>
                            <?exit;?>
                        <?endif;?>
                    </div>
                </div>
            <?endif;?>
        <?else:?>
            <div class="detail-block ordered-block associated">
                <h3 class="switcher-title"><?=$arParams['T_ASSOCIATED'];?></h3>
                <div class="ajax-pagination-wrapper" data-class="associated-list-inner">
                    <?if ($isAjax === 'Y'):?>
                        <?$APPLICATION->RestartBuffer();?>
                    <?endif;?>
                        <?=$html_associated;?>
                    <?if ($isAjax === 'Y'):?>
                        <?exit;?>
                    <?endif;?>
                </div>
            </div>
        <?endif;?>
    <?endif;?>
<?endif;?>
