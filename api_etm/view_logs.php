<?php
// Просмотр логов ETM интеграции
header('Content-Type: text/plain; charset=utf-8');

$logDir = __DIR__ . '/logs/';

if (!is_dir($logDir)) {
    echo "Папка логов не найдена: $logDir\n";
    echo "Создаём...\n";
    mkdir($logDir, 0755, true);
    echo "Папка создана. Логи появятся после первого заказа.\n";
    exit;
}

$files = glob($logDir . '*.log');

if (empty($files)) {
    echo "=== Логи ETM интеграции ===\n\n";
    echo "Логов пока нет.\n";
    echo "Они появятся после создания заказа с товаром ETM.\n";
    exit;
}

// Сортируем по дате изменения (новые первые)
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$file = isset($_GET['file']) ? $_GET['file'] : '';
$lines = isset($_GET['lines']) ? intval($_GET['lines']) : 100;

if ($file && file_exists($logDir . basename($file))) {
    $filepath = $logDir . basename($file);
    echo "=== " . basename($file) . " ===\n";
    echo "Размер: " . round(filesize($filepath) / 1024, 2) . " KB\n";
    echo "Изменён: " . date('Y-m-d H:i:s', filemtime($filepath)) . "\n";
    echo "Последние $lines строк:\n";
    echo "-------------------------------------------\n\n";

    // Читаем последние N строк
    $content = file($filepath);
    $content = array_slice($content, -$lines);
    echo implode('', $content);
} else {
    echo "=== Логи ETM интеграции ===\n\n";
    echo "Доступные файлы:\n\n";

    foreach ($files as $f) {
        $name = basename($f);
        $size = round(filesize($f) / 1024, 2);
        $date = date('Y-m-d H:i:s', filemtime($f));
        echo "- $name ($size KB, $date)\n";
        echo "  ?file=$name&lines=100\n\n";
    }
}
