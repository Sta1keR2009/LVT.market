<?$bTab = isset($tabCode) && $tabCode === 'desc';?>
<?// show desc block?>
<?if ($bTab):?>
    <?if (!isset($arTabs[$tabCode])):?>
        <?
        $arTabs[$tabCode] = ['classList' => []];
        if (empty($templateData['DETAIL_TEXT'])) {
            $arTabs[$tabCode]['classList'][] = 'hidden';
        }
        ?>
    <?else:?>
        <div class="tab-pane <?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList']);?>" id="desc">
            <?$APPLICATION->ShowViewContent('PRODUCT_DETAIL_TEXT_INFO');?>
        </div>
    <?endif;?>
<?else:?>
    <div class="detail-block ordered-block desc<?=empty($templateData['DETAIL_TEXT']) ? ' hidden' : '';?>">
        <?$APPLICATION->ShowViewContent('PRODUCT_DETAIL_TEXT_INFO');?>
    </div>
<?endif;?>
