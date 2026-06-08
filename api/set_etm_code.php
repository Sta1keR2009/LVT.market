<?php
// Установка ETM кода товару
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = '/var/www/www-root/data/www/lvtgroup.ru';
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
\Bitrix\Main\Loader::includeModule('iblock');

header('Content-Type: text/plain; charset=utf-8');

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$etmCode = isset($_GET['code']) ? trim($_GET['code']) : '';

if (!$productId || !$etmCode) {
    echo "=== Установка ETM кода товару ===\n\n";
    echo "Использование: ?id=ID_ТОВАРА&code=ETM_КОД\n\n";
    echo "Пример: ?id=198028&code=2655564\n\n";
    echo "Коды товаров ETM можно найти на сайте etm.ru\n";
    exit;
}

echo "=== Установка ETM кода ===\n\n";
echo "Товар ID: $productId\n";
echo "ETM код: $etmCode\n\n";

// Проверяем товар
$product = CIBlockElement::GetByID($productId)->Fetch();
if (!$product) {
    echo "[ОШИБКА] Товар не найден!\n";
    exit;
}

echo "Товар: {$product['NAME']}\n\n";

// Устанавливаем свойство ETMCODE (ID 1251)
CIBlockElement::SetPropertyValuesEx($productId, 11, ['ETMCODE' => $etmCode]);

echo "[OK] ETM код установлен!\n\n";

// Проверяем
$res = CIBlockElement::GetList(
    [],
    ['ID' => $productId],
    false,
    false,
    ['ID', 'NAME', 'PROPERTY_ETMCODE']
);
$item = $res->Fetch();

echo "Проверка: ETMCODE = " . ($item['PROPERTY_ETMCODE_VALUE'] ?: 'пусто') . "\n";
echo "\nТеперь создайте заказ с этим товаром - он отправится в ETM!\n";
