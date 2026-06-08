<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

use Bitrix\Main\Localization\Loc;

$this->setFrameMode(true);

Loc::loadMessages(__FILE__);

$arItemFilter = TSolution::GetIBlockAllElementsFilter($arParams);
$arElements = TSolution\Cache::CIblockElement_GetList(['CACHE' => ['TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 'MULTI' => 'Y']], $arItemFilter, false, false, ['ID', 'NAME', 'PREVIEW_TEXT']);
?>

<?if (empty($arElements)):?>
    <div class="alert alert-warning"><?=Loc::getMessage('FAQ__SECTION_EMPTY');?></div>
<?else:?>
    <?php
    TSolution::CheckComponentTemplatePageBlocksParams($arParams, __DIR__);
    global $arTheme;

    // section elements
    $sViewElementsTemplate = ($arParams['SECTION_ELEMENTS_TYPE_VIEW'] == 'FROM_MODULE' ? $arTheme['FAQS_PAGE']['VALUE'] : $arParams['SECTION_ELEMENTS_TYPE_VIEW']);
    include_once 'page_blocks/'.$sViewElementsTemplate.'.php';

    $arFaqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [],
    ];
    foreach ($arElements as $element) {
        $arFaqSchema['mainEntity'][] = [
            '@type' => 'Question',
            'name' => $element['NAME'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $element['PREVIEW_TEXT'],
            ],
        ];
    }
    ?>

    <script type="application/ld+json"><?=str_replace("'", '"', CUtil::PhpToJSObject($arFaqSchema, false, true)); ?></script>

    <?php
    if ($arParams['SHOW_ASK_QUESTION_BLOCK'] == 'Y') {
        include 'include_bottom_block.php';
    }
    ?>
<?endif; ?>
