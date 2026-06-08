<?php
// --- Аналогично load.php подключаем ядро, переменные, ID складов и типов цен ---
// ... здесь весь блок подключения и определений такой же, как выше

// Например (если запуск по cron или через web hook):
$mpnArray = получитьСписокMPNИзКаталога(); // функция для сбора всех используемых MPN из инфоблока

$digiProducts = fetchDigiKeyProducts($mpnArray);
$mouserProducts = fetchMouserProducts($mpnArray);

foreach ($mpnArray as $mpn) {
    $productId = получитьIDТовараПоMPN($mpn); // функция поиска по инфоблоку
    if (!$productId) continue;

    // Digi-Key update
    if (isset($digiProducts['Products'][$mpn])) {
        $data = $digiProducts['Products'][$mpn];
        $stock = $data['QuantityAvailable'] ?? 0;
        $price = $data['StandardPricing'][0]['Price'] ?? 0;
        updateStock($productId, DIGIKEY_STORE_ID, $stock);
        updatePrice($productId, $price, DIGIKEY_PRICE_TYPE_ID, 'USD');
    }
    // Mouser update
    if (isset($mouserProducts['SearchResults']['Parts'][$mpn])) {
        $data = $mouserProducts['SearchResults']['Parts'][$mpn];
        $stock = $data['Availability'] ?? 0;
        $price = $data['PriceBreaks'][0]['Price'] ?? 0;
        updateStock($productId, MOUSER_STORE_ID, $stock);
        updatePrice($productId, $price, MOUSER_PRICE_TYPE_ID, 'USD');
    }
}

// ... далее те же функции updateStock и updatePrice, как в load.php

echo "Обновление цен и остатков завершено";
