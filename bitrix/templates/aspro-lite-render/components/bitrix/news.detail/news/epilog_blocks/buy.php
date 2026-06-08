<?
use \Bitrix\Main\Localization\Loc;

$bTab = isset($tabCode) && $tabCode === 'buy';
?>
<?//show buy block?>
<?if($arParams["SHOW_BUY"] === 'Y'):?>
    <?if($bTab):?>
        <?if(!isset($bShow_buy)):?>
            <?$bShow_buy = true;?>
        <?else:?>
            <div class="tab-pane <?=(!($iTab++) ? 'active' : '')?>" id="buy">
                <?$APPLICATION->ShowViewContent('PRODUCT_BUY_INFO')?>
            </div>
        <?endif;?>
    <?else:?>
        <div class="detail-block ordered-block buy">
            <h3 class="switcher-title"><?=$arParams["T_BUY"]?></h3>
            <?$APPLICATION->ShowViewContent('PRODUCT_BUY_INFO')?>
        </div>
    <?endif;?>
<?endif;?>