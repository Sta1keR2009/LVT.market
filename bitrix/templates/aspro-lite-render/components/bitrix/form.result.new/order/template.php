<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?use \Bitrix\Main\Localization\Loc;?>
<div class="form order<?=($arResult['FORM_NOTE'] ? ' success' : '')?><?=($arResult['isFormErrors'] == 'Y' ? ' error' : '')?>">
	<!--noindex-->
	<?if($arResult["isFormErrors"] == "Y"):?>
		<div class="form-error alert alert-danger"><?=$arResult["FORM_ERRORS_TEXT"]?></div>
	<?endif;?>
	<?=$arResult["FORM_HEADER"]?>
	<?=bitrix_sessid_post();?>
		<div class="row">
			<div class="col-md-12 col-sm-12">
				<?if($arResult["isFormDescription"] == "Y"):?>
					<div class="description"><?=$arResult["FORM_DESCRIPTION"]?></div>
				<?endif;?>
			</div>
			<div class="col-md-12 col-sm-12">
				<?if(is_array($arResult["QUESTIONS"])):?>
					<?foreach($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion):?>
						<?if($FIELD_SID == "ORDER_LIST"):?>
							<div class="hidden">
						<?endif;?>
						<?TSolution::drawFormField($FIELD_SID, $arQuestion, 'ORDER', $arParams);?>
						<?if($FIELD_SID == "ORDER_LIST"):?>
							</div>
						<?endif;?>
					<?endforeach;?>
				<?endif;?>
				<?if($arResult["isUseCaptcha"] == "Y"):?>
					<div class="form-control captcha-row clearfix">
						<label class="font_14"><span><?=GetMessage("FORM_CAPRCHE_TITLE")?>&nbsp;<span class="star">*</span></span></label>
						<div class="captcha_image">
							<img src="/bitrix/tools/captcha.php?captcha_sid=<?=htmlspecialcharsbx($arResult["CAPTCHACode"])?>" border="0" />
							<input type="hidden" name="captcha_sid" value="<?=htmlspecialcharsbx($arResult["CAPTCHACode"])?>" />
							<div class="captcha_reload"></div>
						</div>
						<div class="captcha_input">
							<input type="text" class="inputtext captcha" name="captcha_word" size="30" maxlength="50" value="" required />
						</div>
					</div>
				<?elseif($arParams["HIDDEN_CAPTCHA"] == "Y"):?>
					<textarea name="nspm" style="display:none;"></textarea>
				<?endif;?>

                <?
                $isShowLicenses = $arParams["SHOW_LICENCE"] == "Y";
                $isShowOferta = $arParams["SHOW_OFFER"] == "Y";
                ?>
                <?if($isShowLicenses || $isShowOferta):?>
                    <div class="mb mb--24 mt mt--6">
                        <input type="hidden" name="aspro_lite_form_validate">
                        <?if($isShowLicenses):?>
                            <div class="form-checkbox mb mb--8 relative">
                                <label for="licenses_popup_OCB" class="form-checkbox__label">
                                    <span class="form-checbox__text"><?include(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR."include/licenses_text.php"));?></span>
                                    <span class="form-checkbox__box form-box"></span>
                                </label>
                                <input class="form-checkbox__input form-checkbox__input--visible" type="checkbox" id="licenses_popup_OCB" <?=TSolution::GetFrontParametrValue('LICENCE_CHECKED') === 'Y' ? 'checked' : '';?> name="licenses_popup" required value="Y">
                            </div>
                        <?endif;?>
                        <?if($isShowOferta):?>
                            <div class="form-checkbox mt mt--16 relative">
                                <label for="oferta_popup_OCB" class="form-checkbox__label">
                                    <span class="form-checbox__text"><?include(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].SITE_DIR."include/offer_text.php"));?></span>
                                    <span class="form-checkbox__box form-box"></span>
                                </label>
                                <input class="form-checkbox__input form-checkbox__input--visible" type="checkbox" id="oferta_popup_OCB" <?=TSolution::GetFrontParametrValue('OFFER_CHECKED') === 'Y' ? 'checked' : '';?> name="oferta_popup" required value="Y">
                            </div>
                        <?endif;?>
                    </div>
                <?endif;?>
				<div class="form-footer">
					<div>
						<input type="submit" class="btn btn-default btn-lg btn-wide" value="<?=$arResult["arForm"]["BUTTON"]?>" name="web_form_submit">
						<input type="hidden" value="<?= $arResult["arForm"]["BUTTON"] ?>" name="web_form_submit">
					</div>
				</div>
			</div>
		</div>
	<?=$arResult["FORM_FOOTER"]?>
	<!--/noindex-->
	<script type="text/javascript">
	BX.message({
            FORM_FILE_DEFAULT: '<?= Loc::getMessage('FORM_FILE_DEFAULT') ?>',
		});

    if (typeof appAspro === 'object' && appAspro && appAspro.phone){

        appAspro.phone.init($('.order form[name="<?=$arResult["arForm"]["VARNAME"]?>"] input.phone'));
    }

	$(document).ready(function(){
		if(arAsproOptions['THEME']['USE_FULLORDER_GOALS'] !== 'N'){
			var eventdata = {goal: 'goal_order_begin'};
			BX.onCustomEvent('onCounterGoals', [eventdata]);
		}

		var sessionID = '<?=bitrix_sessid()?>';
		$('input[data-sid=SESSION_ID]').val(sessionID);

		$('form[name="<?=$arResult["arForm"]["VARNAME"]?>"]').validate({
			submitHandler: function( form ){
				if( $('form[name="<?=$arResult["arForm"]["VARNAME"]?>"]').valid() ){
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
                },
                oferta_popup: {
                    required : BX.message('JS_REQUIRED_LICENSES')
                }
            }
		});

		if(arAsproOptions['THEME']['DATE_MASK'].length)
		{
			$('form[name="<?=$arResult["arForm"]["VARNAME"]?>"] input.date').inputmask('datetime', {
				'inputFormat':  arAsproOptions['THEME']['DATE_MASK'],
				'placeholder': arAsproOptions['THEME']['DATE_PLACEHOLDER'],
				'showMaskOnHover': false
			});
		}

		if(arAsproOptions['THEME']['DATETIME_MASK'].length)
		{
			$('form[name="<?=$arResult["arForm"]["VARNAME"]?>"] input.datetime').inputmask('datetime', {
				'inputFormat':  arAsproOptions['THEME']['DATETIME_MASK'],
				'placeholder': arAsproOptions['THEME']['DATETIME_PLACEHOLDER'],
				'showMaskOnHover': false
			});
		}

		$('.jqmClose').on('click', function(e){
			e.preventDefault();
			$(this).closest('.jqmWindow').jqmHide();
		});

		$('form[name="<?=$arResult["arForm"]["VARNAME"]?>"] input[type=file]').uniform({fileButtonHtml: BX.message('JS_FILE_BUTTON_NAME'), fileDefaultHtml: BX.message('FORM_FILE_DEFAULT')});
		$(document).on('change', 'form[name="<?=$arResult["arForm"]["VARNAME"]?>"] input[type=file]', function(){
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

		$('form[name="<?=$arResult["arForm"]["VARNAME"]?>"] .add_file').on('click', function(){
			var index = $(this).closest('.input').find('input[type=file]').length+1;
			$('<input type="file" id="POPUP_FILE'+index+'" name="FILE_n'+index+'"   class="inputfile" value="" />').insertBefore($(this));
			$('form[name="<?=$arResult["arForm"]["VARNAME"]?>"] input[type=file]').uniform({fileButtonHtml: BX.message('JS_FILE_BUTTON_NAME'), fileDefaultHtml: BX.message('FORM_FILE_DEFAULT')});
		});
	});
	</script>
</div>
