<?php
/**
 * Обработчик событий заказов Bitrix для отправки в ETM iPRO Order API.
 *
 * @see /api_etm/classes/ETMOrderAPI.php
 * @see /api_etm/README.txt
 */

use Bitrix\Main\Loader;
use Bitrix\Sale\Order;

class OrderETMOrderHandler
{
    private static ?string $logFile = null;

    /** @var array{login:string,password:string,testMode:bool} */
    private static array $apiConfig = [
        'login' => '330252858fad',
        'password' => '2K1R8apl',
        'testMode' => false,
    ];

    /** ID свойства «Код товара» (IB41), fallback если нет по CODE */
    private static int $etmCodePropertyId = 2568;

    /** Символьные коды свойства с ETM-кодом (по приоритету) */
    private static array $etmCodePropertyCodes = ['kod_tovara_', 'ETMCODE', 'ID_ELEMENTA'];

    public static function onOrderSaved(\Bitrix\Main\Event $event): void
    {
        try {
            self::initLog();
            self::log('=== Order event triggered ===');

            /** @var Order|null $order */
            $order = $event->getParameter('ENTITY');
            if (!$order instanceof Order) {
                self::log('Error: Order object not found in event', 'ERROR');
                return;
            }

            $orderId = (int)$order->getId();
            self::log("Processing order ID: {$orderId}");

            if (!$order->isNew()) {
                self::log("Order {$orderId} is not new, skipping ETM sync");
                return;
            }

            $orderData = self::getOrderData($order);
            if (empty($orderData['items'])) {
                self::log("Order {$orderId} has no items with ETM codes, skipping", 'WARNING');
                return;
            }

            $result = self::sendOrderToETM($orderData);
            if ($result) {
                self::log("Order {$orderId} successfully sent to ETM", 'SUCCESS');
                self::addOrderComment($order, $result);
            } else {
                self::log("Failed to send order {$orderId} to ETM", 'ERROR');
            }
        } catch (\Throwable $e) {
            self::log('Exception in onOrderSaved: ' . $e->getMessage(), 'ERROR');
            self::log('Stack trace: ' . $e->getTraceAsString(), 'DEBUG');
        }
    }

    private static function getOrderData(Order $order): array
    {
        $orderId = (int)$order->getId();
        $orderData = [
            'bitrix_order_id' => $orderId,
            'order_number' => $orderId,
            'items' => [],
            'total' => 0.0,
        ];

        $basket = $order->getBasket();
        if (!$basket) {
            self::log("Order {$orderId} has no basket", 'WARNING');
            return $orderData;
        }

        foreach ($basket->getBasketItems() as $basketItem) {
            $productId = (int)$basketItem->getProductId();
            $quantity = (float)$basketItem->getQuantity();
            $price = (float)$basketItem->getPrice();

            self::log(
                "Processing basket item: Product ID {$productId}, Qty {$quantity}, Price {$price}",
                'DEBUG'
            );

            $etmCode = self::getProductETMCode($productId);
            if ($etmCode === null || $etmCode === '') {
                self::log("Product {$productId} has no ETM code, skipping", 'WARNING');
                continue;
            }

            $orderData['items'][] = [
                'etm_code' => $etmCode,
                'quantity' => (int)round($quantity),
                'price' => $price,
                'name' => (string)$basketItem->getField('NAME'),
                'product_id' => $productId,
            ];
            $orderData['total'] += $price * $quantity;
        }

        $propertyCollection = $order->getPropertyCollection();
        if ($propertyCollection) {
            foreach ($propertyCollection as $property) {
                if ($property->getField('CODE') === 'ADDRESS') {
                    $orderData['delivery_address'] = $property->getValue();
                }
            }
        }

        self::log(
            'Order data prepared: ' . count($orderData['items']) . ' items, total: ' . $orderData['total'],
            'DEBUG'
        );

        return $orderData;
    }

    private static function getProductETMCode(int $productId): ?string
    {
        try {
            if (!Loader::includeModule('iblock')) {
                return null;
            }

            $select = ['ID', 'IBLOCK_ID', 'CODE'];
            foreach (self::getEtmCodePropertyCodes() as $code) {
                $select[] = 'PROPERTY_' . $code;
            }

            $element = \CIBlockElement::GetList(
                [],
                ['ID' => $productId],
                false,
                false,
                $select
            )->Fetch();

            if (!$element) {
                self::log("Product {$productId} not found", 'WARNING');
                return null;
            }

            foreach (self::getEtmCodePropertyCodes() as $code) {
                $value = trim((string)($element['PROPERTY_' . $code . '_VALUE'] ?? ''));
                if ($value !== '') {
                    return $value;
                }
            }

            $propId = self::getEtmCodePropertyId();
            if ($propId > 0) {
                $byId = \CIBlockElement::GetList(
                    [],
                    ['ID' => $productId],
                    false,
                    false,
                    ['ID', 'PROPERTY_' . $propId]
                )->Fetch();
                $value = trim((string)($byId['PROPERTY_' . $propId . '_VALUE'] ?? ''));
                if ($value !== '') {
                    return $value;
                }
                self::log("Product {$productId} has no ETM code in property {$propId}", 'DEBUG');
            }

            $elementCode = (string)($element['CODE'] ?? '');
            if (preg_match('/^etm_(\d+)/i', $elementCode, $m)) {
                return $m[1];
            }

            return null;
        } catch (\Throwable $e) {
            self::log("Error getting ETM code for product {$productId}: " . $e->getMessage(), 'ERROR');
            return null;
        }
    }

