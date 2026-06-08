<?$bTab = isset($tabCode) && $tabCode === 'big_gallery';?>
<?// show gallery block?>
<?if ($templateData['BIG_GALLERY']):?>
    <?if ($bTab):?>
        <?if (!isset($arTabs[$tabCode])):?>
            <?$arTabs[$tabCode] = ['classList' => []];?>
        <?else:?>
            <div class="tab-pane<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList'], leadingDelimiter: true);?>" id="big_gallery">
                <?$APPLICATION->ShowViewContent('PRODUCT_BIG_GALLERY_INFO');?>
            </div>
        <?endif;?>
    <?else:?>
        <div class="detail-block ordered-block big_gallery">
            <h3 class="switcher-title"><?=$arParams['T_BIG_GALLERY'];?></h3>
            <?$APPLICATION->ShowViewContent('PRODUCT_BIG_GALLERY_INFO');?>
        </div>
    <?endif;?>
    <?TSolution\Extensions::init(['gallery']);?>
<?endif;?>
