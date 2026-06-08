<?
use \Bitrix\Main\Localization\Loc;

$bTab = isset($tabCode) && $tabCode === 'delivery';
?>
<?//show delivery block?>
<?if($arParams["SHOW_DELIVERY"] === 'Y'):?>
    <?if($bTab):?>
        <?if(!isset($bShow_delivery)):?>
            <?$bShow_delivery = true;?>
        <?else:?>
            <div class="tab-pane <?=(!($iTab++) ? 'active' : '')?>" id="delivery">
                <?$APPLICATION->ShowViewContent('PRODUCT_DELIVERY_INFO')?>
            </div>
        <?endif;?>
    <?else:?>
        <div class="detail-block ordered-block delivery">
            <h3 class="switcher-title"><?=$arParams["T_DELIVERY"]?></h3>
            <?$APPLICATION->ShowViewContent('PRODUCT_DELIVERY_INFO')?>
        </div>
    <?endif;?>
<?endif;?>