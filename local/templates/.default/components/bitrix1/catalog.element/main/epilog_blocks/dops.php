<?$bTab = isset($tabCode) && $tabCode === 'dops';?>
<?// show dops block?>
<?if ($arParams['SHOW_DOPS'] === 'Y'):?>
    <?php
    if (!isset($html_dops)) {
        ob_start();
        $APPLICATION->IncludeFile($templateData['INCLUDE_FOLDER_PATH'].'/index_dops.php', [], ['MODE' => 'html', 'NAME' => GetMessage('T_DOPS')]);
        $html_dops = trim(ob_get_clean());
    }
    ?>
    <?if ($bTab):?>
        <?if (!isset($arTabs[$tabCode])):?>
            <?$arTabs[$tabCode] = ['classList' => []];?>
        <?else:?>
            <div class="tab-pane<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList'], leadingDelimiter: true);?>" id="dops">
                <?=$html_dops;?>
            </div>
        <?endif;?>
    <?else:?>
        <div class="detail-block ordered-block dops">
            <h3 class="switcher-title"><?=$arParams['T_DOPS'];?></h3>
            <?=$html_dops;?>
        </div>
    <?endif;?>
<?endif;?>
