<?php
/**
 * Возвращает следующий по порядку номер для названия склада "Склад №N".
 * Сканирует существующие склады с TITLE "Склад №1", "Склад №2", ... и возвращает max + 1.
 *
 * @return int
 */
/**
 * Возвращает следующий номер для "Склад №N". Нумерация новых складов идёт после 3-го (минимум 4).
 *
 * @return int
 */
function promelec_next_store_number() {
    $minNumber = 4;
    $max = $minNumber - 1;
    $res = CCatalogStore::GetList(['ID' => 'ASC'], [], false, false, ['ID', 'TITLE']);
    while ($row = $res->Fetch()) {
        if (preg_match('/^Склад\s*№\s*(\d+)\s*$/u', trim($row['TITLE']), $m)) {
            $n = (int)$m[1];
            if ($n > $max) {
                $max = $n;
            }
        }
    }
    return max($minNumber, $max + 1);
}

/**
 * Ищет в Битрикс склад с названием "Склад N дн." (N = $days). Если найден — возвращает ID первого совпадения, иначе null.
 * Поиск по точному совпадению и по нормализованной строке (пробелы), чтобы не создавать дубли.
 *
 * @param int $days срок доставки (дней)
 * @return int|null
 */
function promelec_find_store_by_delivery_days($days) {
    $days = (int) $days;
    $needle = 'Склад ' . $days . ' дн.';
    $res = CCatalogStore::GetList(['ID' => 'ASC'], [], false, false, ['ID', 'TITLE']);
    if (!$res) {
        return null;
    }
    while ($row = $res->Fetch()) {
        $title = isset($row['TITLE']) ? trim((string) $row['TITLE']) : '';
        if ($title === $needle) {
            return (int) $row['ID'];
        }
    }
    return null;
}

/**
 * Ищет тип цен по внутреннему имени (NAME). Возвращает ID или null.
 *
 * @param string $name например 'PROMELEC2'
 * @return int|null
 */
function promelec_find_price_type_by_name($name) {
    if ($name === '') {
        return null;
    }
    $res = CCatalogGroup::GetList(['ID' => 'ASC'], ['NAME' => $name], false, ['nTopCount' => 1], ['ID']);
    $row = $res ? $res->Fetch() : null;
    return $row ? (int) $row['ID'] : null;
}

/**
 * Добавляет маппинг срока доставки в конфиг и сохраняет файл (без дублей складов при повторном обмене).
 *
 * @param string $configPath путь к promelec_delivery_mapping.php
 * @param int $days срок доставки (дней)
 * @param int $storeId ID склада Битрикс
 * @param int $priceTypeId ID типа цен Битрикс
 * @return bool
 */
function promelec_save_delivery_mapping($configPath, $days, $storeId, $priceTypeId) {
    $days = (int) $days;
    $storeId = (int) $storeId;
    $priceTypeId = (int) $priceTypeId;
    $data = ['delivery_to_store' => [], 'delivery_to_price_type' => []];
    if (file_exists($configPath)) {
        $loaded = include $configPath;
        if (is_array($loaded)) {
            if (!empty($loaded['delivery_to_store'])) {
                $data['delivery_to_store'] = $loaded['delivery_to_store'];
            }
            if (!empty($loaded['delivery_to_price_type'])) {
                $data['delivery_to_price_type'] = $loaded['delivery_to_price_type'];
            }
        }
    }
    $data['delivery_to_store'][$days] = $storeId;
    $data['delivery_to_price_type'][$days] = $priceTypeId;
    $dir = dirname($configPath);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
        if (is_dir($dir) && !file_exists($dir . '/.htaccess')) {
            @file_put_contents($dir . '/.htaccess', "Deny from all\n");
        }
    }
    $php = "<?php\n/** Маппинг сроков доставки PromElec → склады/типы цен (доп. созданные автоматически) */\nreturn " . var_export($data, true) . ";\n";
    return (bool) file_put_contents($configPath, $php, LOCK_EX);
}

