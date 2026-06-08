<?php

declare(strict_types=1);

namespace Itnull\Updater;

/**
 * Класс для работы с API Битрикса
 * Использует прямые HTTP запросы к серверу обновлений
 *
 * @package Itnull\Updater
 */
class BitrixAPI
{
    /** @var string Путь для сохранения обновлений */
    private const UPDATES_PATH = '/bitrix/updates/';

    /** @var string Сервер обновлений по умолчанию */
    private const DEFAULT_UPDATE_SERVER = 'www.1c-bitrix.ru';

    /**
     * Запись отладочной информации
     */
    private static function debugLog(string $message, $data = null): void
    {
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/itnull_updater_debug.log';
        $logMessage = date('Y-m-d H:i:s') . ' [BitrixAPI] ' . $message;
        if ($data !== null) {
            if (is_array($data) || is_object($data)) {
                $logMessage .= ' | ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } else {
                $logMessage .= ' | ' . $data;
            }
        }
        file_put_contents($logFile, $logMessage . "\n", FILE_APPEND);
    }

    /**
     * Получение информации о ключе и списка модулей
     * Использует прямой HTTP запрос с fullmoduleinfo=Y для получения полного списка
     *
     * @param string $key Лицензионный ключ
     * @return array{success: bool, data?: array, error?: string}
     */
    public static function getKeyInfo(string $key): array
    {
        try {
            self::debugLog('=== getKeyInfo START ===');
            self::ensureMainModule();

            self::validateLicenseKey($key);
            self::debugLog('Key validated', substr($key, 0, 10) . '***');

            // Делаем прямой HTTP запрос с fullmoduleinfo=Y
            $response = self::makeDirectRequest($key, 'info_key');

            if ($response === false) {
                self::debugLog('ERROR: Direct request failed');
                return self::errorResponse('Ошибка соединения с сервером обновлений');
            }

            self::debugLog('Response received', strlen($response) . ' bytes');

            // Парсим XML ответ
            $arrInfoKey = [];
            $strError = '';
            \CUpdateClientPartner::__ParseServerData($response, $arrInfoKey, $strError);

            self::debugLog('Parsed response keys', is_array($arrInfoKey) ? array_keys($arrInfoKey) : 'not array');

            if (!empty($strError)) {
                self::debugLog('Parse error', $strError);
                return self::errorResponse($strError);
            }

            // Проверяем на ошибки в ответе
            if (isset($arrInfoKey['DATA']['#']['ERROR'])) {
                $error = $arrInfoKey['DATA']['#']['ERROR'][0]['#'] ?? 'Неизвестная ошибка';
                self::debugLog('API error', $error);
                return self::errorResponse($error);
            }

            // Парсим результат
            $keyInfo = self::parseDirectResponse($arrInfoKey);

            self::debugLog('Parsed keyInfo', [
                'NAME' => $keyInfo['NAME'],
                'SUPPORT_PERIOD' => $keyInfo['SUPPORT_PERIOD'],
                'MODULES_count' => isset($keyInfo['MODULES']) ? count($keyInfo['MODULES']) : 0
            ]);

            self::debugLog('=== getKeyInfo END ===');

            return self::successResponse($keyInfo);
        } catch (\Throwable $e) {
            self::debugLog('EXCEPTION', $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            self::logError('getKeyInfo', $e->getMessage());
            return self::errorResponse($e->getMessage());
        }
    }

    /**
     * Делает прямой HTTP запрос к серверу обновлений Bitrix
     *
     * @param string $key Лицензионный ключ
     * @param string $step Тип запроса: info_key, module_info, download, updates
     * @param string|null $moduleId ID модуля (для module_info, download, updates)
     * @param string|null $data Дополнительные данные
     * @return string|false
     */
    public static function makeDirectRequest(string $key, string $step, ?string $moduleId = null, ?string $data = null)
    {
        try {
            $requestIP = \COption::GetOptionString('main', 'update_site', self::DEFAULT_UPDATE_SERVER);
            $requestPort = 80;

            // Определяем страницу в зависимости от типа запроса
            $page = ($step === 'info_key') ? 'smp_updater_list.php' : 'smp_updater_modules.php';

            // Формируем строку запроса
            $hashedKey = md5($key);
            $str = "utf=Y" .
                "&lang=ru" .
                "&stable=Y" .
                "&CANGZIP=N" .
                "&SUPD_DBS=MYSQL" .
                "&XE=N" .
                "&LICENSE_KEY=" . $hashedKey .
                "&SUPD_STS=1" .
                "&SUPD_URS=0" .
                "&SUPD_URSA=1" .
                "&TYPENC=E" .
                "&CLIENT_PHPVER=" . phpversion() .
                "&dbv=5.7.0";

            // Добавляем параметры в зависимости от типа запроса
            switch ($step) {
                case 'info_key':
                    $str .= "&fullmoduleinfo=Y&SUPD_SRS=RU&SUPD_CMP=N&product=BSM&verfix=2";
                    break;
                case 'module_info':
                    $str .= "&reqm=" . urlencode($moduleId) . "&lim=Y&SUPD_SRS=RU&SUPD_CMP=N&product=BSM&verfix=2";
                    break;
                case 'download':
                    $str .= "&reqm=" . urlencode($moduleId) . "&lim=Y&SUPD_SRS=RU&SUPD_CMP=N&UFILE=" . urlencode($data) . "&USTART=0&product=BSM&verfix=2";
                    break;
                case 'updates':
                    $str .= "&instm=" . urlencode($data) . "&reqm=" . urlencode($moduleId) . "&lim=Y&SUPD_SRS=RU&SUPD_CMP=N&product=BSM&verfix=2";
                    break;
            }

            self::debugLog('Making request to', $requestIP . '/bitrix/updates/' . $page);
            self::debugLog('Request params (partial)', substr($str, 0, 200) . '...');

            // Открываем соединение
            $fp = @fsockopen($requestIP, $requestPort, $errno, $errstr, 120);

            if (!$fp) {
                self::debugLog('Connection failed', "$errstr ($errno)");
                return false;
            }

            // Формируем HTTP запрос
            $request = "POST /bitrix/updates/{$page} HTTP/1.0\r\n";
            $request .= "User-Agent: BitrixSMUpdater\r\n";
            $request .= "Accept: */*\r\n";
            $request .= "Host: {$requestIP}\r\n";
            $request .= "Accept-Language: en\r\n";
            $request .= "Content-type: application/x-www-form-urlencoded\r\n";
            $request .= "Content-length: " . strlen($str) . "\r\n\r\n";
            $request .= $str;
            $request .= "\r\n";

            fputs($fp, $request);

            // Читаем заголовки
            $bChunked = false;
            while (!feof($fp)) {
                $line = fgets($fp, 4096);
                if ($line === "\r\n") {
                    break;
                }
                if (preg_match("/Transfer-Encoding: +chunked/i", $line)) {
                    $bChunked = true;
                }
            }

            // Читаем содержимое
            $content = '';
            if ($bChunked) {
                $content = self::readChunkedResponse($fp);
            } else {
                while (!feof($fp)) {
                    $content .= fread($fp, 4096);
                }
            }

            fclose($fp);

            self::debugLog('Response length', strlen($content));

            return $content;
        } catch (\Throwable $e) {
            self::debugLog('Request exception', $e->getMessage());
            return false;
        }
    }

    /**
     * Читает chunked ответ
     */
    private static function readChunkedResponse($fp): string
    {
        $content = '';
        $maxReadSize = 4096;

        $line = fgets($fp, $maxReadSize);
        $line = strtolower($line);

        $strChunkSize = '';
        $i = 0;
        while ($i < strlen($line) && in_array($line[$i], ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'])) {
            $strChunkSize .= $line[$i];
            $i++;
        }

        $chunkSize = hexdec($strChunkSize);

        while ($chunkSize > 0) {
            $processedSize = 0;
            $readSize = ($chunkSize > $maxReadSize) ? $maxReadSize : $chunkSize;

            while ($readSize > 0 && $data = fread($fp, $readSize)) {
                $content .= $data;
                $processedSize += strlen($data);
                $newSize = $chunkSize - $processedSize;
                $readSize = ($newSize > $maxReadSize) ? $maxReadSize : $newSize;
            }

            fgets($fp, $maxReadSize); // \r\n
            $line = fgets($fp, $maxReadSize);
            $line = strtolower($line);

            $strChunkSize = '';
            $i = 0;
            while ($i < strlen($line) && in_array($line[$i], ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'])) {
                $strChunkSize .= $line[$i];
                $i++;
            }

            $chunkSize = hexdec($strChunkSize);
        }

        return $content;
    }

    /**
     * Парсит ответ от прямого запроса
     */
    private static function parseDirectResponse(array $arrInfoKey): array
    {
        $keyInfo = [
            'NAME' => 'N/A',
            'SUPPORT_PERIOD' => 'N/A',
            'EDITION' => 'N/A',
            'DATE_FROM' => 'N/A',
            'DATE_TO' => 'N/A',
            'MODULES' => [],
            'raw' => $arrInfoKey
        ];

        // Парсим информацию о клиенте
        if (isset($arrInfoKey['DATA']['#']['CLIENT'][0]['@'])) {
            $clientAttr = $arrInfoKey['DATA']['#']['CLIENT'][0]['@'];

            $keyInfo['NAME'] = $clientAttr['NAME'] ?? 'N/A';
            $keyInfo['DATE_FROM'] = $clientAttr['DATE_FROM'] ?? 'N/A';
            $keyInfo['DATE_TO'] = $clientAttr['DATE_TO'] ?? 'N/A';
            $keyInfo['SUPPORT_PERIOD'] = $clientAttr['DATE_TO'] ?? 'N/A';
            $keyInfo['EDITION'] = self::detectEdition($clientAttr);
        }

        // Парсим модули
        if (isset($arrInfoKey['DATA']['#']['MODULE']) && is_array($arrInfoKey['DATA']['#']['MODULE'])) {
            foreach ($arrInfoKey['DATA']['#']['MODULE'] as $moduleData) {
                if (!isset($moduleData['@']['ID'])) {
                    continue;
                }

                $moduleId = $moduleData['@']['ID'];
                $moduleAttr = $moduleData['@'];

                $moduleInfo = [
                    'ID' => $moduleId,
                    'NAME' => $moduleAttr['NAME'] ?? $moduleId,
                    'DATE_FROM' => $moduleAttr['DATE_FROM'] ?? '',
                    'DATE_TO' => $moduleAttr['DATE_TO'] ?? '',
                    'UPDATE_END' => $moduleAttr['UPDATE_END'] ?? 'N',
                    'KEY' => 'Y',
                    'VERSIONS' => []
                ];

                // Парсим версии модуля
                if (!empty($moduleData['#']['VERSION']) && is_array($moduleData['#']['VERSION'])) {
                    foreach ($moduleData['#']['VERSION'] as $versionData) {
                        if (!isset($versionData['@']['ID'])) {
                            continue;
                        }

                        $verId = $versionData['@']['ID'];
                        $moduleInfo['VERSIONS'][$verId] = [
                            'ID' => $moduleId,
                            'UPDATE_VERSION' => $verId,
                            'DESC' => $versionData['#']['DESCRIPTION'][0]['#'] ?? ''
                        ];
                    }
                }

                $keyInfo['MODULES'][$moduleId] = $moduleInfo;
            }
        }

        self::debugLog('Parsed modules count', count($keyInfo['MODULES']));

        return $keyInfo;
    }

    /**
     * Получение списка текущих модулей
     *
     * @return array{success: bool, data?: array, error?: string}
     */
    public static function getCurrentModules(): array
    {
        try {
            self::ensureMainModule();

            $strError = '';
            $result = \CUpdateClientPartner::GetCurrentModules($strError);

            if ($strError !== '') {
                return self::errorResponse($strError);
            }

            return self::successResponse($result);
        } catch (\Throwable $e) {
            self::logError('getCurrentModules', $e->getMessage());
            return self::errorResponse($e->getMessage());
        }
    }

    /**
     * Получение списка доступных обновлений (через стандартный API)
     *
     * @param string $key Лицензионный ключ
     * @return array{success: bool, data?: array, error?: string}
     */
    public static function getUpdatesList(string $key): array
    {
        try {
            self::ensureMainModule();
            self::validateLicenseKey($key);

            $strError = '';
            $hashedKey = md5($key);
            $result = \CUpdateClientPartner::GetUpdatesList(
                $strError,
                LANGUAGE_ID,
                'Y',
                [],
                ['LICENSE_KEY' => $hashedKey]
            );

            if ($result === false || $strError !== '') {
                return self::errorResponse($strError ?: 'Ошибка получения списка обновлений');
            }

            return self::successResponse($result);
        } catch (\Throwable $e) {
            self::logError('getUpdatesList', $e->getMessage());
            return self::errorResponse($e->getMessage());
        }
    }

    /**
     * Поиск модулей
     *
     * @param string $searchQuery Поисковый запрос
     * @return array{success: bool, data?: array, error?: string}
     */
    public static function searchModules(string $searchQuery): array
    {
        try {
            self::ensureMainModule();

            $result = \CUpdateClientPartner::SearchModules($searchQuery, LANGUAGE_ID);

            if ($result === false) {
                return self::errorResponse('Ошибка поиска модулей');
            }

            return self::successResponse($result);
        } catch (\Throwable $e) {
            self::logError('searchModules', $e->getMessage());
            return self::errorResponse($e->getMessage());
        }
    }

    /**
     * Загрузка обновлений модулей
     *
     * @param string $key Лицензионный ключ
     * @param array $requestedModules Запрашиваемые модули
     * @return array{success: bool, data?: array, error?: string}
     */
    public static function loadModulesUpdates(string $key, array $requestedModules = []): array
    {
        try {
            self::ensureMainModule();
            self::validateLicenseKey($key);

            $strError = '';
            $arUpdateDescription = [];

            $result = \CUpdateClientPartner::LoadModulesUpdates(
                $strError,
                $arUpdateDescription,
                LANGUAGE_ID,
                'Y',
                $requestedModules,
                false
            );

            if ($result === false || $strError !== '') {
                return self::errorResponse($strError ?: 'Ошибка загрузки обновлений');
            }

            return self::successResponse([
                'result' => $result,
                'description' => $arUpdateDescription
            ]);
        } catch (\Throwable $e) {
            self::logError('loadModulesUpdates', $e->getMessage());
            return self::errorResponse($e->getMessage());
        }
    }

    /**
     * Проверяет и подключает модуль main и класс CUpdateClientPartner
     *
     * @throws \RuntimeException
     */
    private static function ensureMainModule(): void
    {
        if (!\CModule::IncludeModule('main')) {
            throw new \RuntimeException('Не удалось подключить модуль main');
        }

        // Подключаем класс CUpdateClientPartner если он ещё не загружен
        if (!class_exists('CUpdateClientPartner', false)) {
            $updateClientPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/update_client_partner.php';
            if (file_exists($updateClientPath)) {
                require_once($updateClientPath);
            } else {
                throw new \RuntimeException('Файл CUpdateClientPartner не найден');
            }
        }
    }

    /**
     * Определяет редакцию продукта Bitrix по атрибутам клиента
     *
     * @param array $clientAttr Атрибуты клиента из API
     * @return string Название редакции
     */
    private static function detectEdition(array $clientAttr): string
    {
        if (!empty($clientAttr['LICENSE'])) {
            return $clientAttr['LICENSE'];
        }

        $maxSites = (int)($clientAttr['MAX_SITES'] ?? 0);
        $maxUsers = (int)($clientAttr['MAX_USERS'] ?? 0);
        $encType = $clientAttr['ENC_TYPE'] ?? '';

        if ($encType === 'D') {
            return 'Демо-версия';
        }

        if ($maxSites === 2 && $maxUsers > 0 && $maxUsers <= 5) {
            return 'Старт';
        } elseif ($maxSites === 2) {
            return 'Стандарт';
        } elseif ($maxSites === 0 && $maxUsers === 0) {
            if ($encType === 'F') {
                return 'Бизнес';
            } elseif ($encType === 'E') {
                return 'Энтерпрайз';
            }
            return 'Бизнес';
        } elseif ($maxUsers > 0) {
            return 'Малый бизнес';
        }

        return 'Неизвестно';
    }

    /**
     * Валидация лицензионного ключа
     *
     * @param string $key
     * @throws \InvalidArgumentException
     */
    private static function validateLicenseKey(string $key): void
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Лицензионный ключ не может быть пустым');
        }

        if (strlen($key) < 23) {
            throw new \InvalidArgumentException('Лицензионный ключ слишком короткий');
        }

        if (!preg_match('/^[A-Za-z0-9\-]+$/', $key)) {
            throw new \InvalidArgumentException('Лицензионный ключ содержит недопустимые символы');
        }
    }

    /**
     * Формирует успешный ответ
     *
     * @param array $data
     * @return array
     */
    private static function successResponse(array $data): array
    {
        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * Формирует ответ с ошибкой
     *
     * @param string $error
     * @return array
     */
    private static function errorResponse(string $error): array
    {
        return [
            'success' => false,
            'error' => $error
        ];
    }

    /**
     * Логирует ошибку
     *
     * @param string $method
     * @param string $message
     */
    private static function logError(string $method, string $message): void
    {
        if (class_exists('\Bitrix\Main\Diag\Debug')) {
            \Bitrix\Main\Diag\Debug::writeToFile(
                date('Y-m-d H:i:s') . " [BitrixAPI::{$method}] {$message}",
                '',
                'itnull_updater.log'
            );
        }
    }
}
