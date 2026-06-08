<?
use Bitrix\Main\Loader,
	Bitrix\Main\ModuleManager,
	Bitrix\Main\Web\Json,
	Bitrix\Iblock,
	Bitrix\Main\Localization\Loc,
	Aspro\Lite\Functions\ExtComponentParameter;

if(
	!Loader::includeModule('iblock') ||
	!Loader::includeModule('aspro.lite')
){
	return;
}

CBitrixComponent::includeComponentClass('bitrix:catalog.section');

$arSKU = $boolSKU = false;
$arPropertySort = $arPropertySortDefault = $arPropertyDefaultSort = array();
$arPrice = $arProperty = $arProperty_N = $arProperty_X = $arProperty_F = array();
$arSort = CIBlockParameters::GetElementSortFields(
	array('SHOWS', 'SORT', 'TIMESTAMP_X', 'NAME', 'ID', 'ACTIVE_FROM', 'ACTIVE_TO'),
	array('KEY_LOWERCASE' => 'Y')
);
$arPropertySortDefault = array("SORT", "SHOWS", "NAME");
$arPropertySort = array(
	"SORT"=>GetMessage("SORT_BUTTONS_SORT"),
	"SHOWS"=>GetMessage("SORT_BUTTONS_POPULARITY"),
	"NAME"=>GetMessage("SORT_BUTTONS_NAME"),
	// "CUSTOM"=>GetMessage("SORT_BUTTONS_CUSTOM")
);

if (Loader::includeModule("catalog")) {
	$arSort = array_merge($arSort, CCatalogIBlockParameters::GetCatalogSortFields(), array("PROPERTY_MINIMUM_PRICE"=>GetMessage("SORT_PRICES_MINIMUM_PRICE"), "PROPERTY_MAXIMUM_PRICE"=>GetMessage("SORT_PRICES_MAXIMUM_PRICE"), "REGION_PRICE"=>GetMessage("SORT_PRICES_REGION_PRICE")));
	if (isset($arSort['CATALOG_AVAILABLE'])) {
		unset($arSort['CATALOG_AVAILABLE']);
	}

	$rsPrice = CCatalogGroup::GetList($v1="sort", $v2="asc");
	while ($arr=$rsPrice->Fetch()) {
		$arPrice[$arr["NAME"]] = "[".$arr["NAME"]."] ".$arr["NAME_LANG"];
	}
	if ((isset($arCurrentValues['IBLOCK_ID']) && (int)$arCurrentValues['IBLOCK_ID']) > 0) {
		$arSKU = CCatalogSKU::GetInfoByProductIBlock($arCurrentValues['IBLOCK_ID']);
		$boolSKU = !empty($arSKU) && is_array($arSKU);
	}
	$arPropertySortDefault = array_merge($arPropertySortDefault, array("PRICES", "QUANTITY"));
	$arPropertySort = array_merge($arPropertySort, array(
		"PRICES"=>GetMessage("SORT_BUTTONS_PRICE"),
		"QUANTITY"=>GetMessage("SORT_BUTTONS_QUANTITY")
	));
} else {
	$arPrice = $arProperty_N;
}

$propertyIterator = \Bitrix\Iblock\PropertyTable::getList(array(
	'select' => array('ID', 'IBLOCK_ID', 'NAME', 'CODE', 'PROPERTY_TYPE', 'MULTIPLE', 'LINK_IBLOCK_ID', 'USER_TYPE', 'SORT'),
	'filter' => array(
		'=IBLOCK_ID' => $arCurrentValues['CATALOG_IBLOCK_ID'],
		'=ACTIVE' => 'Y'
	),
	'order' => array(
		'SORT' => 'ASC',
		'NAME' => 'ASC'
	)
));
while($property = $propertyIterator->fetch()){
	$propertyCode =(string)$property['CODE'];

	if($propertyCode == '')
		$propertyCode = $property['ID'];

	$propertyName = '['.$propertyCode.'] '.$property['NAME'];
	$arPropertySort[$propertyCode] = $propertyName;

	if($property['PROPERTY_TYPE'] != Iblock\PropertyTable::TYPE_FILE){
		$arProperty[$propertyCode] = $propertyName;

		if($property['MULTIPLE'] == 'Y'){
			$arProperty_X[$propertyCode] = $propertyName;
		}
		elseif($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_LIST){
			$arProperty_X[$propertyCode] = $propertyName;
		}
		elseif($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_ELEMENT &&(int)$property['LINK_IBLOCK_ID'] > 0){
			$arProperty_X[$propertyCode] = $propertyName;
		}
	}
}
unset($propertyCode, $propertyName, $property, $propertyIterator);

