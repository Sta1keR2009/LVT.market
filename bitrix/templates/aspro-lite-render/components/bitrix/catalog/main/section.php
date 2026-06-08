<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}?>
<?$this->setFrameMode(true); ?>
<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loader::includeModule('iblock');

global $arTheme, $NextSectionID, $arRegion, $bHideLeftBlock;

$arPageParams = $arSection = $section = [];

if (!$arParams['SECTION_DISPLAY_PROPERTY']) {
    $arParams['SECTION_DISPLAY_PROPERTY'] = 'UF_VIEWTYPE';
}
if (!$arParams['SECTION_DISPLAY_COUNT_PROPERTY']) {
    $arParams['SECTION_DISPLAY_COUNT_PROPERTY'] = 'UF_LINE_ELEMENT_CNT';
}
$bOrderViewBasket = (trim($arTheme['ORDER_VIEW']['VALUE']) === 'Y');
$_SESSION['SMART_FILTER_VAR'] = $arParams['FILTER_NAME']; ?>

<?$bShowLeftBlock = ($arTheme['LEFT_BLOCK_CATALOG_SECTIONS']['VALUE'] == 'Y' && !defined('ERROR_404') && !$bHideLeftBlock); ?>

<?$APPLICATION->SetPageProperty('MENU', 'N'); ?>
<?$APPLICATION->AddViewContent('right_block_class', 'catalog_page '); ?>

<?if(TSolution::checkAjaxRequest()):?>
    <div>
<?endif; ?>

<div class="top-content-block">
    <?$APPLICATION->ShowViewContent('top_content'); ?>
    <?$APPLICATION->ShowViewContent('top_content2'); ?>
</div>

<?if(TSolution::checkAjaxRequest()):?>
    </div>
<?endif; ?>

<?php
$arParams['SHOW_ONE_CLINK_BUY'] = $arTheme['SHOW_ONE_CLICK_BUY']['VALUE'];
$arParams['MAX_GALLERY_ITEMS'] = $arTheme['SHOW_CATALOG_GALLERY_IN_LIST']['DEPENDENT_PARAMS']['MAX_GALLERY_ITEMS']['VALUE'];
$arParams['SHOW_GALLERY'] = $arTheme['SHOW_CATALOG_GALLERY_IN_LIST']['VALUE'];

$arParams['SHOW_DISCOUNT_TIME'] = TSolution::GetFrontParametrValue('SHOW_DISCOUNT_TIME');
$arParams['SHOW_DISCOUNT_PERCENT'] = TSolution::GetFrontParametrValue('SHOW_DISCOUNT_PERCENT');
$arParams['SHOW_OLD_PRICE'] = TSolution::GetFrontParametrValue('SHOW_OLD_PRICE');

$arParams['SHOW_RATING'] = Loader::includeModule('blog') ? TSolution::GetFrontParametrValue('SHOW_RATING') : false;
$arParams['CONVERT_CURRENCY'] = TSolution::GetFrontParametrValue('CONVERT_CURRENCY');
$arParams['CURRENCY_ID'] = TSolution::GetFrontParametrValue('CURRENCY_ID');
$arParams['PRICE_VAT_INCLUDE'] = TSolution::GetFrontParametrValue('PRICE_VAT_INCLUDE');

// set params for props from module
TSolution\Functions::replacePropsParams($arParams);

// get current section ID
$arSectionFilter = [];
if ($arResult['VARIABLES']['SECTION_ID'] > 0) {
    $arSectionFilter = ['GLOBAL_ACTIVE' => 'Y', 'ID' => $arResult['VARIABLES']['SECTION_ID'], 'IBLOCK_ID' => $arParams['IBLOCK_ID']];
} elseif (strlen(trim($arResult['VARIABLES']['SECTION_CODE'])) > 0) {
    $arSectionFilter = ['GLOBAL_ACTIVE' => 'Y', '=CODE' => $arResult['VARIABLES']['SECTION_CODE'], 'IBLOCK_ID' => $arParams['IBLOCK_ID']];
}
if ($arSectionFilter) {
    $section = TSolution\Cache::CIBlockSection_GetList(['CACHE' => ['MULTI' => 'N', 'TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID'])]], TSolution::makeSectionFilterInRegion($arSectionFilter), false, ['ID', 'IBLOCK_ID', 'NAME', 'DESCRIPTION', 'UF_SECTION_DESCR', 'UF_FILTER_VIEW', 'UF_OFFERS_TYPE', 'UF_TABLE_PROPS', 'UF_INCLUDE_SUBSECTION', 'UF_LINKED_BANNERS', $arParams['SECTION_DISPLAY_PROPERTY'], $arParams['SECTION_DISPLAY_COUNT_PROPERTY'], 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL', 'LEFT_MARGIN', 'RIGHT_MARGIN']);
}

