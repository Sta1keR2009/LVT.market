<?php
/**
 * Логирование обмена API ETM + статистика для просмотра в браузере.
 */

class ApiEtmLogger
{
    private string $logDir;
    private string $sessionLog;
    private array $stats = [
        'started_at' => '',
        'finished_at' => '',
        'requests' => 0,
        'products_total' => 0,
        'products_created' => 0,
        'products_updated' => 0,
        'products_skipped' => 0,
        'errors' => 0,
        'messages' => [],
    ];

    public function __construct(?string $logDir = null)
    {
        $this->logDir = $logDir ?? (defined('API_ETM_LOGS_DIR') ? API_ETM_LOGS_DIR : __DIR__ . '/logs');
        $this->sessionLog = $this->logDir . '/exchange_' . date('Y-m-d_H-i-s') . '.log';
        $this->stats['started_at'] = date('Y-m-d H:i:s');
        if (!is_dir($this->logDir)) {
            @mkdir($this->logDir, 0755, true);
        }
    }

    public function log(string $msg, string $level = 'INFO'): void
    {
        $line = date('Y-m-d H:i:s') . ' [' . $level . '] ' . $msg . "\n";
        @file_put_contents($this->sessionLog, $line, FILE_APPEND | LOCK_EX);
        $this->stats['messages'][] = ['t' => date('H:i:s'), 'l' => $level, 'm' => $msg];
        if ($level === 'ERROR') {
            $this->stats['errors']++;
        }
    }

    public function incRequests(): void
    {
        $this->stats['requests']++;
    }

    public function incCreated(): void
    {
        $this->stats['products_created']++;
    }

    public function incUpdated(): void
    {
        $this->stats['products_updated']++;
    }

    public function incSkipped(): void
    {
        $this->stats['products_skipped']++;
    }

    public function addProductsTotal(int $n): void
    {
        $this->stats['products_total'] += $n;
    }

    public function finish(): void
    {
        $this->stats['finished_at'] = date('Y-m-d H:i:s');
    }

    public function getStats(): array
    {
        return $this->stats;
    }

    public function getSessionLogPath(): string
    {
        return $this->sessionLog;
    }

    /**
     * Список файлов логов (последние сначала).
     */
    public static function listLogs(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }
        $files = [];
        foreach (glob($dir . '/*.log') ?: [] as $f) {
            $files[] = ['path' => $f, 'name' => basename($f), 'mtime' => filemtime($f)];
        }
        usort($files, fn($a, $b) => $b['mtime'] - $a['mtime']);
        return $files;
    }
}
