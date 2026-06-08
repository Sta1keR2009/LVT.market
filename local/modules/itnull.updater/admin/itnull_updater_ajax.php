<?php
// VERSION: 2025-02-01-15:42

// Логирование при загрузке файла
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/itnull_updater_debug.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . ' | FILE LOADED v2025-02-01-15:42' . "\n", FILE_APPEND);

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

global $USER, $APPLICATION;

// Классы подключаются автоматически через autoload.php

// Проверяем сессию
if (!check_bitrix_sessid()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid session']);
    exit;
}

// Проверяем права администратора
if (!$USER->IsAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

try {
    $request = Context::getCurrent()->getRequest();
    $action = $request->getPost('action') ?: $request->get('action');
    
    // Загружаем локализацию
    Loc::loadMessages(__FILE__);
    
    $response = ['success' => false, 'message' => 'Action not found'];
    
    // Функция для записи отладочной информации
    $debugLog = function($message, $data = null) {
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/itnull_updater_debug.log';
        $logMessage = date('Y-m-d H:i:s') . ' | ' . $message;
        if ($data !== null) {
            $logMessage .= ' | ' . print_r($data, true);
        }
        file_put_contents($logFile, $logMessage . "\n", FILE_APPEND);
    };

    switch ($action) {
        case 'load_key_info':
            $key = $request->getPost('key');

            $debugLog('=== START load_key_info ===');
            $debugLog('Key received', substr($key, 0, 10) . '***');

            if (empty($key)) {
                $response = ['success' => false, 'message' => 'License key is required'];
                break;
            }

            // Загружаем необходимые классы
            if (!Loader::includeModule('itnull.updater')) {
                $debugLog('ERROR: Module not loaded');
                $response = ['success' => false, 'message' => 'Module not loaded'];
                break;
            }

            $debugLog('Module loaded successfully');

            // Используем BitrixAPI для получения информации о ключе
            $apiResult = \Itnull\Updater\BitrixAPI::getKeyInfo($key);

            $debugLog('API Result success', $apiResult['success']);
            $debugLog('API Result error', $apiResult['error'] ?? 'none');
            $debugLog('API Result data keys', is_array($apiResult['data']) ? array_keys($apiResult['data']) : 'not array');

            // Логируем raw данные если есть
            if (isset($apiResult['data']['raw'])) {
                $debugLog('RAW data keys', array_keys($apiResult['data']['raw']));
                $debugLog('RAW CLIENT data', $apiResult['data']['raw']['CLIENT'] ?? 'not set');
                $debugLog('RAW MODULE count', isset($apiResult['data']['raw']['MODULE']) ? count($apiResult['data']['raw']['MODULE']) : 0);
            }

            $debugLog('Parsed NAME', $apiResult['data']['NAME'] ?? 'not set');
            $debugLog('Parsed SUPPORT_PERIOD', $apiResult['data']['SUPPORT_PERIOD'] ?? 'not set');
            $debugLog('Parsed MODULES count', isset($apiResult['data']['MODULES']) ? count($apiResult['data']['MODULES']) : 0);

            if ($apiResult['success']) {
                // Сохраняем ключ в сессии
                \Itnull\Updater\SessionManager::setKey($key);
                \Itnull\Updater\SessionManager::setKeyInfo($apiResult['data']);

                $debugLog('About to get modules', 'Getting available updates');

                // Получаем список модулей
                $modules = \Itnull\Updater\ModuleList::getAvailableUpdates($key);

                $debugLog('Modules received', 'Count: ' . count($modules));

                $debugLog('ModuleList result count', count($modules));
                $debugLog('Hidden modules', \Itnull\Updater\ModuleList::getHiddenModules());
                
                // Логируем все модули для отладки
                $debugLog('Starting module logging', 'Total modules: ' . count($modules));
                
                foreach ($modules as $index => $module) {
                    $debugLog("Module #{$index}", [
                        'id' => $module['id'] ?? 'N/A',
                        'name' => $module['name'] ?? 'N/A',
                        'installedVersion' => $module['installedVersion'] ?? 'N/A',
                        'updateVersion' => $module['updateVersion'] ?? 'N/A',
                        'licenseValid' => $module['licenseValid'] ?? 'N/A',
                        'canDownload' => $module['canDownload'] ?? 'N/A',
                        'installed' => $module['installed'] ?? 'N/A',
                        'versions_count' => isset($module['versions']) ? count($module['versions']) : 0
                    ]);
                }

                // Сохраняем модули в сессии
                \Itnull\Updater\SessionManager::setModules($modules);

                $response = [
                    'success' => true,
                    'message' => 'Key information loaded successfully',
                    'keyInfo' => $apiResult['data'],
                    'modules' => $modules,
                    'debug' => [
                        'raw_keys' => isset($apiResult['data']['raw']) ? array_keys($apiResult['data']['raw']) : [],
                        'modules_from_api' => isset($apiResult['data']['MODULES']) ? count($apiResult['data']['MODULES']) : 0,
                        'modules_filtered' => count($modules)
                    ]
                ];

                $debugLog('Response prepared successfully');
            } else {
                $debugLog('API call failed', $apiResult['error']);
                $response = [
                    'success' => false,
                    'message' => $apiResult['error'] ?? 'Error getting key information'
                ];
            }

            $debugLog('=== END load_key_info ===');
            break;
            
        case 'download':
            $moduleId = $request->getPost('moduleId');
            $type = $request->getPost('type') ?: 'mod';
            $version = $request->getPost('version');
            $prevVersion = $request->getPost('prevVersion'); // Для дельта-обновлений

            if (empty($moduleId)) {
                $response = ['success' => false, 'message' => 'Module ID is required'];
                break;
            }

            // Версия обязательна только для delta-обновлений
            if ($type === 'delta' && empty($version)) {
                $response = ['success' => false, 'message' => 'Version is required for delta updates'];
                break;
            }

            // Загружаем модуль
            if (!Loader::includeModule('itnull.updater')) {
                $response = ['success' => false, 'message' => 'Module not loaded'];
                break;
            }

            // Используем UpdateManager для скачивания
            $downloadResult = \Itnull\Updater\UpdateManager::downloadUpdate($moduleId, $type, $version, $prevVersion);

            if ($downloadResult['success']) {
                // Обновляем список модулей для обновления данных на фронтенде
                $key = \Itnull\Updater\SessionManager::getKey();
                $modules = $key ? \Itnull\Updater\ModuleList::getAvailableUpdates($key) : [];

                $response = [
                    'success' => true,
                    'message' => $downloadResult['message'],
                    'data' => [
                        'moduleId' => $moduleId,
                        'version' => $version,
                        'file_path' => $downloadResult['file_path'],
                        'file_name' => $downloadResult['file_name'],
                        'fileExists' => true,
                        'canInstall' => true
                    ],
                    'modules' => $modules
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => $downloadResult['message']
                ];
            }
            break;
            
        case 'install':
            $moduleId = $request->getPost('moduleId');
            $type = $request->getPost('type') ?: 'mod';
            $version = $request->getPost('version');

            if (empty($moduleId)) {
                $response = ['success' => false, 'message' => 'Module ID is required'];
                break;
            }

            // Версия обязательна только для delta-обновлений
            if ($type === 'delta' && empty($version)) {
                $response = ['success' => false, 'message' => 'Version is required for delta updates'];
                break;
            }

            // Загружаем модуль
            if (!Loader::includeModule('itnull.updater')) {
                $response = ['success' => false, 'message' => 'Module not loaded'];
                break;
            }

            // Находим файлы обновления
            $updateDir = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/updates/';

            if (!is_dir($updateDir)) {
                $response = ['success' => false, 'message' => 'Updates directory not found'];
                break;
            }

            $files = @scandir($updateDir);
            if ($files === false) {
                $response = ['success' => false, 'message' => 'Cannot read updates directory'];
                break;
            }

            // Собираем все файлы для данного модуля
            $modFile = null;
            $deltaFiles = [];

            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $filePath = $updateDir . $file;
                if (!is_file($filePath)) {
                    continue;
                }

                // Парсим имя файла
                $parsedName = \Itnull\Updater\ModuleList::parseUpdateFileName($file, true);

                // Проверяем, относится ли файл к нашему модулю
                if ($parsedName['module_id'] !== $moduleId && strpos($file, $moduleId) === false) {
                    continue;
                }

                // Для конкретной версии (delta)
                if ($type === 'delta' && !empty($version)) {
                    if (strpos($file, $version) !== false || $parsedName['version'] === $version) {
                        $modFile = $filePath;
                        break;
                    }
                    continue;
                }

                // Для типа mod - собираем все файлы
                if ($parsedName['type'] === 'mod') {
                    $modFile = $filePath;
                } elseif ($parsedName['type'] === 'delta' && !empty($parsedName['version'])) {
                    $deltaFiles[$parsedName['version']] = $filePath;
                }
            }

            // Если есть mod файл - устанавливаем его
            if ($modFile) {
                $installResult = \Itnull\Updater\UpdateManager::installUpdate($modFile);

                if ($installResult['success']) {
                    $key = \Itnull\Updater\SessionManager::getKey();
                    $modules = $key ? \Itnull\Updater\ModuleList::getAvailableUpdates($key) : [];

                    $response = [
                        'success' => true,
                        'message' => $installResult['message'],
                        'data' => [
                            'moduleId' => $moduleId,
                            'version' => $version,
                            'installed' => true
                        ],
                        'modules' => $modules
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => $installResult['message']
                    ];
                }
                break;
            }

            // Если нет mod файла, но есть delta файлы - устанавливаем их последовательно от меньшей к большей версии
            if (!empty($deltaFiles)) {
                // Сортируем версии от меньшей к большей
                uksort($deltaFiles, 'version_compare');

                $installedVersions = [];
                $lastError = '';

                foreach ($deltaFiles as $deltaVersion => $deltaFilePath) {
                    $installResult = \Itnull\Updater\UpdateManager::installUpdate($deltaFilePath);

                    if ($installResult['success']) {
                        $installedVersions[] = $deltaVersion;
                    } else {
                        $lastError = $installResult['message'];
                        break; // Останавливаемся при первой ошибке
                    }
                }

                if (!empty($installedVersions)) {
                    $key = \Itnull\Updater\SessionManager::getKey();
                    $modules = $key ? \Itnull\Updater\ModuleList::getAvailableUpdates($key) : [];

                    $message = 'Установлены delta-обновления: ' . implode(', ', $installedVersions);
                    if ($lastError) {
                        $message .= '. Ошибка на версии: ' . $lastError;
                    }

                    $response = [
                        'success' => true,
                        'message' => $message,
                        'data' => [
                            'moduleId' => $moduleId,
                            'installedVersions' => $installedVersions,
                            'installed' => true
                        ],
                        'modules' => $modules
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => $lastError ?: 'Не удалось установить delta-обновления'
                    ];
                }
                break;
            }

            // Файлы не найдены
            $response = [
                'success' => false,
                'message' => 'Файл обновления не найден для модуля: ' . $moduleId
            ];
            break;
            
        case 'refresh':
            // Загружаем модуль
            if (!Loader::includeModule('itnull.updater')) {
                $response = ['success' => false, 'message' => 'Module not loaded'];
                break;
            }

            // Обновляем данные о модулях
            $key = \Itnull\Updater\SessionManager::getKey();

            if (empty($key)) {
                $response = ['success' => false, 'message' => 'No license key in session'];
                break;
            }

            // Получаем обновленный список модулей
            $modules = \Itnull\Updater\ModuleList::getAvailableUpdates($key);

            // Сохраняем модули в сессии
            \Itnull\Updater\SessionManager::setModules($modules);

            $response = [
                'success' => true,
                'message' => 'Data refreshed successfully',
                'modules' => $modules
            ];
            break;

        // =====================
        // Демо-режим
        // =====================

        case 'demo_status':
            // Загружаем модуль
            if (!Loader::includeModule('itnull.updater')) {
                $response = ['success' => false, 'message' => 'Module not loaded'];
                break;
            }

            $response = [
                'success' => true,
                'data' => \Itnull\Updater\DemoManager::getDemoStatus(),
                'licenseTypes' => \Itnull\Updater\DemoManager::getLicenseTypes()
            ];
            break;

        case 'demo_get_key':
            // Загружаем модуль
            if (!Loader::includeModule('itnull.updater')) {
                $response = ['success' => false, 'message' => 'Module not loaded'];
                break;
            }

            $licenseType = $request->getPost('licenseType');

            if (empty($licenseType)) {
                $response = ['success' => false, 'message' => 'Не выбран тип лицензии'];
                break;
            }

            $result = \Itnull\Updater\DemoManager::getNewDemoKey($licenseType);
            $response = $result;

            // Добавляем обновлённый статус
            if ($result['success']) {
                $response['data'] = \Itnull\Updater\DemoManager::getDemoStatus();
            }
            break;

        case 'demo_extend':
            // Загружаем модуль
            if (!Loader::includeModule('itnull.updater')) {
                $response = ['success' => false, 'message' => 'Module not loaded'];
                break;
            }

            $result = \Itnull\Updater\DemoManager::extendDemo();
            $response = $result;

            // Добавляем обновлённый статус
            if ($result['success']) {
                $response['data'] = \Itnull\Updater\DemoManager::getDemoStatus();
            }
            break;

        case 'demo_hide_message':
            // Загружаем модуль
            if (!Loader::includeModule('itnull.updater')) {
                $response = ['success' => false, 'message' => 'Module not loaded'];
                break;
            }

            $result = \Itnull\Updater\DemoManager::hideDemoMessage();
            $response = $result;

            // Добавляем обновлённый статус
            if ($result['success']) {
                $response['data'] = \Itnull\Updater\DemoManager::getDemoStatus();
            }
            break;

        case 'demo_show_message':
            // Загружаем модуль
            if (!Loader::includeModule('itnull.updater')) {
                $response = ['success' => false, 'message' => 'Module not loaded'];
                break;
            }

            $result = \Itnull\Updater\DemoManager::showDemoMessage();
            $response = $result;

            // Добавляем обновлённый статус
            if ($result['success']) {
                $response['data'] = \Itnull\Updater\DemoManager::getDemoStatus();
            }
            break;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Exception occurred: ' . $e->getMessage()
    ]);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');