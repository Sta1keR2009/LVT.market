<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)die();?>
<?
$arAuthServices = $arPost = array();
if(is_array($arParams["~AUTH_SERVICES"]))
{
	$arAuthServices = $arParams["~AUTH_SERVICES"];
}
if(is_array($arParams["~POST"]))
{
	$arPost = $arParams["~POST"];
}
?>
<?if ($arParams["~AUTH_SERVICES"]):?>
	<div class="socserv">
		<div class="social">
			<div class="social__title font_14"><?=GetMessage('LOGIN_BY')?></div>
			<ul class="social__items social__items--type-color social__items--grid grid-list--items-4">
				<?foreach($arParams["~AUTH_SERVICES"] as $service):?>
					<li class="social__item grid-list__item hover_blink <?=htmlspecialcharsbx($service["ICON"])?>">
						<?
						if(($arParams["~FOR_SPLIT"] == 'Y') && (is_array($service["FORM_HTML"])))
							$onClickEvent = $service["FORM_HTML"]["ON_CLICK"];
						else
							$onClickEvent = "onclick=\"BxShowAuthService('".$service['ID']."', '".$arParams['SUFFIX']."')\"";
						if(strpos($service["FORM_HTML"], "OPENID_IDENTITY")!==false){
						?>
							<a title="<?=htmlspecialcharsbx($service["NAME"])?>" href="javascript:void(0)" <?=$onClickEvent?> class="social__link shine" type="<?=htmlspecialcharsbx($service["ICON"])?>" id="bx_auth_href_<?=$arParams["SUFFIX"]?><?=$service["ID"]?>"></a>
						<?}else{?>
							<?$service["FORM_HTML"]=str_replace(['class="', 'bx-ss-button'], ['title="'.htmlspecialcharsbx($service["ICON"]).'" class="', 'bx-ss-button social__link shine'], $service["FORM_HTML"]);?>
							<?=$service["FORM_HTML"];?>
						<?}?>
					</li>
				<?endforeach?>
			</ul>
		</div>
		<form method="post" name="bx_auth_services<?=$arParams["SUFFIX"]?>" target="_top" action="<?=$arParams["AUTH_URL"]?>">
			<div id="bx_auth_serv<?=$arParams["SUFFIX"]?>" style="display:none">
				<?foreach($arParams["~AUTH_SERVICES"] as $service):?>
					<?if(($arParams["~FOR_SPLIT"] != 'Y') || (!is_array($service["FORM_HTML"]))):?>
						<?$service["FORM_HTML"]=str_replace('"button"', '"btn btn-sm btn-default"', $service["FORM_HTML"]);?>
						<?$service["FORM_HTML"]=str_replace('"required"', '"required form-control"', $service["FORM_HTML"]);?>
						<div id="bx_auth_serv_<?=$arParams["SUFFIX"]?><?=$service["ID"]?>" style="display:none"><?=$service["FORM_HTML"]?></div>
					<?endif;?>
				<?endforeach?>
			</div>
			<?foreach($arPost as $key => $value):?>
					<?if(!preg_match("|OPENID_IDENTITY|", $key)):?>
						<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
					<?endif;?>
			<?endforeach?>
			<input type="hidden" name="auth_service_id" value="" />
		</form>
	</div>

	<script>
		$("#bx_auth_serv<?=$arParams["SUFFIX"]?> input[type=text]").each(function()
		{
			$(this).addClass("required form-control").attr("required", "true");
		});
		function BxShowAuthService(id, suffix)
		{
			var bxCurrentAuthId = '';
			if(window['bxCurrentAuthId'+suffix])
				bxCurrentAuthId = window['bxCurrentAuthId'+suffix];

			BX('bx_auth_serv'+suffix).style.display = '';
			if(bxCurrentAuthId != '' && bxCurrentAuthId != id)
			{
				BX('bx_auth_serv_'+suffix+bxCurrentAuthId).style.display = 'none';
			}
			BX('bx_auth_href_'+suffix+id).blur();
			BX('bx_auth_serv_'+suffix+id).style.display = '';
			var el = BX.findChild(BX('bx_auth_serv_'+suffix+id), {'tag':'input', 'attribute':{'type':'text'}}, true);
			if(el)
				try{el.focus();}catch(e){}
			window['bxCurrentAuthId'+suffix] = id;
			if(document.forms['bx_auth_services'+suffix])
				document.forms['bx_auth_services'+suffix].auth_service_id.value = id;
			else if(document.forms['bx_user_profile_form'+suffix])
				document.forms['bx_user_profile_form'+suffix].auth_service_id.value = id;
		}

		var bxAuthWnd = false;
		function BxShowAuthFloat(id, suffix)
		{
			var bCreated = false;
			if(!bxAuthWnd)
			{
				bxAuthWnd = new BX.CDialog({
					'content':'<div id="bx_auth_float_container"></div>',
					'width': 640,
					'height': 400,
					'resizable': false
				});
				bCreated = true;
			}
			bxAuthWnd.Show();

			if(bCreated)
				BX('bx_auth_float_container').appendChild(BX('bx_auth_float'));

			BxShowAuthService(id, suffix);
		}
	</script>
<?endif;?>