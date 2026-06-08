<?php
global $templateData;
if (!empty($templateData['LVT_HIDE_MOUSER_DESC_BLOCK'])) {
    return;
}
?>
<?$bTab = isset($tabCode) && $tabCode === 'desc';?>
<?// show desc block?>
<?if ($bTab):?>
    <?if (!isset($arTabs[$tabCode])):?>
        <?
        $arTabs[$tabCode] = ['classList' => []];
        ?>
    <?else:?>
        <div class="tab-pane <?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList']);?>" id="desc">
            <?$APPLICATION->ShowViewContent('PRODUCT_DETAIL_TEXT_INFO');?>
        </div>
    <?endif;?>
<?else:?>
    <div class="detail-block ordered-block desc">
        <?$APPLICATION->ShowViewContent('PRODUCT_DETAIL_TEXT_INFO');?>
    </div>
<?endif;?>
