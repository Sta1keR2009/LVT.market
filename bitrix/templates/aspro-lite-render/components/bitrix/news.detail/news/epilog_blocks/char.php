<?
use \Bitrix\Main\Localization\Loc;

$bTab = isset($tabCode) && $tabCode === 'char';
?>
<?//show char block?>
<?if($templateData['CHARACTERISTICS']):?>
    <?if($bTab):?>
        <?if(!isset($bShow_char)):?>
            <?$bShow_char = true;?>
        <?else:?>
            <div class="tab-pane <?=(!($iTab++) ? 'active' : '')?>" id="char">
                <h3 class="switcher-title"><?=$arParams["T_CHAR"]?></h3>
                <?$APPLICATION->ShowViewContent('PRODUCT_PROPS_INFO')?>
            </div>
        <?endif;?>
    <?else:?>
        <div class="detail-block ordered-block char">
            <h3 class="switcher-title"><?=$arParams["T_CHAR"]?></h3>
            <?$APPLICATION->ShowViewContent('PRODUCT_PROPS_INFO')?>
        </div>
    <?endif;?>
    <?
    TSolution\Extensions::init(['chars']);
    ?>
<?endif;?>