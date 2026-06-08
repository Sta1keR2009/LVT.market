<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if($arResult['ITEMS']):?>
	<?$APPLICATION->addViewContent('section_additional_class', 'hidden_top_sort ');?>
<?endif;?>
<?TSolution\Extensions::init('alphanumeric');?>