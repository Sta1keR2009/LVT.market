<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

// Проверяем права администратора
global $USER;
if (!$USER->IsAdmin()) {
    die("Доступ запрещен. Только для администраторов.");
}

// Проверяем наличие модуля инфоблоков
if (!CModule::IncludeModule("iblock")) {
    die("Модуль инфоблоков не подключен");
}

// ID инфоблока
$iblockId = 40;

// Проверяем существование инфоблока
$iblock = CIBlock::GetByID($iblockId)->Fetch();
if (!$iblock) {
    die("Инфоблок с ID $iblockId не найден!");
}

echo "<h2>Создание свойств для инфоблока: {$iblock['NAME']} (ID: $iblockId)</h2>";

// Массив свойств для создания (убираем HTML-свойства, используем стандартные поля Битрикса)
$properties = [
    // Строковые свойства
    [
        'CODE' => 'MOUSER_PART_NUMBER',
        'NAME' => 'Артикул Mouser',
        'TYPE' => 'S',
        'IS_REQUIRED' => 'Y',
        'SEARCHABLE' => 'Y',
        'FILTRABLE' => 'Y',
        'SORT' => 100
    ],
    [
        'CODE' => 'MANUFACTURER_PART_NUMBER',
        'NAME' => 'Артикул производителя',
        'TYPE' => 'S',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'Y',
        'FILTRABLE' => 'Y',
        'SORT' => 110
    ],
    [
        'CODE' => 'MANUFACTURER',
        'NAME' => 'Производитель',
        'TYPE' => 'S',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'Y',
        'FILTRABLE' => 'Y',
        'SORT' => 120
    ],
    [
        'CODE' => 'AVAILABILITY',
        'NAME' => 'Наличие',
        'TYPE' => 'S',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'Y',
        'SORT' => 130
    ],
    [
        'CODE' => 'ROHS_STATUS',
        'NAME' => 'Статус RoHS',
        'TYPE' => 'S',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'Y',
        'SORT' => 140
    ],
    [
        'CODE' => 'LEAD_TIME',
        'NAME' => 'Время поставки',
        'TYPE' => 'S',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'Y',
        'SORT' => 150
    ],
    [
        'CODE' => 'PACKAGING',
        'NAME' => 'Упаковка',
        'TYPE' => 'S',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'Y',
        'SORT' => 160
    ],
    
    // Числовые свойства
    [
        'CODE' => 'QUANTITY',
        'NAME' => 'Количество на складе',
        'TYPE' => 'N',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'Y',
        'SORT' => 170
    ],
    [
        'CODE' => 'PRICE',
        'NAME' => 'Цена (основная)',
        'TYPE' => 'N',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'Y',
        'SORT' => 180
    ],
    [
        'CODE' => 'MIN_ORDER_QTY',
        'NAME' => 'Минимальный заказ',
        'TYPE' => 'N',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'Y',
        'SORT' => 190
    ],
    
    // Ссылки (строковые с типом URL)
    [
        'CODE' => 'DATASHEET_URL',
        'NAME' => 'Ссылка на документацию',
        'TYPE' => 'S',
        'USER_TYPE' => 'URL',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'N',
        'SORT' => 200
    ],
    [
        'CODE' => 'PRODUCT_URL',
        'NAME' => 'Ссылка на товар на Mouser',
        'TYPE' => 'S',
        'USER_TYPE' => 'URL',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'N',
        'SORT' => 210
    ],
    
    // Файловые свойства
    [
        'CODE' => 'IMAGE',
        'NAME' => 'Изображение товара',
        'TYPE' => 'F',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'N',
        'SORT' => 220,
        'FILE_TYPE' => 'jpg, gif, bmp, png, jpeg, webp'
    ],
    
    // Привязка к разделам
    [
        'CODE' => 'CATEGORY',
        'NAME' => 'Категория',
        'TYPE' => 'G',
        'LINK_IBLOCK_ID' => $iblockId,
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'Y',
        'SORT' => 230
    ],
    
    // Дополнительные свойства (простой текст)
    [
        'CODE' => 'PRICE_BREAKS',
        'NAME' => 'Цены по объемам',
        'TYPE' => 'S',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'N',
        'SORT' => 240,
        'MULTIPLE' => 'N',
        'ROW_COUNT' => 10,
        'COL_COUNT' => 50
    ],
    [
        'CODE' => 'PRODUCT_ATTRIBUTES',
        'NAME' => 'Атрибуты товара (JSON)',
        'TYPE' => 'S',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'N',
        'SORT' => 250,
        'MULTIPLE' => 'N',
        'ROW_COUNT' => 10,
        'COL_COUNT' => 50
    ],
    [
        'CODE' => 'ALTERNATE_PACKAGING',
        'NAME' => 'Альтернативная упаковка',
        'TYPE' => 'S',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'Y',
        'SORT' => 260
    ],
    [
        'CODE' => 'REACH_COMPLIANT',
        'NAME' => 'Соответствие REACH',
        'TYPE' => 'L',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'Y',
        'SORT' => 270,
        'VALUES' => [
            ['VALUE' => 'Да', 'DEF' => 'N', 'SORT' => 100],
            ['VALUE' => 'Нет', 'DEF' => 'N', 'SORT' => 200],
            ['VALUE' => 'Не определено', 'DEF' => 'Y', 'SORT' => 300]
        ]
    ],
    [
        'CODE' => 'MOUSER_LAST_UPDATE',
        'NAME' => 'Дата последнего обновления из Mouser',
        'TYPE' => 'S',
        'USER_TYPE' => 'DateTime',
        'IS_REQUIRED' => 'N',
        'SEARCHABLE' => 'N',
        'FILTRABLE' => 'Y',
        'SORT' => 280
    ]
];

