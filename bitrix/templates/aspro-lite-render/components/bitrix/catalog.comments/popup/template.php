<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$this->setFrameMode(true);

$templateData = [
	'TABS_ID' => 'soc_comments_' . $arResult['ELEMENT']['ID'],
	'TABS_FRAME_ID' => 'soc_comments_div_' . $arResult['ELEMENT']['ID'],
	'BLOG_USE' => ($arResult['BLOG_USE'] ? 'Y' : 'N'),
	'FB_USE' => 'N',
	'VK_USE' => 'N',
	'BLOG' => [
		'BLOG_FROM_AJAX' => $arResult['BLOG_FROM_AJAX'],
	],
];

if (!$templateData['BLOG']['BLOG_FROM_AJAX']) {
	if (!empty($arResult['ERRORS'])) {
		ShowError(implode('<br>', $arResult['ERRORS']));
		return;
	}

	if ($arResult['BLOG_USE']) {
		$templateData['BLOG']['AJAX_PARAMS'] = [
			'IBLOCK_ID' => $arResult['ELEMENT']['IBLOCK_ID'],
			'ELEMENT_ID' => $arResult['ELEMENT']['ID'],
			'XML_ID' => $arParams['XML_ID'],
			'URL_TO_COMMENT' => $arParams['~URL_TO_COMMENT'],
			'WIDTH' => $arParams['WIDTH'],
			'COMMENTS_COUNT' => $arParams['COMMENTS_COUNT'],
			'BLOG_USE' => 'Y',
			'BLOG_FROM_AJAX' => 'Y',
			'FB_USE' => 'N',
			'VK_USE' => 'N',
			'BLOG_TITLE' => $arParams['~BLOG_TITLE'],
			'BLOG_URL' => $arParams['~BLOG_URL'],
			'PATH_TO_SMILE' => $arParams['~PATH_TO_SMILE'],
			'EMAIL_NOTIFY' => $arParams['EMAIL_NOTIFY'],
			'AJAX_POST' => $arParams['AJAX_POST'],
			'SHOW_SPAM' => $arParams['SHOW_SPAM'],
			'SHOW_RATING' => $arParams['SHOW_RATING'],
			'RATING_TYPE' => $arParams['~RATING_TYPE'],
			'CACHE_TYPE' => 'N',
			'CACHE_TIME' => '0',
			'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
			'TEMPLATE_THEME' => $arParams['~TEMPLATE_THEME'],
			'SHOW_DEACTIVATED' => $arParams['SHOW_DEACTIVATED'],
			'COMMENT_PROPERTY' => [
				'UF_BLOG_COMMENT_DOC'
			],
			"REVIEW_COMMENT_REQUIRED" => $arParams["REVIEW_COMMENT_REQUIRED"],
			"REVIEW_FILTER_BUTTONS" => $arParams["REVIEW_FILTER_BUTTONS"],
			"REAL_CUSTOMER_TEXT" => $arParams["REAL_CUSTOMER_TEXT"],
			"RATE" => $arParams["RATE"] ?? "",
		];
	}
	else {
		ShowError(GetMessage("IBLOCK_CSC_NO_DATA"));
	}
}