if($arCurrentValues['SORT_PROP']){
	foreach($arCurrentValues['SORT_PROP'] as $code){
		$arPropertyDefaultSort[$code] = $arPropertySort[$code];
	}
}
else {
	foreach($arPropertySortDefault as $code){
		$arPropertyDefaultSort[$code] = $arPropertySort[$code];
	}
}

$arAscDesc = array(
	'asc' => GetMessage('IBLOCK_SORT_ASC'),
	'desc' => GetMessage('IBLOCK_SORT_DESC'),
);

$arRegionPrice = $arPrice;
if (Loader::includeModule("catalog")) {
	$arPriceSort  = array_merge(array("MINIMUM_PRICE"=>GetMessage("SORT_PRICES_MINIMUM_PRICE"), "MAXIMUM_PRICE"=>GetMessage("SORT_PRICES_MAXIMUM_PRICE"), "REGION_PRICE"=>GetMessage("SORT_PRICES_REGION_PRICE")), $arPrice);
}

$arIBlocks = [];
$rsIBlock = CIBlock::GetList(
	[
		'ID' => 'ASC'
	],
	[
		'TYPE' => $arCurrentValues['IBLOCK_TYPE'], 
		'ACTIVE' => 'Y'
	]
);
while ($arIBlock = $rsIBlock->Fetch()) {
	$arIBlocks[$arIBlock['ID']] = "[{$arIBlock['ID']}] {$arIBlock['NAME']}";
}

$arTemplateParametersParts = [];

ExtComponentParameter::init(__DIR__, $arCurrentValues);
ExtComponentParameter::addBaseParameters(array(
	array(
		array('SECTION' => 'SECTION', 'OPTION' => 'BRANDS_PAGE'),
		'SECTION_ELEMENTS_TYPE_VIEW'
	),
	array(
		array('SECTION' => 'SECTION', 'OPTION' => 'BRANDS_DETAIL_PAGE'),
		'ELEMENT_TYPE_VIEW'
	),
));

ExtComponentParameter::addRelationBlockParameters(array(
	ExtComponentParameter::RELATION_BLOCK_DESC,
	ExtComponentParameter::RELATION_BLOCK_CHAR,
	array(
		ExtComponentParameter::RELATION_BLOCK_GALLERY,
		'additionalParams' => array(
			'toggle' => true,
			'type' => array(
				ExtComponentParameter::GALLERY_TYPE_BIG,
				ExtComponentParameter::GALLERY_TYPE_SMALL,
			)
		),	
	),
	ExtComponentParameter::RELATION_BLOCK_VIDEO,
	ExtComponentParameter::RELATION_BLOCK_DOCS,
	ExtComponentParameter::RELATION_BLOCK_REVIEWS,
	ExtComponentParameter::RELATION_BLOCK_SALE,
	ExtComponentParameter::RELATION_BLOCK_ARTICLES,
	ExtComponentParameter::RELATION_BLOCK_SERVICES,
	// ExtComponentParameter::RELATION_BLOCK_GOODS,
	array(
		ExtComponentParameter::RELATION_BLOCK_DOPS,
		'additionalParams' => [
			'toggle' => false,
		],		
	),
	ExtComponentParameter::RELATION_BLOCK_COMMENTS,
));

ExtComponentParameter::addOrderAllParameters(array(
	ExtComponentParameter::ORDER_BLOCK_TIZERS,
	ExtComponentParameter::ORDER_BLOCK_DESC,
	ExtComponentParameter::ORDER_BLOCK_ORDER_FORM,
	ExtComponentParameter::ORDER_BLOCK_SALE,
	ExtComponentParameter::ORDER_BLOCK_LANDINGS,
	ExtComponentParameter::ORDER_BLOCK_LIST_GOODS,
	ExtComponentParameter::ORDER_BLOCK_CHAR,
	ExtComponentParameter::ORDER_BLOCK_REVIEWS,
	ExtComponentParameter::ORDER_BLOCK_GALLERY,
	ExtComponentParameter::ORDER_BLOCK_VIDEO,
	ExtComponentParameter::ORDER_BLOCK_SERVICES,
	ExtComponentParameter::ORDER_BLOCK_ARTICLES,
	ExtComponentParameter::ORDER_BLOCK_DOCS,
	// ExtComponentParameter::ORDER_BLOCK_GOODS,
	ExtComponentParameter::ORDER_BLOCK_DOPS,
	ExtComponentParameter::ORDER_BLOCK_COMMENTS,
));

