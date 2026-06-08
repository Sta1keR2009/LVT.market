<?
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Config\Option,
	CLite as Solution,
	Aspro\Lite\PhoneAuth;

define('NOT_CHECK_PERMISSIONS', true);
define('STATISTIC_SKIP_ACTIVITY_CHECK', true);
define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);

$SITE_ID = $_REQUEST['site_id'] ?? 's1';
define('SITE_ID', $SITE_ID);

$SITE_DIR = $_REQUEST['site_dir'] ?? '/';
define('SITE_DIR', $SITE_DIR);

$SITE_TEMPLATE_PATH = preg_replace('/\/js\/'.preg_quote(basename(__FILE__)).'$/i', '', $_SERVER['SCRIPT_NAME']);
define('SITE_TEMPLATE_PATH', $SITE_TEMPLATE_PATH);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
Loader::includeModule(Solution::moduleID);

$arSite = CSite::GetByID($SITE_ID)->Fetch();
$SITE_DIR = preg_replace('/\/+/', '/', '/'.$arSite['DIR'].'/');

$arFrontParametrs = Solution::GetFrontParametrsValues($SITE_ID, $SITE_DIR);

$tmp = $arFrontParametrs['DATE_FORMAT'];
$DATE_MASK = ($tmp == 'DOT' ? 'dd.mm.yyyy': ($tmp == 'HYPHEN' ? 'dd-mm-yyyy': ($tmp == 'SPACE' ? 'dd mm yyyy': ($tmp == 'SLASH' ? 'dd/mm/yyyy': 'dd:mm:yyyy'))));
$VALIDATE_DATE_MASK = ($tmp == 'DOT' ? '^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}$': ($tmp == 'HYPHEN' ? '^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}$': ($tmp == 'SPACE' ? '^[0-9]{1,2} [0-9]{1,2} [0-9]{4}$': ($tmp == 'SLASH' ? '^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$': '^[0-9]{1,2}\:[0-9]{1,2}\:[0-9]{4}$'))));
$DATE_PLACEHOLDER = ($tmp == 'DOT' ? GetMessage('DATE_FORMAT_DOT') : ($tmp == 'HYPHEN' ? GetMessage('DATE_FORMAT_HYPHEN') : ($tmp == 'SPACE' ? GetMessage('DATE_FORMAT_SPACE') : ($tmp == 'SLASH' ? GetMessage('DATE_FORMAT_SLASH') : GetMessage('DATE_FORMAT_COLON')))));
$DATETIME_MASK = $DATE_MASK.' h:s';
$DATETIME_PLACEHOLDER = ($tmp == 'DOT' ? GetMessage('DATE_FORMAT_DOT') : ($tmp == 'HYPHEN' ? GetMessage('DATE_FORMAT_HYPHEN') : ($tmp == 'SPACE' ? GetMessage('DATE_FORMAT_SPACE') : ($tmp == 'SLASH' ? GetMessage('DATE_FORMAT_SLASH') : GetMessage('DATE_FORMAT_COLON'))))).' '.GetMessage('TIME_FORMAT_COLON');
$VALIDATE_DATETIME_MASK = ($tmp == 'DOT' ? '^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4} [0-9]{1,2}\:[0-9]{1,2}$': ($tmp == 'HYPHEN' ? '^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4} [0-9]{1,2}\:[0-9]{1,2}$': ($tmp == 'SPACE' ? '^[0-9]{1,2} [0-9]{1,2} [0-9]{4} [0-9]{1,2}\:[0-9]{1,2}$': ($tmp == 'SLASH' ? '^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4} [0-9]{1,2}\:[0-9]{1,2}$': '^[0-9]{1,2}\:[0-9]{1,2}\:[0-9]{4} [0-9]{1,2}\:[0-9]{1,2}$'))));

// get banner`s index of current preset
$currentBannerIndex = Solution::getCurrentPresetBannerIndex($SITE_ID);

