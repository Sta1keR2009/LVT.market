<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<form action="<?=SITE_DIR.$arParams["PAGE"]?>" method="post" class="subscribe-edit__form">
	<?//echo bitrix_sessid_post();?>
	<input type="hidden" name="sessid" value="">
	<input type="text" name="EMAIL" class="subscribe-edit__text form-control subscribe-input required" placeholder="<?=GetMessage("EMAIL_INPUT");?>" value="" size="30" maxlength="255" />

	<?foreach($arResult["RUBRICS"] as $itemID => $itemValue):?>
		<input type="hidden" name="RUB_ID[]" value="<?=$itemValue["ID"]?>" />
	<?endforeach;?>

	<input type="hidden" name="FORMAT" value="html" />
	<input type="submit" name="Save" class="subscribe-edit__btn btn btn-default btn--no-rippple subscribe-btn" value="<?echo GetMessage("ADD_USER");?>" />

	<input type="hidden" name="PostAction" value="Add" />
	<input type="hidden" name="ID" value="" />
</form>