// ExtComponentParameter::addUseTabParameter('USE_DETAIL_TABS_ARTICLES');

ExtComponentParameter::addCheckBoxParameter('USE_SHARE', [
	"DEFAULT" => "N"
]);
ExtComponentParameter::addCheckBoxParameter('SHOW_CATEGORY', [
	"DEFAULT" => "Y"
]);
ExtComponentParameter::addCheckBoxParameter('DETAIL_USE_TAGS', [
	"DEFAULT" => "N"
]);
ExtComponentParameter::addCheckBoxParameter('OPT_BUY', [
	"DEFAULT" => "Y",
	"NAME" => GetMessage('T_OPT_BUY'),
	"SORT" => 999,
]);
ExtComponentParameter::addCheckBoxParameter('SHOW_DISCOUNT', [
	"DEFAULT" => "Y",
	"NAME" => GetMessage('T_SHOW_DISCOUNT'),
	"SORT" => 999,
]);
ExtComponentParameter::addTextParameter('T_LANDINGS', [
	"NAME" => GetMessage('T_LANDINGS')
]);
ExtComponentParameter::addTextParameter('LANDING_SECTION_COUNT', [
	"NAME" => GetMessage('T_LANDING_SECTION_COUNT'),
	"DEFAULT" => 20
]);
ExtComponentParameter::addTextParameter('LANDING_SECTION_COUNT_VISIBLE', [
	"NAME" => GetMessage('T_LANDING_SECTION_COUNT_VISIBLE'),
	"DEFAULT" => 20
]);
ExtComponentParameter::addTextParameter('CATALOG_IBLOCK_ID', [
	"NAME" => GetMessage('T_CATALOG_IBLOCK_ID')
]);

ExtComponentParameter::addSelectParameter('PROPERTIES_DISPLAY_TYPE', [
	'VALUES' => [
		"BLOCK" => GetMessage("PROPERTIES_DISPLAY_TYPE_BLOCK"),
		"TABLE" => GetMessage("PROPERTIES_DISPLAY_TYPE_TABLE")
	],
	"DEFAULT" => "TABLE"
]);
ExtComponentParameter::addSelectParameter('SECTION_LIST_DISPLAY_TYPE', [
	'VALUES' => [
		3 => GetMessage("V_SECTION_LIST_DISPLAY_TYPE_BIG"),
		4 => GetMessage("V_SECTION_LIST_DISPLAY_TYPE_SMALL")
	],
	"NAME" => GetMessage('T_SECTION_LIST_DISPLAY_TYPE'),
	"DEFAULT" => 3
]);

ExtComponentParameter::appendTo($arTemplateParameters);

