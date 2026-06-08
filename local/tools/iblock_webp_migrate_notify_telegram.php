<?php
/**
 * Статус миграции iblock → WebP в Telegram (@lvtmarket_bot / TELEGRAM_LEAD_*).
 *
 *   sudo -u www-root php local/tools/iblock_webp_migrate_notify_telegram.php
 *   sudo -u www-root php local/tools/iblock_webp_migrate_notify_telegram.php --text="Свой текст"
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die("CLI only\n");
}

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_CRONTAB', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtTelegramProxy.php';

$customText = '';
foreach (array_slice($argv ?? [], 1) as $arg) {
    if (strpos($arg, '--text=') === 0) {
        $customText = substr($arg, 7);
    }
}

function webpNotifyLoadTelegramCfg(): array
{
    $cfg = ['bot_token' => '', 'chat_id' => '', 'proxy_url' => ''];
    foreach (['TELEGRAM_LEAD_BOT_TOKEN' => 'bot_token', 'TELEGRAM_LEAD_CHAT_ID' => 'chat_id', 'TELEGRAM_PROXY_URL' => 'proxy_url'] as $env => $key) {
        $v = getenv($env);
        if (is_string($v) && $v !== '') {
            $cfg[$key] = $v;
        }
    }
    $configFile = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/telegram_lead_config.php';
    if (is_file($configFile)) {
        $localCfg = include $configFile;
        if (is_array($localCfg)) {
            foreach (['bot_token', 'chat_id', 'proxy_url'] as $k) {
                if ($cfg[$k] === '' && !empty($localCfg[$k])) {
                    $cfg[$k] = (string)$localCfg[$k];
                }
            }
        }
    }
    return $cfg;
}

function webpNotifySend(string $text): bool
{
    $cfg = webpNotifyLoadTelegramCfg();
    if ($cfg['bot_token'] === '' || $cfg['chat_id'] === '') {
        fwrite(STDERR, "telegram_not_configured\n");
        return false;
    }
    if ($cfg['proxy_url'] !== '' && getenv('TELEGRAM_PROXY_URL') === false) {
        putenv('TELEGRAM_PROXY_URL=' . $cfg['proxy_url']);
    }

    $url = 'https://api.telegram.org/bot' . $cfg['bot_token'] . '/sendMessage';
    $post = [
        'chat_id' => $cfg['chat_id'],
        'text' => mb_substr($text, 0, 4000),
        'disable_web_page_preview' => true,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);
    LvtTelegramProxy::applyToCurl($ch);
    $resp = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false || $code !== 200) {
        fwrite(STDERR, "telegram_send HTTP {$code}\n" . (string)$resp . "\n");
        return false;
    }
    return true;
}

function webpNotifyCountManifest(string $path, string $status): int
{
    if (!is_file($path)) {
        return 0;
    }
    $needle = ',' . $status . ',';
    $count = 0;
    $fh = fopen($path, 'rb');
    if (!$fh) {
        return 0;
    }
    while (($line = fgets($fh)) !== false) {
        if (strpos($line, $needle) !== false) {
            $count++;
        }
    }
    fclose($fh);
    return $count;
}

function webpNotifyFormatBytes(int $bytes): string
{
    if ($bytes >= 1073741824) {
        return round($bytes / 1073741824, 1) . ' ГБ';
    }
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 1) . ' МБ';
    }
    return round($bytes / 1024, 1) . ' КБ';
}

function webpNotifyBuildStatus(): string
{
    global $DB;

    $docroot = $_SERVER['DOCUMENT_ROOT'];
    $stateFile = $docroot . '/upload/_ORIGINALIMG/migrate_last_id.txt';
    $manifest = $docroot . '/upload/_ORIGINALIMG/migrate_manifest.csv';
    $pidFile = $docroot . '/upload/_ORIGINALIMG/migrate_loop.pid';
    $logFile = $docroot . '/upload/_ORIGINALIMG/migrate_loop.log';

    $lastId = is_file($stateFile) ? (int)trim((string)file_get_contents($stateFile)) : 0;

    $left = (int)$DB->Query("
        SELECT COUNT(*) C FROM b_file
        WHERE SUBDIR LIKE 'iblock/%'
          AND CONTENT_TYPE IN ('image/jpeg', 'image/png')
    ")->Fetch()['C'];

    $webp = (int)$DB->Query("
        SELECT COUNT(*) C FROM b_file
        WHERE SUBDIR LIKE 'iblock/%' AND CONTENT_TYPE = 'image/webp'
    ")->Fetch()['C'];

    $maxId = (int)$DB->Query("
        SELECT MAX(ID) C FROM b_file WHERE SUBDIR LIKE 'iblock/%'
    ")->Fetch()['C'];

    $converted = webpNotifyCountManifest($manifest, 'converted');
    $forced = webpNotifyCountManifest($manifest, 'forced_webp');
    $skipped = webpNotifyCountManifest($manifest, 'skipped_below_target');
    $missing = webpNotifyCountManifest($manifest, 'missing');
    $errors = webpNotifyCountManifest($manifest, 'error');

    $done = $converted + $forced;
    $totalQueue = $done + $left;
    $pctQueue = $totalQueue > 0 ? round($done / $totalQueue * 100, 1) : 0;
    $pctId = ($maxId > 0 && $lastId > 0) ? round($lastId / $maxId * 100, 1) : 0;

    $running = false;
    $loopPid = '';
    if (is_file($pidFile)) {
        $loopPid = trim((string)file_get_contents($pidFile));
        if ($loopPid !== '' && ctype_digit($loopPid) && function_exists('posix_kill')) {
            $running = posix_kill((int)$loopPid, 0);
        }
    }

    $lastBatch = '';
    if (is_readable($logFile)) {
        $tail = shell_exec('tail -n 80 ' . escapeshellarg($logFile)) ?: '';
        if (preg_match('/Batch done\. last-id=(\d+) ok=(\d+) skip=(\d+) err=(\d+)/', $tail, $m)) {
            $lastBatch = "батч id≤{$m[1]}: ok={$m[2]}, skip={$m[3]}, err={$m[4]}";
        } elseif (preg_match('/batch ok=(\d+) skip=(\d+) last-id=(\d+)/', $tail, $m)) {
            $lastBatch = "батч: ok={$m[1]}, skip={$m[2]}, id={$m[3]}";
        }
    }

    $archiveSize = '';
    $archiveDir = $docroot . '/upload/_ORIGINALIMG';
    if (is_dir($archiveDir)) {
        $du = trim((string)shell_exec('du -sh ' . escapeshellarg($archiveDir) . ' 2>/dev/null | cut -f1'));
        if ($du !== '') {
            $archiveSize = $du;
        }
    }

    $lines = [
        '🖼 WebP — миграция iblock',
        'Статус: ' . ($running ? 'выполняется' : 'остановлена'),
        '',
        "✅ Сконвертировано: {$done} шт.",
        "⏳ Осталось jpg/png: {$left} шт.",
        "📊 Прогресс: {$pctQueue}% ({$done} из {$totalQueue})",
        "🔢 WebP в каталоге (всего): {$webp} шт.",
        '',
        "⏭ Пропущено (менее 18% выигрыша): {$skipped}",
        "📁 Файл не найден: {$missing}",
    ];
    if ($errors > 0) {
        $lines[] = "❌ Ошибки: {$errors}";
    }
    $lines[] = '';
    $lines[] = "Текущий ID: {$lastId} / {$maxId} ({$pctId}%)";
    if ($lastBatch !== '') {
        $lines[] = 'Последний: ' . $lastBatch;
    }
    if ($loopPid !== '') {
        $lines[] = 'PID цикла: ' . $loopPid;
    }
    if ($archiveSize !== '') {
        $lines[] = 'Архив оригиналов: ' . $archiveSize;
    }
    $lines[] = 'Время: ' . date('Y-m-d H:i:s');

    return implode("\n", $lines);
}

$text = $customText !== '' ? $customText : webpNotifyBuildStatus();
$prefix = "🔔 lvt.market — WebP iblock\n";
$ok = webpNotifySend($prefix . $text);
exit($ok ? 0 : 1);
