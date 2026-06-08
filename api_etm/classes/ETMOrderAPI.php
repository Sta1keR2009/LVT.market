<?php
/**
 * Класс для работы с ETM iPRO Order API
 *
 * API Documentation: https://ipro.etm.ru/api/v1
 *
 * @author Claude Code
 * @version 1.0
 * @date 2026-01-20
 */

class ETMOrderAPI {
    /**
     * @var string API base URL
     */
    private $apiUrl;

    /**
     * @var string Login для авторизации
     */
    private $login;

    /**
     * @var string Пароль для авторизации
     */
    private $password;

    /**
     * @var string Session ID (действителен 8 часов)
     */
    private $sessionId;

    /**
     * @var string Путь к файлу логов
     */
    private $logFile;

    /**
     * @var bool Режим работы (true = тест, false = продакшн)
     */
    private $testMode;

    /**
     * @var string ILN продавца (константа из документации)
     */
    const SELLER_ILN = '4660011519999';

    /**
     * Конструктор
     *
     * @param string $login Login для API
     * @param string $password Пароль для API
     * @param bool $testMode Режим работы (true = тест, false = продакшн)
     */
    public function __construct($login, $password, $testMode = false) {
        $this->login = $login;
        $this->password = $password;
        $this->testMode = $testMode;

        // Выбираем URL в зависимости от режима
        $this->apiUrl = $testMode
            ? 'https://itest2.etm.ru/api/v1'
            : 'https://ipro.etm.ru/api/v1';

        // Настраиваем логирование
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $this->logFile = $logDir . '/etm_order_api_' . date('Y-m-d') . '.log';

        $this->log('ETMOrderAPI initialized in ' . ($testMode ? 'TEST' : 'PRODUCTION') . ' mode');
    }

