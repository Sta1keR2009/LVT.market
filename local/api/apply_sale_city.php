<?php
define('NO_KEEP_STATISTIC', true);
define('STOP_STATISTICS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');

if (!check_bitrix_sessid()) {
    echo json_encode(['ok' => false, 'error' => 'sessid'], JSON_UNESCAPED_UNICODE);
    return;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/SaleLocationCityService.php';

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if ($request->getPost('auto') === '1') {
    $cityConfirmed = (($_COOKIE['lvt_city_confirmed'] ?? '') === '1');

    if (!$cityConfirmed) {
        $r = SaleLocationCityService::tryApplyFromIp();
        if (!empty($r['ok'])) {
            SaleLocationCityService::markGeoDoneCookie();
            $r['reload'] = false;
            if (empty($r['city']) && !empty($r['displayCity'])) {
                $r['city'] = $r['displayCity'];
            }
            echo json_encode($r, JSON_UNESCAPED_UNICODE);
            return;
        }

        $skip = (string)($r['skip'] ?? '');
        if (in_array($skip, ['no_ip', 'no_geo_city', 'no_finder_hit'], true)) {
            SaleLocationCityService::markGeoDoneCookie();
        }

        $sync = SaleLocationCityService::ensureLvtCookiesFromCurrentRegion();
        if (!empty($sync['ok'])) {
            $sync['reload'] = false;
            echo json_encode($sync, JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode($r, JSON_UNESCAPED_UNICODE);
        return;
    }

    $sync = SaleLocationCityService::ensureLvtCookiesFromCurrentRegion();
    if (!empty($sync['ok'])) {
        $sync['reload'] = false;
        echo json_encode($sync, JSON_UNESCAPED_UNICODE);
        return;
    }

    echo json_encode(['ok' => false, 'skip' => 'city_confirmed'], JSON_UNESCAPED_UNICODE);
    return;
}

$code = trim((string)$request->getPost('sale_location_code'));
if ($code === '') {
    echo json_encode(['ok' => false, 'error' => 'no_code'], JSON_UNESCAPED_UNICODE);
    return;
}

$r = SaleLocationCityService::applyByLocationCode($code);
$r['reload'] = !empty($r['ok']);
echo json_encode($r, JSON_UNESCAPED_UNICODE);
