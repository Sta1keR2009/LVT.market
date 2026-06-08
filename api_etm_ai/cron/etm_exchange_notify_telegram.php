<?php
/**
 * Статус полного обмена ETM → Telegram (бот онлайн-консультанта @lvtmarket_bot).
 * Те же TELEGRAM_LEAD_* / local/php_interface/telegram_lead_config.php, что ai_consultant_lead.php.
 *
 *   php api_etm_ai/cron/etm_exchange_notify_telegram.php
 *   php api_etm_ai/cron/etm_exchange_notify_telegram.php --text="Свой текст"
 */
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtTelegramProxy.php';

$customText = '';
foreach (array_slice($argv, 1) as $arg) {
    if (strpos($arg, '--text=') === 0) {
        $customText = substr($arg, 7);
    }
}

function etmNotifyLoadTelegramCfg(): array
{
    $cfg = ['bot_token' => '', 'chat_id' => '', 'proxy_url' => ''];
    $envToken = getenv('TELEGRAM_LEAD_BOT_TOKEN');
    $envChat = getenv('TELEGRAM_LEAD_CHAT_ID');
    $envProxy = getenv('TELEGRAM_PROXY_URL');
    if (is_string($envToken) && $envToken !== '') {
        $cfg['bot_token'] = $envToken;
    }
    if (is_string($envChat) && $envChat !== '') {
        $cfg['chat_id'] = $envChat;
    }
    if (is_string($envProxy) && $envProxy !== '') {
        $cfg['proxy_url'] = $envProxy;
    }
    $configFile = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/telegram_lead_config.php';
    if (is_file($configFile)) {
        $localCfg = include $configFile;
        if (is_array($localCfg)) {
            if ($cfg['bot_token'] === '' && !empty($localCfg['bot_token'])) {
                $cfg['bot_token'] = (string)$localCfg['bot_token'];
            }
            if ($cfg['chat_id'] === '' && !empty($localCfg['chat_id'])) {
                $cfg['chat_id'] = (string)$localCfg['chat_id'];
            }
            if ($cfg['proxy_url'] === '' && !empty($localCfg['proxy_url'])) {
                $cfg['proxy_url'] = (string)$localCfg['proxy_url'];
            }
        }
    }
    return $cfg;
}

