<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

global $USER;
$arExtensions = ['validate', 'uniform', 'phone_input', 'phone_mask','rating'];

if (Tsolution::GetFrontParametrValue('USE_INTL_PHONE') === 'Y') {
	$arExtensions[] = 'intl_phone_input';
}
TSolution\Extensions::init($arExtensions);


?>
<?Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID("form-block".$arParams["WEB_FORM_ID"]);?>
<?if($USER->IsAuthorized()):?>
	<?
	$dbRes = CUser::GetList(($by = "id"), ($order = "asc"), array("ID" => $USER->GetID()), array("FIELDS" => array("ID", "PERSONAL_PHONE")));
	$arUser = $dbRes->Fetch();

	$fio = $USER->GetFullName();
	$phone = $arUser['PERSONAL_PHONE'];
	$email = $USER->GetEmail();
	?>
	<script type="text/javascript">
	$(document).ready(function() {
		try{
			<?if ($fio):?>
				$('.form.order input[data-sid=CLIENT_NAME], .form.order input[data-sid=FIO], .form.order input[data-sid=NAME]').val('<?=$fio?>').addClass('input-filed');
			<?endif;?>
			<?if ($phone):?>
				$('.form.order input[data-sid=PHONE]').val('<?=$phone?>').addClass('input-filed');
			<?endif;?>
			<?if ($email):?>
				$('.form.order input[data-sid=EMAIL]').val('<?=$email?>').addClass('input-filed');
			<?endif;?>
		}
		catch(e){
		}
	});
	</script>
<?endif;?>
<?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID("form-block".$arParams["WEB_FORM_ID"], "");?>
