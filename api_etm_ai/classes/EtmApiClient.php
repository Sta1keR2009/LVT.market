<?php
/**
 * Клиент API ETM (itest2.etm.ru).
 * Авторизация POST /user/login, ключ сессии 8 ч.
 * Лимиты: логин 1/2 мин; goods, price, remains — 1 запрос/сек; price до 50 ids в запросе.
 */

class ApiEtmClient
{
    private string $baseUrl;
    private string $login;
    private string $password;
    private string $statePath;
    private int $timeout = 30;
    private int $connectTimeout = 10; // таймаут подключения
    private int $loginIntervalSec;
    private int $sessionTtlSec;
    private int $throttleSec;
    private int $priceBatchSize;

    /** @var array|null */
    public $lastRawResponse;
    /** @var int */
    public $lastHttpCode = 0;
    /** @var string */
    public $lastError = '';

    public function __construct(
        string $baseUrl,
        string $login,
        string $password,
        ?string $statePath = null
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->login = $login;
        $this->password = $password;
        $this->statePath = $statePath ?? (defined('API_ETM_STATE_FILE') ? API_ETM_STATE_FILE : '');
        $this->loginIntervalSec = defined('API_ETM_LOGIN_INTERVAL_SEC') ? (int)API_ETM_LOGIN_INTERVAL_SEC : 120;
        $this->sessionTtlSec = defined('API_ETM_SESSION_TTL_SEC') ? (int)API_ETM_SESSION_TTL_SEC : 28800;
        $this->throttleSec = defined('API_ETM_GOODS_PRICE_REMAINS_INTERVAL_SEC') ? (int)API_ETM_GOODS_PRICE_REMAINS_INTERVAL_SEC : 1;
        $this->priceBatchSize = defined('API_ETM_PRICE_BATCH_SIZE') ? (int)API_ETM_PRICE_BATCH_SIZE : 50;
    }

    private function loadState(): array
    {
        if ($this->statePath === '' || !is_readable($this->statePath)) {
            return [];
        }
        $j = @file_get_contents($this->statePath);
        if ($j === false) {
            return [];
        }
        $a = json_decode($j, true);
        return is_array($a) ? $a : [];
    }

