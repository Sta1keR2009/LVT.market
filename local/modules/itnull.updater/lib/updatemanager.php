<?php

declare(strict_types=1);

namespace Itnull\Updater;

/**
 * Класс для скачивания и установки обновлений
 *
 * @package Itnull\Updater
 */
class UpdateManager
{
    /** @var string Путь к директории обновлений */
    private const UPDATES_DIR = '/bitrix/updates/';

    /** @var array Поддерживаемые форматы архивов */
    private const SUPPORTED_ARCHIVES = ['gz', 'zip', 'tar'];

    /**
     * Проверка возможности скачивания модуля
     *
     * @param string $moduleId ID модуля
     * @return bool
     */
    public static function canDownload(string $moduleId): bool
    {
        return !empty($moduleId);
    }

    /**
     * Проверка возможности установки модуля
     *
     * @param string $moduleId ID модуля
     * @param string $version Версия
     * @return bool
     */
    public static function canInstall(string $moduleId, string $version = ''): bool
    {
        try {
            if (empty($moduleId)) {
                return false;
            }

            $updateDir = self::getUpdatesDirectory();

            if (!is_dir($updateDir)) {
                return false;
            }

            $files = scandir($updateDir);
            if ($files === false) {
                return false;
            }

            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $filePath = $updateDir . $file;

                if (!is_file($filePath)) {
                    continue;
                }

                $moduleName = ModuleList::parseUpdateFileName($file);

                if (strpos($file, $moduleId) !== false || $moduleName === $moduleId) {
                    if (empty($version) || strpos($file, $version) !== false) {
                        return true;
                    }
                }
            }

            return false;
        } catch (\Throwable $e) {
            self::logError('canInstall', $e->getMessage());
            return false;
        }
    }

    /**
     * Скачивание обновления модуля
     *
     * @param string $moduleId ID модуля
     * @param string $type Тип (mod - полный модуль, delta - патч)
     * @param string $version Версия
     * @param string $prevVersion Предыдущая версия (для дельта-обновлений)
     * @return array{success: bool, file_path?: string, file_name?: string, message: string, error?: string}
     */
    public static function downloadUpdate(
        string $moduleId,
        string $type,
        string $version,
        string $prevVersion = ''
    ): array {
        try {
            self::validateDownloadParams($moduleId, $type, $version);
            self::ensureMainModule();

            // Получаем ключ из сессии
            $key = SessionManager::getKey();
            if (empty($key)) {
                return [
                    'success' => false,
                    'error' => 'Лицензионный ключ не найден в сессии',
                    'message' => 'Ошибка: необходимо ввести лицензионный ключ'
                ];
            }

            $updateDir = self::getUpdatesDirectory();

            // Создаём директорию если не существует
            if (!is_dir($updateDir)) {
                if (!mkdir($updateDir, 0755, true) && !is_dir($updateDir)) {
                    return [
                        'success' => false,
                        'error' => 'Не удалось создать директорию для обновлений',
                        'message' => 'Ошибка: не удалось создать директорию /bitrix/updates/'
                    ];
                }
            }

            // Сначала получаем имя файла для скачивания
            $fileInfo = self::getModuleFileInfo($moduleId, $type, $prevVersion, $key);
            if (!$fileInfo['success']) {
                return $fileInfo;
            }

            $ufile = $fileInfo['file_name'];
            $ufileSize = $fileInfo['file_size'];

            // Проверяем, не скачан ли уже файл
            if (self::isFileAlreadyDownloaded($moduleId, $type, $version, $ufileSize)) {
                return [
                    'success' => true,
                    'file_path' => $updateDir,
                    'file_name' => $moduleId . '.' . $version . '.' . $type . '.upd',
                    'message' => 'Файл уже был скачан ранее'
                ];
            }

            // Скачиваем файл
            $downloadResult = self::downloadModuleFile($moduleId, $type, $version, $prevVersion, $ufile, $key);
            if (!$downloadResult['success']) {
                return $downloadResult;
            }

            $downloadedFile = $downloadResult['file_path'];

            // Проверяем размер скачанного файла
            if (filesize($downloadedFile) != $ufileSize) {
                @unlink($downloadedFile);
                return [
                    'success' => false,
                    'error' => 'Размер скачанного файла не совпадает',
                    'message' => 'Ошибка при скачивании: файл повреждён'
                ];
            }

            // Для типа mod ищем версию в файле и переименовываем
            if ($type === 'mod') {
                $actualVersion = self::extractVersionFromFile($downloadedFile);
                if ($actualVersion) {
                    $newFileName = $moduleId . '.' . $actualVersion . '.mod.upd';
                    $newFilePath = $updateDir . $newFileName;
                    rename($downloadedFile, $newFilePath);
                    $downloadedFile = $newFilePath;
                }
            }

            return [
                'success' => true,
                'file_path' => $downloadedFile,
                'file_name' => basename($downloadedFile),
                'message' => 'Обновление успешно скачано'
            ];
        } catch (\Throwable $e) {
            self::logError('downloadUpdate', $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Ошибка при скачивании обновления'
            ];
        }
    }

    /**
     * Получение информации о файле модуля для скачивания
     */
    private static function getModuleFileInfo(string $moduleId, string $type, string $prevVersion, string $key): array
    {
        $strError = '';
        $hashedKey = md5($key);

        // Логирование для отладки
        $debugLog = function($message, $data = null) {
            $logFile = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/itnull_updater_download.log';
            $logMessage = date('Y-m-d H:i:s') . ' ' . $message;
            if ($data !== null) {
                $logMessage .= ' | ' . print_r($data, true);
            }
            file_put_contents($logFile, $logMessage . "\n", FILE_APPEND);
        };

        $debugLog('getModuleFileInfo called', [
            'moduleId' => $moduleId,
            'type' => $type,
            'prevVersion' => $prevVersion
        ]);

        // Базовые параметры запроса (как в старой версии)
        $baseParams = "utf=Y&lang=ru&stable=Y&CANGZIP=N&SUPD_DBS=MYSQL&XE=N"
            . "&LICENSE_KEY=" . $hashedKey
            . "&SUPD_STS=1&SUPD_URS=0&SUPD_URSA=1&TYPENC=E"
            . "&CLIENT_PHPVER=" . phpversion()
            . "&dbv=5.5.41";

        // Формируем запрос для получения информации о файле
        if ($type === 'mod') {
            $str = $baseParams
                . "&reqm=" . $moduleId
                . "&lim=Y&SUPD_SRS=RU&SUPD_CMP=N&product=BSM&verfix=2";
        } else {
            $str = $baseParams
                . "&instm=" . $moduleId . "%2C" . $prevVersion . "%2CN"
                . "&reqm=" . $moduleId
                . "&lim=Y&SUPD_SRS=RU&SUPD_CMP=N&product=BSM&verfix=2";
        }

        $debugLog('Request string', $str);

        $response = self::sendBitrixRequest('smp_updater_modules.php', $str);

        $debugLog('Response received', [
            'response_length' => $response !== false ? strlen($response) : 'false',
            'response_preview' => $response !== false ? substr($response, 0, 500) : 'false'
        ]);

        if ($response === false) {
            return [
                'success' => false,
                'error' => 'Ошибка связи с сервером Bitrix',
                'message' => 'Не удалось получить информацию о файле'
            ];
        }

        // Парсим ответ
        $arrInfo = [];
        \CUpdateClientPartner::__ParseServerData($response, $arrInfo, $strError);

        $debugLog('Parsed response', [
            'strError' => $strError,
            'arrInfo_keys' => array_keys($arrInfo),
            'has_DATA' => isset($arrInfo['DATA']),
            'has_FILE' => isset($arrInfo['DATA']['#']['FILE']),
            'arrInfo' => $arrInfo
        ]);

        // Проверяем наличие ошибки от сервера Bitrix
        if (isset($arrInfo['DATA']['#']['ERROR'][0]['#'])) {
            $serverError = $arrInfo['DATA']['#']['ERROR'][0]['#'];
            $errorType = $arrInfo['DATA']['#']['ERROR'][0]['@']['TYPE'] ?? '';
            return [
                'success' => false,
                'error' => $serverError,
                'message' => $serverError,
                'error_type' => $errorType
            ];
        }

        if (!empty($strError) || !isset($arrInfo['DATA']['#']['FILE'][0]['@']['NAME'])) {
            return [
                'success' => false,
                'error' => $strError ?: 'Файл обновления не найден',
                'message' => 'Не удалось получить информацию о файле'
            ];
        }

        return [
            'success' => true,
            'file_name' => $arrInfo['DATA']['#']['FILE'][0]['@']['NAME'],
            'file_size' => (int)($arrInfo['DATA']['#']['FILE'][0]['@']['SIZE'] ?? 0)
        ];
    }

    /**
     * Скачивание файла модуля
     */
    private static function downloadModuleFile(
        string $moduleId,
        string $type,
        string $version,
        string $prevVersion,
        string $ufile,
        string $key
    ): array {
        $hashedKey = md5($key);
        $updateDir = self::getUpdatesDirectory();

        // Базовые параметры запроса (как в старой версии)
        $baseParams = "utf=Y&lang=ru&stable=Y&CANGZIP=N&SUPD_DBS=MYSQL&XE=N"
            . "&LICENSE_KEY=" . $hashedKey
            . "&SUPD_STS=1&SUPD_URS=0&SUPD_URSA=1&TYPENC=E"
            . "&CLIENT_PHPVER=" . phpversion()
            . "&dbv=5.5.41";

        // Формируем запрос на скачивание
        if ($type === 'mod') {
            $str = $baseParams
                . "&reqm=" . $moduleId
                . "&lim=Y&SUPD_SRS=RU&SUPD_CMP=N&UFILE=" . $ufile
                . "&USTART=0&product=BSM&verfix=2";
            $fileName = $moduleId . '.0.0.0.mod.upd';
        } else {
            $str = $baseParams
                . "&instm=" . $moduleId . "%2C" . $prevVersion . "%2CN"
                . "&reqm=" . $moduleId
                . "&lim=Y&SUPD_SRS=RU&SUPD_CMP=N&UFILE=" . $ufile
                . "&USTART=0&product=BSM&verfix=2";
            $fileName = $moduleId . '.' . $version . '.' . $type . '.upd';
        }

        $filePath = $updateDir . $fileName;

        $result = self::sendBitrixRequestToFile('smp_updater_modules.php', $str, $filePath);
        if (!$result) {
            return [
                'success' => false,
                'error' => 'Не удалось скачать файл',
                'message' => 'Ошибка при скачивании файла модуля'
            ];
        }

        return [
            'success' => true,
            'file_path' => $filePath
        ];
    }

    /**
     * Проверка, был ли файл уже скачан
     */
    private static function isFileAlreadyDownloaded(
        string $moduleId,
        string $type,
        string $version,
        int $expectedSize
    ): bool {
        $updateDir = self::getUpdatesDirectory();
        $files = @scandir($updateDir);

        if ($files === false) {
            return false;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $updateDir . $file;
            if (!is_file($filePath)) {
                continue;
            }

            // Проверяем соответствие файла модулю, типу и версии
            $parsed = ModuleList::parseUpdateFileName($file, true);
            if ($parsed['module_id'] === $moduleId && $parsed['type'] === $type) {
                if ($type === 'mod' || $parsed['version'] === $version) {
                    if (filesize($filePath) === $expectedSize) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Извлечение версии из скачанного файла модуля
     */
    private static function extractVersionFromFile(string $filePath): ?string
    {
        $handle = @fopen($filePath, 'r');
        if (!$handle) {
            return null;
        }

        $version = null;
        while (($line = fgets($handle, 4096)) !== false) {
            if (preg_match('/VERSION\s*[^0-9]*=>\s*[^0-9]*(\d+\.\d+\.\d+)/', $line, $matches)) {
                $version = $matches[1];
                break;
            }
        }

        fclose($handle);
        return $version;
    }

    /**
     * Отправка запроса на сервер Bitrix
     */
    private static function sendBitrixRequest(string $page, string $postData): ?string
    {
        $requestIP = \COption::GetOptionString('main', 'update_site', 'www.1c-bitrix.ru');
        $requestPort = 80;

        $fp = @fsockopen($requestIP, $requestPort, $errno, $errstr, 120);
        if (!$fp) {
            return null;
        }

        $request = "POST /bitrix/updates/" . $page . " HTTP/1.0\r\n";
        $request .= "User-Agent: BitrixSMUpdater\r\n";
        $request .= "Accept: */*\r\n";
        $request .= "Host: " . $requestIP . "\r\n";
        $request .= "Accept-Language: en\r\n";
        $request .= "Content-type: application/x-www-form-urlencoded\r\n";
        $request .= "Content-length: " . strlen($postData) . "\r\n\r\n";
        $request .= $postData . "\r\n";

        fputs($fp, $request);

        // Пропускаем заголовки
        while (!feof($fp)) {
            $line = fgets($fp, 4096);
            if ($line === "\r\n") {
                break;
            }
        }

        // Читаем тело ответа
        $content = '';
        while (!feof($fp)) {
            $content .= fread($fp, 4096);
        }

        fclose($fp);
        return $content;
    }

    /**
     * Отправка запроса на сервер Bitrix с сохранением в файл
     */
    private static function sendBitrixRequestToFile(string $page, string $postData, string $filePath): bool
    {
        $requestIP = \COption::GetOptionString('main', 'update_site', 'www.1c-bitrix.ru');
        $requestPort = 80;

        $fp = @fsockopen($requestIP, $requestPort, $errno, $errstr, 120);
        if (!$fp) {
            return false;
        }

        $request = "POST /bitrix/updates/" . $page . " HTTP/1.0\r\n";
        $request .= "User-Agent: BitrixSMUpdater\r\n";
        $request .= "Accept: */*\r\n";
        $request .= "Host: " . $requestIP . "\r\n";
        $request .= "Accept-Language: en\r\n";
        $request .= "Content-type: application/x-www-form-urlencoded\r\n";
        $request .= "Content-length: " . strlen($postData) . "\r\n\r\n";
        $request .= $postData . "\r\n";

        fputs($fp, $request);

        // Пропускаем заголовки
        while (!feof($fp)) {
            $line = fgets($fp, 4096);
            if ($line === "\r\n") {
                break;
            }
        }

        // Сохраняем тело в файл
        $file = fopen($filePath, 'w');
        if (!$file) {
            fclose($fp);
            return false;
        }

        while (!feof($fp)) {
            $data = fread($fp, 4096);
            fwrite($file, $data);
        }

        fclose($file);
        fclose($fp);
        return true;
    }

    /**
     * Установка обновления из файла .upd
     *
     * @param string $filePath Путь к файлу обновления (.upd)
     * @return array{success: bool, message: string, error?: string}
     */
    public static function installUpdate(string $filePath): array
    {
        try {
            self::ensureMainModule();

            // Если передана директория, используем старую логику
            if (is_dir($filePath)) {
                return self::installFromDirectory($filePath);
            }

            // Если передан файл, сначала распаковываем
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'error' => 'Файл обновления не найден: ' . $filePath,
                    'message' => 'Ошибка: файл обновления не найден'
                ];
            }

            // Распаковываем .upd файл
            $unarchResult = self::unarchUpd($filePath);
            if (!$unarchResult['success']) {
                return $unarchResult;
            }

            // Используем имя папки (не полный путь) - как в старой версии
            $updatesDirName = $unarchResult['updates_dir_name'];

            // Устанавливаем обновление
            $strError = '';
            $result = \CUpdateClientPartner::UpdateStepModules($updatesDirName, $strError);

            if ($result === false || $strError !== '') {
                return [
                    'success' => false,
                    'message' => 'Ошибка при установке обновления',
                    'error' => $strError ?: 'UpdateStepModules вернул false'
                ];
            }

            return [
                'success' => true,
                'message' => 'Обновление успешно установлено'
            ];
        } catch (\Throwable $e) {
            self::logError('installUpdate', $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Ошибка при установке обновления'
            ];
        }
    }

    /**
     * Установка из директории
     */
    private static function installFromDirectory(string $updatesDir): array
    {
        // Если передан полный путь, извлекаем только имя папки
        $updatesDirName = basename($updatesDir);

        $strError = '';
        $result = \CUpdateClientPartner::UpdateStepModules($updatesDirName, $strError);

        if ($result === false || $strError !== '') {
            return [
                'success' => false,
                'message' => 'Ошибка при установке обновления',
                'error' => $strError ?: 'UpdateStepModules вернул false'
            ];
        }

        return [
            'success' => true,
            'message' => 'Обновление успешно установлено'
        ];
    }

    /**
     * Распаковка .upd файла (формат Bitrix)
     *
     * @param string $filePath Путь к .upd файлу
     * @return array{success: bool, updates_dir?: string, updates_dir_name?: string, message: string, error?: string}
     */
    public static function unarchUpd(string $filePath): array
    {
        try {
            $updateDir = self::getUpdatesDirectory();
            $fileName = basename($filePath);
            $updatesDirName = 'update_' . $fileName; // Только имя папки для UpdateStepModules
            $updatesDir = $updateDir . $updatesDirName; // Полный путь для распаковки

            $f = fopen($filePath, 'r');
            if (!$f) {
                return [
                    'success' => false,
                    'error' => 'Не удалось открыть файл',
                    'message' => 'Ошибка при распаковке: не удалось открыть файл'
                ];
            }

            // Проверяем заголовок BITRIX
            $flabel = fread($f, strlen('BITRIX'));
            if ($flabel !== 'BITRIX') {
                fclose($f);
                return [
                    'success' => false,
                    'error' => 'Неверный формат файла: отсутствует заголовок BITRIX',
                    'message' => 'Ошибка: неверный формат файла обновления'
                ];
            }

            while (true) {
                $addInfoSize = fread($f, 5);
                $addInfoSize = trim($addInfoSize);

                if ((int)$addInfoSize > 0 && (int)$addInfoSize . '!' === $addInfoSize . '!') {
                    $addInfoSize = (int)$addInfoSize;
                } else {
                    break;
                }

                $addInfo = fread($f, $addInfoSize);
                $addInfoArr = explode('|', $addInfo);

                if (count($addInfoArr) !== 3) {
                    break;
                }

                $size = (int)$addInfoArr[0];
                $curpath = $addInfoArr[1];
                $crc32 = $addInfoArr[2];

                $contents = '';
                if ($size > 0) {
                    $contents = fread($f, $size);
                }

                // Проверяем контрольную сумму
                $crc32New = dechex(crc32($contents));
                if ($crc32New !== $crc32) {
                    break;
                }

                // Создаём директории и записываем файл
                self::checkDir($updatesDir . $curpath, true);

                $fp1 = fopen($updatesDir . $curpath, 'wb');
                if (!$fp1) {
                    break;
                }

                if (strlen($contents) > 0 && !fwrite($fp1, $contents)) {
                    fclose($fp1);
                    break;
                }
                fclose($fp1);

                // Проверяем записанный файл
                $crc32New = dechex(crc32(file_get_contents($updatesDir . $curpath)));
                if ($crc32New !== $crc32) {
                    break;
                }
            }

            fclose($f);

            return [
                'success' => true,
                'updates_dir' => $updatesDir,
                'updates_dir_name' => $updatesDirName, // Имя папки для UpdateStepModules
                'message' => 'Файл успешно распакован'
            ];
        } catch (\Throwable $e) {
            self::logError('unarchUpd', $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Ошибка при распаковке файла'
            ];
        }
    }

    /**
     * Создание пути если его нет
     */
    private static function checkDir(string $path, bool $bPermission = true): void
    {
        $badDirs = [];
        $path = str_replace('\\', '/', $path);
        $path = str_replace('//', '/', $path);

        // Отрезаем имя файла
        if ($path[strlen($path) - 1] !== '/') {
            $p = self::strrposbx($path, '/');
            $path = substr($path, 0, $p);
        }

        // Отрезаем / в конце
        while (strlen($path) > 1 && $path[strlen($path) - 1] === '/') {
            $path = substr($path, 0, strlen($path) - 1);
        }

        $p = self::strrposbx($path, '/');
        while ($p > 0) {
            if (file_exists($path) && is_dir($path)) {
                if ($bPermission && !is_writable($path)) {
                    @chmod($path, BX_DIR_PERMISSIONS);
                }
                break;
            }
            $badDirs[] = substr($path, $p + 1);
            $path = substr($path, 0, $p);
            $p = self::strrposbx($path, '/');
        }

        for ($i = count($badDirs) - 1; $i >= 0; $i--) {
            $path = $path . '/' . $badDirs[$i];
            @mkdir($path, BX_DIR_PERMISSIONS);
        }
    }

    /**
     * Поиск последнего вхождения подстроки
     */
    private static function strrposbx(string $haystack, string $needle)
    {
        $index = strpos(strrev($haystack), strrev($needle));
        if ($index === false) {
            return false;
        }
        return strlen($haystack) - strlen($needle) - $index;
    }

    /**
     * Распаковка архива обновления
     *
     * @param string $file Путь к архиву
     * @return array{success: bool, extract_path?: string, message: string, error?: string}
     */
    public static function extractArchive(string $file): array
    {
        try {
            self::validateFilePath($file);

            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $extractPath = dirname($file) . '/' . $filename . '_extracted/';

            self::ensureDirectoryExists($extractPath);

            if ($extension === 'gz' || $extension === 'tar') {
                $phar = new \PharData($file);
                $phar->extractTo($extractPath, null, true);
            } elseif ($extension === 'zip') {
                $zip = new \ZipArchive();
                $res = $zip->open($file);

                if ($res !== true) {
                    throw new \RuntimeException('Не удалось открыть ZIP архив');
                }

                $zip->extractTo($extractPath);
                $zip->close();
            } else {
                throw new \InvalidArgumentException('Неподдерживаемый формат архива: ' . $extension);
            }

            return [
                'success' => true,
                'extract_path' => $extractPath,
                'message' => 'Архив успешно распакован'
            ];
        } catch (\Throwable $e) {
            self::logError('extractArchive', $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Ошибка при распаковке архива'
            ];
        }
    }

    /**
     * Распаковка gz архива через CUpdateClientPartner
     *
     * @return array{success: bool, updates_dir?: string, message: string, error?: string}
     */
    public static function unGzipArchive(): array
    {
        try {
            self::ensureMainModule();

            $strError = '';
            $updatesDir = '';

            $result = \CUpdateClientPartner::UnGzipArchive($updatesDir, $strError, true);

            if ($result === false || $strError !== '') {
                return [
                    'success' => false,
                    'error' => $strError ?: 'Ошибка распаковки архива',
                    'message' => 'Ошибка при распаковке архива'
                ];
            }

            return [
                'success' => true,
                'updates_dir' => $updatesDir,
                'message' => 'Архив успешно распакован'
            ];
        } catch (\Throwable $e) {
            self::logError('unGzipArchive', $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Ошибка при распаковке архива'
            ];
        }
    }

    /**
     * Извлечение ID модуля из имени файла
     *
     * @param string $filename Имя файла
     * @return string
     */
    public static function extractModuleIdFromFilename(string $filename): string
    {
        // Убираем двойные расширения
        $name = preg_replace('/\.(tar\.gz|tar\.bz2|tar|gz|zip)$/i', '', $filename);

        $parts = explode('-', $name);

        if (count($parts) > 1) {
            $lastPart = end($parts);
            if (preg_match('/^\d+(\.\d+)*$/', $lastPart)) {
                array_pop($parts);
                return implode('-', $parts);
            }
        }

        return $name;
    }

    /**
     * Получает директорию обновлений
     *
     * @return string
     */
    private static function getUpdatesDirectory(): string
    {
        return ($_SERVER['DOCUMENT_ROOT'] ?? '') . self::UPDATES_DIR;
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
     * Создает директорию если не существует
     *
     * @param string $path
     * @throws \RuntimeException
     */
    private static function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true) && !is_dir($path)) {
                throw new \RuntimeException('Не удалось создать директорию: ' . $path);
            }
        }
    }

    /**
     * Валидация параметров скачивания
     *
     * @param string $moduleId
     * @param string $type
     * @param string $version
     * @throws \InvalidArgumentException
     */
    private static function validateDownloadParams(string $moduleId, string $type, string $version): void
    {
        if (empty($moduleId)) {
            throw new \InvalidArgumentException('ID модуля не может быть пустым');
        }

        if (!in_array($type, ['mod', 'delta'], true)) {
            throw new \InvalidArgumentException('Недопустимый тип скачивания');
        }

        // Версия обязательна только для delta-обновлений
        if ($type === 'delta' && empty($version)) {
            throw new \InvalidArgumentException('Версия обязательна для delta-обновлений');
        }
    }

    /**
     * Валидация пути к файлу
     *
     * @param string $file
     * @throws \InvalidArgumentException
     */
    private static function validateFilePath(string $file): void
    {
        if (empty($file)) {
            throw new \InvalidArgumentException('Путь к файлу не может быть пустым');
        }

        if (!file_exists($file)) {
            throw new \InvalidArgumentException('Файл не найден: ' . $file);
        }
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
                date('Y-m-d H:i:s') . " [UpdateManager::{$method}] {$message}",
                '',
                'itnull_updater.log'
            );
        }
    }
}
