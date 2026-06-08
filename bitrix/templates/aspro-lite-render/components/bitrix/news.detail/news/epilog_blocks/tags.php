<?
use \Bitrix\Main\Localization\Loc;
?>
<?//show tags block?>
<?if($arParams["DETAIL_USE_TAGS"] == "Y" && $templateData["TAGS"]):?>

    <div class="detail-block ordered-block comments">
        <?$arTags = explode(",", $templateData['TAGS']);?>
        <div class="line-block line-block--6 line-block--6-vertical line-block--flex-wrap">
            <?foreach($arTags as $text):?>
                <div class="line-block__item">
                    <a href="<?=SITE_DIR;?>search/index.php?tags=<?=htmlspecialcharsex($text);?>" class="bordered chip chip--transparent" rel="nofollow">
                        <span class="chip__label font_14"><?=$text;?></span>
                    </a>
                </div>
            <?endforeach;?>
        </div>
    </div>
<?endif;?>