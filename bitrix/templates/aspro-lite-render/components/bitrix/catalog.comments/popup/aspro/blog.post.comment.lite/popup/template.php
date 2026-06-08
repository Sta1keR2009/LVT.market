<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

/** @global CMain $APPLICATION */
CJSCore::Init(array("image"));

TSolution\Extensions::init('uniform');

$application = \Bitrix\Main\Application::getInstance();
$request = $application->getContext()->getRequest();
$post = $request->getPostList();

$bAjaxPost = $arResult["is_ajax_post"] === 'Y';

if (!function_exists('makeInputsFromParams')) {
    function makeInputsFromParams($arParams, $name="PARAMS") {
        $result = "";

        if (is_array($arParams)) {
            foreach ($arParams as $key => $value) {
                if(substr($key, 0, 1) != "~") {
                    $inputName = $name.'['.$key.']';

                    if(is_array($value))
                        $result .= makeInputsFromParams($value, $inputName);
                    else
                        $result .= '<input type="hidden" name="'.$inputName.'" value="'.$value.'">'.PHP_EOL;
                }
            }
        }

        return $result;
    }
}
?>
<div class="flexbox">
    <div class="comments-block__inner-wrapper">
        <div class="blog-comments" id="blg-comment-<?=$arParams["ID"]?>">

            <?if ($bAjaxPost):?>
                <?$APPLICATION->RestartBuffer();?>
                <div>
                    <?if ($arResult['ajax_comment']):?>
                        <div class="flexbox">
                            <div class="form popup success">
                                <!--noindex-->
                                <div class="form-header">
                                    <div class="text">
                                        <div class="title switcher-title font_24 color_222"><?=Loc::getMessage('T_ADD_REVIEW_POPUP_TITLE')?></div>
                                        <?if (Loc::getMessage('T_ADD_REVIEW_POPUP_DESC')):?>
                                            <div class="form_desc fornt_16"><?=Loc::getMessage('T_ADD_REVIEW_POPUP_DESC')?></div>
                                        <?endif;?>
                                    </div>
                                </div>

                                <div class="form-body">
                                    <div class="form-inner form-inner--popup">
                                        <div class="form-send">
                                            <div class="flexbox flexbox--direction-column flexbox--align-center">
                                                <div class="form-send__icon">
                                                    <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/form_icons.svg#success-80-80', 'fill-more-theme', ['WIDTH' => 80,'HEIGHT' => 80]);?>
                                                </div>
                                                <div class="form-send__info">
                                                    <div class="form-send__info-title switcher-title font_24"><?=Loc::getMessage('T_ADD_REVIEW_POPUP_PHANKS_TEXT')?></div>
                                                    <div class="form-send__info-text">
                                                        <?=Loc::getMessage('T_ADD_REVIEW_POPUP_SUCCESS_SUBMIT_FORM');?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-footer">
                                    <div class="btn btn-transparent-border btn-lg jqmClose btn-wide"><?=Loc::getMessage('T_ADD_REVIEW_POPUP_CLOSE_BUTTON_NAME')?></div>
                                </div>
                            </div>
                        </div>
                    <?endif;?>
            <?endif;?>

            <?if (!$bAjaxPost):?>
                <? include($_SERVER["DOCUMENT_ROOT"] . $templateFolder . "/script.php"); ?>
            <?else:?>
                <script>
                window.BX = top.BX;
                <?if ($arResult["use_captcha"]):?>
                    var cc ='<?=$arResult["CaptchaCode"]?>';
                    if(BX('captcha')){
                        BX('captcha').src='/bitrix/tools/captcha.php?captcha_sid='+cc;
                    }

                    if(BX('captcha_sid')){
                        BX('captcha_sid').value = cc;
                    }

                    if(BX('captcha_word')){
                        BX('captcha_word').value = "";
                    }
                <?endif;?>
                </script>

                <?if (strlen($arResult["COMMENT_ERROR"])>0):?>
                    <script>top.commentEr = 'Y';</script>
                    <div class="alert alert-danger blog-note-box blog-note-error">
                        <div class="blog-error-text">
                            <?=$arResult["COMMENT_ERROR"]?>
                        </div>
                    </div>
                <?endif;?>
            <?endif;?>

            <?if (strlen($arResult["MESSAGE"]) > 0):?>
                <div class="blog-textinfo blog-note-box">
                    <div class="blog-textinfo-text">
                        <?=$arResult["MESSAGE"]?>
                    </div>
                </div>
            <?endif;?>

            <?if (strlen($arResult["ERROR_MESSAGE"]) > 0):?>
                <div class="alert alert-danger blog-note-box blog-note-error">
                    <div class="blog-error-text" id="blg-com-err">
                        <?=$arResult["ERROR_MESSAGE"]?>
                    </div>
                </div>
            <?endif;?>

            <?if (strlen($arResult["FATAL_MESSAGE"]) > 0):?>
                <div class="alert alert-danger blog-note-box blog-note-error">
                    <div class="blog-error-text">
                        <?=$arResult["FATAL_MESSAGE"]?>
                    </div>
                </div>
            <?else:?>
                <?if ($arResult["imageUploadFrame"] !== "Y"):?>
                    <?if (
                        !$bAjaxPost &&
                        $arResult["CanUserComment"]
                    ):?>
                        <?if (
                            strlen($arResult["COMMENT_ERROR"]) > 0
                            && strlen($post["parentId"]) < 2
                            && IntVal($post["parentId"])==0
                            && IntVal($post["edit_id"]) <= 0
                        ):?>
                            <div class="alert alert-danger blog-note-box blog-note-error">
                                <div class="blog-error-text"><?=$arResult["COMMENT_ERROR"]?></div>
                            </div>
                        <?endif;?>

                        <div class="js-form-comment" id="form_comment_0">
                            <div id="new_comment_0" style="display:none;"></div>

                            <div id="form_c_del">
                                <div class="blog-comment__form form popup">
                                    <form enctype="multipart/form-data" method="POST" name="form_comment" id="form_comment" action="<?=$templateFolder.'/ajax.php';?>">
                                        <input type="hidden" name="parentId" id="parentId" value="">
                                        <input type="hidden" name="edit_id" id="edit_id" value="">
                                        <input type="hidden" name="act" id="act" value="add">
                                        <input type="hidden" name="post" value="Y">

                                        <?if (isset($arParams["IBLOCK_ID"])):?>
                                            <input type="hidden" name="IBLOCK_ID" value="<?=(int)$arParams["IBLOCK_ID"]?>">
                                        <?endif;?>

                                        <?if (isset($arParams["ELEMENT_ID"])):?>
                                            <input type="hidden" name="ELEMENT_ID" value="<?=(int)$arParams["ELEMENT_ID"]?>">
                                        <?endif;?>

                                        <?if (isset($arParams["XML_ID"])):?>
                                            <input type="hidden" name="XML_ID" value="<?=$arParams["XML_ID"]?>">
                                        <?endif;?>

                                        <input type="hidden" name="SITE_ID" value="<?=htmlspecialcharsbx($arParams["SITE_ID"] ?? SITE_ID)?>">

                                        <?=makeInputsFromParams($arParams["PARENT_PARAMS"]); ?>
                                        <?=bitrix_sessid_post(); ?>

                                        <div class="blog-comment-fields">
                                            <div class="form-header">
                                                <div class="text">
                                                    <div class="title switcher-title font_24 color_222"><?=Loc::getMessage('T_ADD_REVIEW_POPUP_TITLE')?></div>
                                                    <?if (Loc::getMessage('T_ADD_REVIEW_POPUP_DESC')):?>
                                                        <div class="form_desc fornt_16"><?=Loc::getMessage('T_ADD_REVIEW_POPUP_DESC')?></div>
                                                    <?endif;?>
                                                </div>
                                            </div>

                                            <div class="form-body">
                                                <div id="err_comment_0"></div>

                                                <?if (empty($arResult["User"])):?>
                                                    <div class="blog-comment-field blog-comment-field-user">
                                                        <div class="row form">
                                                            <div class="col-md-12">
                                                                <div class="form-group <?=($_SESSION["blog_user_name"] ? 'input-filed' : '');?>">
                                                                    <label for="user_name"><?=Loc::getMessage("B_B_MS_NAME")?> <span class="required-star">*</span></label>
                                                                    <div class="input">
                                                                    <input maxlength="255" size="30" class="form-control" required tabindex="3" type="text" name="user_name" id="user_name" value="<?=htmlspecialcharsEx($_SESSION["blog_user_name"])?>">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group <?=($_SESSION["blog_user_email"] ? 'input-filed' : '');?>">
                                                                    <label for="user_email">E-mail</label>
                                                                    <div class="input">
                                                                    <input maxlength="255" size="30" class="form-control" tabindex="4" type="email" name="user_email" id="user_email" value="<?=htmlspecialcharsEx($_SESSION["blog_user_email"])?>">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?endif;?>

                                                <?if ($arParams["NOT_USE_COMMENT_TITLE"] != "Y"):?>
                                                    <div class="row form">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="user_sbj"><?=Loc::getMessage("BPC_SUBJECT")?></label>
                                                                <div class="input">
                                                                    <input maxlength="255" size="70" class="form-control" tabindex="3" type="text" name="subject" id="user_sbj" value="">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?endif;?>

                                                <?$rate = $arParams['RATE'] ?? '';?>
                                                <div class="row form">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label class="rating_label"><?=Loc::getMessage("BPC_RATING")?> <span class="required-star">*</span></label>
                                                            <div class="votes_block nstar big with-text" data-rating="<?=$rate?>">
                                                                <div class="ratings">
                                                                    <div class="inner_rating rating__star-svg">
                                                                        <?for($i=1; $i<=5; $i++):?>
                                                                            <div class="item-rating rating__star-svg<?=($rate >= $i ? ' rating__star-svg--filled' : '')?>" data-message="<?=Loc::getMessage('RATING_MESSAGE_'.$i); ?>">
                                                                                <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . "/images/svg/catalog/item_icons.svg#star-13-13", '', [
                                                                                    'WIDTH' => 16,
                                                                                    'HEIGHT' => 16,
                                                                                ]);?>
                                                                            </div>
                                                                        <?endfor;?>
                                                                    </div>
                                                                </div>
                                                                <div class="rating_message muted" data-message="<?=Loc::getMessage('RATING_MESSAGE_'.($rate ?: 0))?>"><?=Loc::getMessage('RATING_MESSAGE_'.($rate ?: 0))?></div>
                                                                <input class="hidden" name="rating" value="<?=$rate?>" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row form virtues">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="virtues"><?=Loc::getMessage("BPC_VIRTUES")?></label>
                                                            <div class="input">
                                                            <textarea rows="3" class="form-control" tabindex="3" name="virtues" id="virtues" value=""></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row form limitations">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="limitations"><?=Loc::getMessage("BPC_LIMITATIONS")?></label>
                                                            <div class="input">
                                                            <textarea rows="3" class="form-control" tabindex="3" name="limitations" id="limitations" value=""></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row form comment">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="comment"><?=Loc::getMessage("BPC_MESSAGE")?></label>
                                                            <div class="input">
                                                                <textarea rows="3" class="form-control" tabindex="3" name="comment" id="comment" value=""></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row form files">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <div class="input">
                                                                <input type="file" class="form-control inputfile" tabindex="3" name="comment_images[]" id="comment_images" value="">

                                                                <div class="add_file color-theme">
                                                                    <span class="font_12"><?=Loc::getMessage('JS_FILE_ADD');?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?if (strlen($arResult["NoCommentReason"]) > 0):?>
                                                    <div id="nocommentreason" style="display:none;"><?=$arResult["NoCommentReason"]?></div>
                                                <?endif;?>

                                                <?if ($arResult["use_captcha"]):?>
                                                    <div class="captcha-row clearfix fill-animate">
                                                        <label class="font_14" for="captcha_word"><span><?=Loc::getMessage("B_B_MS_CAPTCHA_SYM")?>&nbsp;<span class="required-star">*</span></span></label>
                                                        <div class="captcha_image">
                                                            <img data-src="" src="/bitrix/tools/captcha.php?captcha_sid=<?=htmlspecialcharsbx($arResult["CaptchaCode"])?>" width="180" height="40" id="captcha" border="0" class="captcha_img" />
                                                            <input type="hidden" id="captcha_sid" name="captcha_sid" class="captcha_sid" value="<?=htmlspecialcharsbx($arResult["CaptchaCode"])?>" />
                                                            <div class="captcha_reload"></div>
                                                            <span class="refresh"><a href="javascript:;" rel="nofollow"><?=Loc::getMessage("REFRESH")?></a></span>
                                                        </div>
                                                        <div class="captcha_input">
                                                            <input type="text" class="inputtext form-control captcha" name="captcha_word" size="30" maxlength="50" value="" required />
                                                        </div>
                                                    </div>
                                                <?endif;?>
                                            </div>

                                            <div class="form-footer clearfix">
                                                <div class="blog-comment-buttons-wrapper font_15">
                                                    <input tabindex="10" class="btn btn-default btn-lg btn-wide" value="<?=Loc::getMessage("B_B_MS_SEND")?>" type="button" name="sub-post" id="post-button" onclick="submitComment()">
                                                </div>

                                                <?if ($arParams['SHOW_LICENCE'] == 'Y'):?>
                                                    <div class="licence_block">
                                                        <label for="licenses_popup_form_comment">
                                                            <span><?include(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR."include/licenses_text.php"));?></span>
                                                        </label>
                                                        <input type="checkbox" class="form-checkbox__input" id="licenses_popup_form_comment" checked name="licenses_popup" required value="Y">
                                                    </div>
                                                <?endif;?>
                                            </div>
                                        </div>
                                        <input type="hidden" name="blog_upload_cid" id="upload-cid" value="">
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div id="new_comment_cont_0" class="hidden"></div>
                    <?endif;?>

                    <?
                    if ($bAjaxPost) {
                        $arParams["is_ajax_post"] = "Y";
                    }
                    ?>
                <?endif;?>
            <?endif;?>

            <?
            if ($bAjaxPost) {
                echo '</div>';
                die();
            }
            ?>
        </div>
    </div>
</div>
