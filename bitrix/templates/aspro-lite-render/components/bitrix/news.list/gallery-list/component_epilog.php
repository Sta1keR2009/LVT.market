<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arScripts = ['swiper', 'fancybox', 'cross', 'gallery'];
\Aspro\Lite\Functions\Extensions::init($arScripts);

\Aspro\Lite\Functions\Extensions::init($arExtensions);

if ($arParams['USE_FILTER'] != 'N') {
    \Aspro\Lite\Functions\Extensions::init('section_filter');
}?>
