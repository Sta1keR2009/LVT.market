<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(false);

global $USER, $arTheme;

TSolution\Extensions::init(['personal', 'profile']);

$rsUser = CUser::GetByID($USER->GetID());
$arUser = $rsUser->Fetch();
if ($arUser["EXTERNAL_AUTH_ID"] != '') {
	LocalRedirect(SITE_DIR.'personal/private/');
}
?>
<div class="lk-page passw">
	<div class="form">
		<div class="top-form bordered_block">
			<?if($arResult['strProfileError']):?>
				<div class="alert alert-danger"><?=$arResult['strProfileError']?></div>
			<?endif;?>

			<?if($arResult['DATA_SAVED'] == 'Y'):?>
				<div class="alert alert-success"><?=GetMessage('PROFILE_DATA_SAVED')?></div>
			<?endif;?>

			<h4><?=GetMessage('CHANGE_PASSWORD');?></h4>

			<form method="post" name="form1" class="pass-form" action="<?=$arResult['FORM_TARGET']?>?" enctype="multipart/form-data">
				<?=$arResult["BX_SESSION_CHECK"]?>
				<input type="hidden" name="LOGIN" maxlength="50" value="<?=$arResult['arUser']['LOGIN']?>" />
				<input type="hidden" name="EMAIL" maxlength="50" placeholder="name@company.ru" value="<?=$arResult['arUser']['EMAIL']?>" />
				<input type="hidden" name="lang" value="<?=LANG?>" />
				<input type="hidden" name="ID" value=<?=$arResult['ID']?> />

				<div class="form-body">
					<div class="form-group">
						<div class="iblock label_block">
							<label for="NEW_PASSWORD" class="font_14"><span><?=GetMessage("NEW_PASSWORD")?>&nbsp;<span class="required-star">*</span></span></label>
							<div class="input">
								<input type="password" name="NEW_PASSWORD" id="NEW_PASSWORD" maxlength="50" class="form-control password" value="" />
							</div>
						</div>
						<div class="text_block font_13"><?=GetMessage('PERSONAL_PASWORD_TEXT');?></div>
					</div>					

					<div class="form-group">
						<div class="iblock label_block">
							<label for="NEW_PASSWORD_CONFIRM" class="font_14"><span><?=GetMessage("NEW_PASSWORD_CONFIRM")?>&nbsp;<span class="required-star">*</span></span></label>
							<div class="input">
								<input type="password" name="NEW_PASSWORD_CONFIRM" id="NEW_PASSWORD_CONFIRM" maxlength="50" class="form-control confirm_password" value="" />
							</div>
						</div>
					</div>
				</div>

				<div class="form-footer">
					<button class="btn btn-default btn-lg" type="submit" name="save" value="<?=(($arResult["ID"]>0) ? GetMessage("SAVE") : GetMessage("ADD"))?>"><span><?=(($arResult["ID"]>0) ? GetMessage("SAVE") : GetMessage("ADD"))?></span></button>
				</div>
			</form>

		</div>
		<script>
		$(document).ready(function(){
			$('form.pass-form').validate({
				rules: {
					NEW_PASSWORD_CONFIRM: {
						equalTo: '#NEW_PASSWORD'
					}
				},
				messages: {
					NEW_PASSWORD_CONFIRM: {
						equalTo: '<?=GetMessage('PASSWORDS_DOES_NOT_MATCH')?>'
					}
				}
			});
		});
		</script>
	</div>
</div>