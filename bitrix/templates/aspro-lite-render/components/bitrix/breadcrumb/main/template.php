<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$strReturn = '';
if($arResult){
	\Bitrix\Main\Loader::includeModule("iblock");
	global $NextSectionID, $APPLICATION;
	$cnt = count($arResult);
	$lastindex = $cnt - 1;
	$visibleMobile = 0;
	if(\Bitrix\Main\Loader::includeModule(VENDOR_MODULE_ID))
	{
		global $arTheme;
        $bShowCatalogSubsections = ($arTheme["SHOW_BREADCRUMBS_CATALOG_SUBSECTIONS"]["VALUE"] == "Y");
		$bMobileBreadcrumbs = ($arTheme["MOBILE_CATALOG_BREADCRUMBS"]["VALUE"] == "compact" && $NextSectionID);
	}
	if ($bMobileBreadcrumbs) {
		$visibleMobile = $lastindex - 1;
	}
	for($index = 0; $index < $cnt; ++$index){
		$arSubSections = array();
		$bShowMobileArrow = false;
		$arItem = $arResult[$index];
		$title = htmlspecialcharsex($arItem["TITLE"]);
		$bLast = $index == $lastindex;

        if ($NextSectionID) {
			if ($bMobileBreadcrumbs && $visibleMobile == $index) {
				$bShowMobileArrow = true;
			}

            if ($bShowCatalogSubsections) {
				$arSubSections = array_filter(TSolution::getChainNeighbors($NextSectionID, $arItem['LINK']), fn ($arSubSection) => $arSubSection['LINK'] !== $arItem['LINK']);
			}
		}
		if($index){
			$strReturn .= '<span class="breadcrumbs__separator relative">&ndash;</span>';
		}
		if($arItem["LINK"] <> "" && $arItem['LINK'] != GetPagePath() && $arItem['LINK']."index.php" != GetPagePath() || $arSubSections){
			$strReturn .= '<div class="breadcrumbs__item font_13'.($bMobileBreadcrumbs ? ' breadcrumbs__item--mobile' : '').($bShowMobileArrow ? ' breadcrumbs__item--visible-mobile' : '').($arSubSections ? ' breadcrumbs__item--with-dropdown colored_theme_hover_bg-block dropdown-select' : '').($bLast ? ' cat_last' : '').' relative" id="bx_breadcrumb_'.$index.'" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';

			$strReturn .= '<a class="breadcrumbs__link" href="'.$arItem["LINK"].'" title="'.$title.'" itemprop="item">';

			$strReturn .= '<span itemprop="name" class="breadcrumbs__item-name">'.$title.'</span><meta itemprop="position" content="'.($index + 1).'">';

            if ($arSubSections) {
			    $strReturn .= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/arrows.svg#down-7-5', 'breadcrumbs__dropdown-select__icon-down hide-600', ['WIDTH' => 7, 'HEIGHT' => 5]);
            }

			$strReturn .= '</a>';

            if ($arSubSections) {
                $strReturn .= '<div class="breadcrumbs__dropdown-wrapper dropdown-select__list dropdown-menu-wrapper dropdown-menu-wrapper--visible outer-rounded-x hide-600"><div class="breadcrumbs__dropdown dropdown-menu-inner scrollbar outer-rounded-x shadow-hovered color-theme-parent-all">';
					foreach($arSubSections as $arSubSection){
						$strReturn .= '<div class="breadcrumbs__dropdown-select__list-item"><a class="breadcrumbs__dropdown-item dark_link font_13" href="'.$arSubSection["LINK"].'">'.$arSubSection["NAME"].'</a></div>';
					}
				$strReturn .= '</div></div>';
            }

			$strReturn .= '</div>';
		}
		else{
			$strReturn .= '<span class="breadcrumbs__item link-opacity-color link-opacity-color--secondary-color category-separator-sibling category-separator-sibling--inline font_12'.($bMobileBreadcrumbs ? ' breadcrumbs__item--mobile' : '').'" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><link href="'.GetPagePath().'" itemprop="item" /><span><span itemprop="name" class="breadcrumbs__item-name font_13
            ">'.$title.'</span><meta itemprop="position" content="'.($index + 1).'"></span></span>';
		}
	}

	return '<div class="breadcrumbs swipeignore" itemscope="" itemtype="http://schema.org/BreadcrumbList">'.$strReturn.'</div>';
}
else{
	return $strReturn;
}
?>