list($bPhoneAuthSupported, $bPhoneAuthShow, $bPhoneAuthRequired, $bPhoneAuthUse) = PhoneAuth::getOptions();
?>
<?header('Content-Type: application/javascript;charset='.LANG_CHARSET);?>
var arAsproOptions = window[solutionName] = ({
	SITE_DIR: '<?=htmlspecialcharsbx($SITE_DIR)?>',
	SITE_ID: '<?=htmlspecialcharsbx($SITE_ID)?>',
	SITE_TEMPLATE_PATH: '<?=$SITE_TEMPLATE_PATH?>',
	SITE_ADDRESS: '<?=$arSite['SERVER_NAME'];?>',
	FORM: ({
		ASK_FORM_ID: 'ASK',
		SERVICES_FORM_ID: 'SERVICES',
		FEEDBACK_FORM_ID: 'FEEDBACK',
		CALLBACK_FORM_ID: 'CALLBACK',
		RESUME_FORM_ID: 'RESUME',
		TOORDER_FORM_ID: 'TOORDER'
	}),
	PAGES : ({
		"CATALOG_PAGE_URL" : "<?=$arFrontParametrs['CATALOG_PAGE_URL']?>",
		"COMPARE_PAGE_URL" : "<?=$arFrontParametrs['COMPARE_PAGE_URL']?>",
		"SEARCH_PAGE_URL" : "<?=$arFrontParametrs['SEARCH_PAGE_URL']?>",
		"BASKET_PAGE_URL" : "<?=$arFrontParametrs['BASKET_PAGE_URL']?>",
		"SHARE_BASKET_PAGE_URL" : "<?=$arFrontParametrs['SHARE_BASKET_PAGE_URL']?>",
		"ORDER_PAGE_URL" : "<?=$arFrontParametrs['ORDER_PAGE_URL']?>",
		"PERSONAL_PAGE_URL" : "<?=$arFrontParametrs['PERSONAL_PAGE_URL']?>",
		"SUBSCRIBE_PAGE_URL" : "<?=$arFrontParametrs['SUBSCRIBE_PAGE_URL']?>",
	}),
	PRICES : ({
		"MIN_PRICE" : "<?=trim(Option::get(Solution::moduleID, "MIN_ORDER_PRICE", "1000", $SITE_ID));?>",
	}),
	THEME: <?=CUtil::PhpToJSObject(
		array_merge(
			$arFrontParametrs,
			array(
				'DATE_MASK' => $DATE_MASK,
				'DATE_PLACEHOLDER' => $DATE_PLACEHOLDER,
				'VALIDATE_DATE_MASK' => $VALIDATE_DATE_MASK,
				'DATETIME_MASK' => $DATETIME_MASK,
				'DATETIME_PLACEHOLDER' => $DATETIME_PLACEHOLDER,
				'VALIDATE_DATETIME_MASK' => $VALIDATE_DATETIME_MASK,
				'INSTAGRAMM_INDEX' => isset($arFrontParametrs[$arFrontParametrs['INDEX_TYPE'].'_INSTAGRAMM_INDEX']) ? $arFrontParametrs[$arFrontParametrs['INDEX_TYPE'].'_INSTAGRAMM_INDEX'] : 'Y',
				'USE_PHONE_AUTH' => $bPhoneAuthUse ? 'Y' : 'N',
				'IS_BASKET_PAGE' => Solution::IsBasketPage($arFrontParametrs['BASKET_PAGE_URL']),
				'IS_ORDER_PAGE' => Solution::IsOrderPage($arFrontParametrs['ORDER_PAGE_URL']),
			)
		)
	)?>,
	PRESETS: <?=CUtil::PhpToJSObject(
		array(
			'VALUE' => Solution::getCurrentPreset($SITE_ID),
			'LIST' => Solution::$arPresetsList,
		)
	)?>,
	THEMATICS: <?=CUtil::PhpToJSObject(
		array(
			'VALUE' => Solution::getCurrentThematic($SITE_ID),
			'LIST' => Solution::$arThematicsList,
		)
	)?>,
	REGIONALITY: ({
		USE_REGIONALITY: '<?=$arFrontParametrs['USE_REGIONALITY']?>',
		REGIONALITY_VIEW: '<?=$arFrontParametrs['REGIONALITY_VIEW']?>',
	}),
	COUNTERS: ({
		YANDEX_COUNTER: 1,
		GOOGLE_COUNTER: 1,
		YANDEX_ECOMERCE: '<?=Option::get(Solution::moduleID, 'YANDEX_ECOMERCE', false, $SITE_ID)?>',
		GOOGLE_ECOMERCE: '<?=Option::get(Solution::moduleID, 'GOOGLE_ECOMERCE', false, $SITE_ID)?>',
		GA_VERSION: '<?=Option::get(Solution::moduleID, 'GA_VERSION', 'v3', $SITE_ID)?>',
		TYPE: {
			ONE_CLICK: '<?=GetMessage('ONE_CLICK_BUY');?>',
			QUICK_ORDER: '<?=GetMessage('QUICK_ORDER');?>',
		},
		GOOGLE_EVENTS: {
			ADD2BASKET: '<?=trim(Option::get(Solution::moduleID, 'BASKET_ADD_EVENT', 'addToCart', $SITE_ID))?>',
			REMOVE_BASKET: '<?=trim(Option::get(Solution::moduleID, 'BASKET_REMOVE_EVENT', 'removeFromCart', $SITE_ID))?>',
			CHECKOUT_ORDER: '<?=trim(Option::get(Solution::moduleID, 'CHECKOUT_ORDER_EVENT', 'checkout', $SITE_ID))?>',
			PURCHASE: '<?=trim(Option::get(Solution::moduleID, 'PURCHASE_ORDER_EVENT', 'gtm.dom', $SITE_ID))?>',
		}
	}),
	OID: '<?=$arFrontParametrs['CATALOG_OID'];?>',
	JS_ITEM_CLICK: ({
		'precision': 6,
		'precisionFactor': Math.pow(10, 6)
	}),
	MODULES: {
		sale: <?=(Solution::isSaleMode() && Loader::includeModule('sale') ? 'true' : 'false');?>,
	},
});
<?if ($currentBannerIndex && Bitrix\Main\Config\Option::get(Solution::moduleID, 'USE_BIG_BANNERS', 'N', $SITE_ID) === 'Y'):?>
	window[solutionName]['CURRENT_BANNER_INDEX'] = "<?=$currentBannerIndex;?>";
<?endif;?>
