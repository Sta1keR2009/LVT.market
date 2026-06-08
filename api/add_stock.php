<?php
// Скрипт для добавления складских остатков товару
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = '/var/www/www-root/data/www/lvtgroup.ru';
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('iblock');

// БЕЗОПАСНОСТЬ: Проверка авторизации администратора
global $USER;
if (!is_object($USER)) {
    $USER = new CUser;
}

if (!$USER->IsAuthorized() || !$USER->IsAdmin()) {
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    die('<h2>Доступ запрещен</h2><p>Требуется авторизация администратора.</p>');
}

header('Content-Type: text/html; charset=utf-8');

// ID товара передаётся через GET параметр
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$quantity = isset($_GET['qty']) ? intval($_GET['qty']) : 100;

if (!$productId) {
    echo "<h2>Добавление складских остатков</h2>";
    echo "<p>Использование: <code>?id=ID_ТОВАРА&qty=КОЛИЧЕСТВО</code></p>";
    echo "<p>Пример: <code>?id=12345&qty=100</code></p>";

    // Показать последние товары
    echo "<h3>Последние товары в каталоге:</h3>";
    $res = CIBlockElement::GetList(
        ['ID' => 'DESC'],
        ['IBLOCK_ID' => 11, 'ACTIVE' => 'Y'],
        false,
        ['nTopCount' => 10],
        ['ID', 'NAME']
    );
    echo "<ul>";
    while ($item = $res->Fetch()) {
        echo "<li><a href='?id={$item['ID']}&qty=100'>ID {$item['ID']}</a> - {$item['NAME']}</li>";
    }
    echo "</ul>";
    exit;
}

echo "<h2>Добавление остатков для товара ID: $productId</h2>";

// Получаем информацию о товаре
$product = CIBlockElement::GetByID($productId)->Fetch();
if (!$product) {
    echo "<p style='color:red'>Товар не найден!</p>";
    exit;
}

echo "<p><strong>Товар:</strong> {$product['NAME']}</p>";

// Получаем список складов
$stores = [];
$storeRes = CCatalogStore::GetList([], ['ACTIVE' => 'Y'], false, false, ['ID', 'TITLE', 'ADDRESS']);
while ($store = $storeRes->Fetch()) {
    $stores[] = $store;
}

if (empty($stores)) {
    echo "<p style='color:orange'>Склады не найдены. Создаём склад по умолчанию...</p>";

    $storeId = CCatalogStore::Add([
        'TITLE' => 'Основной склад',
        'ACTIVE' => 'Y',
        'ADDRESS' => 'Основной склад',
        'ISSUING_CENTER' => 'Y',
    ]);

    if ($storeId) {
        echo "<p style='color:green'>Склад создан, ID: $storeId</p>";
        $stores[] = ['ID' => $storeId, 'TITLE' => 'Основной склад'];
    } else {
        echo "<p style='color:red'>Ошибка создания склада!</p>";
        exit;
    }
}

// Используем первый активный склад
$storeId = $stores[0]['ID'];
echo "<p><strong>Склад:</strong> {$stores[0]['TITLE']} (ID: $storeId)</p>";

// Проверяем текущие остатки
$currentStock = CCatalogStoreProduct::GetList(
    [],
    ['PRODUCT_ID' => $productId, 'STORE_ID' => $storeId]
)->Fetch();

if ($currentStock) {
    echo "<p><strong>Текущие остатки:</strong> {$currentStock['AMOUNT']}</p>";

    // Обновляем остатки
    $result = CCatalogStoreProduct::Update($currentStock['ID'], ['AMOUNT' => $quantity]);
    if ($result) {
        echo "<p style='color:green'>Остатки обновлены до: $quantity</p>";
    } else {
        echo "<p style='color:red'>Ошибка обновления остатков!</p>";
    }
} else {
    // Добавляем остатки
    $result = CCatalogStoreProduct::Add([
        'PRODUCT_ID' => $productId,
        'STORE_ID' => $storeId,
        'AMOUNT' => $quantity,
    ]);

    if ($result) {
        echo "<p style='color:green'>Остатки добавлены: $quantity</p>";
    } else {
        echo "<p style='color:red'>Ошибка добавления остатков!</p>";
    }
}

// Проверяем настройки товара в каталоге
$catalogProduct = CCatalogProduct::GetByID($productId);
if (!$catalogProduct) {
    echo "<p style='color:orange'>Товар не зарегистрирован в каталоге. Регистрируем...</p>";

    CCatalogProduct::Add([
        'ID' => $productId,
        'QUANTITY' => $quantity,
        'QUANTITY_TRACE' => 'N', // Не отслеживать количество
        'CAN_BUY_ZERO' => 'Y',   // Можно покупать при нуле
        'AVAILABLE' => 'Y',
    ]);
    echo "<p style='color:green'>Товар зарегистрирован в каталоге</p>";
} else {
    // Обновляем настройки
    CCatalogProduct::Update($productId, [
        'QUANTITY' => $quantity,
        'QUANTITY_TRACE' => 'N',
        'CAN_BUY_ZERO' => 'Y',
        'AVAILABLE' => 'Y',
    ]);
    echo "<p style='color:green'>Настройки каталога обновлены (доступен для покупки)</p>";
}

echo "<hr>";
echo "<p><strong>Готово!</strong> Теперь товар можно добавить в заказ.</p>";
echo "<p><a href='/bitrix/admin/cat_product_edit.php?IBLOCK_ID=11&type=aspro_max_catalog&ID=$productId&lang=ru'>Открыть товар в админке</a></p>";
