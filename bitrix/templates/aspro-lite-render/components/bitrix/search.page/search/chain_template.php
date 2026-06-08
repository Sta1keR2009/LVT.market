<?
//Navigation chain template
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arChainBody = '';
foreach($arCHAIN as $i => $item){
	if(strlen($item["LINK"])<strlen(SITE_DIR)){
		continue;
	}
	if($item["LINK"] <> ""){
		$arChainBody .= '<li><a class="muted search-path__link" href="'.$item["LINK"].'">'.htmlspecialcharsex($item["TITLE"]).'</a><span class="search-path__separator">'.TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#right-7-5', 'muted-use fill-dark-light', ['WIDTH' => 7,'HEIGHT' => 5]).'</span></li>';
	}
	else{
		$arChainBody .= '<li>'.htmlspecialcharsex($item["TITLE"]).'</li>';
	}
}
return $arChainBody;
?>