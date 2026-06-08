<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(false);

TSolution\Extensions::init(['profile']);

if($arResult['PHONE_REGISTRATION']){
    CJSCore::Init('phone_auth');
    TSolution\Extensions::init('phonecode');
}

if(isset($APPLICATION->arAuthResult)){
    $arResult['ERROR_MESSAGE'] = $APPLICATION->arAuthResult;

    if($arResult['ERROR_MESSAGE']['TYPE'] === 'OK'){
        unset($_SESSION['system.auth.changepasswd']); // /bitrix/components/bitrix/system.auth.forgotpasswd/component.php:25
    }
}
$lastLogin = (isset($_SESSION['lastLoginSave']) ? $_SESSION['lastLoginSave'] : ($arResult["LAST_LOGIN"]?:''));
global $arTheme;
?>
<div class="changepasswd-page pk-page">
    <?if(!$arResult['ERROR_MESSAGE']):?>
        <?
        if(isset($_POST['LAST_LOGIN']) && empty($_POST['LAST_LOGIN'])){
            $arResult['ERRORS']['LAST_LOGIN'] = GetMessage('REQUIRED_FIELD');
        }
        if(isset($_POST['USER_PASSWORD']) && strlen($_POST['USER_PASSWORD']) < 6){
            $arResult['ERRORS']['USER_PASSWORD'] = GetMessage('PASSWORD_MIN_LENGTH_2');
        }
        if(isset($_POST['USER_PASSWORD']) && empty($_POST['USER_PASSWORD'])){
            $arResult['ERRORS']['USER_PASSWORD'] = GetMessage('REQUIRED_FIELD');
        }
        if(isset($_POST['USER_CONFIRM_PASSWORD']) && strlen($_POST['USER_CONFIRM_PASSWORD']) < 6 ){
            $arResult['ERRORS']['USER_CONFIRM_PASSWORD'] = GetMessage('PASSWORD_MIN_LENGTH_2');
        }
        if(isset($_POST['USER_CONFIRM_PASSWORD']) && empty($_POST['USER_CONFIRM_PASSWORD'])){
            $arResult['ERRORS']['USER_CONFIRM_PASSWORD'] = GetMessage('REQUIRED_FIELD');
        }
        if($_POST['USER_PASSWORD'] != $_POST['USER_CONFIRM_PASSWORD']){
            $arResult['ERRORS']['USER_CONFIRM_PASSWORD'] = GetMessage('WRONG_PASSWORD_CONFIRM');
        }
        ?>
    <?endif;?>

    <div class="form <?=($arResult['PHONE_REGISTRATION'] ? 'form--send-sms' : '')?>">
        <?if($arResult['ERROR_MESSAGE']):?>
            <div class="alert <?=($arResult['ERROR_MESSAGE']['TYPE'] === 'OK' ? 'alert-success' : 'alert-danger')?>"><?=$arResult['ERROR_MESSAGE']['MESSAGE'].($arResult['ERROR_MESSAGE']['TYPE'] === 'OK' ? GetMessage('CHANGE_SUCCESS') : '')?></div>
        <?endif;?>

        <?if(
            !$arResult['ERROR_MESSAGE'] ||
            $arResult['ERROR_MESSAGE']['TYPE'] !== 'OK'
        ):?>
            <?if($arResult['PHONE_REGISTRATION']):?>
                <div class="form-header">
                    <div class="text">
                        <div class="title switcher-title font_24 color_222"><?=GetMessage('PASSWORD_FIELD_SMS_SENDED_TITLE')?></div>
                        <div class="form_desc font_16"><?=GetMessage('change_pass_code_sent', ['#PHONE_NUMBER#' => $arResult['USER_PHONE_NUMBER']])?></div>
                    </div>
                </div>
            <?endif;?>

            <form id="changepasswd-page-form" method="post" action="<?=POST_FORM_ACTION_URI?>" name="bform">
                <?if($arResult['BACKURL'] <> ''):?>
                    <input type="hidden" name="backurl" value="<?=htmlspecialcharsbx($arResult['BACKURL'])?>" />
                <?endif;?>

                <input type="hidden" name="AUTH_FORM" value="Y">
                <input type="hidden" name="TYPE" value="CHANGE_PWD">

                <div class="form-body">
                    <?if($arResult['PHONE_REGISTRATION']):?>
                        <input type="hidden" name="USER_PHONE_NUMBER" value="<?=htmlspecialcharsbx($arResult['USER_PHONE_NUMBER'])?>" />

                        <div class="form-group fill-animate phone_code">
                            <label for="USER_CHECKWORD" class="font_14"><span><?=GetMessage('change_pass_code')?>&nbsp;<span class="star">*</span></span></label>
                            <div class="input">
                                <input type="text" id="USER_CHECKWORD" name="USER_CHECKWORD" required maxlength="50" value="<?=$arResult['USER_CHECKWORD']?>" class="bx-auth-input form-control"  />
                            </div>
                        </div>
                    <?else:?>
                        <div class="form-group fill-animate <?=($lastLogin ? 'input-filed' : '');?>">
                            <label for="USER_LOGIN" class="font_14"><span><?=($arTheme['CABINET']['DEPENDENT_PARAMS']['LOGIN_EQUAL_EMAIL']['VALUE'] != "Y" ? GetMessage("AUTH_LOGIN_MAIN") : GetMessage("AUTH_LOGIN"));?> <span class="required-star">*</span></span></label>
                            <div class="input">
                                <input type="text" maxlength="50" value="<?=$lastLogin?>" class="form-control bx-auth-input  <?=($_POST && empty($_POST['USER_LOGIN']) ? 'error': '')?>" disabled required />
                                <input type="hidden" id="USER_LOGIN" name="USER_LOGIN" value="<?=$lastLogin?>" />
                            </div>
                        </div>

                        <?if($arResult["USE_PASSWORD"]):?>
                            <div class="form-group fill-animate <?=($arResult["USER_CURRENT_PASSWORD"] ? 'input-filed' : '');?>">
                                <label for="USER_CURRENT_PASSWORD" class="font_14"><span><?=GetMessage("AUTH_CURRENT_PASSWORD")?>&nbsp;<span class="required-star">*</span></span></label>
                                <div class="input">
                                    <input type="password" name="USER_CURRENT_PASSWORD" id="USER_CURRENT_PASSWORD" maxlength="50" required value="<?=$arResult["USER_CURRENT_PASSWORD"]?>" class="form-control bg-color current_password <?=((isset($arResult["ERRORS"]) && array_key_exists("USER_CURRENT_PASSWORD", $arResult["ERRORS"])) ? 'error': '')?>" />
                                </div>
                                <div class="text_block font_13">
                                    <?=GetMessage("PASSWORD_MIN_LENGTH")?>
                                </div>
                            </div>
                        <?else:?>
                            <input type="hidden" name="USER_CHECKWORD" maxlength="50" value="<?=$arResult["USER_CHECKWORD"]?>" class="bx-auth-input"  />
                        <?endif;?>
                    <?endif;?>

                    <?if($arResult['PHONE_REGISTRATION']):?>
                        <div class="hidden">
                    <?endif;?>

                    <div class="form-group fill-animate <?=($arResult["USER_PASSWORD"] ? 'input-filed' : '');?>">
                        <label for="USER_PASSWORD" class="font_14"><?=GetMessage("AUTH_NEW_PASSWORD_REQ")?>&nbsp;<span class="required-star">*</span></label>
                        <div class="input">
                            <input type="password" name="USER_PASSWORD" id="USER_PASSWORD" maxlength="50" required value="<?=$arResult["USER_PASSWORD"]?>" class="form-control bg-color password <?=(isset($arResult["ERRORS"]) && array_key_exists("USER_PASSWORD", $arResult["ERRORS"]))? "error": ''?>" />
                        </div>
                        <div class="text_block font_13">
                            <?=GetMessage("PASSWORD_MIN_LENGTH")?>
                        </div>
                    </div>

                    <div class="form-group fill-animate <?=($arResult["USER_CONFIRM_PASSWORD"] ? 'input-filed' : '');?>">
                        <label for="USER_CONFIRM_PASSWORD" class="font_14"><span><?=GetMessage("AUTH_NEW_PASSWORD_CONFIRM")?>&nbsp;<span class="required-star">*</span></span></label>
                        <div class="input">
                            <input type="password" name="USER_CONFIRM_PASSWORD" id="USER_CONFIRM_PASSWORD" maxlength="50" required value="<?=$arResult["USER_CONFIRM_PASSWORD"]?>" class="form-control bg-color confirm_password <?=(isset($arResult["ERRORS"]) && array_key_exists( "USER_CONFIRM_PASSWORD", $arResult["ERRORS"]))? "error": ''?>"  />
                        </div>
                    </div>

                    <?if ($arResult["USE_CAPTCHA"]):?>
                        <?
                        /**
                         * @var TSolution\Captcha\Service $captcha
                         */
                        $captcha = TSolution\Captcha::getInstance();
                        ?>
                        <div class="clearboth"></div>
                        <div class="captcha-row clearfix">
                            <label for="FORGOTPASSWD_CAPTCHA" class="font_14"><span><?=($captcha->isService() && $captcha->isActive() ? GetMessage("FORM_GENERAL_RECAPTCHA") : GetMessage("CAPTCHA_PROMT"))?>&nbsp;<span class="required-star">*</span></span></label>
                            <div class="captcha_image">
                                <img src="/bitrix/tools/captcha.php?captcha_sid=<?=htmlspecialcharsbx($arResult["CAPTCHA_CODE"])?>" class="captcha_img" border="0" />
                                <input type="hidden" name="captcha_sid" class="captcha_sid" value="<?=htmlspecialcharsbx($arResult["CAPTCHA_CODE"])?>" />
                                <div class="captcha_reload"></div>
                                <span class="refresh"><a href="javascript:;" rel="nofollow"><?=GetMessage("REFRESH")?></a></span>
                            </div>
                            <div class="captcha_input">
                                <input id="FORGOTPASSWD_CAPTCHA" type="text" class="inputtext form-control captcha" name="captcha_word" size="30" maxlength="50" value="" required />
                            </div>
                        </div>
                        <div class="clearboth"></div>
                    <?endif;?>

                    <?if($arResult['PHONE_REGISTRATION']):?>
                        </div> <?// class="hidden"?>
                    <?endif;?>
                </div>

                <div class="form-footer <?=($arResult['PHONE_REGISTRATION'] ? 'hidden' : '')?>">
                    <button class="btn btn-default btn-lg btn-wide" type="submit" name="change_pwd" value="<?=GetMessage("AUTH_CHANGE")?>" <?=($arResult['PHONE_REGISTRATION'] ? 'disabled' : '')?>><span><?=GetMessage("CHANGE_PASSWORD")?></span></button>
                    <div class="clearboth"></div>
                </div>
            </form>

            <?if($arResult['PHONE_REGISTRATION']):?>
                <div id="bx_chpass_error" style="display:none"><?ShowError('error')?></div>
                <div id="bx_chpass_resend"></div>
            <?endif;?>

            <script>
            $(document).ready(function(){
                $('#changepasswd-page-form').validate({
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
                                if(!$button.hasClass('loadings')){
                                    $button.addClass('loadings');

                                    var eventdata = {type: 'form_submit', form: form, form_name: 'CHANGE_PASSWD'};
                                    BX.onCustomEvent('onSubmitForm', [eventdata]);
                                }
                            }
                        }
                    },
                    errorPlacement: function(error, element){
                        error.insertAfter(element);
                    },
                    rules: {
                        USER_CONFIRM_PASSWORD: {
                            equalTo: '#USER_PASSWORD'
                        },
                        <?if($arTheme['CABINET']['DEPENDENT_PARAMS']['LOGIN_EQUAL_EMAIL']['VALUE'] === 'Y'):?>
                        USER_LOGIN: {email: true}
                        <?endif;?>
                    },
                    messages: {
                        USER_CONFIRM_PASSWORD: {
                            equalTo: '<?=GetMessage('PASSWORDS_DONT_MATCH')?>',
                        }
                    }
                });

                setTimeout(function(){
                    $('#changepasswd-page-form').find('input:visible').eq(0).focus();
                }, 50);
            });
            </script>

            <?if($arResult['PHONE_REGISTRATION']):?>
                <script>
                document.bform.USER_CHECKWORD.focus();

                $('#changepasswd-page-form .phone_code input[type=text]').phonecode(
                    <?=CUtil::PhpToJSObject(
                        [
                            'AUTH' => 'Y',
                            'USER_PHONE_NUMBER' => $arResult['USER_PHONE_NUMBER'],
                        ]
                    )?>,
                    function(input, data, response) {
                        if (
                            typeof response !== 'undefined' &&
                            response === 'true'
                        ) {
                            let $form = $(input).closest('.form');

                            if (
                                $form.length &&
                                !$form.find('button[type=submit].loadings').length
                            ) {
                                $form.find('.form-footer button[type=submit]').prop('disabled', false);
                                $form.find('.form-footer').removeClass('hidden');
                                $form.find('.form-body > .hidden').removeClass('hidden');
                                $form.find('.form-header').addClass('hidden');
                                $form.find('.phone_code').hide();
                                $form.find('#bx_chpass_error').hide();
                                $form.find('#bx_chpass_resend').hide();
                            }
                        }
                    }
                );

                new BX.PhoneAuth({
                    containerId: 'bx_chpass_resend',
                    errorContainerId: 'bx_chpass_error',
                    interval: <?=$arResult['PHONE_CODE_RESEND_INTERVAL']?>,
                    data:
                        <?=CUtil::PhpToJSObject([
                            'signedData' => $arResult['SIGNED_DATA']
                        ])?>,
                    onError:
                        function(response)
                        {
                            var errorNode = BX('bx_chpass_error');
                            errorNode.innerHTML = '';
                            for(var i = 0; i < response.errors.length; i++)
                            {
                                errorNode.innerHTML = errorNode.innerHTML + BX.util.htmlspecialchars(response.errors[i].message) + '<br />';
                            }
                            errorNode.style.display = '';
                        }
                });
                </script>
            <?else:?>
                <script>document.bform.USER_PASSWORD.focus();</script>
            <?endif;?>
        <?endif;?>
    </div>
</div>
