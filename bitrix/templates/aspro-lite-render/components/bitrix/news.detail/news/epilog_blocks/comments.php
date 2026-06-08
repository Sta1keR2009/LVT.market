<?
use \Bitrix\Main\Localization\Loc;

$bTab = isset($tabCode) && $tabCode === 'comments';
?>
<?//show comments block?>
<?if($arParams["DETAIL_USE_COMMENTS"] == "Y" && $arParams["BLOG_USE"] == "Y"):?>
    <?if(!isset($html_comments)):?>
        <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/rating_likes.js");?>
        <?ob_start();?>
            <?$APPLICATION->IncludeComponent(
                "bitrix:catalog.comments",
                "main",
                array(
                    'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                    'CACHE_TIME' => $arParams['CACHE_TIME'],
                    'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                    "COMMENTS_COUNT" => (isset($arParams["MESSAGES_PER_PAGE"]) ? $arParams["MESSAGES_PER_PAGE"] : $arParams['COMMENTS_COUNT']),
                    "ELEMENT_CODE" => "",
                    "ELEMENT_ID" => $arResult["ID"],
                    "FB_USE" => $arParams["FB_USE"],
                    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                    "IBLOCK_TYPE" => "aspro_lite_catalog",
                    "SHOW_DEACTIVATED" => "N",
                    "TEMPLATE_THEME" => "blue",
                    "URL_TO_COMMENT" => "",
                    "VK_USE" => $arParams["VK_USE"],
                    "AJAX_POST" => "Y",
                    "WIDTH" => "",
                    "COMPONENT_TEMPLATE" => ".default",
                    "BLOG_USE" => $arParams["BLOG_USE"],
                    "BLOG_TITLE" => $arParams["BLOG_TITLE"],
                    "BLOG_URL" => $arParams["BLOG_URL"],
                    "PATH_TO_SMILE" => '/bitrix/images/blog/smile/',
                    "EMAIL_NOTIFY" => $arParams["BLOG_EMAIL_NOTIFY"],
                    "SHOW_SPAM" => "Y",
                    "SHOW_RATING" => "Y",
                    "RATING_TYPE" => "like_graphic",
                    "FB_TITLE" => $arParams["FB_TITLE"],
                    "FB_USER_ADMIN_ID" => "",
                    "FB_APP_ID" => $arParams["FB_APP_ID"],
                    "FB_COLORSCHEME" => "light",
                    "FB_ORDER_BY" => "reverse_time",
                    "VK_TITLE" => $arParams["VK_TITLE"],
                    "VK_API_ID" => $arParams["VK_API_ID"]
                ),
                false, array("HIDE_ICONS" => "Y")
            );?>
        <?$html_comments = trim(ob_get_clean());?>
    <?endif;?>

    <?if($html_comments && strpos($html_comments, 'error') === false):?>
        <?if($bTab):?>
            <?if(!isset($bShow_comments)):?>
                <?$bShow_comments = true;?>
            <?else:?>
                <div class="tab-pane <?=(!($iTab++) ? 'active' : '')?>" id="comments">
                    <?=$html_comments?>
                </div>
            <?endif;?>
        <?else:?>
            <div class="detail-block ordered-block comments">
                <?=$html_comments?>
            </div>
        <?endif;?>
    <?endif;?>
<?endif;?>