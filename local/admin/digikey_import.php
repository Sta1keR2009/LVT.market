<?php
// /local/admin/digikey_import.php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin.php';

// Подключаем наши классы
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/classes/DigikeyAPI.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/classes/DigikeyImporter.php';

$APPLICATION->SetTitle('Импорт товаров из Digi-Key в инфоблок 40');

echo '<div style="padding: 20px;">';
echo '<h1>Импорт товаров из Digi-Key</h1>';
echo '<p><strong>Инфоблок:</strong> 40</p>';

// Проверяем существование свойств
CModule::IncludeModule('iblock');
$requiredProps = ['MANUFACTURER', 'MANUFACTURER_PART_NUMBER', 'DIGIKEY_PART_NUMBER', 'QUANTITY_AVAILABLE', 'UNIT_PRICE'];
$missingProps = [];

foreach ($requiredProps as $propCode) {
    $prop = CIBlockProperty::GetList([], ['IBLOCK_ID' => 40, 'CODE' => $propCode])->Fetch();
    if (!$prop) {
        $missingProps[] = $propCode;
    }
}

if (!empty($missingProps)) {
    echo '<div style="color: red; padding: 10px; background: #fff0f0; border: 1px solid red; margin: 10px 0;">';
    echo '<strong>⚠️ Внимание!</strong> Отсутствуют необходимые свойства:';
    echo '<ul>';
    foreach ($missingProps as $propCode) {
        echo '<li>' . $propCode . '</li>';
    }
    echo '</ul>';
    echo '<p><a href="/local/admin/digikey_setup.php">➡ Создать свойства</a></p>';
    echo '</div>';
}

if ($_POST['import'] || $_GET['auto']) {
    try {
        $limit = $_POST['limit'] ?? 10;
        $importer = new DigikeyImporter();
        $result = $importer->importProducts($limit);
        
        if ($result['success']) {
            echo '<div style="color: green; padding: 10px; background: #f0fff0; border: 1px solid green; margin: 10px 0;">';
            echo '<strong>✅ УСПЕШНО!</strong><br>';
            echo 'Импортировано: ' . $result['imported'] . ' из ' . $result['total'] . ' товаров';
            echo '</div>';
            
            if (!empty($result['errors'])) {
                echo '<div style="color: orange; padding: 10px; background: #fffaf0; border: 1px solid orange; margin-top: 10px;">';
                echo '<strong>⚠️ Ошибки:</strong><br>';
                foreach ($result['errors'] as $error) {
                    echo '• ' . $error . '<br>';
                }
                echo '</div>';
            }
        } else {
            echo '<div style="color: red; padding: 10px; background: #fff0f0; border: 1px solid red;">';
            echo '<strong>❌ ОШИБКА:</strong> ' . $result['message'];
            echo '</div>';
        }
        
    } catch (Exception $e) {
        echo '<div style="color: red; padding: 10px; background: #fff0f0; border: 1px solid red;">';
        echo '<strong>❌ КРИТИЧЕСКАЯ ОШИБКА:</strong> ' . $e->getMessage();
        echo '</div>';
    }
}

echo '<form method="POST" style="margin: 20px 0; padding: 20px; background: #f5f5f5; border-radius: 5px;">';
echo '<label>Количество товаров для импорта: </label>';
echo '<input type="number" name="limit" value="10" min="1" max="50" style="margin: 0 10px;">';
echo '<input type="submit" name="import" value="🚀 Запустить импорт" class="adm-btn-green" style="padding: 10px 20px;">';
echo '</form>';

echo '<p><a href="?auto=1">🔄 Быстрый импорт (10 товаров)</a></p>';

echo '<hr>';
echo '<p><a href="/local/admin/digikey_setup.php">⚙️ Настройка свойств</a></p>';

echo '</div>';

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';