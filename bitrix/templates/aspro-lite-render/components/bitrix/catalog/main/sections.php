<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}
$this->setFrameMode(true);

global $arTheme, $APPLICATION, $arSectionFilter, $arRegion;
$APPLICATION->AddViewContent('right_block_class', 'catalog_page ');

$bShowLeftBlock = ($arTheme['LEFT_BLOCK_CATALOG_ROOT']['VALUE'] === 'Y' && !defined('ERROR_404'));
$APPLICATION->SetPageProperty('MENU', 'N');
?>
<div class="main-wrapper flexbox flexbox--direction-row">
    <div class="section-content-wrapper<?=$bShowLeftBlock ? ' with-leftblock' : '';?> flex-1">
        <?// intro text?>
        <?ob_start();?>
            <?$APPLICATION->IncludeComponent(
                'bitrix:main.include',
                '',
                [
                    'AREA_FILE_SHOW' => 'page',
                    'AREA_FILE_SUFFIX' => 'inc',
                    'EDIT_TEMPLATE' => '',
                ]
            );?>
        <?$html = trim(ob_get_clean());?>
        <?if ($html):?>
            <div class="text_before_items">
                <?=$html;?>
            </div>
        <?endif;?>
        <?unset($html);?>

        <?php
        // get section items count and subsections
        $arParams['CHECK_DATES'] = 'Y';
        $arItemFilter = TSolution::GetCurrentSectionElementFilter($arResult['VARIABLES'], $arParams, false);
        $arSubSectionFilter = TSolution::GetCurrentSectionSubSectionFilter($arResult['VARIABLES'], $arParams, false);
        $itemsCnt = TSolution\Cache::CIBlockElement_GetList(['CACHE' => ['TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID'])]], $arItemFilter, []);
        $arSubSections = TSolution\Cache::CIBlockSection_GetList(['CACHE' => ['TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 'MULTI' => 'Y']], $arSubSectionFilter, false, ['ID']);
        ?>
        <?if (!$itemsCnt && !$arSubSections):?>
            <div class="alert alert-warning"><?=GetMessage('SECTION_EMPTY');?></div>
        <?else:?>
            <?php
            $arSectionFilter = ['IBLOCK_ID' => $arParams['IBLOCK_ID']];
            TSolution::makeSectionFilterInRegion($arSectionFilter);

            TSolution::CheckComponentTemplatePageBlocksParams($arParams, __DIR__);

            $sViewElementTemplate = ($arParams['SECTIONS_TYPE_VIEW'] === 'FROM_MODULE'
                ? $arTheme['SECTIONS_TYPE_VIEW_CATALOG']['VALUE']
                : $arParams['SECTIONS_TYPE_VIEW']);
            include_once 'page_blocks/'.$sViewElementTemplate.'.php';

            if (!$arSubSections) {
                // section elements
                if (strlen($arParams['FILTER_NAME'])) {
                    $GLOBALS[$arParams['FILTER_NAME']] = array_merge((array) $GLOBALS[$arParams['FILTER_NAME']], $arItemFilter);
                } else {
                    $arParams['FILTER_NAME'] = 'arrFilter';
                    $GLOBALS[$arParams['FILTER_NAME']] = $arItemFilter;
                }

                $sViewElementTemplate = $arParams['SECTION_ELEMENTS_TYPE_VIEW'] === 'FROM_MODULE'
                    ? $arTheme['ELEMENTS_CATALOG_PAGE']['VALUE']
                    : $arParams['SECTION_ELEMENTS_TYPE_VIEW'];
                // include_once 'page_blocks/'.$sViewElementTemplate.'.php';
            }
            ?>
        <?endif;?>

        <?// outro text?>
        <?ob_start();?>
            <?$APPLICATION->IncludeComponent(
                'bitrix:main.include',
                '',
                [
                    'AREA_FILE_SHOW' => 'page',
                    'AREA_FILE_SUFFIX' => 'bottom',
                    'EDIT_TEMPLATE' => '',
                ]
            );?>
        <?$html = trim(ob_get_clean());?>
        <?if ($html):?>
            <div class="text_after_items">
                <?$APPLICATION->IncludeComponent(
                    'bitrix:main.include',
                    '',
                    [
                        'AREA_FILE_SHOW' => 'page',
                        'AREA_FILE_SUFFIX' => 'bottom',
                        'EDIT_TEMPLATE' => '',
                    ]
                );?>
            </div>
        <?endif;?>
        <?unset($html);?>
    </div>
    <?php
    if ($bShowLeftBlock) {
        TSolution::ShowPageType('left_block');
    }
    ?>
</div>