// Функция для создания свойства (исправленная)
function createProperty($iblockId, $propertyData) {
    $property = new CIBlockProperty;
    
    // Базовые настройки
    $fields = [
        'IBLOCK_ID' => $iblockId,
        'CODE' => $propertyData['CODE'],
        'NAME' => $propertyData['NAME'],
        'ACTIVE' => 'Y',
        'SORT' => $propertyData['SORT'],
        'IS_REQUIRED' => $propertyData['IS_REQUIRED'],
        'SEARCHABLE' => $propertyData['SEARCHABLE'],
        'FILTRABLE' => $propertyData['FILTRABLE'],
        'MULTIPLE' => $propertyData['MULTIPLE'] ?? 'N',
        'ROW_COUNT' => $propertyData['ROW_COUNT'] ?? 1,
        'COL_COUNT' => $propertyData['COL_COUNT'] ?? 30,
        'HINT' => $propertyData['HINT'] ?? '',
    ];
    
    // Тип свойства
    $fields['PROPERTY_TYPE'] = $propertyData['TYPE'];
    
    // Пользовательский тип
    if (isset($propertyData['USER_TYPE'])) {
        $fields['USER_TYPE'] = $propertyData['USER_TYPE'];
    }
    
    // Для привязки к разделам
    if ($propertyData['TYPE'] == 'G') {
        $fields['LINK_IBLOCK_ID'] = $propertyData['LINK_IBLOCK_ID'] ?? $iblockId;
    }
    
    // Для файлов
    if ($propertyData['TYPE'] == 'F' && isset($propertyData['FILE_TYPE'])) {
        $fields['FILE_TYPE'] = $propertyData['FILE_TYPE'];
    }
    
    // Проверяем, существует ли уже свойство
    $existing = CIBlockProperty::GetList(
        [],
        ['IBLOCK_ID' => $iblockId, 'CODE' => $propertyData['CODE']]
    )->Fetch();
    
    if ($existing) {
        // Обновляем существующее свойство
        $result = $property->Update($existing['ID'], $fields);
        $action = "Обновлено";
        $propertyId = $existing['ID'];
    } else {
        // Создаем новое свойство
        $propertyId = $property->Add($fields);
        $result = $propertyId !== false;
        $action = "Создано";
    }
    
    // Добавляем значения для списка (только для новых свойств)
    if ($result && $propertyData['TYPE'] == 'L' && isset($propertyData['VALUES']) && !$existing) {
        if ($propertyId) {
            foreach ($propertyData['VALUES'] as $value) {
                CIBlockPropertyEnum::Add([
                    'PROPERTY_ID' => $propertyId,
                    'VALUE' => $value['VALUE'],
                    'DEF' => $value['DEF'],
                    'SORT' => $value['SORT']
                ]);
            }
        }
    }
    
    return [
        'success' => $result !== false,
        'action' => $action,
        'id' => $propertyId,
        'error' => !$result ? $property->LAST_ERROR : null
    ];
}

// Создаем свойства
echo "<div style='padding: 20px; background: #f5f5f5;'>";
echo "<h3>Процесс создания свойств:</h3>";
echo "<ul style='list-style: none; padding: 0;'>";

$results = [];
foreach ($properties as $prop) {
    echo "<li style='margin: 5px 0; padding: 10px; background: white; border-radius: 4px;'>";
    echo "<strong>{$prop['NAME']}</strong> ({$prop['CODE']})... ";
    
    $result = createProperty($iblockId, $prop);
    
    if ($result['success']) {
        echo "<span style='color: green;'>✓ {$result['action']} (ID: {$result['id']})</span>";
    } else {
        echo "<span style='color: red;'>✗ Ошибка: " . ($result['error'] ?: 'Неизвестная ошибка') . "</span>";
    }
    
    echo "</li>";
    
    $results[] = $result;
}

echo "</ul>";

// Статистика
$successCount = count(array_filter($results, function($r) { return $r['success']; }));
$totalCount = count($results);

echo "<div style='margin-top: 20px; padding: 15px; background: " . ($successCount == $totalCount ? '#d4edda' : '#f8d7da') . "; border-radius: 4px;'>";
echo "<h3>Результат:</h3>";
echo "<p>Успешно создано/обновлено: <strong>$successCount из $totalCount</strong> свойств</p>";

if ($successCount < $totalCount) {
    echo "<p style='color: #721c24;'>Некоторые свойства не были созданы. Проверьте ошибки выше.</p>";
} else {
    echo "<p style='color: #155724;'>Все свойства успешно созданы!</p>";
}

echo "<p><strong>Примечание:</strong> Описание товаров будет сохраняться в стандартные поля Битрикса (PREVIEW_TEXT и DETAIL_TEXT), а не в отдельные свойства.</p>";
echo "</div>";

// Ссылки для дальнейших действий
echo "<div style='margin-top: 20px;'>";
echo "<a href='/bitrix/admin/iblock_edit.php?ID=$iblockId&type=catalog&lang=ru&admin=Y' target='_blank' style='display: inline-block; padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;'>";
echo "Перейти к настройкам инфоблока";
echo "</a>";

echo "<a href='/api-mouser/admin_import.php' style='display: inline-block; padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;'>";
echo "Запустить импорт";
echo "</a>";

echo "<a href='/bitrix/admin/iblock_element_admin.php?IBLOCK_ID=$iblockId&type=catalog&lang=ru' target='_blank' style='display: inline-block; padding: 10px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;'>";
echo "Просмотреть товары";
echo "</a>";
echo "</div>";

echo "</div>";

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog.php');
?>