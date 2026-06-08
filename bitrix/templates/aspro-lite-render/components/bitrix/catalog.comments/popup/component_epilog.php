<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @var array $arResult */
/** @var array $arParams */
/** @var CBitrixComponent $this */

$ajaxMode = isset($templateData['BLOG']['BLOG_FROM_AJAX']) && $templateData['BLOG']['BLOG_FROM_AJAX'];
if (!$ajaxMode) {
	CJSCore::Init(array('window', 'ajax'));
}

global $BLOG_DATA;
$BLOG_DATA = $arResult;

if (isset($templateData['BLOG_USE']) && $templateData['BLOG_USE'] == 'Y') {
	if (!$ajaxMode) {
		$_SESSION['IBLOCK_CATALOG_COMMENTS_PARAMS_' . $templateData['BLOG']['AJAX_PARAMS']["IBLOCK_ID"] . '_' . $templateData['BLOG']['AJAX_PARAMS']["ELEMENT_ID"]] = $templateData['BLOG']['AJAX_PARAMS'];
	}

	$arBlogCommentParams = [
		'SEO_USER' => 'N',
		'ID' => $arResult['BLOG_DATA']['BLOG_POST_ID'],
		'BLOG_URL' => $arResult['BLOG_DATA']['BLOG_URL'],
		'PATH_TO_SMILE' => $arParams['PATH_TO_SMILE'],
		'COMMENTS_COUNT' => $arParams['COMMENTS_COUNT'],
		"DATE_TIME_FORMAT" => $DB->DateFormatToPhp(FORMAT_DATETIME),
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"AJAX_POST" => $arParams["AJAX_POST"],
		"AJAX_MODE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"SIMPLE_COMMENT" => "Y",
		"SHOW_SPAM" => $arParams["SHOW_SPAM"],
		"NOT_USE_COMMENT_TITLE" => "Y",
		"SHOW_RATING" => $arParams["SHOW_RATING"],
		"RATING_TYPE" => $arParams["RATING_TYPE"],
		"PATH_TO_POST" => $arResult["URL_TO_COMMENT"],
		"REVIEW_COMMENT_REQUIRED" => $arParams["REVIEW_COMMENT_REQUIRED"],
		"REVIEW_FILTER_BUTTONS" => $arParams["REVIEW_FILTER_BUTTONS"],
		"REAL_CUSTOMER_TEXT" => $arParams["REAL_CUSTOMER_TEXT"],
		"IBLOCK_ID" => (array_key_exists('AJAX_PARAMS', $templateData['BLOG']) && array_key_exists('IBLOCK_ID', $templateData['BLOG']['AJAX_PARAMS']) && $templateData['BLOG']['AJAX_PARAMS']['IBLOCK_ID'] ? $templateData['BLOG']['AJAX_PARAMS']['IBLOCK_ID'] : $_REQUEST['IBLOCK_ID']),
		"ELEMENT_ID" => (array_key_exists('AJAX_PARAMS', $templateData['BLOG']) && array_key_exists('ELEMENT_ID', $templateData['BLOG']['AJAX_PARAMS']) && $templateData['BLOG']['AJAX_PARAMS']['ELEMENT_ID'] ? $templateData['BLOG']['AJAX_PARAMS']['ELEMENT_ID'] : $_REQUEST['ELEMENT_ID']),
		"XML_ID" => (array_key_exists('AJAX_PARAMS', $templateData['BLOG']) && array_key_exists('XML_ID', $templateData['BLOG']['AJAX_PARAMS']) && $templateData['BLOG']['AJAX_PARAMS']['XML_ID'] ? $templateData['BLOG']['AJAX_PARAMS']['XML_ID'] : $_REQUEST['XML_ID']),
		"NO_URL_IN_COMMENTS" => "",
		"USE_FILTER" => $arParams["USE_FILTER"],
		"RATE" => $arParams["RATE"] ?? "",
		"SHOW_LICENCE" => TSolution::GetFrontParametrValue('SHOW_LICENCE', SITE_ID),
	];

	$APPLICATION->IncludeComponent(
		'aspro:blog.post.comment.lite',
		'popup',
		$arBlogCommentParams,
		$this,
		['HIDE_ICONS' => 'Y']
	);
}
