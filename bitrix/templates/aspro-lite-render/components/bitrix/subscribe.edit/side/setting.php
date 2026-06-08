<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="subscribe-side-block bordered outer-rounded-x">
	<div class="subscribe-side-block__text <?=$arParams["PARAMS"]["DOP_CLASS"] ? $arParams["PARAMS"]["DOP_CLASS"] : ''?>">
		<span><?=GetMessage("SUBSCRIBE_TEXT")?></span>
	</div>
	<div class="subscribe-side-block__button">
		<div class="btn btn-default btn-wide"  data-event="jqm" data-param-type="subscribe" data-name="subscribe">
			<?=GetMessage("ADD_USER")?>
		</div>
    </div>
</div>