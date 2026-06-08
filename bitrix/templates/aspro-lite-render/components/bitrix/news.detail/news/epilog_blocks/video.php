<?
use \Bitrix\Main\Localization\Loc;

$bTab = isset($tabCode) && $tabCode === 'video';
?>
<?//show video block?>
<?if($templateData['VIDEO']):?>
    <?if($bTab):?>
        <?if(!isset($bShow_video)):?>
            <?$bShow_video = true;?>
        <?else:?>
            <div class="tab-pane <?=(!($iTab++) ? 'active' : '')?>" id="video">
                <h3 class="switcher-title"><?=$arParams["T_VIDEO"]?></h3>
                <?$APPLICATION->ShowViewContent('PRODUCT_VIDEO_INFO')?>
            </div>
        <?endif;?>
    <?else:?>
        <div class="detail-block ordered-block video">
            <h3 class="switcher-title"><?=$arParams["T_VIDEO"]?></h3>
            <?$APPLICATION->ShowViewContent('PRODUCT_VIDEO_INFO')?>
        </div>
    <?endif;?>
    <?
    TSolution\Extensions::init(['video']);
    ?>
<?endif;?>