    /**
     * Запись в лог
     *
     * @param string $message Сообщение для лога
     * @param string $level Уровень (INFO, ERROR, DEBUG)
     */
    private function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Авторизация в API
     *
     * @return bool Success status
     */
    public function authorize() {
        $this->log('Attempting authorization for user: ' . $this->login);

        $url = $this->apiUrl . '/user/login?log=' . urlencode($this->login) . '&pwd=' . urlencode($this->password);

        try {
            $response = $this->makeRequest('POST', $url);

            if (isset($response['session-id']) || isset($response['data']['session'])) {
                $this->sessionId = $response['session-id'] ?? $response['data']['session'];
                $this->log('Authorization successful. Session ID: ' . $this->sessionId);
                return true;
            } else {
                $this->log('Authorization failed: No session-id in response', 'ERROR');
                $this->log('Response: ' . json_encode($response), 'DEBUG');
                return false;
            }
        } catch (Exception $e) {
            $this->log('Authorization error: ' . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Проверка наличия активной сессии
     *
     * @return bool
     */
    private function ensureSession() {
        if (empty($this->sessionId)) {
            $this->log('No active session, authorizing...', 'DEBUG');
            return $this->authorize();
        }
        return true;
    }

    /**
     * Создание заказа в ETM
     *
     * @param array $orderData Данные заказа из Bitrix
     * @return array|false Результат создания заказа или false при ошибке
     */
    public function createOrder($orderData) {
        if (!$this->ensureSession()) {
            $this->log('Cannot create order: authorization failed', 'ERROR');
            return false;
        }

        $this->log('Creating order: ' . $orderData['OrderNumber']);

        // Формируем тело запроса согласно документации
        $requestBody = [
            'OrderNumber' => $orderData['OrderNumber'],
            'DocumentFunctionCode' => $orderData['DocumentFunctionCode'] ?? 'O', // O = Original
            'Seller' => [
                'ILN' => self::SELLER_ILN
            ],
            'Order-Lines' => $orderData['Order-Lines']
        ];

        // Добавляем опциональные поля если есть
        if (!empty($orderData['BuyerOrderNumber'])) {
            $requestBody['BuyerOrderNumber'] = $orderData['BuyerOrderNumber'];
        }

        if (!empty($orderData['DeliveryAddress'])) {
            $requestBody['DeliveryAddress'] = $orderData['DeliveryAddress'];
        }

        if (!empty($orderData['Comment'])) {
            $requestBody['Comment'] = $orderData['Comment'];
        }

        $this->log('Request body: ' . json_encode($requestBody, JSON_UNESCAPED_UNICODE), 'DEBUG');

        $url = $this->apiUrl . '/invoice/create?session-id=' . $this->sessionId;

        try {
            $response = $this->makeRequest('POST', $url, $requestBody);
            $this->log('Order created successfully: ' . json_encode($response, JSON_UNESCAPED_UNICODE));
            return $response;
        } catch (Exception $e) {
            $this->log('Order creation error: ' . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Получение информации о заказе
     *
     * @param string $invoiceNumber Номер заказа в ETM
     * @return array|false Данные заказа или false при ошибке
     */
    public function getOrderStatus($invoiceNumber) {
        if (!$this->ensureSession()) {
            $this->log('Cannot get order status: authorization failed', 'ERROR');
            return false;
        }

        $this->log('Getting status for order: ' . $invoiceNumber);

        $url = $this->apiUrl . '/invoice?session-id=' . $this->sessionId . '&number=' . urlencode($invoiceNumber);

        try {
            $response = $this->makeRequest('GET', $url);
            $this->log('Order status retrieved: ' . json_encode($response, JSON_UNESCAPED_UNICODE), 'DEBUG');
            return $response;
        } catch (Exception $e) {
            $this->log('Error getting order status: ' . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Получение детальной информации о заказе
     *
     * @param int $invoiceId ID заказа в ETM
     * @return array|false Детальные данные заказа или false при ошибке
     */
    public function getOrderDetails($invoiceId) {
        if (!$this->ensureSession()) {
            $this->log('Cannot get order details: authorization failed', 'ERROR');
            return false;
        }

        $this->log('Getting details for order ID: ' . $invoiceId);

        $url = $this->apiUrl . '/invoice/' . $invoiceId . '/body?session-id=' . $this->sessionId;

        try {
            $response = $this->makeRequest('GET', $url);
            $this->log('Order details retrieved successfully', 'DEBUG');
            return $response;
        } catch (Exception $e) {
            $this->log('Error getting order details: ' . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Печать счета
     *
     * @param int $invoiceId ID заказа в ETM
     * @return array|false Результат или false при ошибке
     */
    public function printInvoice($invoiceId) {
        if (!$this->ensureSession()) {
            $this->log('Cannot print invoice: authorization failed', 'ERROR');
            return false;
        }

        $this->log('Printing invoice for order ID: ' . $invoiceId);

        $url = $this->apiUrl . '/invoice/' . $invoiceId . '/print/bill?session-id=' . $this->sessionId;

        try {
            $response = $this->makeRequest('POST', $url);
            $this->log('Invoice printed successfully');
            return $response;
        } catch (Exception $e) {
            $this->log('Error printing invoice: ' . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Выполнение HTTP запроса к API
     *
     * @param string $method HTTP метод (GET, POST)
     * @param string $url URL запроса
     * @param array|null $data Данные для отправки (для POST)
     * @return array Ответ API
     * @throws Exception При ошибке запроса
     */
    private function makeRequest($method, $url, $data = null) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($jsonData)
                ]);
            }
        }

        $this->log("Making {$method} request to: {$url}", 'DEBUG');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($curlError) {
            throw new Exception('CURL error: ' . $curlError);
        }

        if ($httpCode >= 400) {
            $this->log("HTTP error {$httpCode}: {$response}", 'ERROR');
            throw new Exception("HTTP error {$httpCode}: {$response}");
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON decode error: ' . json_last_error_msg() . '. Response: ' . $response);
        }

        return $decodedResponse;
    }

    /**
     * Получение текущего session ID
     *
     * @return string|null
     */
    public function getSessionId() {
        return $this->sessionId;
    }
}
