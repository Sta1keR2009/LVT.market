<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}
$this->setFrameMode(false);

global $arTheme;

TSolution\Extensions::init(['profile']);

if ($arResult['PHONE_REGISTRATION']) {
    TSolution\Extensions::init('phoneorlogin');
}

if(isset($APPLICATION->arAuthResult)) {
    $arResult['ERROR_MESSAGE'] = $APPLICATION->arAuthResult;
}

$bEmailAsLogin = TSolution::GetFrontParametrValue('LOGIN_EQUAL_EMAIL') === 'Y';
$fieldLang = $bEmailAsLogin ? GetMessage('forgot_pass_email') : GetMessage('forgot_pass_login');
$bByPhoneRequest = $arResult['PHONE_REGISTRATION'] && isset($_POST['USER_PHONE_NUMBER']) && isset($_POST['send_account_info']);

?>
<div class="forgotpasswd-page pk-page">
    <div class="form">
        <?if($arResult['ERROR_MESSAGE']):?>
            <div class="alert <?= $arResult['ERROR_MESSAGE']['TYPE'] === 'OK' ? 'alert-success' : 'alert-danger'; ?>"><?=$arResult['ERROR_MESSAGE']['MESSAGE']; ?></div>
        <?endif; ?>

        <?if(
            !$arResult['ERROR_MESSAGE']
            || $arResult['ERROR_MESSAGE']['TYPE'] != 'OK'
        ):?>
            <form id="forgotpasswd-page-form" method="post" action="<?=POST_FORM_ACTION_URI; ?>" name="bform">
                <?$description_file = $bEmailAsLogin ? '_email' : ''; ?>
                <?if($arResult['PHONE_REGISTRATION']):?>
                    <?if (TSolution::checkContentFile(SITE_DIR.'include/forgotpasswd_phone'.$description_file.'_description.php')):?>
                        <div class="top-text font_16">
                            <?$APPLICATION->IncludeFile(SITE_DIR.'include/forgotpasswd_phone'.$description_file.'_description.php', [], ['MODE' => 'html', 'NAME' => '']); ?>
                        </div>
                    <?endif; ?>
                <?else:?>
                    <?if (TSolution::checkContentFile(SITE_DIR.'include/forgotpasswd'.$description_file.'_description.php')):?>
                        <div class="top-text font_16">
                            <?$APPLICATION->IncludeFile(SITE_DIR.'include/forgotpasswd'.$description_file.'_description.php', [], ['MODE' => 'html', 'NAME' => '']); ?>
                        </div>
                    <?endif; ?>
                <?endif; ?>

                <?if($arResult['BACKURL'] != ''):?>
                    <input type="hidden" name="backurl" value="<?=htmlspecialcharsbx($arResult['BACKURL']); ?>" />
                <?endif; ?>

                <input type="hidden" name="AUTH_FORM" value="Y">
                <input type="hidden" name="TYPE" value="SEND_PWD">

                <div class="form-body">
                    <?if($arResult['PHONE_REGISTRATION']):?>
                        <div class="form-group fill-animate phone_or_login">
                            <label for="FORGOTPASSWD_PHONE_OR_LOGIN" class="font_14"><span><?=GetMessage('forgot_pass_phone_number_or_login', ['#FIELD#' => $fieldLang]); ?>&nbsp;<span class="required-star">*</span></span></label>
                            <label for="FORGOTPASSWD_PHONE_OR_LOGIN" class="font_14"><span><?=GetMessage('AUTH_FORGOT_BY_LOGIN_OR_EMAIL', ['#FIELD#' => $fieldLang]); ?>&nbsp;<span class="required-star">*</span></span></label>
                            <label for="FORGOTPASSWD_PHONE_OR_LOGIN" class="font_14"><span><?=GetMessage('forgot_pass_phone_number'); ?>&nbsp;<span class="required-star">*</span></span></label>
                            <div class="input">
                                <input id="FORGOTPASSWD_PHONE_OR_LOGIN" class="form-control required" type="text" name="FORGOTPASSWD_PHONE_OR_LOGIN" maxlength="255" autocomplete="off" />
                                <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/phoneorlogin.svg#login-26-26', 'colored_theme_svg', ['WIDTH' => 26, 'HEIGHT' => 26]); ?>
                                <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/phoneorlogin.svg#phone-26-26', 'colored_theme_svg', ['WIDTH' => 26, 'HEIGHT' => 26]); ?>
                            </div>
                            <div class="text_block font_13"><?=GetMessage('forgot_pass_phone_number_note'); ?></div>
                            <div class="text_block font_13"><?=GetMessage('forgot_pass_login_note'); ?></div>
                        </div>
                    <?else:?>
                        <div class="form-group fill-animate">
                            <label for="FORGOTPASSWD_USER_LOGIN" class="font_14"><span><?=GetMessage('AUTH_FORGOT_BY_LOGIN_OR_EMAIL', ['#FIELD#' => $fieldLang]); ?>&nbsp;<span class="required-star">*</span></span></label>
                            <div class="input">
                                <input id="FORGOTPASSWD_USER_LOGIN" class="form-control required" type="<?= $bEmailAsLogin ? 'email' : 'text'; ?>" name="USER_LOGIN" required maxlength="255" autocomplete="off" />
                                <input type="hidden" name="USER_EMAIL" maxlength="255" autocomplete="off" />
                            </div>
                            <div class="text_block font_13"><?=GetMessage('forgot_pass_login_note'); ?></div>
                        </div>
                    <?endif; ?>

                    <?if ($arResult['USE_CAPTCHA']):?>
                        <?php
                        /**
                         * @var TSolution\Captcha\Service $captcha
                         */
                        $captcha = TSolution\Captcha::getInstance();
                        ?>
                        <div class="clearboth"></div>
                        <div class="captcha-row clearfix">
                            <label for="FORGOTPASSWD_CAPTCHA" class="font_14"><span><?= $captcha->isService() && $captcha->isActive() ? GetMessage('FORM_GENERAL_RECAPTCHA') : GetMessage('CAPTCHA_PROMT'); ?>&nbsp;<span class="required-star">*</span></span></label>
                            <div class="captcha_image">
                                <img src="/bitrix/tools/captcha.php?captcha_sid=<?=htmlspecialcharsbx($arResult['CAPTCHA_CODE']); ?>" class="captcha_img" border="0" />
                                <input type="hidden" name="captcha_sid" class="captcha_sid" value="<?=htmlspecialcharsbx($arResult['CAPTCHA_CODE']); ?>" />
                                <div class="captcha_reload"></div>
                                <span class="refresh"><a href="javascript:;" rel="nofollow"><?=GetMessage('REFRESH'); ?></a></span>
                            </div>
                            <div class="captcha_input">
                                <input id="FORGOTPASSWD_CAPTCHA" type="text" class="inputtext form-control captcha" name="captcha_word" size="30" maxlength="50" value="" required />
                            </div>
                        </div>
                        <div class="clearboth"></div>
                    <?endif; ?>
                </div>

                <div class="form-footer">
                    <button class="btn btn-default btn-lg btn-wide" type="submit" name="send_account_info" value=""><span><?=GetMessage('RETRIEVE'); ?></span></button>
                    <div class="clearboth"></div>
                </div>
            </form>

            <script type="text/javascript">
            $(document).ready(function(){
                $('#forgotpasswd-page-form').validate({
                    highlight: function(element){
                        $(element).parent().addClass('error');
                    },
                    unhighlight: function(element){
                        $(element).parent().removeClass('error');
                    },
                    submitHandler: function(form){
                        if($(form).valid()){
                            var $button = $(form).find('button[type=submit]');
                            if($button.length){

                                // sync USER_EMAIL value with USER_LOGIN
                                $(form).find('[name=USER_EMAIL]').val($(form).find('[name=USER_LOGIN]').val());

                                if(!$button.hasClass('loadings')){
                                    $button.addClass('loadings');

                                    var eventdata = {type: 'form_submit', form: form, form_name: 'FORGOT'};
                                    BX.onCustomEvent('onSubmitForm', [eventdata]);
                                }
                            }
                        }
                    },
                    errorPlacement: function(error, element){
                        error.insertAfter(element);
                    },
                });

                setTimeout(function(){
                    $('#forgotpasswd-page-form').find('input:visible').eq(0).focus();
                }, 50);

                $('#forgotpasswd-page-form .phone_or_login input').phoneOrLogin(function(input, test){
                    var $form = $(input).closest('form');
                    if(test.bPossiblePhone){
                        if(!$form.find('input[name=USER_PHONE_NUMBER]').length){
                            $form.find('input[name=USER_LOGIN],input[name=USER_EMAIL]').remove();
                            $form.prepend('<input type="hidden" name="USER_PHONE_NUMBER" />');
                        }
                        $form.find('input[name=USER_PHONE_NUMBER]').val(test.value);
                    }
                    else{
                        if(!$form.find('input[name=USER_LOGIN]').length){
                            $form.find('input[name=USER_PHONE_NUMBER],input[name=USER_EMAIL]').remove();
                            $form.prepend('<input type="hidden" name="USER_LOGIN" />');
                            $form.prepend('<input type="hidden" name="USER_EMAIL" />');
                        }
                        $form.find('input[name=USER_LOGIN]').val(test.value);
                        $form.find('input[name=USER_EMAIL]').val(test.value);
                    }
                });
            });
            </script>
        <?endif; ?>
    </div>
</div>
