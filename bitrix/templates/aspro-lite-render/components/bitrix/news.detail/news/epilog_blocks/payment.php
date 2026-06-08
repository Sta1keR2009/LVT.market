<?
use \Bitrix\Main\Localization\Loc;

$bTab = isset($tabCode) && $tabCode === 'payment';
?>
<?//show payment block?>
<?if($arParams["SHOW_PAYMENT"] === 'Y'):?>
    <?if($bTab):?>
        <?if(!isset($bShow_payment)):?>
            <?$bShow_payment = true;?>
        <?else:?>
            <div class="tab-pane <?=(!($iTab++) ? 'active' : '')?>" id="payment">
                <?$APPLICATION->ShowViewContent('PRODUCT_PAYMENT_INFO')?>
            </div>
        <?endif;?>
    <?else:?>
        <div class="detail-block ordered-block payment">
            <h3 class="switcher-title"><?=$arParams["T_PAYMENT"]?></h3>
            <?$APPLICATION->ShowViewContent('PRODUCT_PAYMENT_INFO')?>
        </div>
    <?endif;?>
<?endif;?>