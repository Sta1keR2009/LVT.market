<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="subscribe-block">
	<?ob_start();?>
	<?$APPLICATION->IncludeFile(SITE_DIR."include/footer/left_subscribe_text.php", Array(), Array(
			"MODE" => "php",
			"NAME" => "Subscribe text",
		)
	);?>
	<?$html = ob_get_contents();?>
	<?ob_end_clean();?>
	<?if(trim($html)):?>
		<div class="subscribe-block__part--left">
			<div class="subscribe-block__text">
				<?=$html;?>
			</div>
		</div>
	<?endif;?>
	<div class="subscribe-block__part--right">
		<form action="<?=SITE_DIR.$arParams["PAGE"]?>" method="post" class="subscribe-form">
			<?echo bitrix_sessid_post();?>
			<input type="text" name="EMAIL" class="form-control subscribe-input required" placeholder="<?=GetMessage("EMAIL_INPUT");?>" value="<?=$arResult["USER_EMAIL"] ? $arResult["USER_EMAIL"] : ($arResult["SUBSCRIPTION"]["EMAIL"]!=""?$arResult["SUBSCRIPTION"]["EMAIL"]:$arResult["REQUEST"]["EMAIL"]);?>" size="30" maxlength="255" />

			<?foreach($arResult["RUBRICS"] as $itemID => $itemValue):?>
				<input type="hidden" name="RUB_ID[]" value="<?=$itemValue["ID"]?>" />
			<?endforeach;?>

			<input type="hidden" name="FORMAT" value="html" />

			<div class="subscribe-form__save stroke-dark-light-block">
				<button type="submit" name="Save" class="btn btn-lg subscribe-btn">
					<?=GetMessage("ADD_USER")?>
				</button>
			</div>

			<input type="hidden" name="PostAction" value="Add" />
			<input type="hidden" name="ID" value="<?echo $arResult["SUBSCRIPTION"]["ID"];?>" />
		</form>
	</div>
</div>