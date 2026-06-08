<?php

use Bitrix\Main\Loader;

/**
 * Режим «поиск по партномеру»: если в каталоге пусто, но в Mouser ровно одна позиция — создать карточку и сразу редирект на детальную.
 */
class LvtMouserCatalogSearchRedirectHelper
{
    private static function debugLog(string $runId, string $hypothesisId, string $location, string $message, array $data = []): void
    {
    }

    /**
     * При успешном редиректе не возвращает управление (LocalRedirect).
     */
    public static function redirectIfSingleMouserCatalogHit(int $catalogIblockId, string $query): void
    {
        if (!function_exists('lvt_is_mouser_api_enabled') || !lvt_is_mouser_api_enabled()) {
            return;
        }

        $runId = 'pre-fix-' . substr(md5($catalogIblockId . '|' . $query . '|' . microtime(true)), 0, 10);
        $query = trim($query);
        self::debugLog($runId, 'H2', 'LvtMouserCatalogSearchRedirectHelper.php:31', 'redirectIfSingleMouserCatalogHit enter', [
            'catalogIblockId' => $catalogIblockId,
            'query' => $query,
            'queryLen' => mb_strlen($query),
        ]);

        if ($catalogIblockId <= 0 || mb_strlen($query) < 3 || !Loader::includeModule('iblock')) {
            self::debugLog($runId, 'H2', 'LvtMouserCatalogSearchRedirectHelper.php:39', 'redirect helper early return', [
                'reason' => 'invalid_input_or_no_iblock_module',
            ]);
            return;
        }

        $dr = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
        if ($dr === '') {
            self::debugLog($runId, 'H2', 'LvtMouserCatalogSearchRedirectHelper.php:46', 'redirect helper early return', [
                'reason' => 'empty_document_root',
            ]);
            return;
        }

        $client = $dr . '/mouser/mouser_client.php';
        if (!is_file($client)) {
            self::debugLog($runId, 'H2', 'LvtMouserCatalogSearchRedirectHelper.php:54', 'redirect helper early return', [
                'reason' => 'missing_mouser_client',
                'client' => $client,
            ]);
            return;
        }
        require_once $client;
        require_once $dr . '/local/php_interface/classes/MouserCatalogElementHelper.php';
        require_once $dr . '/local/php_interface/classes/MouserCatalogSearchBridge.php';
        require_once $dr . '/local/php_interface/classes/GetchipsPartnumberSearchHelper.php';

        $mouserPn = '';

        if (function_exists('mouser_part_number_search')) {
            $res = mouser_part_number_search($query);
            self::debugLog($runId, 'H3', 'LvtMouserCatalogSearchRedirectHelper.php:68', 'mouser_part_number_search response', [
                'ok' => !empty($res['ok']),
                'partsCount' => is_array($res['data']['SearchResults']['Parts'] ?? null) ? count($res['data']['SearchResults']['Parts']) : -1,
            ]);
            if (!empty($res['ok']) && is_array($res['data'] ?? null)) {
                $parts = $res['data']['SearchResults']['Parts'] ?? null;
                if (is_array($parts) && count($parts) === 1 && is_array($parts[0])) {
                    $mouserPn = trim((string) ($parts[0]['MouserPartNumber'] ?? ''));
                    if ($mouserPn === '') {
                        $mouserPn = $query;
                    }
                }
            }
        }

        if ($mouserPn === '') {
            $rows = MouserCatalogSearchBridge::searchPartsCached($query);
            self::debugLog($runId, 'H3', 'LvtMouserCatalogSearchRedirectHelper.php:82', 'fallback keyword search rows', [
                'rowsCount' => count($rows),
            ]);
            if (count($rows) !== 1) {
                self::debugLog($runId, 'H3', 'LvtMouserCatalogSearchRedirectHelper.php:86', 'redirect helper exit: no single mouser hit', [
                    'rowsCount' => count($rows),
                ]);
                return;
            }
            $mouserPn = trim((string) ($rows[0]['MouserPartNumber'] ?? ''));
            if ($mouserPn === '') {
                return;
            }
        }

        $id = MouserCatalogElementHelper::upsertFromMouserPartNumber($catalogIblockId, $mouserPn);
        self::debugLog($runId, 'H4', 'LvtMouserCatalogSearchRedirectHelper.php:96', 'upsertFromMouserPartNumber result', [
            'mouserPn' => $mouserPn,
            'elementId' => $id,
        ]);
        if ($id <= 0) {
            return;
        }

        $url = GetchipsPartnumberSearchHelper::getElementDetailUrl($id);
        self::debugLog($runId, 'H5', 'LvtMouserCatalogSearchRedirectHelper.php:104', 'detail URL after upsert', [
            'elementId' => $id,
            'url' => $url,
        ]);
        if ($url === '') {
            return;
        }

        LocalRedirect($url, true, '302 Found');
    }
}
