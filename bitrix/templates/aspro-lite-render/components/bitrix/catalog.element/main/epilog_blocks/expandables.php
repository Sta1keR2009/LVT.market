<?php
global $arCrossItems;

$bTab = isset($tabCode) && $tabCode === 'expandables';

if ($arParams['USE_EXPANDABLES_CROSS'] && isset($arCrossItems['EXPANDABLES'])) {
    $templateData['EXPANDABLES']['VALUE'] = $arCrossItems['EXPANDABLES'];
}
$filterName = 'arrExpandablesFilter';
?>
<?// show expandables block?>
<?if ($templateData['EXPANDABLES']['VALUE']):?>
    <?if (!isset($html_expandables)):?>
        <?$GLOBALS[$filterName] = ['ID' => $templateData['EXPANDABLES']['VALUE']];?>
        <?php
        $bCheckAjaxBlock = TSolution::checkRequestBlock('expandables-list-inner');
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
        <?$html_expandables = trim(ob_get_clean());?>
    <?endif;?>

    <?if ($html_expandables && strpos($html_expandables, 'error') === false):?>
        <?if ($bTab):?>
            <?if (!isset($arTabs[$tabCode])):?>
                <?$arTabs[$tabCode] = ['classList' => []];?>
            <?else:?>
                <div class="tab-pane<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList'], leadingDelimiter: true);?>" id="expandables">
                    <div class="ajax-pagination-wrapper" data-class="expandables-list-inner">
                        <?if ($isAjax === 'Y'):?>
                            <?$APPLICATION->RestartBuffer();?>
                        <?endif;?>
                            <?=$html_expandables;?>
                        <?if ($isAjax === 'Y'):?>
                            <?exit;?>
                        <?endif;?>
                    </div>
                </div>
            <?endif;?>
        <?else:?>
            <div class="detail-block ordered-block expandables">
                <h3 class="switcher-title"><?=$arParams['T_EXPANDABLES'];?></h3>
                <div class="ajax-pagination-wrapper" data-class="expandables-list-inner">
                    <?if ($isAjax === 'Y'):?>
                        <?$APPLICATION->RestartBuffer();?>
                    <?endif;?>
                        <?=$html_expandables;?>
                    <?if ($isAjax === 'Y'):?>
                        <?exit;?>
                    <?endif;?>
                </div>
            </div>
        <?endif;?>
    <?endif;?>
<?endif;?>