    /**
     * @return array<int, string>
     */
    private static function getEtmCodePropertyCodes(): array
    {
        if (defined('API_ETM_PROP_ETM_CODE')) {
            $code = (string)API_ETM_PROP_ETM_CODE;
            if ($code !== '' && !in_array($code, self::$etmCodePropertyCodes, true)) {
                array_unshift(self::$etmCodePropertyCodes, $code);
            }
        } else {
            $configPath = $_SERVER['DOCUMENT_ROOT'] . '/api_etm_ai/config_ib40.php';
            if (is_file($configPath)) {
                @include_once $configPath;
                if (defined('API_ETM_PROP_ETM_CODE')) {
                    $code = (string)API_ETM_PROP_ETM_CODE;
                    if ($code !== '' && !in_array($code, self::$etmCodePropertyCodes, true)) {
                        array_unshift(self::$etmCodePropertyCodes, $code);
                    }
                }
            }
        }

        return self::$etmCodePropertyCodes;
    }

    private static function getEtmCodePropertyId(): int
    {
        if (defined('API_ETM_PROP_ETM_CODE_ID')) {
            return (int)API_ETM_PROP_ETM_CODE_ID;
        }

        return self::$etmCodePropertyId;
    }

    /**
     * @param array<string, mixed> $orderData
     * @return array<string, mixed>|false
     */
    private static function sendOrderToETM(array $orderData)
    {
        try {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/api_etm/classes/ETMOrderAPI.php';

            $api = new ETMOrderAPI(
                self::$apiConfig['login'],
                self::$apiConfig['password'],
                self::$apiConfig['testMode']
            );

            self::log('Authorizing in ETM API...');
            if (!$api->authorize()) {
                self::log('ETM API authorization failed', 'ERROR');
                return false;
            }

            $etmOrderData = [
                'OrderNumber' => 'BX-' . $orderData['order_number'],
                'DocumentFunctionCode' => 'O',
                'BuyerOrderNumber' => (string)$orderData['bitrix_order_id'],
                'Order-Lines' => [],
            ];

            $lineNumber = 1;
            foreach ($orderData['items'] as $item) {
                $etmOrderData['Order-Lines'][] = [
                    'Line-Number' => $lineNumber++,
                    'SupplierItemCode' => (string)$item['etm_code'],
                    'OrderedQuantity' => (int)$item['quantity'],
                ];
            }

            if (!empty($orderData['delivery_address'])) {
                $etmOrderData['DeliveryAddress'] = $orderData['delivery_address'];
            }

            self::log('Sending order to ETM: ' . json_encode($etmOrderData, JSON_UNESCAPED_UNICODE), 'DEBUG');

            $result = $api->createOrder($etmOrderData);
            if ($result) {
                self::log('ETM API response: ' . json_encode($result, JSON_UNESCAPED_UNICODE), 'DEBUG');
                return $result;
            }

            self::log('ETM API returned false', 'ERROR');
            return false;
        } catch (\Throwable $e) {
            self::log('Error sending order to ETM: ' . $e->getMessage(), 'ERROR');
            self::log('Stack trace: ' . $e->getTraceAsString(), 'DEBUG');
            return false;
        }
    }

    /**
     * @param array<string, mixed> $etmResult
     */
    private static function addOrderComment(Order $order, array $etmResult): void
    {
        try {
            $etmId = $etmResult['data']['id']
                ?? $etmResult['data']['ids'][0]['docid']
                ?? $etmResult['InvoiceId']
                ?? null;

            $comment = "\n\n--- ETM iPRO Integration ---\n";
            $comment .= "Заказ отправлен в ETM iPRO\n";
            $comment .= 'Дата: ' . date('Y-m-d H:i:s') . "\n";

            if ($etmId) {
                $comment .= 'ID заказа ETM: ' . $etmId . "\n";
            }

            $statusCode = $etmResult['status']['code'] ?? null;
            if ($statusCode !== null) {
                $comment .= 'HTTP status: ' . $statusCode . "\n";
            }

            $currentComment = (string)$order->getField('USER_DESCRIPTION');
            $order->setField('USER_DESCRIPTION', $currentComment . $comment);
            $order->save();

            self::log('Comment added to order ' . $order->getId());
        } catch (\Throwable $e) {
            self::log('Error adding comment to order: ' . $e->getMessage(), 'ERROR');
        }
    }

    private static function initLog(): void
    {
        if (self::$logFile !== null) {
            return;
        }

        $logDir = $_SERVER['DOCUMENT_ROOT'] . '/api_etm/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        self::$logFile = $logDir . '/etm_order_integration_' . date('Y-m-d') . '.log';
    }

    private static function log(string $message, string $level = 'INFO'): void
    {
        self::initLog();
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents(self::$logFile, "[{$timestamp}] [{$level}] {$message}\n", FILE_APPEND);
    }
}
