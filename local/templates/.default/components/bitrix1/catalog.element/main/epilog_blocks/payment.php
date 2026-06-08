<?$bTab = isset($tabCode) && $tabCode === 'payment';?>
<?// show payment block?>
<?if ($arParams['SHOW_PAYMENT'] === 'Y'):?>
    <?php
    if (!isset($html_payment)) {
        ob_start();
        $APPLICATION->IncludeFile($templateData['INCLUDE_FOLDER_PATH'].'/index_payment.php', [], ['MODE' => 'html', 'NAME' => GetMessage('T_PAYMENT')]);
        $html_payment = trim(ob_get_clean());
    }
    ?>
    <?if ($html_payment && strpos($html_payment, 'error') === false):?>
        <?if ($bTab):?>
            <?if (!isset($arTabs[$tabCode])):?>
                <?$arTabs[$tabCode] = ['classList' => ['tab-pane']];?>
            <?else:?>
                <div class="<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList']);?>" id="payment">
                    <?= $html_payment;?>
                </div>
            <?endif;?>
        <?else:?>
            <div class="detail-block ordered-block payment">
                <h3 class="switcher-title"><?=$arParams['T_PAYMENT'];?></h3>
                <?= $html_payment;?>
            </div>
        <?endif;?>
    <?endif;?>
<?endif;?>
