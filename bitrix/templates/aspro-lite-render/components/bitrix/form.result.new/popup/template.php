<?
if( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true ) die();
$this->setFrameMode(false);
use \Bitrix\Main\Localization\Loc;

$isOCB = $arResult['arForm']['ID'] == TSolution::getFormID(VENDOR_PARTNER_NAME.'_'.VENDOR_SOLUTION_NAME.'_quick_buy');

$isShowLicenses = $arParams["SHOW_LICENCE"] == "Y";
$isLicenseAsCheckbox = TSolution::GetFrontParametrValue('LICENCE_AS_CHECKBOX') == "Y";
$isShowLicensesAsCheckBox = $isShowLicenses && $isLicenseAsCheckbox;
$isShowLicensesAsText = $isShowLicenses && !$isLicenseAsCheckbox;
?>
<div class="flexbox">
    <div class="form popup<?=($arResult['FORM_NOTE'] ? ' success' : '')?><?=($arResult['isFormErrors'] == 'Y' ? ' error' : '')?>">
        <!--noindex-->
        <div class="form-header">
            <?if($arResult["isFormTitle"] == "Y"):?>
                <div class="text">
                    <div class="title switcher-title font_24 color_222"><?=($arResult['FORM_NOTE'] ? GetMessage("SUCCESS_TITLE") : $arResult["FORM_TITLE"]);?></div>
                    <?if($arResult["isFormDescription"] == "Y" && !$arResult['FORM_NOTE']):?>
                        <div class="form_desc font_16"><?=$arResult["FORM_DESCRIPTION"]?></div>
                    <?endif;?>
                </div>
            <?endif;?>
        </div>
        <?if ($arResult["FORM_NOTE"]):?>
            <div class="form-body">
                <div class="form-inner form-inner--popup">
                    <div class="form-send">
                        <div class="flexbox flexbox--direction-column flexbox--align-center">
                            <div class="form-send__icon">
                                <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/form_icons.svg#success-80-80', 'fill-more-theme', ['WIDTH' => 80,'HEIGHT' => 80]);?>
                            </div>
                            <div class="form-send__info">
                                <div class="form-send__info-title switcher-title font_24"><?=Loc::getMessage($isOCB ? "PHANKS_ORDER_TEXT" : "PHANKS_TEXT")?></div>
                                <div class="form-send__info-text">
                                    <?if ($arResult["isFormErrors"] == "Y"):?>
                                        <?=$arResult["FORM_ERRORS_TEXT"]?>
                                    <?else:?>
                                        <?$successNoteFile = SITE_DIR."include/form/success_{$arResult["arForm"]["SID"]}.php";?>
                                        <?if (\Bitrix\Main\IO\File::isFileExists(\Bitrix\Main\Application::getDocumentRoot().$successNoteFile)):?>
                                            <?$APPLICATION->IncludeFile($successNoteFile, array(), array("MODE" => "html", "NAME" => "Form success note"));?>
                                        <?elseif($arParams["SUCCESS_MESSAGE"]):?>
                                            <?=htmlspecialchars_decode($arParams["~SUCCESS_MESSAGE"]);?>
                                        <?else:?>
                                            <?=Loc::getMessage("SUCCESS_SUBMIT_FORM");?>
                                        <?endif;?>
                                        <script>
                                            if (arAsproOptions['THEME']['USE_FORMS_GOALS'] !== 'NONE') {
                                                var id = '_'+'<?=((isset($arResult["arForm"]["ID"]) && $arResult["arForm"]["ID"]) ? $arResult["arForm"]["ID"] : $arResult["ID"] )?>';
                                                var eventdata = {goal: 'goal_webform_success' + (arAsproOptions['THEME']['USE_FORMS_GOALS'] === 'COMMON' ? '' : id)};
                                                BX.onCustomEvent('onCounterGoals', [eventdata]);
                                            }
                                            $('.ocb_frame').addClass('compact');
                                        </script>
                                    <?endif;?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-footer">
                <?if ( $arParams["DISPLAY_CLOSE_BUTTON"] != "N" ):?>
                    <div class="btn btn-transparent-border btn-lg jqmClose btn-wide"><?=($arParams["CLOSE_BUTTON_NAME"] ? $arParams["CLOSE_BUTTON_NAME"] : Loc::getMessage("SEND_MORE"));?></div>
                <?endif;?>
            </div>
        <?endif;?>
        <?if(!$arResult["FORM_NOTE"]){?>
            <?=$arResult["FORM_HEADER"]?>
            <?=bitrix_sessid_post();?>
            <div class="form-body">
                <?if($arResult["isFormErrors"] == "Y"):?>
                    <div class="form-error alert alert-danger"><?=$arResult["FORM_ERRORS_TEXT"]?></div>
                <?endif;?>
                <?if(is_array($arResult["QUESTIONS"])):?>
                    <?foreach($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion):?>
                        <?TSolution::drawFormField($FIELD_SID, $arQuestion);?>
                    <?endforeach;?>
                <?endif;?>

                <?if($arResult["isUseCaptcha"] == "Y"):?>
                    <div class="captcha-row clearfix fill-animate">
                        <label class="font_14"><span><?=GetMessage("FORM_CAPRCHE_TITLE")?>&nbsp;<span class="required-star">*</span></span></label>
                        <div class="captcha_image">
                            <img data-src="" src="/bitrix/tools/captcha.php?captcha_sid=<?=htmlspecialcharsbx($arResult["CAPTCHACode"])?>" class="captcha_img" />
                            <input type="hidden" name="captcha_sid" class="captcha_sid" value="<?=htmlspecialcharsbx($arResult["CAPTCHACode"])?>" />
                            <div class="captcha_reload"></div>
                            <span class="refresh"><a href="javascript:;" rel="nofollow"><?=GetMessage("REFRESH")?></a></span>
                        </div>
                        <div class="captcha_input">
                            <input type="text" class="inputtext form-control captcha" name="captcha_word" size="30" maxlength="50" value="" required />
                        </div>
                    </div>
                <?else:?>
                    <textarea name="nspm" style="display:none;"></textarea>
                <?endif;?>

                <?if($isShowLicensesAsCheckBox):?>
                    <div class=" form-checkbox mb mb--8 mt mt--4 relative">
                        <label for="licenses_popup_<?=$arResult["arForm"]["ID"];?>" class="form-checkbox__label">
                            <span class="form-checbox__text"><?include(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR."include/licenses_text.php"));?></span>
                            <span class="form-checkbox__box form-box"></span>
                        </label>
                        <input type="hidden" name="aspro_lite_form_validate">
                        <input class="form-checkbox__input form-checkbox__input--visible" type="checkbox" id="licenses_popup_<?=$arResult["arForm"]["ID"];?>" <?=TSolution::GetFrontParametrValue('LICENCE_CHECKED') === 'Y' ? 'checked' : '';?> name="licenses_popup" required value="Y">
                    </div>
                <?endif;?>
            </div>
            <div class="form-footer">
                <div>
                    <input type="submit" class="btn btn-default btn-lg btn-wide" value="<?=$arResult["arForm"]["BUTTON"]?>" name="web_form_submit">
                    <input type="hidden" value="<?= $arResult["arForm"]["BUTTON"] ?>" name="web_form_submit">
                </div>
                <?if($isShowLicensesAsText):?>
                    <div class="licence_block">
                        <input type="hidden" name="aspro_lite_form_validate">
                        <label>
                            <span><?include(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR."include/licenses_text.php"));?></span>
                        </label>
                        <input class="form-checkbox__input" type="checkbox" id="licenses_popup_<?=$arResult["arForm"]["ID"];?>" checked name="licenses_popup" required value="Y">
                    </div>
                <?endif;?>
            </div>
            <?=$arResult["FORM_FOOTER"]?>
        <?}?>
        <!--/noindex-->
        <script type="text/javascript">

        BX.message({
            FORM_FILE_DEFAULT: '<?= Loc::getMessage('FORM_FILE_DEFAULT') ?>',
        });

        if (typeof appAspro === 'object' && appAspro && appAspro.phone){
            appAspro.phone.init($('.popup form[name="<?=$arResult["arForm"]["VARNAME"]?>"] input.phone'));
        }

        $(document).ready(function(){
            $('.popup form[name="<?=$arResult["arForm"]["VARNAME"]?>"]').validate({
                submitHandler: function( form ){
                    if( $('.popup form[name="<?=$arResult["arForm"]["VARNAME"]?>"]').valid() ){
                        setTimeout(function() {
                            $(form).find('button[type="submit"]').attr("disabled", "disabled");
                        }, 300);
                        var eventdata = {type: 'form_submit', form: form, form_name: '<?=$arResult["arForm"]["VARNAME"]?>'};
                        BX.onCustomEvent('onSubmitForm', [eventdata]);
                    }
                },
                messages:{
                    licenses_popup: {
                        required : BX.message('JS_REQUIRED_LICENSES')
                    }
                }
            });

            if(arAsproOptions['THEME']['DATE_MASK'].length)
            {
                $('.popup form[name="<?=$arResult["arForm"]["VARNAME"]?>"] input.date').inputmask('datetime', {
                    'inputFormat':  arAsproOptions['THEME']['DATE_MASK'],
                    'placeholder': arAsproOptions['THEME']['DATE_PLACEHOLDER'],
                    'showMaskOnHover': false
                });
            }

            if(arAsproOptions['THEME']['DATETIME_MASK'].length)
            {
                $('.popup form[name="<?=$arResult["arForm"]["VARNAME"]?>"] input.datetime').inputmask('datetime', {
                    'inputFormat':  arAsproOptions['THEME']['DATETIME_MASK'],
                    'placeholder': arAsproOptions['THEME']['DATETIME_PLACEHOLDER'],
                    'showMaskOnHover': false
                });
            }

            $('.jqmClose').on('click', function(e){
                e.preventDefault();
                $(this).closest('.jqmWindow').jqmHide();
            });

            $('.popup form[name="<?=$arResult["arForm"]["VARNAME"]?>"] input[type=file]').uniform({fileButtonHtml: BX.message('JS_FILE_BUTTON_NAME'), fileDefaultHtml: BX.message('FORM_FILE_DEFAULT')});
            $(document).on('change', '.popup form[name="<?=$arResult["arForm"]["VARNAME"]?>"] input[type=file]', function(){
                if($(this).val())
                {
                    $(this).closest('.uploader').addClass('files_add');
                    $(this).valid();
                }
                else
                {
                    $(this).closest('.uploader').removeClass('files_add');
                }
            });

            $('.popup form[name="<?=$arResult["arForm"]["VARNAME"]?>"] .add_file').on('click', function(){
                var index = $(this).closest('.input').find('input[type=file]').length+1;
                $('<input type="file" id="POPUP_FILE'+index+'" name="FILE_n'+index+'"   class="inputfile" value="" />').insertBefore($(this));
                $('.popup form[name="<?=$arResult["arForm"]["VARNAME"]?>"] input[type=file]').uniform({fileButtonHtml: BX.message('JS_FILE_BUTTON_NAME'), fileDefaultHtml: BX.message('FORM_FILE_DEFAULT')});
            });
        });
        </script>
    </div>
</div>
