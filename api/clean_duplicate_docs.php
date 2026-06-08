<?php
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = '/var/www/www-root/data/www/lvtgroup.ru';
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

if (!CModule::IncludeModule('iblock') || !CModule::IncludeModule('main')) {
    die('Не загружены необходимые модули');
}

// КОНФИГУРАЦИЯ ДЛЯ СЕРВЕРА С 128 ГБ RAM
$iblock_id = 11;
$file_property_id = 500; // INSTRUCTIONS
$links_property_id = 1219; // Ссылки на документы
$logFile = __DIR__ . '/clean_duplicate_docs_highmem.log';
$pageSize = 10000; // УВЕЛИЧЕНО: 10,000 товаров за проход
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'full'; // full, check, fix_files, fix_links
$delaySeconds = isset($_GET['delay']) ? (int)$_GET['delay'] : 2;
$skipProcessed = isset($_GET['skip_processed']) ? true : false;
$maxFileSizeForHash = 50 * 1024 * 1024; // 50 MB - максимальный размер файла для хэширования

// ОПТИМАЛЬНЫЕ НАСТРОЙКИ ДЛЯ 128 ГБ RAM
ini_set('memory_limit', '8G'); // 8 ГБ для PHP - достаточно для обработки
set_time_limit(600); // 10 минут на выполнение
ini_set('max_execution_time', 600);
ini_set('max_input_time', 600);
ini_set('post_max_size', '256M');
ini_set('upload_max_filesize', '256M');

// Включение буферизации вывода для больших объемов
if (ob_get_level() == 0) {
    ob_start();
}

function logMessage($message) {
    global $logFile;
    $formatted = date('Y-m-d H:i:s') . " - " . $message . PHP_EOL;
    file_put_contents($logFile, $formatted, FILE_APPEND | LOCK_EX);
    echo $message . "<br>";
    flush();
    ob_flush();
}

// ============ ОПТИМИЗИРОВАННЫЕ ФУНКЦИИ ДЛЯ БОЛЬШИХ ОБЪЕМОВ ============

// 1. Быстрая функция для поиска дубликатов файлов (с оптимизацией памяти)
function findDuplicateFilesFast($fileIds) {
    global $maxFileSizeForHash;
    
    $duplicateGroups = [];
    $fileHashes = [];
    $processedCount = 0;
    
    foreach ($fileIds as $fileId) {
        $dbFile = CFile::GetByID($fileId);
        if ($fileInfo = $dbFile->Fetch()) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $fileInfo['SRC'];
            
            if (file_exists($filePath)) {
                $fileSize = $fileInfo['FILE_SIZE'];
                $fileName = $fileInfo['ORIGINAL_NAME'];
                
                // Сначала группируем по размеру и имени (быстрее всего)
                $sizeNameKey = $fileSize . '_' . md5($fileName);
                
                if (!isset($fileHashes[$sizeNameKey])) {
                    $fileHashes[$sizeNameKey] = [];
                }
                
                // Если файл слишком большой, используем только часть
                if ($fileSize > $maxFileSizeForHash) {
                    $hash = $sizeNameKey . '_large'; // Для больших файлов не вычисляем полный хэш
                } else {
                    $hash = md5_file($filePath);
                }
                
                $fileHashes[$sizeNameKey][] = [
                    'id' => $fileId,
                    'path' => $filePath,
                    'info' => $fileInfo,
                    'hash' => $hash
                ];
                $processedCount++;
                
                // Периодически очищаем память для больших групп
                if ($processedCount % 1000 == 0) {
                    gc_collect_cycles();
                }
            }
        }
    }
    
    logMessage("  Обработано файлов: {$processedCount}");
    
    // Ищем дубликаты в группах с одинаковым размером и именем
    foreach ($fileHashes as $sizeNameKey => $files) {
        if (count($files) > 1) {
            // Группируем по хэшу
            $hashGroups = [];
            foreach ($files as $file) {
                $hashGroups[$file['hash']][] = $file;
            }
            
            foreach ($hashGroups as $hash => $sameFiles) {
                if (count($sameFiles) > 1) {
                    $duplicateGroups[] = $sameFiles;
                }
            }
        }
    }
    
    return $duplicateGroups;
}