/**
 * Обнуляет остатки товара по всем складам (перед внесением новых при обмене).
 *
 * @param int $productId ID товара (элемент каталога)
 */
function promelec_zero_product_stock($productId) {
    $productId = (int) $productId;
    if ($productId <= 0) {
        return;
    }
    $res = CCatalogStoreProduct::GetList([], ['PRODUCT_ID' => $productId], false, false, ['ID', 'AMOUNT']);
    while ($row = $res->Fetch()) {
        if ((int)($row['AMOUNT'] ?? 0) !== 0) {
            CCatalogStoreProduct::Update($row['ID'], ['AMOUNT' => 0]);
        }
    }
}

/**
 * Нормализация pricebreaks из API PromElec перед записью в Битрикс.
 * Оставляет только пороги с количеством > 0 и ценой > 0, сортирует по quant,
 * убирает дубликаты по quant — чтобы создавались только корректные строки цен.
 *
 * @param array $priceBreaks массив элементов ['quant' => int, 'price' => float]
 * @return array отсортированный по quant массив без нулевых и дубликатов
 */
function promelec_normalize_pricebreaks(array $priceBreaks) {
    $out = [];
    foreach ($priceBreaks as $row) {
        if (!is_array($row)) {
            continue;
        }
        $quant = (int)($row['quant'] ?? 0);
        $price = (float)($row['price'] ?? 0);
        if ($quant <= 0 || $price <= 0) {
            continue;
        }
        $out[] = ['quant' => $quant, 'price' => $price];
    }
    if (empty($out)) {
        return [];
    }
    usort($out, function ($a, $b) {
        return $a['quant'] - $b['quant'];
    });
    $seen = [];
    $dedup = [];
    foreach ($out as $row) {
        if (isset($seen[$row['quant']])) {
            continue;
        }
        $seen[$row['quant']] = true;
        $dedup[] = $row;
    }
    return $dedup;
}

/**
 * Проверяет, что у товара по сути одна цена (нет диапазонов).
 * Такие товары грузятся в базовый тип цены "Розничная цена" одной записью.
 *
 * @param array $product элемент товара из API (pricebreaks, vendors[].pricebreaks)
 * @return bool true если один порог цены (после нормализации)
 */
function promelec_has_single_price_only(array $product) {
    $productPricebreaks = isset($product['pricebreaks']) && is_array($product['pricebreaks']) ? $product['pricebreaks'] : [];
    $normalized = promelec_normalize_pricebreaks($productPricebreaks);
    if (!empty($normalized)) {
        return count($normalized) === 1;
    }
    $vendors = isset($product['vendors']) && is_array($product['vendors']) ? $product['vendors'] : [];
    foreach ($vendors as $vendor) {
        $pb = isset($vendor['pricebreaks']) && is_array($vendor['pricebreaks']) ? $vendor['pricebreaks'] : [];
        $norm = promelec_normalize_pricebreaks($pb);
        if (!empty($norm)) {
            return count($norm) === 1;
        }
    }
    return false;
}

/**
 * Возвращает одну цену для товара без диапазонов (для записи в базовый тип цены).
 * Сначала product.pricebreaks, иначе первый непустой vendor.pricebreaks после нормализации.
 *
 * @param array $product элемент товара из API
 * @return array|null один элемент ['quant' => int, 'price' => float] или null
 */
function promelec_get_single_pricebreak(array $product) {
    $productPricebreaks = isset($product['pricebreaks']) && is_array($product['pricebreaks']) ? $product['pricebreaks'] : [];
    $normalized = promelec_normalize_pricebreaks($productPricebreaks);
    if (count($normalized) === 1) {
        return $normalized[0];
    }
    $vendors = isset($product['vendors']) && is_array($product['vendors']) ? $product['vendors'] : [];
    foreach ($vendors as $vendor) {
        $pb = isset($vendor['pricebreaks']) && is_array($vendor['pricebreaks']) ? $vendor['pricebreaks'] : [];
        $norm = promelec_normalize_pricebreaks($pb);
        if (count($norm) === 1) {
            return $norm[0];
        }
    }
    return null;
}
