<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
$arExtensions = ['swiper', 'fancybox', 'gallery'];

if($arParams["USE_SHARE"] || $arParams["USE_RSS"]) {
    $arExtensions[] = 'item_action';
    $arExtensions[] = 'share';
}

\TSolution\Extensions::init($arExtensions);
?>