// 2. Оптимизированная функция очистки дубликатов
function fixFileDuplicatesForElementOptimized($elementId, $iblock_id, $property_id) {
    // Получаем все файлы товара
    $dbProp = CIBlockElement::GetProperty($iblock_id, $elementId, [], ['ID' => $property_id]);
    $fileIds = [];
    $propValues = [];
    
    $index = 0;
    while ($prop = $dbProp->Fetch()) {
        if (!empty($prop['VALUE'])) {
            $fileIds[] = $prop['VALUE'];
            $propValues[$index] = $prop['VALUE'];
            $index++;
        }
    }
    
    if (count($fileIds) <= 1) {
        return ['kept' => $fileIds, 'removed' => [], 'status' => 'no_duplicates'];
    }
    
    logMessage("  Товар ID {$elementId}: найдено " . count($fileIds) . " файлов");
    
    // Находим дубликаты файлов (быстрый метод)
    $duplicateGroups = findDuplicateFilesFast($fileIds);
    
    if (empty($duplicateGroups)) {
        logMessage("  Нет дубликатов файлов по содержимому");
        return ['kept' => $fileIds, 'removed' => [], 'status' => 'no_duplicates_found'];
    }
    
    $keptFiles = [];
    $removedFiles = [];
    $totalDuplicatesRemoved = 0;
    
    foreach ($duplicateGroups as $group) {
        // Сортируем по дате создания, оставляем самый старый файл
        usort($group, function($a, $b) {
            return strtotime($a['info']['TIMESTAMP_X']) <=> strtotime($b['info']['TIMESTAMP_X']);
        });
        
        $keepFile = array_shift($group); // Оставляем самый старый файл
        $keptFiles[] = $keepFile['id'];
        
        logMessage("  Группа дубликатов (" . (count($group) + 1) . " файлов):");
        logMessage("    ✅ Оставляем файл ID {$keepFile['id']} ({$keepFile['info']['ORIGINAL_NAME']})");
        
        // Удаляем дубликаты
        foreach ($group as $duplicate) {
            logMessage("    ❌ Удаляем дубликат ID {$duplicate['id']}");
            
            // Удаляем файл из системы
            CFile::Delete($duplicate['id']);
            $removedFiles[] = $duplicate['id'];
            $totalDuplicatesRemoved++;
        }
    }
    
    // Добавляем уникальные файлы
    foreach ($fileIds as $fileId) {
        if (!in_array($fileId, $keptFiles) && !in_array($fileId, $removedFiles)) {
            $keptFiles[] = $fileId;
        }
    }
    
    // Обновляем привязки товара
    if (!empty($removedFiles)) {
        logMessage("  Обновляем привязки: оставляем " . count($keptFiles) . " файлов, удалено " . count($removedFiles) . " дубликатов");
        
        if (!empty($keptFiles)) {
            // Формируем правильный массив значений для множественного свойства
            $propertyValues = [];
            foreach ($keptFiles as $index => $fileId) {
                $propertyValues[$property_id][$index] = $fileId;
            }
            
            // Обновляем свойство
            $result = CIBlockElement::SetPropertyValuesEx(
                $elementId,
                $iblock_id,
                $propertyValues
            );
            
            if ($result === false) {
                logMessage("  ⚠️ Ошибка при обновлении привязок файлов");
                return ['kept' => $keptFiles, 'removed' => $removedFiles, 'status' => 'error_update'];
            }
        } else {
            // Если файлов не осталось, очищаем свойство
            CIBlockElement::SetPropertyValuesEx(
                $elementId,
                $iblock_id,
                [$property_id => false]
            );
        }
        
        logMessage("  ✅ Обновлено успешно");
        return ['kept' => $keptFiles, 'removed' => $removedFiles, 'status' => 'fixed'];
    }
    
    return ['kept' => $keptFiles, 'removed' => $removedFiles, 'status' => 'no_changes'];
}

