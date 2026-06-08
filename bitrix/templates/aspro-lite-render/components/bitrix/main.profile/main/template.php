<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(false);

global $arTheme;

TSolution\Extensions::init(['personal', 'profile']);

if($arResult['SHOW_SMS_FIELD']){
	CJSCore::Init('phone_auth');
	TSolution\Extensions::init('phonecode');
}

// get phone auth params
list($bPhoneAuthSupported, $bPhoneAuthShow, $bPhoneAuthRequired, $bPhoneAuthUse) = TSolution\PhoneAuth::getOptions();
?>
<div class="lk-page">
	<div class="form <?=(($arResult['SHOW_SMS_FIELD'] && !$arResult['strProfileError']) ? 'form--send-sms' : '')?>">
		<div class="top-form bordered_block">
			<?if($arResult["strProfileError"]):?>
				<div class="alert alert-danger"><?=$arResult['strProfileError']?></div>
			<?endif;?>

			<?if($arResult['DATA_SAVED'] == 'Y'):?>
				<div class="alert alert-success"><?=GetMessage('PROFILE_DATA_SAVED')?></div>
			<?endif;?>

			<?if(
				$arResult['SHOW_SMS_FIELD'] && 
				!$arResult['strProfileError']
			):?>
				<div class="form-header">
					<div class="text">
						<h4><?=GetMessage('PROFILE_SMS_SENDED_TITLE')?></h4>
						<div class="form_desc font_16"><?=GetMessage('main_profile_code_sent', ['#PHONE_NUMBER#' => $arResult['arUser']['PHONE_NUMBER']])?></div>
					</div>
				</div>

				<form id="profile-form" method="post" name="form1" class="main-form" action="<?=$arResult['FORM_TARGET']?>" enctype="multipart/form-data">
					<?=$arResult['BX_SESSION_CHECK']?>
					<input type="hidden" name="lang" value="<?=LANG?>" />
					<input type="hidden" name="ID" value=<?=$arResult['ID']?> />
					<input type="hidden" name="SIGNED_DATA" value="<?=htmlspecialcharsbx($arResult['SIGNED_DATA'])?>" />

					<div class="form-body">
						<div class="form-group fill-animate phone_code">
							<div class="iblock label_block">
								<label class="font_14" for="input_SMS_CODE"><span><?=GetMessage('main_profile_code')?> <span class="required-star">*</span></span></label>
								<div class="input">
									<input id="input_SMS_CODE" class="form-control required" size="30" type="text" name="SMS_CODE" value="<?=htmlspecialcharsbx($arResult['SMS_CODE'])?>" autocomplete="off" />
								</div>
							</div>
						</div>
					</div>

					<div class="form-footer hidden">
						<button class="btn btn-default btn-lg" type="submit" name="code_submit_button" value="Y"><span><?=GetMessage('main_profile_send')?></span></button>
					</div>
				</form>

				<div id="bx_profile_error" style="display:none"><?ShowError('error')?></div>
				<div id="bx_profile_resend"></div>

				<script>
				document.form1.SMS_CODE.focus();

				$(document).ready(function(){
					$("#profile-form").validate();

					$("#profile-form .phone_code input[type=text]").phonecode(
						<?=CUtil::PhpToJSObject(
							[
								'USER_ID' => $arResult['ID'],
								'USER_PHONE_NUMBER' => $arResult['arUser']['PHONE_NUMBER'],
							]
						)?>,
						function(input, data, response) {
							if (
								typeof response !== 'undefined' &&
								response === 'true'
							) {
								let $form = $(input).closest('form');
		
								if (
									$form.length &&
									!$form.find('button[type=submit].loadings').length
								) {
									$form.find('button[type=submit]').trigger('click');
								}
							}
						}
					);
				});

				new BX.PhoneAuth({
					containerId: 'bx_profile_resend',
					errorContainerId: 'bx_profile_error',
					interval: <?=$arResult['PHONE_CODE_RESEND_INTERVAL']?>,
					data:
						<?=CUtil::PhpToJSObject([
							'signedData' => $arResult['SIGNED_DATA'],
						])?>,
					onError:
						function(response)
						{
							var errorDiv = BX('bx_profile_error');
							var errorNode = BX.findChildByClassName(errorDiv, 'errortext');
							errorNode.innerHTML = '';
							for(var i = 0; i < response.errors.length; i++)
							{
								errorNode.innerHTML = errorNode.innerHTML + BX.util.htmlspecialchars(response.errors[i].message) + '<br>';
							}
							errorDiv.style.display = '';
						}
				});
				</script>
			<?else:?>
				<h4><?=GetMessage('PROFILE_TITLE');?></h4>

				<form id="profile-form" method="post" name="form1" class="main-form" action="<?=$arResult['FORM_TARGET']?>" enctype="multipart/form-data">
					<?=$arResult['BX_SESSION_CHECK']?>
					<input type="hidden" name="LOGIN" maxlength="50" value="<?=$arResult['arUser']['LOGIN']?>" />
					<input type="hidden" name="lang" value="<?=LANG?>" />
					<input type="hidden" name="ID" value=<?=$arResult['ID']?> />

					<div class="form-body">
						<?if($arTheme['CABINET']['DEPENDENT_PARAMS']['LOGIN_EQUAL_EMAIL']['VALUE'] != 'Y'):?>
							<div class="form-group <?=($arResult['arUser']['LOGIN'] ? 'input-filed' : '');?>">
								<div class="iblock label_block">
									<label for="LOGIN" class="font_14"><span><?=GetMessage('PERSONAL_LOGIN')?>&nbsp;<span class="required-star">*</span></span></label>
									<div class="input">
										<input required type="text" name="LOGIN" id="LOGIN" maxlength="50" class="form-control" value="<?=$arResult['arUser']['LOGIN']?>" />
									</div>
								</div>
							</div>
						<?endif;?>

						<?if($arTheme['CABINET']['DEPENDENT_PARAMS']['PERSONAL_ONEFIO']['VALUE'] != 'N'):?>
							<?
							$strName = trim(
								implode(
									' ', 
									[
										$arResult['arUser']['LAST_NAME'],
										$arResult['arUser']['NAME'],
										$arResult['arUser']['SECOND_NAME']
									]
								)
							);
							?>
							<div class="form-group <?=($strName ? 'input-filed' : '');?>">
								<div class="iblock label_block">
									<label for="NAME" class="font_14"><span><?=GetMessage('PERSONAL_FIO')?>&nbsp;<span class="required-star">*</span></span></label>
									<div class="input">
										<input required type="text" class="form-control" name="NAME" id="NAME" maxlength="50" value="<?=$strName?>" />
									</div>
								</div>
							</div>
						<?else:?>
							<div class="form-group <?=($arResult['arUser']['LAST_NAME'] ? 'input-filed' : '');?>">
								<div class="iblock label_block">
									<label for="LAST_NAME" class="font_14"><span><?=GetMessage('PERSONAL_LASTNAME')?></span></label>
									<div class="input">
										<input type="text" class="form-control" name="LAST_NAME" id="LAST_NAME" maxlength="50" value="<?=$arResult['arUser']['LAST_NAME'];?>" />
									</div>
								</div>
							</div>

							<div class="form-group <?=($arResult['arUser']['NAME'] ? 'input-filed' : '');?>">
								<div class="iblock label_block">
									<label for="NAME" class="font_14"><?=GetMessage('PERSONAL_NAME')?>&nbsp;<span class="required-star">*</span></span></label>
									<div class="input">
										<input required type="text" class="form-control" name="NAME" id="NAME" maxlength="50" value="<?=$arResult['arUser']['NAME'];?>" />
									</div>
								</div>
							</div>

							<div class="form-group <?=($arResult['arUser']['SECOND_NAME'] ? 'input-filed' : '');?>">
								<div class="iblock label_block">
									<label for="SECOND_NAME" class="font_14"><?=GetMessage('PERSONAL_FATHERNAME')?></span></label>
									<div class="input">
										<input type="text" class="form-control" name="SECOND_NAME" id="SECOND_NAME" maxlength="50" value="<?=$arResult['arUser']['SECOND_NAME'];?>" />
									</div>
								</div>
							</div>
						<?endif;?>

						<div class="form-group <?=($arResult['arUser']['EMAIL'] ? 'input-filed' : '');?>">
							<div class="iblock label_block">
								<label for="EMAIL" class="font_14"><span><?=GetMessage('PERSONAL_EMAIL')?>&nbsp;<span class="required-star">*</span></span></label>
								<div class="input">
									<input required type="text" name="EMAIL" id="EMAIL" maxlength="50" class="form-control" value="<?=$arResult['arUser']['EMAIL']?>" />
								</div>
								<?if(
									$arTheme['CABINET']['DEPENDENT_PARAMS']['LOGIN_EQUAL_EMAIL']['VALUE'] == "Y" &&
									$arResult['arUser']['EMAIL'] === $arResult['arUser']['LOGIN']
								):?>
									<div class="text_block font_13"><?=GetMessage('PERSONAL_EMAIL_DESCRIPTION');?></div>
								<?else:?>
									<div class="text_block font_13"><?=GetMessage('PERSONAL_EMAIL_SHORT_DESCRIPTION');?></div>
								<?endif;?>
							</div>
						</div>

						<?$mask = $arTheme['PHONE_MASK']['VALUE'];?>
						<div class="form-group <?=($arResult['arUser']['PERSONAL_PHONE'] ? 'input-filed' : '');?>">
							<?
							if(strlen($arResult['arUser']['PERSONAL_PHONE']) && strpos($arResult['arUser']['PERSONAL_PHONE'], '+') === false && strpos($mask, '+') !== false){
								$arResult['arUser']['PERSONAL_PHONE'] = '+'.$arResult['arUser']['PERSONAL_PHONE'];
							}
							?>
							<div class="iblock label_block">
								<label for="PERSONAL_PHONE" class="font_14"><span><?=GetMessage('PERSONAL_PHONE')?>&nbsp;<span class="required-star">*</span></span></label>
								<div class="input">
									<input required type="text" name="PERSONAL_PHONE" id="PERSONAL_PHONE" class="form-control phone" maxlength="255" value="<?=$arResult['arUser']['PERSONAL_PHONE']?>" />
								</div>
								<div class="text_block font_13"><?=GetMessage('PERSONAL_PHONE_DESCRIPTION')?></div>
							</div>
						</div>

						<?if($arResult['PHONE_REGISTRATION']):?>
							<div class="form-group <?=($arResult['arUser']['PHONE_NUMBER'] ? 'input-filed' : '');?>">
								<div class="iblock label_block">
									<label for="PHONE_NUMBER" class="font_14"><span><?=GetMessage("main_profile_phone_number")?> <?=($arResult['PHONE_REQUIRED'] ? '<span class="required-star">*</span>' : '')?></span></label>
									<?
									if(
										strlen($arResult['arUser']['PHONE_NUMBER']) &&
										strpos($arResult['arUser']['PHONE_NUMBER'], '+') === false &&
										strpos($mask, '+') !== false
									){
										$arResult['arUser']['PHONE_NUMBER'] = '+'.$arResult['arUser']['PHONE_NUMBER'];
									}
									?>
									<div class="input">
										<input id="PHONE_NUMBER" <?=($arResult['PHONE_REQUIRED'] ? 'required' : '')?> type="tel" name="PHONE_NUMBER" class="form-control phone" maxlength="255" value="<?=$arResult['arUser']['PHONE_NUMBER']?>" />
									</div>
									<div class="text_block font_13"><?=GetMessage('PHONE_NUMBER_DESCRIPTION'.($bPhoneAuthUse ? '_WITH_AUTH' : ''))?></div>
								</div>
							</div>
						<?endif;?>
					</div>

					<div class="form-footer">
						<button class="btn btn-default btn-lg" type="submit" name="save" value="<?=(($arResult['ID']>0) ? GetMessage('MAIN_SAVE_TITLE') : GetMessage('MAIN_ADD_TITLE'))?>"><span><?=(($arResult['ID']>0) ? GetMessage('MAIN_SAVE_TITLE') : GetMessage('MAIN_ADD_TITLE'))?></span></button>
					</div>
				</form>

				<script>
				$(document).ready(function(){
					$('#profile-form').validate({
						rules:{
							EMAIL: {
								email: true
							}
						},
					});
					
					if(arAsproOptions['THEME']['PHONE_MASK'].length){
						var base_mask = arAsproOptions['THEME']['PHONE_MASK'].replace( /(\d)/g, '_' );
						$('.lk-page input.phone').inputmask('mask', {'mask': arAsproOptions['THEME']['PHONE_MASK'], 'showMaskOnHover': false });
						$('.lk-page input.phone').blur(function(){
							if( $(this).val() == base_mask || $(this).val() == '' ){
								if( $(this).hasClass('required') ){
									$(this).parent().find('label.error').html(BX.message('JS_REQUIRED'));
								}
							}
						});
					}
				});
				</script>
			<?endif;?>
		</div>

		<?if(
			$arResult['SOCSERV_ENABLED'] &&
			!$arResult['SHOW_SMS_FIELD']
		):?>
			<?$APPLICATION->IncludeComponent(
				"bitrix:socserv.auth.split",
				"main",
				array(
					"SUFFIX" => "form",
					"SHOW_PROFILES" => "Y",
					"ALLOW_DELETE" => "Y"
				),
				false,
				array("HIDE_ICONS" => "Y")
			);?>
		<?endif;?>
	</div>
</div>