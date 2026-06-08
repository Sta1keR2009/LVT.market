<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if($arResult["ALLOW_ANONYMOUS"]=="Y" && $_REQUEST["authorize"]<>"YES" && $_REQUEST["register"]<>"YES"):?>
	<div class="authorization-block top-form">
		<h4><?=GetMessage("subscr_title_auth2")?></h4>

		<div><?=GetMessage("adm_auth1")?> <a href="<?=SITE_DIR?>personal/?backurl=<?=SITE_DIR;?>personal/subscribe/<?/*=$arResult["FORM_ACTION"]?>?authorize=YES&amp;sf_EMAIL=<?=$arResult["REQUEST"]["EMAIL"]?><?=$arResult["REQUEST"]["RUBRICS_PARAM"]*/?>"><?=GetMessage("adm_auth2")?></a>.</div>
		<?if($arResult["ALLOW_REGISTER"]=="Y"):?>
			<div><?=GetMessage("adm_reg1")?> <a href="<?=SITE_DIR?>personal/registration/?backurl=<?=SITE_DIR;?>personal/subscribe/<?/*=$arResult["FORM_ACTION"]?>?register=YES&amp;sf_EMAIL=<?=$arResult["REQUEST"]["EMAIL"]?><?=$arResult["REQUEST"]["RUBRICS_PARAM"]*/?>"><?=GetMessage("adm_reg2")?></a>.</div>
		<?endif;?>
	</div>
<?elseif($arResult["ALLOW_ANONYMOUS"]=="N" || $_REQUEST["authorize"]=="YES" || $_REQUEST["register"]=="YES"):?>
	<div class="authorization-block top-form">
		<form action="<?=$arResult["FORM_ACTION"]?>" method="post">
			<?=bitrix_sessid_post();?>
			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
			<thead><tr><td colspan="2"><h4><?=GetMessage("adm_auth_exist")?></h4></td></tr></thead>
			<tr valign="top">
				<td width="40%">
					<p><?=GetMessage("adm_auth_login")?><span class="starrequired">*</span><br />
					<input type="text" name="LOGIN" value="<?=$arResult["REQUEST"]["LOGIN"]?>" size="20" /></p>
					<p><?=GetMessage("adm_auth_pass")?><span class="starrequired">*</span><br />
					<input type="password" name="PASSWORD" size="20" value="<?=$arResult["REQUEST"]["PASSWORD"]?>" /></p>
				</td>
				<td width="60%">
					<?if($arResult["ALLOW_ANONYMOUS"]=="Y"):?>
						<?=GetMessage("subscr_auth_note")?>
					<?else:?>
						<?=GetMessage("adm_must_auth")?>
					<?endif;?>
				</td>
			</tr>
			<tfoot><tr><td colspan="2"><input type="submit" name="Save" value="<?=GetMessage("adm_auth_butt")?>" /></td></tr></tfoot>
			</table>
			<?foreach($arResult["RUBRICS"] as $itemID => $itemValue):?>
				<input type="hidden" name="RUB_ID[]" value="<?=$itemValue["ID"]?>">
			<?endforeach;?>
			<input type="hidden" name="PostAction" value="<?=($arResult["ID"]>0? "Update":"Add")?>" />
			<input type="hidden" name="ID" value="<?=$arResult["SUBSCRIPTION"]["ID"];?>" />
			<?if($_REQUEST["register"] == "YES"):?>
				<input type="hidden" name="register" value="YES" />
			<?endif;?>
			<?if($_REQUEST["authorize"]=="YES"):?>
				<input type="hidden" name="authorize" value="YES" />
			<?endif;?>
		</form>

		<br />

		<?if($arResult["ALLOW_REGISTER"]=="Y"):
			?>
			<form action="<?=$arResult["FORM_ACTION"]?>" method="post">
				<?=bitrix_sessid_post();?>
				<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
				<thead><tr><td colspan="2"><h4><?=GetMessage("adm_reg_new")?></h4></td></tr></thead>
				<tr valign="top">
					<td width="40%">
					<p><?=GetMessage("adm_reg_login")?><span class="starrequired">*</span><br />
					<input type="text" name="NEW_LOGIN" value="<?=$arResult["REQUEST"]["NEW_LOGIN"]?>" size="20" /></p>
					<p><?=GetMessage("adm_reg_pass")?><span class="starrequired">*</span><br />
					<input type="password" name="NEW_PASSWORD" size="20" value="<?=$arResult["REQUEST"]["NEW_PASSWORD"]?>" /></p>
					<p><?=GetMessage("adm_reg_pass_conf")?><span class="starrequired">*</span><br />
					<input type="password" name="CONFIRM_PASSWORD" size="20" value="<?=$arResult["REQUEST"]["CONFIRM_PASSWORD"]?>" /></p>
					<p><?=GetMessage("subscr_email")?><span class="starrequired">*</span><br />
					<input type="text" name="EMAIL" value="<?=$arResult["SUBSCRIPTION"]["EMAIL"]!=""?$arResult["SUBSCRIPTION"]["EMAIL"]:$arResult["REQUEST"]["EMAIL"];?>" size="30" maxlength="255" /></p>
				<?
					/* CAPTCHA */
					if (COption::GetOptionString("main", "captcha_registration", "N") == "Y"):
						$capCode = $GLOBALS["APPLICATION"]->CaptchaGetCode();
					?>
						<p><?=GetMessage("subscr_CAPTCHA_REGF_TITLE")?><br />
						<input type="hidden" name="captcha_sid" value="<?= htmlspecialcharsbx($capCode) ?>" />
						<img src="/bitrix/tools/captcha.php?captcha_sid=<?= htmlspecialcharsbx($capCode) ?>" width="180" height="40" alt="CAPTCHA" /></p>
						<p><?=GetMessage("subscr_CAPTCHA_REGF_PROMT")?><span class="starrequired">*</span><br />
						<input type="text" name="captcha_word" size="30" maxlength="50" value="" /></p>
					<?endif;?>
					</td>
					<td width="60%">
						<?if($arResult["ALLOW_ANONYMOUS"]=="Y"):?>
							<?=GetMessage("subscr_auth_note")?>
						<?else:?>
							<?=GetMessage("adm_must_auth")?>
						<?endif;?>
					</td>
				</tr>
				<tfoot><tr><td colspan="2"><input type="submit" name="Save" value="<?=GetMessage("adm_reg_butt")?>" /></td></tr></tfoot>
				</table>
				<?foreach($arResult["RUBRICS"] as $itemID => $itemValue):?>
					<input type="hidden" name="RUB_ID[]" value="<?=$itemValue["ID"]?>">
				<?endforeach;?>
				<input type="hidden" name="PostAction" value="<?=($arResult["ID"]>0? "Update":"Add")?>" />
				<input type="hidden" name="ID" value="<?=$arResult["SUBSCRIPTION"]["ID"];?>" />
				<?if($_REQUEST["register"] == "YES"):?>
					<input type="hidden" name="register" value="YES" />
				<?endif;?>
				<?if($_REQUEST["authorize"]=="YES"):?>
					<input type="hidden" name="authorize" value="YES" />
				<?endif;?>
			</form>
			<br />
		<?endif;?>
	</div>
<?endif; //$arResult["ALLOW_ANONYMOUS"]=="Y" && $authorize<>"YES"?>