$typeSKU = '';
$bSetElementsLineRow = false;

if ($section) {
    $NextSectionID = $arSection['ID'] = $section['ID'];
    $arSection['NAME'] = $section['NAME'];
    $arSection['IBLOCK_SECTION_ID'] = $section['IBLOCK_SECTION_ID'];
    $arSection['DEPTH_LEVEL'] = $section['DEPTH_LEVEL'];
    if ($section[$arParams['SECTION_DISPLAY_PROPERTY']]) {
        $arDisplayRes = CUserFieldEnum::GetList([], ['ID' => $section[$arParams['SECTION_DISPLAY_PROPERTY']]]);
        if ($arDisplay = $arDisplayRes->GetNext()) {
            $arSection['DISPLAY'] = $arDisplay['XML_ID'];
        }
    }
    if($section['UF_LINE_ELEMENT_CNT']) {
        $arDisplayCountRes = CUserFieldEnum::GetList([], ['ID' => $section[$arParams['SECTION_DISPLAY_COUNT_PROPERTY']]]);
        if ($arDisplay = $arDisplayCountRes->GetNext()) {
            $arSection['UF_LINE_ELEMENT_CNT'] = $arDisplay['XML_ID'];
        }
    }
    if (strlen($section['DESCRIPTION'])) {
        $arSection['DESCRIPTION'] = $section['DESCRIPTION'];
    }
    if (strlen($section['UF_SECTION_DESCR'])) {
        $arSection['UF_SECTION_DESCR'] = $section['UF_SECTION_DESCR'];
    }
    if ($section['UF_LINKED_BANNERS']) {
        $arSection['UF_LINKED_BANNERS'] = $section['UF_LINKED_BANNERS'];
    }
    // $posSectionDescr = COption::GetOptionString(VENDOR_MODULE_ID, "SHOW_SECTION_DESCRIPTION", "BOTH", SITE_ID);
    $posSectionDescr = 'BOTH';

    global $arSubSectionFilter;
    $arSubSectionFilter = [
        'SECTION_ID' => $arSection['ID'],
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'ACTIVE' => 'Y',
        'GLOBAL_ACTIVE' => 'Y',
    ];
    $iSectionsCount = count(TSolution\Cache::CIblockSection_GetList(['CACHE' => ['TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 'MULTI' => 'Y']], TSolution::makeSectionFilterInRegion($arSubSectionFilter)));

    if ($arParams['SHOW_MORE_SUBSECTIONS'] === 'N') {
        $iSectionsCount = 0;
    }

    // set smartfilter view
    $viewTmpFilter = 0;
    if ($section['UF_FILTER_VIEW']) {
        $viewTmpFilter = $section['UF_FILTER_VIEW'];
    }

    $viewTableProps = 0;
    if ($section['UF_TABLE_PROPS']) {
        $viewTableProps = $section['UF_TABLE_PROPS'];
    }

    if ($section['UF_OFFERS_TYPE']) {
        $typeSKU = $section['UF_OFFERS_TYPE'];
    }

    $includeSubsection = '';
    if ($section['UF_INCLUDE_SUBSECTION']) {
        $includeSubsection = $section['UF_INCLUDE_SUBSECTION'];
    }

    $countElementsInRow = '';
    if ($section['UF_LINE_ELEMENT_CNT']) {
        $countElementsInRow = $section['UF_LINE_ELEMENT_CNT'];
    }

    if (!$viewTmpFilter || !$arSection['DISPLAY'] || !$viewTableProps || !$includeSubsection || !$typeSKU || !$arSection['UF_LINKED_BANNERS'] || !$arSection['UF_LINE_ELEMENT_CNT']) {
        if ($section['DEPTH_LEVEL'] > 1) {
            $sectionParent = TSolution\Cache::CIBlockSection_GetList(['CACHE' => ['MULTI' => 'N', 'TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID'])]], ['GLOBAL_ACTIVE' => 'Y', 'ID' => $section['IBLOCK_SECTION_ID'], 'IBLOCK_ID' => $arParams['IBLOCK_ID']], false, ['ID', 'IBLOCK_ID', 'NAME', 'UF_FILTER_VIEW', 'UF_OFFERS_TYPE', 'UF_TABLE_PROPS', 'UF_LINKED_BANNERS', $arParams['SECTION_DISPLAY_PROPERTY'], $arParams['SECTION_DISPLAY_COUNT_PROPERTY']]);

            if ($sectionParent['UF_FILTER_VIEW'] && !$viewTmpFilter) {
                $viewTmpFilter = $sectionParent['UF_FILTER_VIEW'];
            }
            if ($sectionParent['UF_TABLE_PROPS'] && !$viewTableProps) {
                $viewTableProps = $sectionParent['UF_TABLE_PROPS'];
            }
            if ($sectionParent['UF_INCLUDE_SUBSECTION'] && !$includeSubsection) {
                $includeSubsection = $sectionParent['UF_INCLUDE_SUBSECTION'];
            }
            if ($sectionParent['UF_OFFERS_TYPE'] && !$typeSKU) {
                $typeSKU = $sectionParent['UF_OFFERS_TYPE'];
            }
            if ($sectionParent['UF_LINKED_BANNERS'] && !$arSection['UF_LINKED_BANNERS']) {
                $arSection['UF_LINKED_BANNERS'] = $sectionParent['UF_LINKED_BANNERS'];
            }
            if ($sectionParent[$arParams['SECTION_DISPLAY_PROPERTY']] && !$arSection['DISPLAY']) {
                $arDisplayRes = CUserFieldEnum::GetList([], ['ID' => $sectionParent[$arParams['SECTION_DISPLAY_PROPERTY']]]);
                if ($arDisplay = $arDisplayRes->GetNext()) {
                    $arSection['DISPLAY'] = $arDisplay['XML_ID'];
                }
            }
            if ($sectionParent[$arParams['SECTION_DISPLAY_COUNT_PROPERTY']] && !$arSection['UF_LINE_ELEMENT_CNT']) {
                $arDisplayCountRes = CUserFieldEnum::GetList([], ['ID' => $sectionParent[$arParams['SECTION_DISPLAY_COUNT_PROPERTY']]]);
                if ($arDisplay = $arDisplayCountRes->GetNext()) {
                    $arSection['UF_LINE_ELEMENT_CNT'] = $arDisplay['XML_ID'];
                }
            }

            if ($section['DEPTH_LEVEL'] > 2) {
                if (!$viewTmpFilter || !$arSection['DISPLAY'] || !$viewTableProps || !$includeSubsection || !$typeSKU || !$arSection['UF_LINKED_BANNERS'] || !$arSection['UF_LINE_ELEMENT_CNT']) {
                    $sectionRoot = TSolution\Cache::CIBlockSection_GetList(['CACHE' => ['MULTI' => 'N', 'TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID'])]], ['GLOBAL_ACTIVE' => 'Y', '<=LEFT_BORDER' => $section['LEFT_MARGIN'], '>=RIGHT_BORDER' => $section['RIGHT_MARGIN'], 'DEPTH_LEVEL' => 1, 'IBLOCK_ID' => $arParams['IBLOCK_ID']], false, ['ID', 'IBLOCK_ID', 'NAME', 'UF_FILTER_VIEW', 'UF_OFFERS_TYPE', 'UF_TABLE_PROPS', 'UF_LINKED_BANNERS', $arParams['SECTION_DISPLAY_PROPERTY'], $arParams['SECTION_DISPLAY_COUNT_PROPERTY']]);
                    if ($sectionRoot['UF_FILTER_VIEW'] && !$viewTmpFilter) {
                        $viewTmpFilter = $sectionRoot['UF_FILTER_VIEW'];
                    }
                    if ($sectionRoot['UF_TABLE_PROPS'] && !$viewTableProps) {
                        $viewTableProps = $sectionRoot['UF_TABLE_PROPS'];
                    }
                    if ($sectionRoot['UF_INCLUDE_SUBSECTION'] && !$includeSubsection) {
                        $includeSubsection = $sectionRoot['UF_INCLUDE_SUBSECTION'];
                    }
                    if ($sectionRoot['UF_OFFERS_TYPE'] && !$typeSKU) {
                        $typeSKU = $sectionRoot['UF_OFFERS_TYPE'];
                    }
                    if ($sectionRoot['UF_LINKED_BANNERS'] && !$arSection['UF_LINKED_BANNERS']) {
                        $arSection['UF_LINKED_BANNERS'] = $sectionRoot['UF_LINKED_BANNERS'];
                    }
                    if ($sectionRoot[$arParams['SECTION_DISPLAY_PROPERTY']] && !$arSection['DISPLAY']) {
                        $arDisplayRes = CUserFieldEnum::GetList([], ['ID' => $sectionRoot[$arParams['SECTION_DISPLAY_PROPERTY']]]);
                        if ($arDisplay = $arDisplayRes->GetNext()) {
                            $arSection['DISPLAY'] = $arDisplay['XML_ID'];
                        }
                    }
                    if ($sectionParent[$arParams['SECTION_DISPLAY_COUNT_PROPERTY']] && !$arSection['UF_LINE_ELEMENT_CNT']) {
                        $arDisplayCountRes = CUserFieldEnum::GetList([], ['ID' => $sectionRoot[$arParams['SECTION_DISPLAY_COUNT_PROPERTY']]]);
                        if ($arDisplay = $arDisplayCountRes->GetNext()) {
                            $arSection['UF_LINE_ELEMENT_CNT'] = $arDisplay['XML_ID'];
                        }
                    }
                }
            }
        }
    }
    if ($viewTmpFilter) {
        $rsViews = CUserFieldEnum::GetList([], ['ID' => $viewTmpFilter]);
        if ($arView = $rsViews->Fetch()) {
            $viewFilter = $arView['XML_ID'];
            $arTheme['FILTER_VIEW']['VALUE'] = strtoupper($viewFilter);
        }
    }
    if ($viewTableProps) {
        $rsViews = CUserFieldEnum::GetList([], ['ID' => $viewTableProps]);
        if ($arView = $rsViews->Fetch()) {
            $typeTableProps = strtolower($arView['XML_ID']);
        }
    }
    if ($includeSubsection) {
        $rsViews = CUserFieldEnum::GetList([], ['ID' => $includeSubsection]);
        if ($arView = $rsViews->Fetch()) {
            $arParams['INCLUDE_SUBSECTIONS'] = $arView['XML_ID'];
        }
    }
    if ($typeSKU) {
        $rsViews = CUserFieldEnum::GetList([], ['ID' => $typeSKU]);
        if ($arView = $rsViews->Fetch()) {
            $typeSKU = $arView['XML_ID'];
            $arTheme['CATALOG_PAGE_DETAIL_SKU']['VALUE'] = $typeSKU;
        }
    }
    if ($countElementsInRow) {
        $rsViews = CUserFieldEnum::GetList([], ['ID' => $countElementsInRow]);
        if ($arView = $rsViews->Fetch()) {
            $countElementsInRow = $arView['XML_ID'];
        }
    }
    $arElementFilter = ['SECTION_ID' => $arSection['ID'], 'ACTIVE' => 'Y', 'INCLUDE_SUBSECTIONS' => $arParams['INCLUDE_SUBSECTIONS'], 'IBLOCK_ID' => $arParams['IBLOCK_ID']];
    if ($arParams['INCLUDE_SUBSECTIONS'] == 'A') {
        $arElementFilter['INCLUDE_SUBSECTIONS'] = 'Y';
        $arElementFilter['SECTION_GLOBAL_ACTIVE'] = 'Y';
        $arElementFilter['SECTION_ACTIVE '] = 'Y';
    }

    $itemsCnt = TSolution\Cache::CIBlockElement_GetList(['CACHE' => ['TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID'])]], TSolution::makeElementFilterInRegion($arElementFilter), []);
}

