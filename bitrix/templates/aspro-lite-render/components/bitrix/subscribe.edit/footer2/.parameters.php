<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arTemplateParameters = array(
	"PAGE" => Array(
		"NAME" => GetMessage("T_PAGE"),
		"TYPE" => "STRING",
		"DEFAULT" => "personal/subscribe/",
	),
	"TITLE" => Array(
		"NAME" => GetMessage("T_TITLE"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("V_TITLE"),
	),
);
?>
