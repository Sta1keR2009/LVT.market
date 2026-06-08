<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/getchips_site.php';

use Bitrix\Main\Loader;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;

try {
    $stubId = (int)(defined('GETCHIPS_STUB_PRODUCT_ID') ? GETCHIPS_STUB_PRODUCT_ID : 0);
    if ($stubId <= 0) {
        echo json_encode(['ok' => false, 'items' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (!Loader::includeModule('sale')) {
        echo json_encode(['ok' => false, 'items' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $siteId = defined('SITE_ID') ? SITE_ID : 's1';
    $basket = Basket::loadItemsForFUser(Fuser::getId(), $siteId);
    $items = [];

    foreach ($basket->getBasketItems() as $bi) {
        if ((int)$bi->getProductId() !== $stubId) {
            continue;
        }
        $row = [
            'basket_item_id' => (int)$bi->getId(),
            'quantity' => (int)$bi->getQuantity(),
            'provider' => '',
            'part_number' => '',
            'supplier' => '',
            'url' => '',
            'source_price' => '',
            'lead_days' => '',
        ];
        foreach ($bi->getPropertyCollection() as $prop) {
            $code = strtoupper(trim((string)$prop->getField('CODE')));
            $val = $prop->getField('VALUE');
            if (is_array($val)) {
                $val = reset($val);
            }
            $val = trim((string)$val);
            if ($code === 'GETCHIPS_PROVIDER') {
                $row['provider'] = $val;
            } elseif ($code === 'GETCHIPS_PART') {
                $row['part_number'] = $val;
            } elseif ($code === 'GETCHIPS_SUPPLIER') {
                $row['supplier'] = $val;
            } elseif ($code === 'GETCHIPS_URL') {
                $row['url'] = $val;
            } elseif ($code === 'GETCHIPS_SOURCE_PRICE') {
                $row['source_price'] = $val;
            } elseif ($code === 'GETCHIPS_LEAD_DAYS') {
                $row['lead_days'] = $val;
            }
        }
        $items[] = $row;
    }

    echo json_encode(['ok' => true, 'items' => $items], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage(), 'items' => []], JSON_UNESCAPED_UNICODE);
}