$bHideSideSectionBlock = ($arParams['SHOW_SIDE_BLOCK_LAST_LEVEL'] == 'Y' && $iSectionsCount && $arParams['INCLUDE_SUBSECTIONS'] == 'N');
if ($bHideSideSectionBlock) {
    $APPLICATION->SetPageProperty('MENU', 'N');
}

$arParams['FILTER_VIEW'] = 'VERTICAL';
if($arTheme['SHOW_SMARTFILTER']['VALUE'] !== 'N' && $itemsCnt) {
    if (
        $arTheme['SHOW_SMARTFILTER']['DEPENDENT_PARAMS']['FILTER_VIEW']['VALUE'] == 'COMPACT' || !$bShowLeftBlock
    ) {
        $arParams['FILTER_VIEW'] = 'COMPACT';
    }
}
?>
<div class="main-wrapper flexbox flexbox--direction-row">
    <div class="section-content-wrapper <?= $bShowLeftBlock ? 'with-leftblock' : ''; ?> flex-1">
        <?if (!$section):?>
            <?Bitrix\Iblock\Component\Tools::process404(
                '', $arParams['SET_STATUS_404'] === 'Y', $arParams['SET_STATUS_404'] === 'Y', $arParams['SHOW_404'] === 'Y', $arParams['FILE_404']
            ); ?>
        <?endif; ?>

        <?if ($section):?>
            <?php
            // seo
            $catalogInfoIblockId = $arParams['LANDING_IBLOCK_ID'];
            if ($catalogInfoIblockId && !$bSimpleSectionTemplate) {
                $arSeoItems = TSolution\Cache::CIBLockElement_GetList(['SORT' => 'ASC', 'CACHE' => ['MULTI' => 'Y', 'TAG' => TSolution\Cache::GetIBlockCacheTag($catalogInfoIblockId)]], ['IBLOCK_ID' => $catalogInfoIblockId, 'ACTIVE' => 'Y'], false, false, ['ID', 'IBLOCK_ID', 'PROPERTY_FILTER_URL', 'PROPERTY_LINK_REGION']);
                $arSeoItem = $arTmpRegionsLanding = [];
                if ($arSeoItems) {
                    $iLandingItemID = 0;
                    $current_url = $APPLICATION->GetCurDir();
                    $url = urldecode(str_replace(' ', '+', $current_url));
                    foreach ($arSeoItems as $arItem) {
                        if (!is_array($arItem['PROPERTY_LINK_REGION_VALUE'])) {
                            $arItem['PROPERTY_LINK_REGION_VALUE'] = (array) $arItem['PROPERTY_LINK_REGION_VALUE'];
                        }

                        if (!$arSeoItem) {
                            $urldecoded = urldecode($arItem['PROPERTY_FILTER_URL_VALUE']);
                            $urldecodedCP = iconv('utf-8', 'windows-1251//IGNORE', $urldecoded);
                            if ($urldecoded == $url || $urldecoded == $current_url || $urldecodedCP == $current_url) {
                                if ($arItem['PROPERTY_LINK_REGION_VALUE']) {
                                    if ($arRegion && in_array($arRegion['ID'], $arItem['PROPERTY_LINK_REGION_VALUE'])) {
                                        $arSeoItem = $arItem;
                                    }
                                } else {
                                    $arSeoItem = $arItem;
                                }

                                if ($arSeoItem) {
                                    $iLandingItemID = $arSeoItem['ID'];
                                    $arSeoItem = TSolution\Cache::CIBLockElement_GetList(['SORT' => 'ASC', 'CACHE' => ['MULTI' => 'N', 'TAG' => TSolution\Cache::GetIBlockCacheTag($catalogInfoIblockId)]], ['IBLOCK_ID' => $catalogInfoIblockId, 'ID' => $iLandingItemID], false, false, ['ID', 'IBLOCK_ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_PICTURE', 'PREVIEW_PICTURE', 'PROPERTY_FILTER_URL', 'PROPERTY_LINK_REGION', 'PROPERTY_FORM_QUESTION', 'PROPERTY_SECTION_SERVICES', 'PROPERTY_TIZERS', 'PROPERTY_SECTION', 'DETAIL_TEXT', 'PROPERTY_I_ELEMENT_PAGE_TITLE', 'PROPERTY_I_ELEMENT_PREVIEW_PICTURE_FILE_ALT', 'PROPERTY_I_ELEMENT_PREVIEW_PICTURE_FILE_TITLE', 'PROPERTY_I_SKU_PAGE_TITLE', 'PROPERTY_I_SKU_PREVIEW_PICTURE_FILE_ALT', 'PROPERTY_I_SKU_PREVIEW_PICTURE_FILE_TITLE', 'ElementValues']);

                                    $arIBInheritTemplates = [
                                        'ELEMENT_PAGE_TITLE' => $arSeoItem['PROPERTY_I_ELEMENT_PAGE_TITLE_VALUE'],
                                        'ELEMENT_PREVIEW_PICTURE_FILE_ALT' => $arSeoItem['PROPERTY_I_ELEMENT_PREVIEW_PICTURE_FILE_ALT_VALUE'],
                                        'ELEMENT_PREVIEW_PICTURE_FILE_TITLE' => $arSeoItem['PROPERTY_I_ELEMENT_PREVIEW_PICTURE_FILE_TITLE_VALUE'],
                                        'SKU_PAGE_TITLE' => $arSeoItem['PROPERTY_I_SKU_PAGE_TITLE_VALUE'],
                                        'SKU_PREVIEW_PICTURE_FILE_ALT' => $arSeoItem['PROPERTY_I_SKU_PREVIEW_PICTURE_FILE_ALT_VALUE'],
                                        'SKU_PREVIEW_PICTURE_FILE_TITLE' => $arSeoItem['PROPERTY_I_SKU_PREVIEW_PICTURE_FILE_TITLE_VALUE'],
                                    ];
                                    if(TSolution::isSmartSeoInstalled()) {
                                        Aspro\Smartseo\General\Smartseo::disallowNoindexRule(true);
                                    }
                                }
                            }
                        }

                        if ($arItem['PROPERTY_LINK_REGION_VALUE']) {
                            if (!$arRegion || !in_array($arRegion['ID'], $arItem['PROPERTY_LINK_REGION_VALUE'])) {
                                $arTmpRegionsLanding[] = $arItem['ID'];
                            }
                        }
                    }
                }

                if ($arSeoItems && $bHideSideSectionBlock) {
                    $arSeoItems = [];
                }
            }

            if ($arRegion) {
                $arParams['USE_REGION'] = 'Y';

                $GLOBALS[$arParams['FILTER_NAME']]['IBLOCK_ID'] = $arParams['IBLOCK_ID'];
                TSolution::makeElementFilterInRegion($GLOBALS[$arParams['FILTER_NAME']]);
            }

            /* hide compare link from module options */
            if (TSolution::GetFrontParametrValue('CATALOG_COMPARE') == 'N') {
                $arParams['USE_COMPARE'] = 'N';
            }

            $arParams['PRICE_CODE'] = explode(',', TSolution::GetFrontParametrValue('PRICES_TYPE'));
            $arParams['STORES'] = explode(',', TSolution::GetFrontParametrValue('STORES'));
            if ($arRegion) {
                if ($arRegion['LIST_PRICES']) {
                    $arParams['PRICE_CODE'] = array_keys($arRegion['LIST_PRICES']);
                }
                if ($arRegion['LIST_STORES']) {
                    $arParams['STORES'] = $arRegion['LIST_STORES'];
                }
            }

            $bContolAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && isset($_GET['control_ajax']) && $_GET['control_ajax'] == 'Y');
            $sViewElementTemplate = ($arParams['SECTION_ELEMENTS_TYPE_VIEW'] == 'FROM_MODULE' ? $arTheme['ELEMENTS_CATALOG_PAGE']['VALUE'] : $arParams['SECTION_ELEMENTS_TYPE_VIEW']);
            ?>
            <?// section elements?>
            <div class="js_wrapper_items<?= $arTheme['LAZYLOAD_BLOCK_CATALOG']['VALUE'] == 'Y' ? ' with-load-block' : ''; ?>" >
                <div class="js-load-wrapper <?=$APPLICATION->ShowViewContent('section_additional_class'); ?>">

                    <?if($bContolAjax):?>
                        <?$APPLICATION->RestartBuffer(); ?>
                    <?endif; ?>
                    <?@include_once 'page_blocks/'.$sViewElementTemplate.'.php'; ?>

                    <?if($bContolAjax):?>
                        <?exit; ?>
                    <?endif; ?>
                </div>
            </div>
            <?TSolution::checkBreadcrumbsChain($arParams, $arSection);?>
            <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.history.js'); ?>
        <?else:?>
            <div class="alert alert-danger">
                <?= $arParams['MESSAGE_404'] ?: Loc::getMessage('NOT_FOUNDED_SECTION'); ?>
            </div>
        <?endif; ?>
    </div>
    <?if($bShowLeftBlock):?>
        <?TSolution::ShowPageType('left_block'); ?>
    <?endif; ?>
</div>

<?php
TSolution::setCatalogSectionDescription(
    [
        'FILTER_NAME' => $arParams['FILTER_NAME'],
        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
        'CACHE_TIME' => $arParams['CACHE_TIME'],
        'SECTION_ID' => $arSection['ID'],
        'SHOW_SECTION_DESC' => $arParams['SHOW_SECTION_DESC'],
        'SEO_ITEM' => $arSeoItem,
    ]
);
?>
<?TSolution\Extensions::init(['filter_panel', 'dropdown_select', 'smart_filter']); ?>
