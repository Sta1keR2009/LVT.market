<?$bTab = isset($tabCode) && $tabCode === 'video';?>
<?// show video block?>
<?if ($templateData['VIDEO']):?>
    <?if ($bTab):?>
        <?if (!isset($arTabs[$tabCode])):?>
            <?$arTabs[$tabCode] = ['classList' => []];?>
        <?else:?>
            <div class="tab-pane<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList'], leadingDelimiter: true);?>" id="video">
                <?$APPLICATION->ShowViewContent('PRODUCT_VIDEO_INFO');?>
            </div>
        <?endif;?>
    <?else:?>
        <div class="detail-block ordered-block video">
            <h3 class="switcher-title"><?=$arParams['T_VIDEO'];?></h3>
            <?$APPLICATION->ShowViewContent('PRODUCT_VIDEO_INFO');?>
        </div>
    <?endif;?>
    <?TSolution\Extensions::init(['video']);?>
<?endif;?>