// 3. Пакетная обработка товаров для экономии памяти
function processElementsBatch($elementIds, $elementNames, $iblock_id, $file_property_id, $mode, &$stats) {
    $batchSize = 100; // Обрабатываем по 100 товаров за раз внутри страницы
    $totalElements = count($elementIds);
    
    for ($i = 0; $i < $totalElements; $i += $batchSize) {
        $batchIds = array_slice($elementIds, $i, $batchSize);
        
        foreach ($batchIds as $elementId) {
            $elementName = $elementNames[$elementId];
            
            if ($mode == 'check') {
                $dbProp = CIBlockElement::GetProperty($iblock_id, $elementId, [], ['ID' => $file_property_id]);
                $fileCount = 0;
                $fileIds = [];
                
                while ($prop = $dbProp->Fetch()) {
                    if (!empty($prop['VALUE'])) {
                        $fileCount++;
                        $fileIds[] = $prop['VALUE'];
                    }
                }
                
                if ($fileCount > 0) {
                    $stats['elements_with_files']++;
                    
                    if ($fileCount > 1) {
                        $stats['elements_with_duplicates']++;
                        logMessage("Товар ID {$elementId}: {$fileCount} файлов");
                        
                        $duplicateGroups = findDuplicateFilesFast($fileIds);
                        if (!empty($duplicateGroups)) {
                            $stats['duplicate_files_found'] += count($duplicateGroups);
                        }
                    }
                }
            }
            elseif ($mode == 'fix_files' || $mode == 'full') {
                $dbProp = CIBlockElement::GetProperty($iblock_id, $elementId, [], ['ID' => $file_property_id]);
                $fileCount = 0;
                $fileIds = [];
                
                while ($prop = $dbProp->Fetch()) {
                    if (!empty($prop['VALUE'])) {
                        $fileCount++;
                        $fileIds[] = $prop['VALUE'];
                    }
                }
                
                if ($fileCount > 0) {
                    $stats['elements_with_files']++;
                    
                    if ($fileCount > 1) {
                        $stats['elements_with_duplicates']++;
                        
                        $result = fixFileDuplicatesForElementOptimized($elementId, $iblock_id, $file_property_id);
                        
                        if ($result['status'] == 'fixed') {
                            $stats['duplicate_files_removed'] += count($result['removed']);
                        } elseif ($result['status'] == 'error') {
                            $stats['errors']++;
                        }
                    }
                }
            }
        }
        
        // Очистка памяти после каждой партии
        unset($batchIds);
        gc_collect_cycles();
        
        // Прогресс
        $processed = min($i + $batchSize, $totalElements);
        $percent = round(($processed / $totalElements) * 100, 1);
        logMessage("  Прогресс: {$processed}/{$totalElements} ({$percent}%)");
    }
}

// 4. Основная функция обработки страницы (оптимизированная)
function processPageOptimized($iblock_id, $file_property_id, $links_property_id, $page, $pageSize, $mode = 'full') {
    $startTime = microtime(true);
    $startMemory = memory_get_usage();
    
    $stats = [
        'elements_processed' => 0,
        'elements_with_files' => 0,
        'elements_with_duplicates' => 0,
        'duplicate_files_found' => 0,
        'duplicate_files_removed' => 0,
        'links_cleaned' => 0,
        'errors' => 0,
        'execution_time' => 0,
        'memory_used' => 0
    ];
    
    logMessage("=== ОБРАБОТКА СТРАНИЦЫ {$page} (режим: {$mode}, размер: {$pageSize}) ===");
    
    // Получаем товары с пагинацией
    $res = CIBlockElement::GetList(
        ['ID' => 'ASC'],
        ['IBLOCK_ID' => $iblock_id, 'ACTIVE' => 'Y'],
        false,
        ['nPageSize' => $pageSize, 'iNumPage' => $page],
        ['ID', 'NAME']
    );
    
    $elementIds = [];
    $elementNames = [];
    
    while ($element = $res->Fetch()) {
        $elementIds[] = $element['ID'];
        $elementNames[$element['ID']] = $element['NAME'];
        $stats['elements_processed']++;
    }
    
    if (empty($elementIds)) {
        $stats['execution_time'] = round(microtime(true) - $startTime, 2);
        $stats['memory_used'] = round((memory_get_usage() - $startMemory) / 1024 / 1024, 2);
        return $stats;
    }
    
    logMessage("Загружено товаров: " . $stats['elements_processed']);
    
    // Пакетная обработка товаров
    processElementsBatch($elementIds, $elementNames, $iblock_id, $file_property_id, $mode, $stats);
    
    // Обработка ссылок
    if ($mode == 'full' || $mode == 'fix_links') {
        logMessage("Обработка ссылок на документы...");
        foreach ($elementIds as $elementId) {
            $dbProp = CIBlockElement::GetProperty($iblock_id, $elementId, [], ['ID' => $links_property_id]);
            
            $links = [];
            $propValues = [];
            
            while ($prop = $dbProp->Fetch()) {
                if (!empty($prop['VALUE'])) {
                    $links[] = $prop['VALUE'];
                    $propValues[] = [
                        'VALUE' => $prop['VALUE'],
                        'DESCRIPTION' => $prop['DESCRIPTION']
                    ];
                }
            }
            
            if (count($links) > 1) {
                $uniqueLinks = [];
                $uniqueValues = [];
                
                foreach ($propValues as $propValue) {
                    if (!in_array($propValue['VALUE'], $uniqueLinks)) {
                        $uniqueLinks[] = $propValue['VALUE'];
                        $uniqueValues[] = $propValue;
                    }
                }
                
                if (count($links) != count($uniqueLinks)) {
                    CIBlockElement::SetPropertyValuesEx(
                        $elementId,
                        $iblock_id,
                        [$links_property_id => false]
                    );
                    
                    if (!empty($uniqueValues)) {
                        $newValues = [];
                        foreach ($uniqueValues as $index => $value) {
                            $newValues[$links_property_id][$index] = [
                                'VALUE' => $value['VALUE'],
                                'DESCRIPTION' => $value['DESCRIPTION']
                            ];
                        }
                        
                        CIBlockElement::SetPropertyValuesEx(
                            $elementId,
                            $iblock_id,
                            $newValues
                        );
                        
                        $stats['links_cleaned'] += (count($links) - count($uniqueLinks));
                    }
                }
            }
        }
    }
    
    $stats['execution_time'] = round(microtime(true) - $startTime, 2);
    $stats['memory_used'] = round((memory_get_usage() - $startMemory) / 1024 / 1024, 2);
    
    logMessage("=== СТРАНИЦА {$page} ОБРАБОТАНА за {$stats['execution_time']} сек. ===");
    
    return $stats;
}

