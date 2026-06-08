<?$bTab = isset($tabCode) && $tabCode === 'docs';?>
<?// show docs block?>
<?if ($templateData['DOCUMENTS']):?>
    <?if ($bTab):?>
        <?if (!isset($arTabs[$tabCode])):?>
            <?$arTabs[$tabCode] = ['classList' => []];?>
        <?else:?>
            <div class="tab-pane<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList'], leadingDelimiter: true);?>" id="docs">
                <?$APPLICATION->ShowViewContent('PRODUCT_FILES_INFO');?>
            </div>
        <?endif;?>
    <?else:?>
        <div class="detail-block ordered-block docs">
            <h3 class="switcher-title"><?=$arParams['T_DOCS'];?></h3>
            <?$APPLICATION->ShowViewContent('PRODUCT_FILES_INFO');?>
        </div>
    <?endif;?>
    <?TSolution\Extensions::init(['docs']);?>
<?endif;?>