    private function saveState(array $s): void
    {
        if ($this->statePath === '') {
            return;
        }
        $dir = dirname($this->statePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $fp = @fopen($this->statePath, 'cb');
        if (!$fp) {
            return;
        }
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return;
        }
        ftruncate($fp, 0);
        fwrite($fp, json_encode($s, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    private function throttle(string $key, int $intervalSec): void
    {
        $s = $this->loadState();
        $last = (int)($s['last_' . $key . '_at'] ?? 0);
        $now = time();
        $wait = $intervalSec - ($now - $last);
        if ($wait > 0) {
            // Ограничиваем максимальное время ожидания для предотвращения таймаутов
            // Для логина может быть до 120 сек, но для веб-интерфейса ограничиваем до 10 сек максимум
            // Для других операций ограничиваем до 2 сек
            $maxWait = ($key === 'login') ? min($wait, 10) : min($wait, 2);
            if ($maxWait > 0 && $maxWait <= 10) {
                // Используем flush для веб-интерфейса, чтобы пользователь видел прогресс
                if (php_sapi_name() !== 'cli' && function_exists('flush')) {
                    // Разбиваем ожидание на маленькие кусочки с flush
                    $chunks = ceil($maxWait);
                    for ($i = 0; $i < $chunks && $i < 10; $i++) {
                        sleep(1);
                        if (function_exists('flush')) {
                            @flush();
                        }
                    }
                } else {
                    sleep($maxWait);
                }
            }
        }
    }

    private function markRequest(string $key): void
    {
        $s = $this->loadState();
        $s['last_' . $key . '_at'] = time();
        $this->saveState($s);
    }

    /**
     * Авторизация: POST /user/login?log=...&pwd=..., не чаще 1 раза в 2 минуты.
     * API принимает параметры в query-строке, а не в JSON-теле.
     * Ключ сессии сохраняется, действует 8 часов.
     */
    public function login(): bool
    {
        $this->throttle('login', $this->loginIntervalSec);

        $url = $this->baseUrl . '/user/login'
            . '?log=' . rawurlencode($this->login)
            . '&pwd=' . rawurlencode($this->password);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $raw = curl_exec($ch);
        $this->lastHttpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->lastError = curl_error($ch);
        $this->lastRawResponse = $raw;
        curl_close($ch);

        $this->markRequest('login');

        if ($raw === false || $this->lastHttpCode < 200 || $this->lastHttpCode >= 300) {
            return false;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return false;
        }

        $keys = ['token', 'session_key', 'access_token', 'key', 'session', 'sessionId', 'session_id'];
        $key = null;
        foreach ($keys as $k) {
            if (isset($data[$k]) && is_string($data[$k]) && $data[$k] !== '') {
                $key = $data[$k];
                break;
            }
        }
        if ($key === null && isset($data['data']) && is_array($data['data'])) {
            foreach ($keys as $k) {
                if (isset($data['data'][$k]) && is_string($data['data'][$k]) && $data['data'][$k] !== '') {
                    $key = $data['data'][$k];
                    break;
                }
            }
        }
        if ($key === null || $key === '') {
            return false;
        }

        $s = $this->loadState();
        $s['session_key'] = $key;
        $s['session_expires_at'] = time() + $this->sessionTtlSec;
        $this->saveState($s);

        return true;
    }

    /**
     * Проверить сессию и при необходимости выполнить логин.
     */
    public function ensureAuth(): bool
    {
        $s = $this->loadState();
        $key = $s['session_key'] ?? '';
        $exp = (int)($s['session_expires_at'] ?? 0);
        if ($key !== '' && $exp > time() + 60) {
            return true;
        }
        return $this->login();
    }

    private function getSessionKey(): string
    {
        $s = $this->loadState();
        return (string)($s['session_key'] ?? '');
    }

    /**
     * Запрос с ключом сессии (и опционально throttle).
     * Сессия передаётся как query-параметр ?session-id=... согласно OpenAPI спеке ETM.
     */
    private function request(string $path, string $method = 'GET', ?array $body = null, ?string $throttleKey = null): ?array
    {
        if ($throttleKey !== null) {
            $this->throttle($throttleKey, $this->throttleSec);
        }

        // Добавляем session-id как query-параметр (требование ETM API)
        $key = $this->getSessionKey();
        if ($key !== '') {
            $sep = (strpos($path, '?') !== false) ? '&' : '?';
            $path .= $sep . 'session-id=' . rawurlencode($key);
        }

        $url = $this->baseUrl . $path;
        $ch = curl_init($url);
        $headers = ['Content-Type: application/json', 'Accept: application/json'];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
            }
        }

        $raw = curl_exec($ch);
        $this->lastHttpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->lastError = curl_error($ch);
        $this->lastRawResponse = $raw;
        curl_close($ch);

        if ($throttleKey !== null) {
            $this->markRequest($throttleKey);
        }

        if ($raw === false) {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        return ['_raw' => $raw];
    }

    /**
     * GET /goods/{id}?type=etm — характеристики товара. 1 запрос/сек.
     */
    public function getGoods(string $id, string $type = 'etm'): ?array
    {
        if (!$this->ensureAuth()) {
            return null;
        }
        return $this->request('/goods/' . rawurlencode($id) . '?type=' . $type, 'GET', null, 'goods');
    }

    /**
     * GET /goods/{ids}/price?type=etm — цены пакетами до 50 id. 1 запрос/сек.
     * Возвращает плоский массив rows [{gdscode, price, pricewnds, price_tarif, price_retail}].
     */
    public function getGoodsPrice(array $ids, string $type = 'etm'): ?array
    {
        if (!$this->ensureAuth()) {
            return null;
        }
        $ids = array_values(array_unique(array_map('strval', $ids)));
        $all = [];
        foreach (array_chunk($ids, $this->priceBatchSize) as $chunk) {
            $q = implode(',', $chunk);
            $data = $this->request('/goods/' . $q . '/price?type=' . $type, 'GET', null, 'price');
            if ($data !== null && isset($data['data']['rows']) && is_array($data['data']['rows'])) {
                foreach ($data['data']['rows'] as $row) {
                    $all[] = $row;
                }
            }
        }
        return $all ?: null;
    }

    /**
     * GET /goods/{id}/remains?type=etm — остатки по складам. 1 запрос/сек.
     * Суммарный остаток в InfoStores с StoreType='all'.
     */
    public function getGoodsRemains(string $id, string $type = 'etm'): ?array
    {
        if (!$this->ensureAuth()) {
            return null;
        }
        return $this->request('/goods/' . rawurlencode($id) . '/remains?type=' . $type, 'GET', null, 'remains');
    }

    /**
     * POST /job/create/40029846 — запуск формирования выгрузки товаров клиента.
     * Возвращает ['uuid' => '...'] или null.
     */
    public function createGoodsJob(): ?array
    {
        if (!$this->ensureAuth()) {
            return null;
        }
        $data = $this->request('/job/create/40029846', 'POST', null, 'api');
        if ($data && isset($data['data']['uuid'])) {
            return $data['data'];
        }
        return null;
    }

    /**
     * GET /job/{uuid} — статус задачи.
     * Возвращает первый row из data.rows или null.
     * Ключи: state ('0','1','2','3'), completed ('true'/'false'), urls[].
     */
    public function getJobStatus(string $uuid): ?array
    {
        if (!$this->ensureAuth()) {
            return null;
        }
        $data = $this->request('/job/' . rawurlencode($uuid), 'GET', null, 'api');
        if ($data && isset($data['data']['rows'][0])) {
            return $data['data']['rows'][0];
        }
        return null;
    }

    /**
     * Скачать файл отчёта по URL (с куки сессии ETM).
     * Возвращает содержимое файла или false при ошибке.
     */
    public function downloadReportFile(string $url)
    {
        $s = $this->loadState();
        $sid = (string)($s['session_key'] ?? '');

        // Пробуем с session-id в query
        $sep = strpos($url, '?') !== false ? '&' : '?';
        $fullUrl = $url . $sep . 'session-id=' . rawurlencode($sid);

        $cookieFile = sys_get_temp_dir() . '/etm_session.txt';
        $ch = curl_init($fullUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR  => $cookieFile,
            CURLOPT_COOKIEFILE => $cookieFile,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw = curl_exec($ch);
        $this->lastHttpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->lastError = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $this->lastHttpCode !== 200) {
            return false;
        }
        return $raw;
    }

    /**
     * Универсальный GET (для тестов). Throttle 1/сек, если сессия есть.
     */
    public function rawGet(string $path = '/'): ?array
    {
        if ($this->ensureAuth()) {
            $this->throttle('api', $this->throttleSec);
            $data = $this->request($path, 'GET', null, null);
            $this->markRequest('api');
            return $data;
        }
        return $this->request($path, 'GET', null, null);
    }

    /**
     * Универсальный POST (для тестов). Throttle 1/сек.
     */
    public function rawPost(string $path, array $body): ?array
    {
        if ($this->ensureAuth()) {
            $this->throttle('api', $this->throttleSec);
            $data = $this->request($path, 'POST', $body, null);
            $this->markRequest('api');
            return $data;
        }
        return $this->request($path, 'POST', $body, null);
    }

    /**
     * Каталог/список товаров. Пробует типичные эндпоинты (с auth + throttle).
     */
    public function fetchCatalog(?int $limit = 10, ?int $offset = 0): ?array
    {
        if (!$this->ensureAuth()) {
            return null;
        }

        // Ограничиваем количество попыток для предотвращения долгого выполнения
        $paths = ['/goods', '/catalog', '/products'];
        $maxAttempts = 3; // Пробуем только первые 3 пути
        
        foreach (array_slice($paths, 0, $maxAttempts) as $path) {
            $sep = strpos($path, '?') !== false ? '&' : '?';
            $full = $path . $sep . 'limit=' . ($limit ?: 10) . '&offset=' . ($offset ?: 0);
            $this->throttle('api', $this->throttleSec);
            $data = $this->request($full, 'GET', null, null);
            $this->markRequest('api');
            if ($data !== null && $this->lastHttpCode >= 200 && $this->lastHttpCode < 300 && !isset($data['_raw'])) {
                return $data;
            }
        }

        return null;
    }

    /**
     * POST с login/password в теле (RPC-стиль, напр. items_data_get). Не используется для /user/login.
     * Throttle 1/сек.
     */
    public function requestPostRpc(string $path, string $method, array $params = []): ?array
    {
        $body = array_merge(['login' => $this->login, 'password' => $this->password, 'method' => $method], $params);
        $this->throttle('api', $this->throttleSec);
        $url = $this->baseUrl . $path;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw = curl_exec($ch);
        $this->lastHttpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->lastError = curl_error($ch);
        $this->lastRawResponse = $raw;
        curl_close($ch);
        $this->markRequest('api');
        if ($raw === false) {
            return null;
        }
        $d = json_decode($raw, true);
        return is_array($d) ? $d : ['_raw' => $raw];
    }
}
