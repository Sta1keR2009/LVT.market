<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//*************************************
//show current authorization section
//*************************************
?>
<div class="authorization-block top-form">
	<h4><?=GetMessage("subscr_title_auth")?></h4>

	<form action="<?=$arResult["FORM_ACTION"]?>" method="post">
	<?=bitrix_sessid_post();?>
		<div ><?=GetMessage("adm_auth_user")?>
			<?=htmlspecialcharsbx($USER->GetFormattedName(false));?> (<?=htmlspecialcharsbx($USER->GetLogin())?>).
		</div>
		<div>
			<?if($arResult["ID"]==0):?>
				<?=GetMessage("subscr_auth_logout1")?> <a href="<?=$arResult["FORM_ACTION"]?>?logout=YES&amp;sf_EMAIL=<?=$arResult["REQUEST"]["EMAIL"]?><?=$arResult["REQUEST"]["RUBRICS_PARAM"]?>"><?=GetMessage("adm_auth_logout")?></a><?=GetMessage("subscr_auth_logout2")?><br />
			<?else:?>
				<?=GetMessage("subscr_auth_logout3")?> <a href="<?=$arResult["FORM_ACTION"]?>?logout=YES&amp;sf_EMAIL=<?=$arResult["REQUEST"]["EMAIL"]?><?=$arResult["REQUEST"]["RUBRICS_PARAM"]?>"><?=GetMessage("adm_auth_logout")?></a><?=GetMessage("subscr_auth_logout4")?><br />
			<?endif;?>
		</div>
	</form>
</div>
