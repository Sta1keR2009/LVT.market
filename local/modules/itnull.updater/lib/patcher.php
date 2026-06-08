<?php

declare(strict_types=1);

namespace Itnull\Updater;

use Bitrix\Main\Config\Option;

/**
 * Класс для патчинга системных файлов
 * Исключает скрытые модули из запросов к серверу обновлений Bitrix
 *
 * @package Itnull\Updater
 */
class Patcher
{
    /** @var string Маркер начала патча */
    private const PATCH_START = '/*ITNULL_UPDATER_PATCH_START*/';

    /** @var string Маркер конца патча */
    private const PATCH_END = '/*ITNULL_UPDATER_PATCH_END*/';

    /** @var string ID модуля */
    private const MODULE_ID = 'itnull.updater';

    /** @var array Файлы для патчинга */
    private const FILES_TO_PATCH = [
        '/bitrix/modules/main/classes/general/update_client_partner.php',
        '/bitrix/modules/main/classes/general/update_client.php'
    ];

    /**
     * Запись отладочной информации
     */
    private static function debugLog(string $message, $data = null): void
    {
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/itnull_updater_debug.log';
        $logMessage = date('Y-m-d H:i:s') . ' [Patcher] ' . $message;
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
     * Патчит системные файлы при установке модуля
     *
     * @return bool
     */
    public static function installPatch(): bool
    {
        try {
            self::debugLog('=== installPatch START ===');

            $documentRoot = self::getDocumentRoot();

            // Патчим update_client_partner.php
            $partnerPath = $documentRoot . self::FILES_TO_PATCH[0];
            if (file_exists($partnerPath)) {
                self::createBackup($partnerPath);
                // Удаляем старый патч если есть и добавляем новый
                self::removePatchFromFile($partnerPath);
                $result = self::patchUpdateClientPartner($partnerPath);
                self::debugLog('update_client_partner.php patched', $result);
            }

            // Патчим update_client.php
            $clientPath = $documentRoot . self::FILES_TO_PATCH[1];
            if (file_exists($clientPath)) {
                self::createBackup($clientPath);
                // Удаляем старый патч если есть и добавляем новый
                self::removePatchFromFile($clientPath);
                $result = self::patchUpdateClient($clientPath);
                self::debugLog('update_client.php patched', $result);
            }

            self::debugLog('=== installPatch END ===');
            return true;
        } catch (\Throwable $e) {
            self::debugLog('installPatch ERROR', $e->getMessage());
            self::logError('installPatch', $e->getMessage());
            return false;
        }
    }

    /**
     * Удаляет патчи из системных файлов при удалении модуля
     *
     * @return bool
     */
    public static function uninstallPatch(): bool
    {
        try {
            self::debugLog('=== uninstallPatch START ===');
            $documentRoot = self::getDocumentRoot();

            foreach (self::FILES_TO_PATCH as $relativePath) {
                $filePath = $documentRoot . $relativePath;

                if (!file_exists($filePath)) {
                    continue;
                }

                // Восстанавливаем из бекапа если есть
                $backupPath = $filePath . '.itnull_backup';
                if (file_exists($backupPath)) {
                    copy($backupPath, $filePath);
                    @unlink($backupPath);
                    self::debugLog('Restored from backup', $filePath);
                } else {
                    // Удаляем патч вручную
                    self::removePatchFromFile($filePath);
                    self::debugLog('Patch removed manually', $filePath);
                }
            }

            self::debugLog('=== uninstallPatch END ===');
            return true;
        } catch (\Throwable $e) {
            self::debugLog('uninstallPatch ERROR', $e->getMessage());
            self::logError('uninstallPatch', $e->getMessage());
            return false;
        }
    }

    /**
     * Обновляет патчи (при изменении настроек hidden_modules)
     *
     * @return bool
     */
    public static function updatePatch(): bool
    {
        return self::installPatch();
    }

    /**
     * Генерирует PHP-код для исключения модулей из массива
     * Читает список модулей напрямую из настроек
     *
     * @return string
     */
    private static function generatePatchCode(): string
    {
        // Код который будет читать настройки напрямую и удалять модули из массива
        $code = '
// ITNULL Updater: Исключение скрытых модулей из запроса к серверу обновлений
if (class_exists("\Bitrix\Main\Config\Option")) {
    $_itnull_hidden = \Bitrix\Main\Config\Option::get("itnull.updater", "hidden_modules", "");
    if (!empty($_itnull_hidden)) {
        $_itnull_arr = array_filter(array_map("trim", explode("\n", $_itnull_hidden)));
        foreach ($_itnull_arr as $_itnull_mod) {
            if (isset($arClientModules[$_itnull_mod])) {
                unset($arClientModules[$_itnull_mod]);
            }
        }
        unset($_itnull_arr, $_itnull_mod, $_itnull_hidden);
    }
}
';
        return $code;
    }

    /**
     * Патчит файл update_client_partner.php
     * Добавляет код исключения модулей перед возвратом $arClientModules
     */
    private static function patchUpdateClientPartner(string $filePath): bool
    {
        $content = file_get_contents($filePath);

        // Ищем: return $arClientModules;
        $pattern = '/return\s+\$arClientModules\s*;/';

        $patchCode = self::generatePatchCode();
        $replacement = self::PATCH_START . $patchCode . self::PATCH_END . 'return $arClientModules;';

        $newContent = preg_replace($pattern, $replacement, $content, 1, $count);

        if ($count === 0) {
            self::debugLog('Pattern not found in update_client_partner.php');
            return false;
        }

        return file_put_contents($filePath, $newContent) !== false;
    }

    /**
     * Патчит файл update_client.php
     * Нужно найти имя переменной и добавить код исключения
     */
    private static function patchUpdateClient(string $filePath): bool
    {
        $content = file_get_contents($filePath);

        // Ищем имя переменной - может быть разным в разных версиях
        preg_match('/function\s+GetCurrentModules\s*\([^)]*\)[^{]*\{/s', $content, $funcMatch, PREG_OFFSET_CAPTURE);

        $varName = 'arClientModules'; // По умолчанию

        if (!empty($funcMatch)) {
            // Ищем return в этой функции
            $funcStart = $funcMatch[0][1];
            $subContent = substr($content, $funcStart, 3000);

            // Ищем return $varName;
            if (preg_match('/return\s+\$(\w+)\s*;/', $subContent, $varMatch)) {
                $varName = $varMatch[1];
            }
        }

        self::debugLog('Variable name in update_client.php', $varName);

        // Генерируем код патча с правильным именем переменной
        $patchCode = '
// ITNULL Updater: Исключение скрытых модулей из запроса к серверу обновлений
if (class_exists("\Bitrix\Main\Config\Option")) {
    $_itnull_hidden = \Bitrix\Main\Config\Option::get("itnull.updater", "hidden_modules", "");
    if (!empty($_itnull_hidden)) {
        $_itnull_arr = array_filter(array_map("trim", explode("\n", $_itnull_hidden)));
        foreach ($_itnull_arr as $_itnull_mod) {
            if (isset($' . $varName . '[$_itnull_mod])) {
                unset($' . $varName . '[$_itnull_mod]);
            }
        }
        unset($_itnull_arr, $_itnull_mod, $_itnull_hidden);
    }
}
';

        // Патчим
        $pattern = '/return\s+\$' . preg_quote($varName, '/') . '\s*;/';
        $replacement = self::PATCH_START . $patchCode . self::PATCH_END . 'return $' . $varName . ';';

        $newContent = preg_replace($pattern, $replacement, $content, 1, $count);

        if ($count === 0) {
            self::debugLog('Pattern not found in update_client.php');
            return false;
        }

        return file_put_contents($filePath, $newContent) !== false;
    }

    /**
     * Удаляет патч из файла
     */
    private static function removePatchFromFile(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $content = file_get_contents($filePath);

        // Удаляем всё между маркерами (включая сами маркеры)
        $pattern = '/' . preg_quote(self::PATCH_START, '/') . '.*?' . preg_quote(self::PATCH_END, '/') . '/s';
        $newContent = preg_replace($pattern, '', $content);

        if ($newContent !== $content) {
            return file_put_contents($filePath, $newContent) !== false;
        }

        return true;
    }

    /**
     * Получает список исключённых модулей из настроек
     *
     * @return array
     */
    public static function getExcludedModules(): array
    {
        $hiddenModules = Option::get(self::MODULE_ID, 'hidden_modules', '');
        return self::parseModulesList($hiddenModules);
    }

    /**
     * Сохраняет список исключённых модулей в настройки
     *
     * @param array $modules
     * @return bool
     */
    public static function setExcludedModules(array $modules): bool
    {
        try {
            $modulesString = implode("\n", array_filter(array_map('trim', $modules)));
            Option::set(self::MODULE_ID, 'hidden_modules', $modulesString);
            return true;
        } catch (\Throwable $e) {
            self::logError('setExcludedModules', $e->getMessage());
            return false;
        }
    }

    /**
     * Проверяет, исключён ли модуль
     *
     * @param string $moduleId
     * @return bool
     */
    public static function isModuleExcluded(string $moduleId): bool
    {
        $excludedModules = self::getExcludedModules();
        return in_array($moduleId, $excludedModules, true);
    }

    /**
     * Проверяет наличие патча в файле
     *
     * @param string $filePath
     * @return bool
     */
    public static function isPatched(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $content = file_get_contents($filePath);

        return strpos($content, self::PATCH_START) !== false;
    }

    /**
     * Проверяет статус патчей
     *
     * @return array
     */
    public static function checkPatchStatus(): array
    {
        $documentRoot = self::getDocumentRoot();

        return [
            'partner_patched' => self::isPatched($documentRoot . self::FILES_TO_PATCH[0]),
            'client_patched' => self::isPatched($documentRoot . self::FILES_TO_PATCH[1])
        ];
    }

    /**
     * Получает корневую директорию сайта
     *
     * @return string
     */
    private static function getDocumentRoot(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] ?? '';
    }

    /**
     * Создает бекап файла
     *
     * @param string $filePath
     * @return bool
     */
    private static function createBackup(string $filePath): bool
    {
        $backupPath = $filePath . '.itnull_backup';

        if (!file_exists($backupPath)) {
            return copy($filePath, $backupPath);
        }

        return true;
    }

    /**
     * Парсит список модулей из строки
     *
     * @param string $modulesList
     * @return array
     */
    private static function parseModulesList(string $modulesList): array
    {
        return array_filter(
            array_map('trim', explode("\n", $modulesList)),
            fn($item) => !empty($item)
        );
    }

    /**
     * Логирует ошибку
     *
     * @param string $method
     * @param string $message
     * @return void
     */
    private static function logError(string $method, string $message): void
    {
        if (class_exists('\Bitrix\Main\Diag\Debug')) {
            \Bitrix\Main\Diag\Debug::writeToFile(
                date('Y-m-d H:i:s') . " [Patcher::{$method}] {$message}",
                '',
                'itnull_updater.log'
            );
        }
    }
}
