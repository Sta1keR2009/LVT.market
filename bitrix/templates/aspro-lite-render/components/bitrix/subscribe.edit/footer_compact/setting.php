<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="subscribe-block subscribe-block--compact">
	<div class="subscribe-block__part--right">
		<form action="<?=SITE_DIR.$arParams["PAGE"]?>" method="post" class="subscribe-form">
			<?echo bitrix_sessid_post();?>
			<input type="text" name="EMAIL" class="form-control subscribe-input required" placeholder="<?=GetMessage("PLACEHOLDER_MESSAGE");?>" value="<?=$arResult["USER_EMAIL"] ? $arResult["USER_EMAIL"] : ($arResult["SUBSCRIPTION"]["EMAIL"]!=""?$arResult["SUBSCRIPTION"]["EMAIL"]:$arResult["REQUEST"]["EMAIL"]);?>" size="30" maxlength="255" />

			<?foreach($arResult["RUBRICS"] as $itemID => $itemValue):?>
				<input type="hidden" name="RUB_ID[]" value="<?=$itemValue["ID"]?>" />
			<?endforeach;?>

			<input type="hidden" name="FORMAT" value="html" />

			<div class="subscribe-form__save stroke-dark-light-block">
				<button type="submit" title="<?=GetMessage('ADD_USER')?>" name="Save" class="btn btn-lg subscribe-btn">
					<?=\TSolution::showSpriteIconSvg( SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#left-18-12', ' subscribe-btn__icon', ['WIDTH' => 18, 'HEIGHT' => 12]);?>
				</button>
			</div>

			<input type="hidden" name="PostAction" value="Add" />
			<input type="hidden" name="ID" value="<?echo $arResult["SUBSCRIPTION"]["ID"];?>" />
		</form>
	</div>
</div>