<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die(); ?>
<?
	use Bitrix\Main\Localization\Loc;
?>
<div class="flexbox">
	<div id="popup_subscribe_container" class="form popup">
		<? if($arResult['EVENT']['SUCCESS_UPDATE']) : ?>
			<? include(__DIR__ . '/success.php'); ?>
		<? else : ?>
			<? include(__DIR__ . '/subscribe.php'); ?>
		<? endif ?>
	</div>
</div>