// ============ ОСНОВНОЙ КОД ============

logMessage("=== HIGH-MEMORY ОЧИСТКА ДУБЛИКАТОВ (128 ГБ RAM) ===");
logMessage("Режим: {$mode}");
logMessage("Страница: {$currentPage}");
logMessage("Размер страницы: {$pageSize}");
logMessage("Макс. размер файла для хэширования: " . round($maxFileSizeForHash / 1024 / 1024, 1) . " MB");
logMessage("Лимит памяти PHP: " . ini_get('memory_limit'));
logMessage("Лимит времени: " . ini_get('max_execution_time') . " сек.");

// Статистика
if ($currentPage == 1) {
    $res = CIBlockElement::GetList(
        ['ID' => 'ASC'],
        ['IBLOCK_ID' => $iblock_id, 'ACTIVE' => 'Y'],
        ['ID'],
        false
    );
    $totalElements = $res->SelectedRowsCount();
    $totalPages = ceil($totalElements / $pageSize);
    
    logMessage("Всего товаров: " . number_format($totalElements, 0, '', ' '));
    logMessage("Всего страниц: " . number_format($totalPages, 0, '', ' '));
    logMessage("Ожидаемое время: ~" . round($totalPages * 2 / 60, 1) . " минут");
    
    // Сохраняем в сессию
    $_SESSION['cleanup_stats'] = [
        'total_elements' => $totalElements,
        'total_pages' => $totalPages,
        'start_time' => time(),
        'processed_pages' => 0
    ];
}

// Обрабатываем страницу
$pageStats = processPageOptimized($iblock_id, $file_property_id, $links_property_id, 
                                  $currentPage, $pageSize, $mode);

// Обновляем статистику
if (isset($_SESSION['cleanup_stats'])) {
    $_SESSION['cleanup_stats']['processed_pages'] = $currentPage;
}

// Вывод результатов
echo "<div style='background: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Результаты страницы {$currentPage} (режим: {$mode})</h3>";
echo "<div style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 15px 0;'>";
echo "<div style='background: #e3f2fd; padding: 10px; border-radius: 4px;'><strong>Обработано:</strong><br>" . number_format($pageStats['elements_processed'], 0, '', ' ') . " товаров</div>";
echo "<div style='background: #fff3e0; padding: 10px; border-radius: 4px;'><strong>С файлами:</strong><br>" . number_format($pageStats['elements_with_files'], 0, '', ' ') . " товаров</div>";
echo "<div style='background: #fce4ec; padding: 10px; border-radius: 4px;'><strong>С дубликатами:</strong><br>" . number_format($pageStats['elements_with_duplicates'], 0, '', ' ') . " товаров</div>";
echo "</div>";
echo "<div style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;'>";
echo "<div style='background: #e8f5e9; padding: 10px; border-radius: 4px;'><strong>Удалено файлов:</strong><br>" . number_format($pageStats['duplicate_files_removed'], 0, '', ' ') . " шт.</div>";
echo "<div style='background: #f3e5f5; padding: 10px; border-radius: 4px;'><strong>Очищено ссылок:</strong><br>" . number_format($pageStats['links_cleaned'], 0, '', ' ') . " шт.</div>";
echo "<div style='background: #e0f7fa; padding: 10px; border-radius: 4px;'><strong>Время:</strong><br>{$pageStats['execution_time']} сек.</div>";
echo "</div>";
echo "<div style='margin-top: 15px; padding: 10px; background: #212121; color: white; border-radius: 4px;'>";
echo "<strong>Использовано памяти:</strong> {$pageStats['memory_used']} MB | ";
echo "<strong>Пик памяти:</strong> " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB";
echo "</div>";
echo "</div>";

