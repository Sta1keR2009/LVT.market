<?php
// Диагностика товара - почему нельзя купить
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = '/var/www/www-root/data/www/lvtgroup.ru';
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('sale');

// БЕЗОПАСНОСТЬ: Проверка авторизации администратора
global $USER;
if (!is_object($USER)) {
    $USER = new CUser;
}

if (!$USER->IsAuthorized() || !$USER->IsAdmin()) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    die('Доступ запрещен. Требуется авторизация администратора.');
}

header('Content-Type: text/plain; charset=utf-8');

// Поиск по артикулу
$article = isset($_GET['art']) ? trim($_GET['art']) : '';
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$productId && !$article) {
    echo "=== Диагностика товара ===\n\n";
    echo "Использование:\n";
    echo "  ?id=ID_ТОВАРА\n";
    echo "  ?art=АРТИКУЛ\n\n";
    echo "Пример: ?art=LLE-MR16-5-230-30-GU5\n";
    exit;
}

// Поиск по артикулу
if ($article && !$productId) {
    echo "Поиск по артикулу: $article\n\n";

    // Ищем в разных свойствах артикула
    $filter = [
        'IBLOCK_ID' => 11,
        [
            'LOGIC' => 'OR',
            ['%PROPERTY_CML2_ARTICLE' => $article],
            ['%PROPERTY_pr_article' => $article],
            ['%NAME' => $article],
            ['%CODE' => $article],
        ]
    ];

    $res = CIBlockElement::GetList(
        ['ID' => 'DESC'],
        ['IBLOCK_ID' => 11, '%NAME' => $article],
        false,
        ['nTopCount' => 5],
        ['ID', 'NAME', 'PROPERTY_CML2_ARTICLE', 'PROPERTY_pr_article']
    );

    $found = [];
    while ($item = $res->Fetch()) {
        $found[] = $item;
    }

    // Также ищем по свойствам напрямую
    $res2 = CIBlockElement::GetList(
        ['ID' => 'DESC'],
        ['IBLOCK_ID' => 11, 'PROPERTY_CML2_ARTICLE' => $article],
        false,
        ['nTopCount' => 5],
        ['ID', 'NAME']
    );
    while ($item = $res2->Fetch()) {
        $exists = false;
        foreach ($found as $f) {
            if ($f['ID'] == $item['ID']) $exists = true;
        }
        if (!$exists) $found[] = $item;
    }

    if (empty($found)) {
        echo "Товар не найден!\n";
        echo "Попробуйте поискать вручную в админке.\n";
        exit;
    }

    echo "Найдено товаров: " . count($found) . "\n\n";
    foreach ($found as $item) {
        echo "ID: {$item['ID']} - {$item['NAME']}\n";
    }

    $productId = $found[0]['ID'];
    echo "\nИспользуем первый: ID $productId\n";
    echo "-------------------------------------------\n\n";
}

echo "=== ДИАГНОСТИКА ТОВАРА ID: $productId ===\n\n";

// 1. Проверка элемента инфоблока
$element = CIBlockElement::GetByID($productId)->Fetch();
if (!$element) {
    echo "[ОШИБКА] Элемент инфоблока не найден!\n";
    exit;
}
echo "[OK] Элемент инфоблока: {$element['NAME']}\n";
echo "    Активность: {$element['ACTIVE']}\n";

// 2. Проверка записи в каталоге
$catalogProduct = CCatalogProduct::GetByID($productId);
if (!$catalogProduct) {
    echo "[ОШИБКА] Товар НЕ зарегистрирован в каталоге!\n";
    echo "    Исправляем...\n";

    CCatalogProduct::Add([
        'ID' => $productId,
        'QUANTITY' => 100,
        'QUANTITY_TRACE' => 'N',
        'CAN_BUY_ZERO' => 'Y',
        'AVAILABLE' => 'Y',
    ]);

    $catalogProduct = CCatalogProduct::GetByID($productId);
}

echo "[OK] Запись в каталоге найдена\n";
echo "    QUANTITY (количество): {$catalogProduct['QUANTITY']}\n";
echo "    QUANTITY_TRACE (отслеживание): {$catalogProduct['QUANTITY_TRACE']}\n";
echo "    CAN_BUY_ZERO (покупка при 0): {$catalogProduct['CAN_BUY_ZERO']}\n";
echo "    AVAILABLE (доступен): {$catalogProduct['AVAILABLE']}\n";

// Проверка проблем
$problems = [];

if ($catalogProduct['AVAILABLE'] != 'Y') {
    $problems[] = "AVAILABLE != Y";
}
if ($catalogProduct['QUANTITY_TRACE'] == 'Y' && $catalogProduct['QUANTITY'] <= 0 && $catalogProduct['CAN_BUY_ZERO'] != 'Y') {
    $problems[] = "Количество 0, отслеживание включено, покупка при 0 запрещена";
}

// 3. Проверка складских остатков
echo "\n--- Складские остатки ---\n";
$storeRes = CCatalogStoreProduct::GetList(
    [],
    ['PRODUCT_ID' => $productId],
    false,
    false,
    ['ID', 'STORE_ID', 'AMOUNT']
);
$hasStock = false;
while ($store = $storeRes->Fetch()) {
    echo "    Склад ID {$store['STORE_ID']}: {$store['AMOUNT']} шт.\n";
    if ($store['AMOUNT'] > 0) $hasStock = true;
}
if (!$hasStock) {
    echo "    [!] Нет остатков на складах\n";
}

// 4. Проверка цены
echo "\n--- Цены ---\n";
$priceRes = CPrice::GetList(
    [],
    ['PRODUCT_ID' => $productId],
    false,
    false,
    ['ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY']
);
$hasPrice = false;
while ($price = $priceRes->Fetch()) {
    echo "    Тип цены {$price['CATALOG_GROUP_ID']}: {$price['PRICE']} {$price['CURRENCY']}\n";
    $hasPrice = true;
}
if (!$hasPrice) {
    echo "    [ОШИБКА] НЕТ ЦЕНЫ! Товар нельзя купить без цены!\n";
    $problems[] = "Нет цены";
}

// 5. Итоги
echo "\n=== ИТОГИ ===\n";
if (empty($problems) && $hasPrice) {
    echo "[OK] Проблем не обнаружено, товар должен быть доступен.\n";
    echo "\nПопробуйте:\n";
    echo "1. Очистить кэш: Настройки -> Автокэширование -> Очистить файлы кеша\n";
    echo "2. Перезагрузить страницу заказа\n";
} else {
    echo "[!] Найдены проблемы:\n";
    foreach ($problems as $p) {
        echo "    - $p\n";
    }
}

// 6. Автоисправление
echo "\n=== АВТОИСПРАВЛЕНИЕ ===\n";
echo "Применяем настройки для доступности товара...\n";

CCatalogProduct::Update($productId, [
    'QUANTITY' => 100,
    'QUANTITY_TRACE' => 'N',
    'CAN_BUY_ZERO' => 'Y',
    'AVAILABLE' => 'Y',
]);

echo "[OK] Настройки применены:\n";
echo "    QUANTITY = 100\n";
echo "    QUANTITY_TRACE = N (не отслеживать)\n";
echo "    CAN_BUY_ZERO = Y (можно при 0)\n";
echo "    AVAILABLE = Y (доступен)\n";

if (!$hasPrice) {
    echo "\n[!] ДОБАВЬТЕ ЦЕНУ ТОВАРУ!\n";
    echo "Админка -> Каталог -> Товар -> вкладка 'Торговый каталог' -> Цены\n";
}

echo "\nГотово! Попробуйте добавить товар в заказ.\n";
