<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?use CLiteCache as SolutionCache;?>
<?
$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();
$arPost = $request->getPostList()->toArray();

$arAllLetters = $APPLICATION->ConvertCharsetArray(json_decode($arPost['LETTERS'], true), 'UTF-8', LANG_CHARSET);
sort($arAllLetters); // reset keys
$arLetters = array_column($arAllLetters, 'LETTER');


$iblockID = $arPost['IBLOCK_ID'];
$arTmpFilter = [
	'ACTIVE' => 'Y',
	'IBLOCK_ID' => $iblockID,
];
$arSort = [
	'SORT' => 'ASC',
	'NAME' => 'ASC'
];

$letterTmp = $arPost['LETTER'];
$letter = '';
$digitCode = 'DIGITS';
if ($letterTmp) {
	$arTmpLetter = explode('--', $letterTmp);
	$letter = strtoupper($arTmpLetter[1]);
	$lang = 'ru';
	if ($arTmpLetter[0] === 'ru') {
		if (!isset($_SESSION['LETTERS_LIST'][$lang])) {
			$mess = IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/js_core_translit.php", $lang, true);
			$trans_from = explode(",", $mess["TRANS_TO"]);
			$trans_to = explode(",", $mess["TRANS_FROM"]);
			foreach ($trans_from as $i => $from) {
				if (!$_SESSION['LETTERS_LIST'][$lang][$from]) {
					$_SESSION['LETTERS_LIST'][$lang][$from] = $trans_to[$i];
				}
			}
		}
		$letter = $_SESSION['LETTERS_LIST'][$lang][$letter];
		if (!$letter) {
			if($key = array_search($arTmpLetter[1], array_column($arAllLetters, 'CODE'))) {
				$letter = $arAllLetters[$key]['LETTER'];
			}
		}
	}
}

$arItems = [];
if ($letter) {
	$arFilter = ['NAME' => $letter.'%'];
	if ($letter === $digitCode) {
		unset($arLetters[0]);
		$letter = $arAllLetters[0]['LETTER'];
		$arFilter = ['!NAME' => array_map(function($a){ return $a.'%';}, $arLetters)];
	}
	$arItems[$letter]['ITEMS'] = SolutionCache::CIBlockElement_GetList(
		array_merge(
			$arSort,
			[
				'CACHE' => [
					"MULTI" =>"Y", 
					"TAG" => SolutionCache::GetIBlockCacheTag($iblockID)
				]
			]
		),
		array_merge(
			$arFilter,
			$arTmpFilter
		),
		false,
		false,
		['ID', 'NAME', 'DETAIL_PAGE_URL']
	);
} else {
	// get brands started from non-alphanumeric characters
	if (($key = array_search($digitCode, array_column($arAllLetters, 'CODE'))) !== false) {
		unset($arLetters[$key]);
		$arItems[$arAllLetters[$key]['LETTER']]['CODE'] = $arAllLetters[$key]['CODE'];
		$arItems[$arAllLetters[$key]['LETTER']]['PREFIX'] = $arAllLetters[$key]['PREFIX'];
		$arItems[$arAllLetters[$key]['LETTER']]['ITEMS'] = SolutionCache::CIBlockElement_GetList(
			array_merge(
				$arSort,
				[
					'CACHE' => [
						"MULTI" =>"Y", 
						"TAG" => SolutionCache::GetIBlockCacheTag($iblockID)
					]
				]
			),
			array_merge(
				[
					'!NAME' => array_map(function($a){ return $a.'%';}, $arLetters)
				],
				$arTmpFilter
			),
			false,
			[
				'nTopCount' => 16
			],
			['ID', 'NAME', 'DETAIL_PAGE_URL']
		);
	}
	
	// get brands started from alphanumeric characters
	foreach ($arLetters as $key => $letter) {
		$arItems[$letter]['CODE'] = $arAllLetters[$key]['CODE'];
		$arItems[$letter]['PREFIX'] = $arAllLetters[$key]['PREFIX'];
		$arItems[$letter]['ITEMS'] = SolutionCache::CIBlockElement_GetList(
			array_merge(
				$arSort,
				[
					'CACHE' => [
						"MULTI" =>"Y", 
						"TAG" => SolutionCache::GetIBlockCacheTag($iblockID)
					]
				]
			),
			array_merge(
				[
					'NAME' => $letter.'%'
				],
				$arTmpFilter
			),
			false,
			[
				'nTopCount' => 16
			],
			['ID', 'NAME', 'DETAIL_PAGE_URL']
		);
	}
}
?>
<?include_once('list.php');?>