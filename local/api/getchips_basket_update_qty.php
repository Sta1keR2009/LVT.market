<?php
/**
 * AJAX: изменение количества позиции Getchips в корзине (товар-заглушка + проверка партномера).
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/getchips_site.php';

use Bitrix\Main\Loader;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!check_bitrix_sessid('sessid')) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Неверная сессия'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stubId = (int) (defined('GETCHIPS_STUB_PRODUCT_ID') ? GETCHIPS_STUB_PRODUCT_ID : 0);
    if ($stubId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Не задан GETCHIPS_STUB_PRODUCT_ID'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $productId = (int) ($_POST['product_id'] ?? 0);
    if ($productId !== $stubId) {
        echo json_encode(['ok' => false, 'error' => 'Некорректный товар'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $basketItemId = (int) ($_POST['basket_item_id'] ?? 0);
    if ($basketItemId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Не указана позиция корзины'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $partExpected = mb_substr(trim((string) ($_POST['part_number'] ?? '')), 0, 255);
    if ($partExpected === '') {
        echo json_encode(['ok' => false, 'error' => 'Не указан партномер'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $minOrder = max(1, (int) ($_POST['min_order'] ?? 1));
    $orderStep = max(1, (int) ($_POST['order_step'] ?? 1));
    $qty = max(1, (int) ($_POST['quantity'] ?? 1));
    if ($qty < $minOrder) {
        $qty = $minOrder;
    } else {
        $rem = ($qty - $minOrder) % $orderStep;
        if ($rem !== 0) {
            $qty += $orderStep - $rem;
        }
    }

    if (!Loader::includeModule('sale')) {
        echo json_encode(['ok' => false, 'error' => 'Модуль sale недоступен'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $siteId = defined('SITE_ID') ? SITE_ID : 's1';
    $basket = Basket::loadItemsForFUser(Fuser::getId(), $siteId);
    $item = null;
    foreach ($basket->getBasketItems() as $bi) {
        if ((int) $bi->getId() !== $basketItemId) {
            continue;
        }
        if ((int) $bi->getProductId() !== $stubId) {
            echo json_encode(['ok' => false, 'error' => 'Позиция не относится к Getchips'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $itemPart = '';
        foreach ($bi->getPropertyCollection() as $prop) {
            if (strtoupper(trim((string) $prop->getField('CODE'))) === 'GETCHIPS_PART') {
                $v = $prop->getField('VALUE');
                $itemPart = mb_substr(trim(is_array($v) ? (string) reset($v) : (string) $v), 0, 255);
                break;
            }
        }
        if ($itemPart === '' || $itemPart !== $partExpected) {
            echo json_encode(['ok' => false, 'error' => 'Позиция корзины не совпадает с предложением'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $item = $bi;
        break;
    }

    if ($item === null) {
        echo json_encode(['ok' => false, 'error' => 'Строка корзины не найдена'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $r = $item->setField('QUANTITY', $qty);
    if (!$r->isSuccess()) {
        echo json_encode(['ok' => false, 'error' => implode('; ', $r->getErrorMessages())], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $save = $basket->save();
    if (!$save->isSuccess()) {
        echo json_encode(['ok' => false, 'error' => implode('; ', $save->getErrorMessages())], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['ok' => true, 'quantity' => $qty], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
