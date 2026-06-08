<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<?php

use  Bitrix\Main\Application;

global $APPLICATION;

$request = Application::getInstance()->getContext()->getRequest();

if ($request->isAjaxRequest() && $request->get('action') == 'subscribe') {
	$APPLICATION->RestartBuffer();

	$email = $request->get('EMAIL');
	$format = $request->get('FORMAT');
	$rubrics = $request->get('RUB_ID');

	$arSubscription = CSubscription::GetList(['ID' => 'ASC'], ['ACTIVE' => 'Y', 'EMAIL' => $email])->Fetch();

	$subscription = new CSubscription;

	if ($arSubscription) {
		$subscription->Update($arSubscription['ID'], ['EMAIL' => $email, 'RUB_ID' => $rubrics], SITE_ID);
	} else {
		$subscription->Add(['EMAIL' => $email, 'FORMAT' => $format, 'RUB_ID' => $rubrics], SITE_ID);
	}

	$arResult['EVENT']['SUCCESS_UPDATE'] = true;
}
