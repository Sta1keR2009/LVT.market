<?php
defined('B_PROLOG_INCLUDED') || die();

/** @var array $arItem */
$showGetchipsSectionTeaser = false;
if (!$showGetchipsSectionTeaser) {
    return;
}

$getchipsTeaserArticle = isset($arItem['GETCHIPS_ARTICLE']) ? (string) $arItem['GETCHIPS_ARTICLE'] : '';
if ($getchipsTeaserArticle === '' || mb_strlen($getchipsTeaserArticle) < 3) {
    return;
}
$getchipsTeaserImg = isset($arItem['GETCHIPS_PREVIEW_SRC']) ? (string) $arItem['GETCHIPS_PREVIEW_SRC'] : '';
$getchipsTeaserEid = (int) ($arItem['ID'] ?? 0);
$getchipsTeaserUrl = (string) ($arItem['DETAIL_PAGE_URL'] ?? '');
?>
<div class="line-block__item font_13 font_12--to-600 getchips-section-teaser-wrap getchips-list-teaser js-getchips-list-teaser"
     style="width:100%;flex-basis:100%;margin-top:0.35rem;"
     data-getchips-article="<?= htmlspecialcharsbx($getchipsTeaserArticle) ?>"
     data-getchips-element-id="<?= $getchipsTeaserEid ?>"
     data-getchips-detail-url="<?= htmlspecialcharsbx($getchipsTeaserUrl) ?>"
     data-getchips-product-img="<?= htmlspecialcharsbx($getchipsTeaserImg) ?>">
    <button type="button" class="btn btn-default btn-sm js-getchips-load-offers-section">Наличие по складам</button>
</div>
