<?php
// Добавление цены товару
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = '/var/www/www-root/data/www/lvtgroup.ru';
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
\Bitrix\Main\Loader::includeModule('catalog');

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

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$price = isset($_GET['price']) ? floatval($_GET['price']) : 0;

if (!$productId) {
    echo "=== Добавление цены товару ===\n\n";
    echo "Использование: ?id=ID_ТОВАРА&price=ЦЕНА\n";
    echo "Пример: ?id=198028&price=100\n";
    exit;
}

if (!$price) {
    $price = 100; // Цена по умолчанию для теста
}

echo "=== Добавление цены ===\n\n";
echo "Товар ID: $productId\n";
echo "Цена: $price руб.\n\n";

// Получаем базовый тип цены
$priceType = CCatalogGroup::GetBaseGroup();
if (!$priceType) {
    // Создаём базовый тип цены
    $priceTypeId = CCatalogGroup::Add([
        'NAME' => 'BASE',
        'BASE' => 'Y',
        'SORT' => 100,
        'XML_ID' => 'BASE',
    ]);
    echo "Создан базовый тип цены ID: $priceTypeId\n";
} else {
    $priceTypeId = $priceType['ID'];
    echo "Базовый тип цены ID: $priceTypeId\n";
}

// Проверяем существующую цену
$existingPrice = CPrice::GetList(
    [],
    ['PRODUCT_ID' => $productId, 'CATALOG_GROUP_ID' => $priceTypeId]
)->Fetch();

if ($existingPrice) {
    // Обновляем цену
    $result = CPrice::Update($existingPrice['ID'], [
        'PRICE' => $price,
        'CURRENCY' => 'RUB',
    ]);
    echo $result ? "[OK] Цена обновлена\n" : "[ОШИБКА] Не удалось обновить цену\n";
} else {
    // Добавляем цену
    $result = CPrice::Add([
        'PRODUCT_ID' => $productId,
        'CATALOG_GROUP_ID' => $priceTypeId,
        'PRICE' => $price,
        'CURRENCY' => 'RUB',
    ]);
    echo $result ? "[OK] Цена добавлена\n" : "[ОШИБКА] Не удалось добавить цену\n";
}

echo "\nГотово! Теперь товар можно добавить в заказ.\n";
