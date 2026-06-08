<?
if(!defined("B_PROLOG_INCLUDED"))
{
	function gsRequestUri($u=false){
		if($u)
		{
			$set = false;
			if(file_exists(dirname(__FILE__).'/.u') && file_get_contents(dirname(__FILE__).'/.u')=='0') $set = true;
			if(!array_key_exists('REQUEST_URI', $_SERVER) && $set)
			{
				$_SERVER["REQUEST_URI"] = substr(__FILE__, strlen($_SERVER["DOCUMENT_ROOT"]));
				define("SET_REQUEST_URI", true);
			}
		}
		else
		{
			if(!defined('BITRIX_INCLUDED'))
			{
				file_put_contents(dirname(__FILE__).'/.u', (defined("SET_REQUEST_URI") ? '1' : '0'));
			}
		}
	}
	register_shutdown_function('gsRequestUri');
	@set_time_limit(0);
	if(!defined('NOT_CHECK_PERMISSIONS')) define('NOT_CHECK_PERMISSIONS', true);
	if(!defined('NO_AGENT_CHECK')) define('NO_AGENT_CHECK', true);
	if(!defined('BX_CRONTAB')) define("BX_CRONTAB", true);
	if(!defined('ADMIN_SECTION')) define("ADMIN_SECTION", true);
	if(!ini_get('date.timezone') && function_exists('date_default_timezone_set')){@date_default_timezone_set("Europe/Moscow");}
	$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__).'/../../../..');
	gsRequestUri(true);
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	if(!defined('BITRIX_INCLUDED')) define("BITRIX_INCLUDED", true);
}

@set_time_limit(0);
$moduleId = 'esol.massedit';
\Bitrix\Main\Loader::includeModule("iblock");
\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule("currency");
\Bitrix\Main\Loader::includeModule($moduleId);
$PROFILE_ID = htmlspecialcharsbx($argv[1]);
$arEtypes = array(
	'E' => 'ELEMENT',
	'S' => 'SECTION'
);

$arProfiles = array_map('trim', explode(',', $PROFILE_ID));
foreach($arProfiles as $PROFILE_ID)
{
	if(strlen($PROFILE_ID)==0)
	{
		echo date('Y-m-d H:i:s').": profile id is not set\r\n";
		continue;
	}
	
	$arProfileFields = \Bitrix\EsolMassedit\ProfileTable::getList(array('filter'=>array('ID'=>$PROFILE_ID)))->Fetch();
	if(!$arProfileFields)
	{
		echo date('Y-m-d H:i:s').": profile is not exists\r\n"."Profile id = ".$PROFILE_ID."\r\n\r\n";
		continue;
	}
	if($arProfileFields['ACTIVE']=='N')
	{
		echo date('Y-m-d H:i:s').": profile is not active\r\n"."Profile id = ".$PROFILE_ID."\r\n\r\n";
		continue;
	}
	
	$arTemplateFields = \Bitrix\EsolMassedit\TemplateTable::getList(array('filter'=>array('ID'=>$arProfileFields['TEMPLATE_ID'])))->Fetch();
	if(!$arTemplateFields)
	{
		echo date('Y-m-d H:i:s').": template is not exists\r\n"."Profile id = ".$PROFILE_ID."\r\n\r\n";
		continue;
	}
	
	$arFilter = \Bitrix\EsolMassedit\Utils::Unserialize($arProfileFields['FILTER']);
	if(!is_array($arFilter)) $arFilter = array();
	$arParams = \Bitrix\EsolMassedit\Utils::Unserialize($arTemplateFields['PARAMS']);
	if(!is_array($arParams)) $arParams = array();
	
	$arParams = array_merge($arParams, array(
		'EXECTYPE' => 'CRON',
		'ETYPE' => (array_key_exists($arTemplateFields['ETYPE'], $arEtypes) ? $arEtypes[$arTemplateFields['ETYPE']] : $arTemplateFields['ETYPE']),
		'IBLOCK_ID' => $arTemplateFields['IBLOCK_ID'],
		'FILTERTYPE' => 'CUSTOMFILTER', 
		'FILTER' => $arFilter
	));
	
	$pr = new \Bitrix\EsolMassedit\Processor($arParams);
	$arCounts = $pr->DoUpdate();
	
	echo date('Y-m-d H:i:s').": update complete\r\n"."Profile id = ".$PROFILE_ID."\r\n".\Bitrix\EsolMassedit\Utils::PhpToJSObject($arCounts)."\r\n\r\n";
}
?>