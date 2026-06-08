<?$bTab = isset($tabCode) && $tabCode === 'buy';?>
<?// show buy block?>
<?if ($arParams['SHOW_BUY'] === 'Y'):?>
    <?php
    if (!isset($html_buy)) {
        ob_start();
        $APPLICATION->IncludeFile($templateData['INCLUDE_FOLDER_PATH'].'/index_howbuy.php', [], ['MODE' => 'html', 'NAME' => GetMessage('T_BUY')]);
        $html_buy = trim(ob_get_clean());
    }
    ?>
    <?if ($bTab):?>
        <?if (!isset($arTabs[$tabCode])):?>
            <?$arTabs[$tabCode] = ['classList' => []];?>
        <?else:?>
            <div class="tab-pane<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList'], leadingDelimiter: true);?>" id="buy">
                <?=$html_buy;?>
            </div>
        <?endif;?>
    <?else:?>
        <div class="detail-block ordered-block buy">
            <h3 class="switcher-title"><?=$arParams['T_BUY'];?></h3>
            <?=$html_buy;?>
        </div>
    <?endif;?>
<?endif;?>