function etmNotifySend(string $text): bool
{
    $cfg = etmNotifyLoadTelegramCfg();
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

function etmNotifyReadTail(string $path, int $maxBytes = 262144): string
{
    if (!is_readable($path)) {
        return '';
    }
    $fp = fopen($path, 'rb');
    if (!$fp) {
        return '';
    }
    $size = filesize($path);
    $read = min(max(0, $size), $maxBytes);
    if ($read > 0) {
        fseek($fp, -$read, SEEK_END);
    }
    $tail = $read > 0 ? (string)fread($fp, $read) : '';
    fclose($fp);
    return $tail;
}

/** Последнее совпадение в тексте (не первое в хвосте лога). */
function etmNotifyLastMatch(string $text, string $pattern): ?array
{
    if (!preg_match_all($pattern, $text, $m)) {
        return null;
    }
    $n = count($m[0]);
    if ($n === 0) {
        return null;
    }
    $i = $n - 1;
    $out = [];
    foreach ($m as $key => $groups) {
        $out[$key] = $groups[$i];
    }
    return $out;
}

function etmNotifyBuildStatus(): string
{
    $docroot = $_SERVER['DOCUMENT_ROOT'];
    $pidFile = $docroot . '/api_etm_ai/logs/full_exchange.pid';
    $pid = is_file($pidFile) ? trim((string)file_get_contents($pidFile)) : '';
    $running = $pid !== '' && is_numeric($pid) && function_exists('posix_kill') && posix_kill((int)$pid, 0);

    $logsDir = $docroot . '/api_etm_ai/logs';
    $log = '';
    $candidates = glob($logsDir . '/full_exchange_*.log');
    if (is_array($candidates) && $candidates) {
        usort($candidates, static function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });
        $log = $candidates[0];
    }

    $stage = 'неизвестно';
    $batchLine = '';
    $started = '';
    $progressPct = '';
    $tail = $log !== '' ? etmNotifyReadTail($log) : '';

    if ($tail !== '') {
        $startM = etmNotifyLastMatch($tail, '/\[full_exchange\] start ([^\n]+)/');
        if ($startM) {
            $started = trim($startM[0]);
        }
        if (strpos($tail, '=== 3/3 REMAINS') !== false) {
            $stage = '3/3 остатки';
        } elseif (strpos($tail, '=== 2/3 PRICES') !== false) {
            $stage = '2/3 цены';
        } elseif (strpos($tail, '=== 1/3 ENRICH') !== false || strpos($tail, 'enrich batch') !== false) {
            $stage = '1/3 enrich';
        }
        $bm = etmNotifyLastMatch($tail, '/\[full_exchange\] enrich batch (\d+)\/(\d+)/');
        if ($bm) {
            $bn = (int)$bm[0];
            $bt = (int)$bm[1];
            $batchLine = "батч {$bn}/{$bt}";
            if ($bt > 0) {
                $progressPct = (string)round(100 * $bn / $bt) . '%';
            }
        } else {
            $rm = etmNotifyLastMatch($tail, '/\[full_exchange\] remains cycle (\d+)\/(\d+)/');
            if ($rm) {
                $batchLine = 'цикл остатков ' . $rm[0] . '/' . $rm[1];
                $bt = (int)$rm[1];
                if ($bt > 0) {
                    $progressPct = (string)round(100 * (int)$rm[0] / $bt) . '%';
                }
            }
        }
        if (strpos($tail, '[full_exchange] finished') !== false) {
            $stage = 'завершён';
            $running = false;
            $progressPct = '100%';
        }
    }

    $catalogTotal = 0;
    $lastProduct = '';
    $runsToday = 0;
    $enrichLog = $logsDir . '/cron_enrich_' . date('Y-m-d') . '.log';
    $enrichTail = is_file($enrichLog) ? etmNotifyReadTail($enrichLog, 524288) : '';
    if ($enrichTail !== '') {
        $cat = etmNotifyLastMatch($enrichTail, '/К обработке: (\d+) товаров/');
        if ($cat) {
            $catalogTotal = (int)$cat[0];
        }
        $off = etmNotifyLastMatch($enrichTail, '/max=\d+ offset=(\d+)/');
        $lastOff = $off ? (int)$off[0] : 0;
        if (preg_match_all('/=== ETM enrich_chars START/', $enrichTail, $starts)) {
            $runsToday = count($starts[0]);
        }
        if (preg_match_all('/etm=(\S+) el=(\d+)/', $enrichTail, $prods)) {
            $pi = count($prods[0]) - 1;
            if ($pi >= 0) {
                $lastProduct = 'etm=' . $prods[1][$pi] . ' el=' . $prods[2][$pi];
            }
        }
    } else {
        $lastOff = 0;
    }

    $stateFile = $docroot . '/api_etm_ai/logs/enrich_chars_state.json';
    $stateStale = false;
    $stateOffset = 0;
    $stateDone = 0;
    if (is_file($stateFile)) {
        $stateMtime = filemtime($stateFile);
        $raw = file_get_contents($stateFile);
        $j = is_string($raw) ? json_decode($raw, true) : null;
        if (is_array($j)) {
            $stateOffset = (int)($j['offset'] ?? 0);
            $stateDone = (int)($j['total_done'] ?? 0);
        }
        if ($log !== '' && is_file($log)) {
            $exchangeStart = filemtime($log);
            if ($stateMtime !== false && $exchangeStart !== false && $stateMtime < $exchangeStart + 60) {
                $stateStale = true;
            }
        }
    }

    $lines = [
        '📦 ETM — полный обмен IB41',
        'Статус: ' . ($running ? 'выполняется' : ($stage === 'завершён' ? 'завершён' : 'остановлен')),
    ];
    if ($stage !== '') {
        $lines[] = 'Этап: ' . $stage;
    }
    if ($batchLine !== '') {
        $lines[] = $batchLine . ($progressPct !== '' ? ' (~' . $progressPct . ' этапа enrich)' : '');
    }
    if ($catalogTotal > 0) {
        $lines[] = 'Каталог: ' . number_format($catalogTotal, 0, '', ' ') . ' SKU';
    }
    if ($runsToday > 0 && $stage === '1/3 enrich') {
        $lines[] = 'Запусков enrich сегодня: ' . $runsToday;
    }
    if ($lastProduct !== '') {
        $lines[] = 'Сейчас: ' . $lastProduct;
    }
    if ($stateStale && $stateOffset === 0 && $stateDone === 0) {
        $lines[] = '⚠️ offset в state.json не обновляется — прогон может повторять те же SKU';
    } elseif ($stateOffset > 0 || $stateDone > 0) {
        $lines[] = 'Enrich state: offset=' . $stateOffset . ', всего=' . $stateDone;
    }
    if ($started !== '') {
        $lines[] = 'Старт: ' . $started;
    }
    if ($pid !== '') {
        $lines[] = 'PID: ' . $pid;
    }
    if ($log !== '') {
        $lines[] = 'Лог: ' . basename($log);
    }
    $lines[] = 'Время: ' . date('Y-m-d H:i:s');

    return implode("\n", $lines);
}

$text = $customText !== '' ? $customText : etmNotifyBuildStatus();
$prefix = "🔔 lvt.market — мониторинг обмена\n";
$ok = etmNotifySend($prefix . $text);
exit($ok ? 0 : 1);
