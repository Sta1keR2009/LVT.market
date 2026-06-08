<?php
/**
 * Статистика и логи обмена API ETM — просмотр в браузере.
 */

require_once __DIR__ . '/bootstrap.php';

header('Content-Type: text/html; charset=utf-8');

$logDir = defined('API_ETM_LOGS_DIR') ? API_ETM_LOGS_DIR : __DIR__ . '/logs';
$logs = ApiEtmLogger::listLogs($logDir);

$view = $_GET['view'] ?? 'list';
$file = $_GET['file'] ?? '';

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API ETM — логи и статистика</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 1rem 2rem; background: #f8f9fa; }
        h1 { color: #0d6efd; }
        a { color: #0d6efd; }
        .nav { margin: 1rem 0; }
        .nav a { margin-right: 1rem; }
        table { border-collapse: collapse; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        th, td { padding: .5rem .75rem; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background: #e9ecef; }
        pre { background: #1e1e1e; color: #d4d4d4; padding: 1rem; overflow-x: auto; font-size: 13px; }
        .log-line { font-family: monospace; font-size: 13px; margin: .2rem 0; }
        .log-ERROR { color: #dc3545; }
        .log-WARN { color: #fd7e14; }
        .log-INFO { color: #0d6efd; }
        .meta { color: #6c757d; font-size: 0.9rem; }
    </style>
</head>
<body>
    <h1>API ETM — логи и статистика</h1>
    <div style="background:#fff3cd;padding:.75rem 1rem;margin:1rem 0;border-radius:6px;max-width:1100px;">
        <strong>Тест на выбранных ETM-кодах</strong> (без полного прогона): в браузере или CLI добавьте
        <code>etm_ids</code> / <code>--etm-ids=</code> к скриптам
        <code>cron/enrich_chars.php</code>, <code>cron/update_prices_ib40.php</code>, <code>cron/update_remains_ib40.php</code>.
        Пример CLI: <code>php cron/enrich_chars.php --etm-ids=169992356 --max=1</code>
    </div>
    <div class="nav">
        <a href="index.php">Логи</a>
        <a href="test_format.php">Тест формата данных</a>
        <a href="run_debug.php">Отладка (без импорта)</a>
        <?php
        $iblockId = defined('API_ETM_IBLOCK_ID') ? API_ETM_IBLOCK_ID : 40;
        $rootSectionId = defined('API_ETM_ROOT_SECTION_ID') ? API_ETM_ROOT_SECTION_ID : 3650;
        echo '<span style="margin-left: 2rem; color: #6c757d;">Инфоблок: ' . htmlspecialchars($iblockId) . ' | Раздел: ' . htmlspecialchars($rootSectionId) . '</span>';
        ?>
        <?php
        $errLog = $logDir . '/api_etm_errors.log';
        if (is_file($errLog)) {
            echo ' <a href="index.php?view=file&amp;file=' . urlencode(basename($errLog)) . '">Лог ошибок</a>';
        }
        ?>
        <form action="import.php" method="get" style="display:inline;">
            <label>Импорт: лимит <input type="number" name="limit" value="50" min="1" max="10000" style="width:70px;"> </label>
            <button type="submit">Запустить</button>
        </form>
    </div>

<?php
$statsPath = $logDir . '/last_import_stats.json';
$lastStats = (is_file($statsPath)) ? json_decode(file_get_contents($statsPath), true) : null;
$importStatus = $_GET['import'] ?? null;
?>
<?php if ($lastStats && ($importStatus === 'ok' || $importStatus === null)): ?>
    <div style="background:<?= $importStatus === 'ok' ? '#d1e7dd' : '#e7f1ff' ?>;padding:1rem;margin:1rem 0;border-radius:6px;">
        <h3>Последний импорт<?= $importStatus === 'ok' ? ' <small>(только что)</small>' : '' ?></h3>
        <p>Старт: <?= htmlspecialchars($lastStats['started_at'] ?? '') ?> — окончание: <?= htmlspecialchars($lastStats['finished_at'] ?? '') ?></p>
        <p>Товаров получено: <?= (int)($lastStats['products_total'] ?? 0) ?> |
           создано: <?= (int)($lastStats['products_created'] ?? 0) ?> |
           обновлено: <?= (int)($lastStats['products_updated'] ?? 0) ?> |
           пропущено: <?= (int)($lastStats['products_skipped'] ?? 0) ?> |
           ошибок: <?= (int)($lastStats['errors'] ?? 0) ?></p>
        <?php if (!empty($lastStats['log_file']) && is_file($lastStats['log_file'])): ?>
            <?php $logName = basename($lastStats['log_file']); ?>
            <p><a href="index.php?view=file&file=<?= urlencode($logName) ?>">Открыть лог (<?= htmlspecialchars($logName) ?>)</a></p>
        <?php endif; ?>
    </div>
<?php elseif ($importStatus === 'error'): ?>
    <div style="background:#f8d7da;padding:1rem;margin:1rem 0;border-radius:6px;">
        <p>Импорт завершился с ошибкой. Проверьте <a href="index.php">логи</a> и <a href="test_format.php">формат данных API</a>.</p>
        <?php if ($lastStats && !empty($lastStats['log_file']) && is_file($lastStats['log_file'])): ?>
            <p><a href="index.php?view=file&file=<?= urlencode(basename($lastStats['log_file'])) ?>">Открыть лог последнего запуска</a></p>
        <?php endif; ?>
    </div>
<?php elseif ($importStatus === 'empty'): ?>
    <div style="background:#fff3cd;padding:1rem;margin:1rem 0;border-radius:6px;">
        <p>Нет товаров для импорта. Проверьте <a href="test_format.php">формат данных API</a>.</p>
    </div>
<?php endif; ?>
<?php if ($view === 'file' && $file && preg_match('/^[\w\.\-]+\.log$/', $file)): ?>
    <?php
    $path = $logDir . '/' . $file;
    $content = is_file($path) ? file_get_contents($path) : '';
    ?>
    <h2>Лог: <?= htmlspecialchars($file) ?></h2>
    <p class="meta">Размер: <?= number_format(strlen($content)) ?> байт</p>
    <pre><?= htmlspecialchars($content) ?></pre>
    <p><a href="index.php">← к списку</a></p>
<?php else: ?>
    <h2>Файлы логов</h2>
    <?php if (empty($logs)): ?>
        <p>Логов пока нет.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Файл</th>
                    <th>Изменён</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['name']) ?></td>
                    <td><?= date('Y-m-d H:i:s', $log['mtime']) ?></td>
                    <td><a href="index.php?view=file&file=<?= urlencode($log['name']) ?>">Открыть</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>
