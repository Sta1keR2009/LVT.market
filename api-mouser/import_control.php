<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

global $APPLICATION;
$APPLICATION->SetTitle("Управление импортом Mouser");

// Проверяем права доступа
global $USER;
if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm("Доступ запрещен");
}

require_once('config.php');

// Получаем статистику
$stats = [];
$stats_file = defined('LOG_PATH') ? LOG_PATH . 'import_stats.json' : $_SERVER['DOCUMENT_ROOT'].'/api-mouser/logs/import_stats.json';
if (file_exists($stats_file)) {
    $stats_content = file_get_contents($stats_file);
    $stats = json_decode($stats_content, true);
}

// Получаем лог файлы
$log_path = defined('LOG_PATH') ? LOG_PATH : $_SERVER['DOCUMENT_ROOT'].'/api-mouser/logs/';
$log_files = [];
if (file_exists($log_path)) {
    $log_files = glob($log_path . 'import_batch_*.log');
    if ($log_files) {
        rsort($log_files);
    }
}

// Статистика Битрикса
$bitrix_stats = [
    'elements' => 0,
    'sections' => 0,
    'last_import' => ''
];

if (CModule::IncludeModule("iblock")) {
    try {
        // Количество элементов - исправленный метод
        $res = CIBlockElement::GetList(
            [], 
            ["IBLOCK_ID" => INFOBLOCK_ID], 
            false, 
            false, 
            ["ID"]
        );
        
        if ($res) {
            // Проверяем тип возвращаемого значения
            if (is_object($res)) {
                $bitrix_stats['elements'] = $res->SelectedRowsCount();
            } else {
                // Альтернативный метод подсчета
                $count = 0;
                while ($res->Fetch()) {
                    $count++;
                }
                $bitrix_stats['elements'] = $count;
            }
        }
        
        // Количество разделов - исправленный метод
        $res = CIBlockSection::GetList(
            [], 
            ["IBLOCK_ID" => INFOBLOCK_ID], 
            false, 
            false, 
            ["ID"]
        );
        
        if ($res) {
            if (is_object($res)) {
                $bitrix_stats['sections'] = $res->SelectedRowsCount();
            } else {
                $count = 0;
                while ($res->Fetch()) {
                    $count++;
                }
                $bitrix_stats['sections'] = $count;
            }
        }
        
        // Последний импорт
        if (!empty($log_files)) {
            $bitrix_stats['last_import'] = date("d.m.Y H:i:s", filemtime($log_files[0]));
        }
    } catch (Exception $e) {
        // Обработка ошибок
        $bitrix_stats['error'] = $e->getMessage();
    }
}
?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); ?>

