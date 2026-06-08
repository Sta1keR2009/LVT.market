<?php

/**
 * Пересчёт цены за единицу по ступеням Getchips при изменении количества в корзине.
 */

use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Sale\BasketBase;

class GetchipsBasketRecalculateHandler
{
    public static function onBasketBeforeSaved(Event $event): void
    {
        if (!Loader::includeModule('sale')) {
            return;
        }

        $basket = $event->getParameter('ENTITY');
        if (!$basket instanceof BasketBase) {
            return;
        }

        $helperPath = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';
        if (!is_readable($helperPath)) {
            return;
        }
        require_once $helperPath;

        foreach ($basket->getBasketItems() as $item) {
            if ($item->getField('DEL') === 'Y') {
                continue;
            }

            $propsByCode = [];
            foreach ($item->getPropertyCollection() as $prop) {
                $code = strtoupper(trim((string) $prop->getField('CODE')));
                if ($code !== '') {
                    $propsByCode[$code] = $prop;
                }
            }

            if (!isset($propsByCode['GETCHIPS_TIERS_RUB_JSON'])) {
                continue;
            }

            $raw = $propsByCode['GETCHIPS_TIERS_RUB_JSON']->getField('VALUE');
            $raw = is_array($raw) ? (string) reset($raw) : (string) $raw;
            $raw = trim($raw);
            if ($raw === '') {
                continue;
            }

            $tiers = GetchipsCatalogOffersHelper::normalizeTiersRubFromJson($raw);
            if ($tiers === []) {
                continue;
            }

            $qty = (float) $item->getQuantity();
            if ($qty <= 0) {
                continue;
            }

            $unitRub = GetchipsCatalogOffersHelper::unitRubForOrderQty($tiers, (int) $qty);
            if ($unitRub === null || $unitRub <= 0) {
                continue;
            }

            $item->setField('CUSTOM_PRICE', 'Y');
            $item->setField('BASE_PRICE', $unitRub);
            $item->setField('PRICE', $unitRub);

            if (isset($propsByCode['GETCHIPS_PRICE_RUB'])) {
                $propsByCode['GETCHIPS_PRICE_RUB']->setField('VALUE', (string) $unitRub);
            }
        }
    }
}
