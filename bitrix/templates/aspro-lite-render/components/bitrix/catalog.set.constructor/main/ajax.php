<?php

/** @global CMain $APPLICATION */
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

if (!include_once ($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/vendor/php/solution.php')) {
    throw new Exception('Error include solution constants');
}

$application = Bitrix\Main\Application::getInstance();
$request = $application->getContext()->getRequest();
$post = $request->getPostList();

if (isset($request['lid']) && !empty($request['lid'])) {
    if (!is_string($request['lid'])) {
        exit;
    }
    if (preg_match('/^[a-z0-9_]{2}$/i', $request['lid'])) {
        define('SITE_ID', $request['lid']);
    }
}

if (!Loader::includeModule('catalog')) {
    return;
}

Loc::loadMessages(__FILE__);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && strlen($post['action']) > 0 && check_bitrix_sessid()) {
    $APPLICATION->RestartBuffer();

    switch ($post['action']) {
        case 'catalogSetAdd2Basket':
            if (is_array($post['set_ids'])) {
                foreach ($post['set_ids'] as $itemID) {
                    if (!is_scalar($itemID)) {
                        continue;
                    }

                    $itemID = (int) $itemID;
                    if ($itemID <= 0) {
                        continue;
                    }

                    $quantity = $post['itemsRatio'][$itemID] ?? 1;
                    $productProperties = [];
                    $offersCartProps = $post['setOffersCartProps'] ?? [];
                    if (!empty($offersCartProps)) {
                        $productProperties = CIBlockPriceTools::GetOfferProperties(
                            $itemID,
                            $post['iblockId'],
                            $offersCartProps
                        );
                    }

                    $propsConfig = [
                        'bAddProps' => (bool) $productProperties,
                        'bPartProps' => false,
                        'propsList' => $offersCartProps,
                        'skuTreeProps' => $productProperties,
                        'propsValues' => [0],
                        'appendQuantity' => true,
                    ];

                    TSolution\Itemaction\Basket::addItem($itemID, $quantity, $propsConfig);
                }
            }
            break;
    }

    exit;
}