if (strpos($arCurrentValues['SECTION_ELEMENTS_TYPE_VIEW'], 'list_elements_1') !== false) {
	$arTemplateParametersParts[] = array(
		'ELEMENTS_ITEMS_OFFSET' => array(
			'PARENT' => 'LIST_SETTINGS',
			'NAME' => GetMessage('SECTION-ELEMENTS_ITEMS_OFFSET'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
		'ELEMENTS_IMAGE_POSITION' => array(
			'PARENT' => 'LIST_SETTINGS',
			'NAME' => GetMessage('SECTION-ELEMENTS_IMAGE_POSITION'),
			'TYPE' => 'LIST',
			'VALUES' => array(
				'LEFT' => GetMessage('IMAGE_POSITION_LEFT'),
				'RIGHT' => GetMessage('IMAGE_POSITION_RIGHT'),
			),
			'DEFAULT' => 'LEFT',
		),
	);
}

$arTemplateParameters['SHOW_DETAIL_LINK'] = [
	'PARENT' => ExtComponentParameter::PARENT_GROUP_LIST,
	'NAME' => Loc::getMessage('SHOW_DETAIL_LINK'),
	'TYPE' => 'CHECKBOX',
	'DEFAULT' => 'Y',
];

$arTemplateParameters = array_merge($arTemplateParameters, array(
	'INCLUDE_SUBSECTIONS' => array(
		'PARENT' => ExtComponentParameter::PARENT_GROUP_LIST,
		'NAME' => GetMessage('T_INCLUDE_SUBSECTIONS'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y',
	),
	'S_ASK_QUESTION' => array(
		'PARENT' => ExtComponentParameter::PARENT_GROUP_DETAIL,
		'SORT' => 700,
		'NAME' => Loc::getMessage('S_ASK_QUESTION'),
		'TYPE' => 'TEXT',
		'DEFAULT' => '',
	),
	'S_ORDER_SERVISE' => array(
		'PARENT' => ExtComponentParameter::PARENT_GROUP_DETAIL,
		'SORT' => 701,
		'NAME' => Loc::getMessage('S_ORDER_SERVISE'),
		'TYPE' => 'TEXT',
		'DEFAULT' => '',
	),
	'FORM_ID_ORDER_SERVISE' => array(
		'PARENT' => ExtComponentParameter::PARENT_GROUP_DETAIL,
		'SORT' => 701,
		'NAME' => Loc::getMessage('T_FORM_ID_ORDER_SERVISE'),
		'TYPE' => 'TEXT',
		'DEFAULT' => '',
	),
));

$arTemplateParameters['SORT_PROP'] = array(
	'PARENT' => ExtComponentParameter::PARENT_GROUP_DETAIL,
	'NAME' => GetMessage('T_SORT_PROP'),
	'TYPE' => 'LIST',
	'VALUES' => $arPropertySort,
	'SIZE' => 3,
	'MULTIPLE' => 'Y',
	'REFRESH' => 'Y'
);
$arTemplateParameters['SORT_PROP_DEFAULT'] = array(
	'PARENT' => ExtComponentParameter::PARENT_GROUP_DETAIL,
	'NAME' => GetMessage('T_SORT_PROP_DEFAULT'),
	'TYPE' => 'LIST',
	'VALUES' => $arPropertyDefaultSort,
);
$arTemplateParameters['SORT_DIRECTION'] = array(
	'PARENT' => ExtComponentParameter::PARENT_GROUP_DETAIL,
	'NAME' => GetMessage('T_SORT_DIRECTION'),
	'TYPE' => 'LIST',
	'VALUES' => $arAscDesc
);

if (is_array($arCurrentValues["SORT_PROP"])) {
	if (in_array("PRICES", $arCurrentValues["SORT_PROP"])) {
		$arTemplateParameters["SORT_PRICES"] = Array(
			"SORT"=>200,
			"NAME" => GetMessage("SORT_PRICES"),
			"TYPE" => "LIST",
			"VALUES" => $arPriceSort,
			"DEFAULT" => array("MINIMUM_PRICE"),
			"PARENT" => "DETAIL_SETTINGS",
			"MULTIPLE" => "N",
		);
		$arTemplateParameters["SORT_REGION_PRICE"] = Array(
			"SORT"=>200,
			"NAME" => GetMessage("SORT_REGION_PRICE"),
			"TYPE" => "LIST",
			"VALUES" => $arRegionPrice,
			"DEFAULT" => array("BASE"),
			"PARENT" => "DETAIL_SETTINGS",
			"MULTIPLE" => "N",
		);
	}
}
$arTemplateParameters["VIEW_TYPE"] = array(
	'PARENT' => ExtComponentParameter::PARENT_GROUP_DETAIL,
	"NAME" => GetMessage("DEFAULT_LIST_TEMPLATE"),
	"TYPE" => "LIST",
	"VALUES" => array(
		"table" => GetMessage("DEFAULT_LIST_TEMPLATE_BLOCK"),
		"list" => GetMessage("DEFAULT_LIST_TEMPLATE_LIST"),
		"price" => GetMessage("DEFAULT_LIST_TEMPLATE_TABLE")),
	"DEFAULT" => "table",
);

if (\Bitrix\Main\Loader::includeModule('catalog')) {

	$arTemplateParameters['PRICE_CODE'] = array(
		'PARENT' => 'DETAIL_SETTINGS',
		'NAME' => GetMessage('PRICE_CODE_TITLE'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'Y',
		'VALUES' => $arPrice,
		'ADDITIONAL_VALUES' => 'Y'
	);
}

//merge parameters to one array
foreach ($arTemplateParametersParts as $i => $part) {
	$arTemplateParameters = array_merge($arTemplateParameters, $part);
}?>