// Прогресс
if (isset($_SESSION['cleanup_stats'])) {
    $stats = $_SESSION['cleanup_stats'];
    $progressPercent = round(($currentPage / $stats['total_pages']) * 100, 1);
    $elapsedTime = time() - $stats['start_time'];
    $estimatedTotalTime = $elapsedTime * $stats['total_pages'] / $currentPage;
    $remainingTime = $estimatedTotalTime - $elapsedTime;
    
    echo "<div style='background: #e8f4f8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>Общий прогресс</h4>";
    echo "<div style='background: #ddd; height: 20px; border-radius: 10px; overflow: hidden; margin: 10px 0;'>";
    echo "<div style='background: #28a745; height: 100%; width: {$progressPercent}%;'></div>";
    echo "</div>";
    echo "<p><strong>Прогресс:</strong> {$currentPage}/{$stats['total_pages']} страниц ({$progressPercent}%)</p>";
    echo "<p><strong>Прошло времени:</strong> " . gmdate("H:i:s", $elapsedTime) . "</p>";
    echo "<p><strong>Осталось времени:</strong> ~" . gmdate("H:i:s", $remainingTime) . "</p>";
    echo "</div>";
}

// Навигация
$nextPage = $currentPage + 1;
if ($pageStats['elements_processed'] > 0) {
    echo "<div style='background: #e8f8e8; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>Управление обработкой</h4>";
    
    // Быстрые кнопки режимов
    echo "<div style='margin-bottom: 15px;'>";
    $modeButtons = [
        'check' => ['🔍 Проверка', '#17a2b8'],
        'fix_files' => ['🛠️ Файлы', '#ffc107'],
        'fix_links' => ['🔗 Ссылки', '#20c997'],
        'full' => ['⚡ Полная', '#28a745']
    ];
    
    foreach ($modeButtons as $modeKey => $modeInfo) {
        $active = $mode == $modeKey ? 'border: 3px solid #333; font-weight: bold;' : '';
        echo "<a href='?page={$currentPage}&mode={$modeKey}&delay={$delaySeconds}' 
              style='margin: 5px; padding: 10px 15px; background: {$modeInfo[1]}; 
              color: " . ($modeKey == 'fix_files' ? 'black' : 'white') . "; 
              border-radius: 5px; display: inline-block; {$active}'>
              {$modeInfo[0]}</a>";
    }
    echo "</div>";
    
    // Навигация по страницам
    echo "<div style='margin-bottom: 15px;'>";
    echo "<a href='?page={$nextPage}&mode={$mode}&delay={$delaySeconds}' 
          style='margin: 5px; padding: 12px 25px; background: #007bff; color: white; 
          border-radius: 5px; font-weight: bold; font-size: 16px;'>
          ▶ Следующая страница ({$nextPage})</a>";
    
    // Быстрые переходы
    $quickPages = [1, 10, 50, 100, $currentPage + 10];
    foreach ($quickPages as $quickPage) {
        if ($quickPage <= $totalPages) {
            echo "<a href='?page={$quickPage}&mode={$mode}&delay={$delaySeconds}' 
                  style='margin: 5px; padding: 8px 12px; background: #6c757d; color: white; 
                  border-radius: 4px;'>
                  {$quickPage}</a>";
        }
    }
    echo "</div>";
    
    // Настройки
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<label><strong>Задержка:</strong> </label>";
    echo "<select onchange=\"location.href='?page={$currentPage}&mode={$mode}&delay='+this.value\" 
          style='padding: 5px; margin: 0 10px;'>";
    $delays = [0 => 'Без задержки', 1 => '1 сек', 2 => '2 сек', 3 => '3 сек', 5 => '5 сек', 10 => '10 сек'];
    foreach ($delays as $delayVal => $delayLabel) {
        $selected = $delaySeconds == $delayVal ? ' selected' : '';
        echo "<option value='{$delayVal}'{$selected}>{$delayLabel}</option>";
    }
    echo "</select>";
    
    echo "<label style='margin-left: 20px;'><strong>Размер страницы:</strong> </label>";
    echo "<select onchange=\"location.href='?page=1&mode={$mode}&delay={$delaySeconds}&pagesize='+this.value\" 
          style='padding: 5px; margin: 0 10px;'>";
    $sizes = [1000 => '1,000', 5000 => '5,000', 10000 => '10,000', 20000 => '20,000', 50000 => '50,000'];
    foreach ($sizes as $sizeVal => $sizeLabel) {
        $selected = $pageSize == $sizeVal ? ' selected' : '';
        echo "<option value='{$sizeVal}'{$selected}>{$sizeLabel}</option>";
    }
    echo "</select>";
    echo "</div>";
    echo "</div>";
    
    // Автопереход
    if ($delaySeconds > 0) {
        echo "<script>
            setTimeout(function() {
                window.location.href = '?page={$nextPage}&mode={$mode}&delay={$delaySeconds}';
            }, {$delaySeconds} * 1000);
            
            // Таймер
            var countdown = {$delaySeconds};
            var timerDiv = document.createElement('div');
            timerDiv.id = 'countdown-timer';
            timerDiv.style.cssText = 'position: fixed; top: 10px; right: 10px; background: #ff9900; 
                                     color: white; padding: 15px; border-radius: 8px; z-index: 1000; 
                                     font-weight: bold; box-shadow: 0 2px 10px rgba(0,0,0,0.2);';
            timerDiv.innerHTML = 'Автопереход через: <span style=\"font-size: 24px;\">' + countdown + '</span> сек';
            document.body.appendChild(timerDiv);
            
            var timer = setInterval(function() {
                countdown--;
                if (countdown <= 0) {
                    clearInterval(timer);
                    timerDiv.innerHTML = 'Переход...';
                } else {
                    timerDiv.querySelector('span').innerHTML = countdown;
                }
            }, 1000);
        </script>";
    }
} else {
    echo "<div style='background: #d4edda; padding: 30px; border-radius: 10px; text-align: center; margin: 30px 0;'>";
    echo "<h3 style='color: #155724;'>✅ Обработка завершена!</h3>";
    echo "<p>Все " . number_format($totalElements, 0, '', ' ') . " товаров обработаны.</p>";
    echo "<a href='?page=1&mode=check' style='padding: 12px 24px; background: #17a2b8; 
          color: white; border-radius: 5px; text-decoration: none; display: inline-block; margin: 10px;'>
          🔄 Начать заново</a>";
    echo "</div>";
    
    unset($_SESSION['cleanup_stats']);
}

