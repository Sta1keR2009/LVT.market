<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//*************************************
//show confirmation form
//*************************************
?>
<div class="confirmation-block top-form">
	<h4><?=GetMessage("subscr_title_confirm")?></h4>
	
	<form action="<?=$arResult["FORM_ACTION"]?>" method="get" class="form">
		<div class="form-body">
			<div class="form-group">
				<label for="CONFIRM_CODE" class="font_14"><span><?=GetMessage("subscr_conf_code")?>&nbsp;<span class="required-star">*</span></span></label>
				<div class="wrap-half-block">				
					<div class="input">
						<input class="form-control" type="text" id="CONFIRM_CODE" name="CONFIRM_CODE" value="<?=$arResult["REQUEST"]["CONFIRM_CODE"];?>" size="20" />
					</div>
					<div class="text_block font_13">
						<?=GetMessage("subscr_conf_note1")?> <a title="<?=GetMessage("adm_send_code")?>" href="<?=$arResult["FORM_ACTION"]?>?ID=<?=$arResult["ID"]?>&amp;action=sendcode&amp;<?=bitrix_sessid_get()?>"><?=GetMessage("subscr_conf_note2")?></a>.
					</div>
				</div>
				<div class="text-info-block text_block font_13">
					<p><?=GetMessage("subscr_conf_date")?> <?=$arResult["SUBSCRIPTION"]["DATE_CONFIRM"];?></p>
				</div>
			</div>
		</div>
		<div class="form-footer">
			<input type="submit" class="btn btn-default btn-lg btn-confirm" name="confirm" value="<?=GetMessage("subscr_conf_button")?>" />
		</div>

		<input type="hidden" name="ID" value="<?=$arResult["ID"];?>" />
		<?=bitrix_sessid_post();?>
	</form>
</div>