<div class="adm-detail-content-wrap">
    <div class="adm-detail-content">
        <div class="adm-detail-title">Управление импортом Mouser</div>
        
        <div class="adm-info-message-wrap">
            <div class="adm-info-message">
                <strong>План импорта:</strong><br>
                50 категорий × 1000 товаров = до 50,000 товаров<br>
                <strong>Инфоблок ID:</strong> <?= INFOBLOCK_ID ?>
            </div>
        </div>
        
        <div class="adm-detail-content-item-block">
            <div class="adm-detail-content-item-block-title">Статистика Битрикса</div>
            <table class="adm-detail-content-table edit-table">
                <tr>
                    <td width="40%">Товаров в инфоблоке:</td>
                    <td><strong><?= $bitrix_stats['elements'] ?></strong></td>
                </tr>
                <tr>
                    <td>Категорий (разделов):</td>
                    <td><strong><?= $bitrix_stats['sections'] ?></strong></td>
                </tr>
                <tr>
                    <td>Последний импорт:</td>
                    <td><?= $bitrix_stats['last_import'] ?: 'не выполнялся' ?></td>
                </tr>
                <?php if (isset($bitrix_stats['error'])): ?>
                <tr>
                    <td>Ошибка:</td>
                    <td style="color: red;"><?= $bitrix_stats['error'] ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <?php if (!empty($stats)): ?>
        <div class="adm-detail-content-item-block">
            <div class="adm-detail-content-item-block-title">Статистика последнего импорта</div>
            <table class="adm-detail-content-table edit-table">
                <tr>
                    <td width="40%">Начало импорта:</td>
                    <td><?= $stats['start_time'] ?? '' ?></td>
                </tr>
                <tr>
                    <td>Обработано категорий:</td>
                    <td><?= ($stats['categories_processed'] ?? 0) . ' из ' . ($stats['categories_total'] ?? 0) ?></td>
                </tr>
                <tr>
                    <td>Импортировано товаров:</td>
                    <td><strong><?= $stats['items_success'] ?? 0 ?></strong></td>
                </tr>
                <tr>
                    <td>Время выполнения:</td>
                    <td><?= ($stats['total_time_seconds'] ?? 0) ?> секунд</td>
                </tr>
            </table>
        </div>
        <?php endif; ?>
        
        <div class="adm-detail-content-item-block">
            <div class="adm-detail-content-item-block-title">Запуск импорта</div>
            
            <div style="margin: 20px 0;">
                <h3>Варианты запуска:</h3>
                
                <div style="margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <h4>1. Полный импорт (50 категорий)</h4>
                    <p>Время: 3-5 часов</p>
                    <a href="/api-mouser/import_batch.php" class="adm-btn adm-btn-save" 
                       onclick="return confirm('Запустить полный импорт 50 категорий? Это займет несколько часов.')">
                       Запустить полный импорт
                    </a>
                </div>
                
                <div style="margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <h4>2. Поэтапный импорт (по 10 категорий)</h4>
                    <p>Время: 30-60 минут на этап</p>
                    <a href="/api-mouser/import_batch.php?start=0&limit=10" class="adm-btn" 
                       onclick="return confirm('Запустить импорт первых 10 категорий?')">
                       Этап 1: Категории 1-10
                    </a>
                    <a href="/api-mouser/import_batch.php?start=10&limit=10" class="adm-btn">
                       Этап 2: Категории 11-20
                    </a>
                    <a href="/api-mouser/import_batch.php?start=20&limit=10" class="adm-btn">
                       Этап 3: Категории 21-30
                    </a>
                    <a href="/api-mouser/import_batch.php?start=30&limit=10" class="adm-btn">
                       Этап 4: Категории 31-40
                    </a>
                    <a href="/api-mouser/import_batch.php?start=40&limit=10" class="adm-btn">
                       Этап 5: Категории 41-50
                    </a>
                </div>
                
                <div style="margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <h4>3. Тестовый импорт (1 категория)</h4>
                    <p>Время: 5-10 минут</p>
                    <a href="/api-mouser/import_batch.php?start=0&limit=1" class="adm-btn">
                       Тест: 1 категория
                    </a>
                </div>
            </div>
            
            <div class="adm-info-message-wrap">
                <div class="adm-info-message">
                    <strong>Перед запуском убедитесь:</strong><br>
                    1. Созданы папки: /upload/mouser_images/ и /api-mouser/logs/<br>
                    2. Созданы свойства инфоблока через <a href="/api-mouser/create_properties.php">create_properties.php</a><br>
                    3. Прокси сервера доступны<br>
                    4. API ключ Mouser действителен
                </div>
            </div>
        </div>
        
        <div class="adm-detail-content-item-block">
            <div class="adm-detail-content-item-block-title">Логи импорта</div>
            
            <?php if (!empty($log_files)): ?>
                <table class="adm-list-table">
                    <thead>
                        <tr>
                            <th>Файл</th>
                            <th>Дата</th>
                            <th>Размер</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($log_files as $i => $log_file): ?>
                            <?php if ($i < 10): ?>
                                <tr>
                                    <td><?= basename($log_file) ?></td>
                                    <td><?= date("d.m.Y H:i:s", filemtime($log_file)) ?></td>
                                    <td><?= round(filesize($log_file) / 1024, 1) ?> KB</td>
                                    <td>
                                        <a href="/api-mouser/logs/<?= basename($log_file) ?>" target="_blank">Скачать</a> |
                                        <a href="javascript:void(0)" onclick="showLog('<?= basename($log_file) ?>')">Просмотреть</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Лог файлы не найдены. После первого импорта появятся здесь.</p>
            <?php endif; ?>
        </div>
        
        <div class="adm-detail-content-item-block">
            <div class="adm-detail-content-item-block-title">Управление</div>
            
            <div style="margin: 20px 0;">
                <a href="/api-mouser/create_properties.php" class="adm-btn">
                    Настроить свойства инфоблока
                </a>
                
                <a href="/bitrix/admin/iblock_edit.php?ID=<?= INFOBLOCK_ID ?>&type=catalog&lang=ru&admin=Y" 
                   target="_blank" class="adm-btn">
                   Настройки инфоблока
                </a>
                
                <a href="/bitrix/admin/iblist.php?IBLOCK_ID=<?= INFOBLOCK_ID ?>&type=catalog&lang=ru" 
                   target="_blank" class="adm-btn">
                   Просмотреть товары
                </a>
                
                <a href="/api-mouser/cleanup.php" class="adm-btn" style="background: #dc3545;"
                   onclick="return confirm('ОСТОРОЖНО! Удалит все товары и категории. Продолжить?')">
                   Очистить инфоблок
                </a>
            </div>
            
            <div class="adm-info-message-wrap">
                <div class="adm-info-message">
                    <strong>Состояние системы:</strong><br>
                    <?php
                    $checks = [];
                    
                    // Проверка папки для изображений
                    $image_path = defined('IMAGE_PATH') ? IMAGE_PATH : $_SERVER['DOCUMENT_ROOT'] . '/upload/mouser_images/';
                    if (file_exists($image_path) && is_writable($image_path)) {
                        $checks[] = "✓ Папка для изображений доступна";
                    } else {
                        $checks[] = "✗ Папка для изображений недоступна";
                    }
                    
                    // Проверка папки для логов
                    if (file_exists($log_path) && is_writable($log_path)) {
                        $checks[] = "✓ Папка для логов доступна";
                    } else {
                        $checks[] = "✗ Папка для логов недоступна";
                    }
                    
                    // Проверка модуля инфоблоков
                    if (CModule::IncludeModule("iblock")) {
                        $checks[] = "✓ Модуль инфоблоков подключен";
                    } else {
                        $checks[] = "✗ Модуль инфоблоков не подключен";
                    }
                    
                    echo implode("<br>", $checks);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showLog(filename) {
    var url = '/api-mouser/logs/' + filename;
    window.open(url, 'logWindow', 'width=1000,height=600,scrollbars=yes');
}
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>