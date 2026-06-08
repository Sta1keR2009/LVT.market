<?
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
CModule::IncludeModule("ipol.robokassa");

$data = array(
	'NAME' => Loc::getMessage('ROBOKASSA.PAYMENT_TITLE'),
	'SORT' => 500,
	'CODES' => array(
		'SHOPLOGIN' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.OPTIONS_SHOPLOGIN'),
			'SORT' => 100,
			'GROUP' => Loc::getMessage('ROBOKASSA.MAIN_SETTINGS'),
		),
		'SHOPPASSWORD' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.OPTIONS_SHOPPASSWORD'),
			'SORT' => 200,
			'GROUP' => Loc::getMessage('ROBOKASSA.MAIN_SETTINGS'),
		),
		'SHOPPASSWORD2' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.OPTIONS_SHOPPASSWORD2'),
			'SORT' => 300,
			'GROUP' => Loc::getMessage('ROBOKASSA.MAIN_SETTINGS'),
		),
        'SHOPPASSWORD_TEST' => array(
            'NAME' => Loc::getMessage('ROBOKASSA.OPTIONS_TEST_SHOPPASSWORD'),
			'SORT' => 500,
			'GROUP' => Loc::getMessage('ROBOKASSA.MAIN_SETTINGS'),
		),
		'SHOPPASSWORD2_TEST' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.OPTIONS_TEST_SHOPPASSWORD2'),
			'SORT' => 600,
			'GROUP' => Loc::getMessage('ROBOKASSA.MAIN_SETTINGS'),
		),
        'SHOPPASSWORD3' => array(
            'NAME' => Loc::getMessage('ROBOKASSA.OPTIONS_SHOPPASSWORD3'),
            'SORT' => 300,
            'GROUP' => Loc::getMessage('ROBOKASSA.MAIN_SETTINGS'),
        ),
		'PAYMENT_ID' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.OPTIONS_PAYMENT_ID'),
			'SORT' => 700,
			'GROUP' => Loc::getMessage('ROBOKASSA.PAYMENT_SETTINGS'),
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'ID',
				'PROVIDER_KEY' => 'PAYMENT'
			)
		),
		'ORDER_NUMBER' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.OPTIONS_ORDER_NUMBER'),
			'SORT' => 750,
			'GROUP' => Loc::getMessage('ROBOKASSA.PAYMENT_SETTINGS'),
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'ACCOUNT_NUMBER',
				'PROVIDER_KEY' => 'ORDER'
			)
		),
		'SNO' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.OPTIONS_SNO'),
			'SORT' => 850,
			'GROUP' => Loc::getMessage('ROBOKASSA.PAYMENT_SETTINGS'),
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'osn'  => Loc::getMessage("ROBOKASSA.OPTION_SNO_OSN"),
					'usn_income'  => Loc::getMessage("ROBOKASSA.OPTION_SNO_USN_INCOME"),
					'usn_income_outcome'  => Loc::getMessage("ROBOKASSA.OPTION_SNO_USN_INCOME_OUTCOME"),
					'envd'  => Loc::getMessage("ROBOKASSA.OPTION_SNO_ENVD"),
					'esn'  => Loc::getMessage("ROBOKASSA.OPTION_SNO_ESN"),
					'patent'  => Loc::getMessage("ROBOKASSA.OPTION_SNO_PATENT"),
				)
			),
		),
		'PAYMENT_METHOD' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.PAYMENT_METHOD'),
			'SORT' => 860,
			'GROUP' => Loc::getMessage('ROBOKASSA.PAYMENT_SETTINGS'),
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'full_prepayment'  => Loc::getMessage("ROBOKASSA.OPTION_METHOD_FULL_PREPAYMENT"),
					'prepayment'  => Loc::getMessage("ROBOKASSA.OPTION_METHOD_PREPAYMENT"),
					'advance'  => Loc::getMessage("ROBOKASSA.OPTION_METHOD_ADVANCE"),
					'full_payment'  => Loc::getMessage("ROBOKASSA.OPTION_METHOD_FULL_PAYMENT"),
					'partial_payment'  => Loc::getMessage("ROBOKASSA.OPTION_METHOD_PARTIAL_PAYMENT"),
					'credit'  => Loc::getMessage("ROBOKASSA.OPTION_METHOD_CREDIT"),
					'credit_payment'  => Loc::getMessage("ROBOKASSA.OPTION_METHOD_CREDIT_PAYMENT"),
				)
			),
		),
		'PAYMENT_OBJECT' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.PAYMENT_OBJECT'),
			'SORT' => 870,
			'GROUP' => Loc::getMessage('ROBOKASSA.PAYMENT_SETTINGS'),
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'commodity'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_COMMODITY"),
					'excise'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_USN_EXCISE"),
					'job'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_USN_INCOME_JOB"),
					'service'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_SERVICE"),
					'gambling_bet'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_GAMBLING_BET"),
					'gambling_prize'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_GAMBLING_PRIZE"),
					'lottery'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_LOTTERY"),
					'lottery_prize'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_LOTTERY_PRIZE"),
					'intellectual_activity'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_INTELLECTUAL_ACTIVITY"),
					'payment'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_PAYMENT"),
					'agent_commission'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_AGENT_COMMISSION"),
					'composite'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_COMPOSITE"),
					'another'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_ANOTHER"),
				)
			),
		),
		'PAYMENT_OBJECT_DELIVERY' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.PAYMENT_OBJECT_DELIVERY'),
			'SORT' => 880,
			'GROUP' => Loc::getMessage('ROBOKASSA.PAYMENT_SETTINGS'),
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'commodity'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_COMMODITY"),
					'excise'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_USN_EXCISE"),
					'job'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_USN_INCOME_JOB"),
					'service'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_SERVICE"),
					'gambling_bet'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_GAMBLING_BET"),
					'gambling_prize'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_GAMBLING_PRIZE"),
					'lottery'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_LOTTERY"),
					'lottery_prize'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_LOTTERY_PRIZE"),
					'intellectual_activity'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_INTELLECTUAL_ACTIVITY"),
					'payment'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_PAYMENT"),
					'agent_commission'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_AGENT_COMMISSION"),
					'composite'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_COMPOSITE"),
					'another'  => Loc::getMessage("ROBOKASSA.OPTION_OBJECT_ANOTHER"),
				)
			),
		),
		'PAYMENT_CURRENCY' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.OPTIONS_CURRENCY'),
			'SORT' => 900,
			'GROUP' => Loc::getMessage('ROBOKASSA.PAYMENT_SETTINGS'),
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'CURRENCY',
				'PROVIDER_KEY' => 'PAYMENT'
			)
		),
		'BUYER_PERSON_EMAIL' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.OPTIONS_EMAIL_USER'),
			'SORT' => 1100,
			'GROUP' => 'BUYER_PERSON',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'EMAIL',
				'PROVIDER_KEY' => 'USER'
			)
		),
		'PS_IS_TEST' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.OPTIONS_TEST'),
			'SORT' => 1300,
			'GROUP' => Loc::getMessage('ROBOKASSA.MAIN_SETTINGS'),
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		),
		'COUNTRY_CODE' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.COUNTRY_CODE'),
			'SORT' => 1500,
			'GROUP' => Loc::getMessage('ROBOKASSA.PAYMENT_SETTINGS'),
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'RU'  => Loc::getMessage("ROBOKASSA.OPTION_COUNTRY_RU"),
					'KZ'  => Loc::getMessage("ROBOKASSA.OPTION_COUNTRY_KZ"),
				)
			)
		),
		'IFRAME_STATUS' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.IFRAME_STATUS'),
			'DESCRIPTION' => Loc::getMessage('ROBOKASSA.IFRAME_DESCRIPTION'),
			'SORT' => 1500,
			'GROUP' => Loc::getMessage('ROBOKASSA.PAYMENT_SETTINGS'),
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		),
		'OUT_CURRENCY' => array(
			'NAME' => Loc::getMessage('ROBOKASSA.OUT_CURRENCY'),
			'SORT' => 1500,
			'GROUP' => Loc::getMessage('ROBOKASSA.PAYMENT_SETTINGS'),
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					''  => Loc::getMessage("ROBOKASSA.OPTION_OUT_CURRENCY_RUB"),
					'USD'  => Loc::getMessage("ROBOKASSA.OPTION_OUT_CURRENCY_USD"),
					'EUR'  => Loc::getMessage("ROBOKASSA.OPTION_OUT_CURRENCY_EUR"),
					'KZT'  => Loc::getMessage("ROBOKASSA.OPTION_OUT_CURRENCY_KZT"),
				)
			)
		),
	)
);
