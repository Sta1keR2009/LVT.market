<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (empty($arResult['SHARE_BASKET']['ITEMS']) || !empty($arResult['ERRORS'])) {
    return;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsShareBasketDisplayHelper.php';

GetchipsShareBasketDisplayHelper::enrichShareBasketResult($arResult);
