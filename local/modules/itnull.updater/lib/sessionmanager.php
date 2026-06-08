<?php

declare(strict_types=1);

namespace Itnull\Updater;

use Bitrix\Main\Application;

/**
 * Класс для работы с сессиями
 *
 * @package Itnull\Updater
 */
class SessionManager
{
    /** @var string Ключ сессии для модуля */
    private const SESSION_KEY = 'ITNULL_UPDATER';

    /** @var bool Флаг инициализации */
    private static bool $initialized = false;

    /**
     * Инициализация сессии
     *
     * @return void
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        // Используем Bitrix Session если доступна
        if (class_exists('\Bitrix\Main\Application')) {
            try {
                $session = Application::getInstance()->getSession();
                if (!$session->has(self::SESSION_KEY)) {
                    $session->set(self::SESSION_KEY, []);
                }
                self::$initialized = true;
                return;
            } catch (\Throwable $e) {
                // Fallback к стандартной сессии
            }
        }

        // Стандартная PHP сессия
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        self::$initialized = true;
    }

    /**
     * Сохранение лицензионного ключа в сессию
     *
     * @param string $key Лицензионный ключ
     * @return bool
     */
    public static function setKey(string $key): bool
    {
        return self::setValue('LICENSE_KEY', $key);
    }

    /**
     * Получение лицензионного ключа из сессии
     *
     * @return string|null
     */
    public static function getKey(): ?string
    {
        $key = self::getValue('LICENSE_KEY');
        return is_string($key) ? $key : null;
    }

    /**
     * Сохранение информации о ключе в сессию
     *
     * @param array $info Информация о ключе
     * @return bool
     */
    public static function setKeyInfo(array $info): bool
    {
        return self::setValue('KEY_INFO', $info);
    }

    /**
     * Получение информации о ключе из сессии
     *
     * @return array|null
     */
    public static function getKeyInfo(): ?array
    {
        $info = self::getValue('KEY_INFO');
        return is_array($info) ? $info : null;
    }

    /**
     * Сохранение списка модулей в сессию
     *
     * @param array $modules Список модулей
     * @return bool
     */
    public static function setModules(array $modules): bool
    {
        return self::setValue('MODULES', $modules);
    }

    /**
     * Получение списка модулей из сессии
     *
     * @return array|null
     */
    public static function getModules(): ?array
    {
        $modules = self::getValue('MODULES');
        return is_array($modules) ? $modules : null;
    }

    /**
     * Очистка сессии
     *
     * @return bool
     */
    public static function clear(): bool
    {
        try {
            self::init();

            if (class_exists('\Bitrix\Main\Application')) {
                try {
                    $session = Application::getInstance()->getSession();
                    $session->remove(self::SESSION_KEY);
                    self::$initialized = false;
                    return true;
                } catch (\Throwable $e) {
                    // Fallback к стандартной сессии
                }
            }

            unset($_SESSION[self::SESSION_KEY]);
            self::$initialized = false;
            return true;
        } catch (\Throwable $e) {
            self::logError('clear', $e->getMessage());
            return false;
        }
    }

    /**
     * Установка произвольного значения в сессию
     *
     * @param string $key Ключ
     * @param mixed $value Значение
     * @return bool
     */
    public static function setValue(string $key, mixed $value): bool
    {
        try {
            self::init();

            if (class_exists('\Bitrix\Main\Application')) {
                try {
                    $session = Application::getInstance()->getSession();
                    $data = $session->get(self::SESSION_KEY) ?? [];
                    $data[$key] = $value;
                    $session->set(self::SESSION_KEY, $data);
                    return true;
                } catch (\Throwable $e) {
                    // Fallback к стандартной сессии
                }
            }

            $_SESSION[self::SESSION_KEY][$key] = $value;
            return true;
        } catch (\Throwable $e) {
            self::logError('setValue', $e->getMessage());
            return false;
        }
    }

    /**
     * Получение произвольного значения из сессии
     *
     * @param string $key Ключ
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        try {
            self::init();

            if (class_exists('\Bitrix\Main\Application')) {
                try {
                    $session = Application::getInstance()->getSession();
                    $data = $session->get(self::SESSION_KEY) ?? [];
                    return $data[$key] ?? $default;
                } catch (\Throwable $e) {
                    // Fallback к стандартной сессии
                }
            }

            return $_SESSION[self::SESSION_KEY][$key] ?? $default;
        } catch (\Throwable $e) {
            self::logError('getValue', $e->getMessage());
            return $default;
        }
    }

    /**
     * Проверка наличия значения в сессии
     *
     * @param string $key
     * @return bool
     */
    public static function hasValue(string $key): bool
    {
        try {
            self::init();

            if (class_exists('\Bitrix\Main\Application')) {
                try {
                    $session = Application::getInstance()->getSession();
                    $data = $session->get(self::SESSION_KEY) ?? [];
                    return isset($data[$key]);
                } catch (\Throwable $e) {
                    // Fallback
                }
            }

            return isset($_SESSION[self::SESSION_KEY][$key]);
        } catch (\Throwable $e) {
            return false;
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
                date('Y-m-d H:i:s') . " [SessionManager::{$method}] {$message}",
                '',
                'itnull_updater.log'
            );
        }
    }
}
