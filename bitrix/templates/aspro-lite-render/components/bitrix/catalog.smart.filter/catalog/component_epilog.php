<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $templateData */
/** @var @global CMain $APPLICATION */
global $APPLICATION;

if(\Bitrix\Main\Loader::includeModule('webdebug.seo')){
  \WD\Seo\SmartFilter\AutoSeo::set($arResult, $arParams);
}

CJSCore::Init(array('fx', 'popup'));?>
<?TSolution\Extensions::init('alphanumeric');?>