// Дополнительные инструменты
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>Дополнительные инструменты</h4>";
echo "<form method='GET' style='display: inline-block; margin-right: 15px;'>
    <input type='hidden' name='mode' value='test'>
    <input type='text' name='test_element_id' placeholder='ID товара' 
           style='padding: 8px; width: 150px; border: 1px solid #ccc; border-radius: 4px;'>
    <button type='submit' style='padding: 8px 15px; background: #17a2b8; color: white; 
            border: none; border-radius: 4px; cursor: pointer;'>Проверить товар</button>
</form>";
echo "<a href='?mode=stats' style='padding: 8px 15px; background: #6f42c1; color: white; 
      border-radius: 4px; text-decoration: none; display: inline-block;'>📊 Статистика</a>";
echo "</div>";

// Футер с информацией
echo "<div style='margin-top: 30px; padding: 15px; background: #f8f9fa; border-top: 2px solid #dee2e6; 
      font-size: 12px; color: #6c757d;'>";
echo "<p><strong>Системная информация:</strong> ";
echo "Память: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB / ";
echo "Пик: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB | ";
echo "Время выполнения: " . round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 2) . " сек";
echo "</p>";
echo "</div>";

// Стили
echo "<style>
    body { 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        margin: 20px; 
        line-height: 1.6; 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
    .container {
        max-width: 1400px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    a { 
        text-decoration: none; 
        transition: all 0.3s ease;
    }
    a:hover { 
        opacity: 0.9; 
        transform: translateY(-2px);
    }
    h3, h4 {
        color: #333;
        margin-top: 0;
    }
    @media (max-width: 768px) {
        body { margin: 10px; }
        .container { padding: 15px; }
        div[style*='grid-template-columns'] {
            grid-template-columns: 1fr !important;
        }
    }
</style>";

echo "<div class='container'>";

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
?>