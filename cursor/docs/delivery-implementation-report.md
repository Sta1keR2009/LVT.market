# Отчёт: Блок расчёта доставки в карточке товара

**Дата:** 2026-03-27  
**Среда:** тестовая lvtec.ru  
**Статус:** реализовано, готово к проверке

---

## 1. Архитектура решения

```
Пользователь открывает карточку товара
        |
        v
[epilog_blocks/catapulto_delivery.php]
  - выводит контейнер #catapulto-delivery-block с data-product-id
  - подключает /local/css/catapulto_delivery_card.css
  - подключает /local/js/catapulto_delivery_card.js
        |
        v
[JS: catapulto_delivery_card.js]
  - показывает лоадер (анимация shimmer)
  - делает fetch к /local/api/catapulto_delivery_quote.php?product_id=X
  - при пустом ответе — retry 3 раза с паузой 3 сек (Catapulto API асинхронный)
  - рендерит: самовывоз + группировка по оператору (СДЭК, КСЭ и т.д.)
        |
        v
[PHP: catapulto_delivery_quote.php -> CatapultoProductDeliveryCalculator]
  - определяет город по IP через Sypex Geo API (ключ v7Q9z)
  - кеширует ответ Sypex Geo по IP на 24 часа
  - проверяет кеш расчёта доставки (ключ: product_id + город + quantity, TTL 24ч)
  - если кеш есть — возвращает мгновенно
  - если нет — вызывает Catapulto API:
    1. widgetGetGeo(город) -> receiver_locality_id
    2. widgetCreateRate(склад Лыткарино, товар, получатель) -> rate_id
    3. widgetGetRate(rate_id, d2d) с polling (до 5 попыток, пауза 2 сек)
  - собирает JSON с полями: operator, rateName, deliveryDay, periodText, price
  - добавляет блок "Самовывоз" (Лыткарино, Москва — бесплатно)
  - сохраняет в кеш
```

---

## 2. Созданные файлы

| Файл | Назначение |
|------|-----------|
| `local/php_interface/classes/CatapultoProductDeliveryCalculator.php` | Backend-калькулятор: Sypex Geo, Catapulto API, кеш 24ч, самовывоз |
| `local/php_interface/classes/CatapultoSaleDeliveryHandler.php` | Обработчик доставки Bitrix Sale для оформления заказа |
| `local/api/catapulto_delivery_quote.php` | HTTP endpoint (GET) |
| `local/js/catapulto_delivery_card.js` | JS: лоадер, fetch, группировка по оператору, рендер |
| `local/css/catapulto_delivery_card.css` | CSS: стили блока, адаптив для мобильного |
| `bitrix/templates/aspro-lite/components/bitrix/catalog.element/main/epilog_blocks/catapulto_delivery.php` | Epilog-блок для desktop-шаблона |
| `bitrix/templates/aspro-lite-mobile/components/bitrix/catalog.element/main/epilog_blocks/catapulto_delivery.php` | Epilog-блок для мобильного шаблона |

---

## 3. Изменённые файлы

| Файл | Что изменено |
|------|-------------|
| `local/php_interface/init.php` | Добавлена регистрация CatapultoSaleDeliveryHandler через событие onSaleDeliveryHandlersClassNamesBuildList |
| `bitrix/templates/aspro-lite/components/bitrix/catalog.element/main/component_epilog.php` | Добавлен `catapulto_delivery` в $arBlockOrder перед `payment` |
| `bitrix/templates/aspro-lite-mobile/components/bitrix/catalog.element/main/component_epilog.php` | Аналогично — `catapulto_delivery` перед `payment` |

---

## 4. Кеширование

- **Кеш расчёта доставки:** файловый, Bitrix Cache, TTL 24 часа, директория `/bitrix/cache/catapulto_delivery/`, ключ `md5(product_id|город|quantity)`
- **Кеш Sypex Geo:** файловый, Bitrix Cache, TTL 24 часа, директория `/bitrix/cache/sypex_geo/`, ключ `md5(IP)`
- Обновление: автоматическое при истечении TTL
- При смене города пользователем: новый ключ кеша, отдельный расчёт

---

## 5. Обработчик доставки Bitrix Sale

- Класс: `CatapultoSaleDeliveryHandler`
- Наследник: `\Bitrix\Sale\Delivery\Services\Base`
- Метод расчёта: использует `CatapultoProductDeliveryCalculator` по городу из свойства заказа LOCATION
- Возвращает самый дешёвый тариф как цену доставки
- Регистрация: через событие в `init.php` (ленивая загрузка — не ломает сайт)
- После создания: добавить как новую службу доставки в админке (Магазин -> Доставка -> Добавить)

---

## 6. Настройки в админке (ручные операции)

1. **Sypex Geo:** Настройки -> Настройки продукта -> Геолокация -> Установить Sypex Geo -> API KEY: `v7Q9z`
2. **Региональность Aspro:** Настройки решения -> Регионалность -> USE_REGIONALITY = Y
3. **Служба доставки:** Магазин -> Доставка -> Добавить -> Тип: "Доставка через Catapulto (LVT)"

---

## 7. Инструкция по переносу на боевой (lvtgroup.ru / lvt.market)

1. Скопировать файлы из п.2 в те же пути на боевом
2. Применить изменения из п.3 (добавить строки в init.php и component_epilog.php)
3. Выполнить настройки из п.6 в админке боевого сайта
4. Очистить кеш Bitrix
5. Проверить карточку товара

---

## 8. API формат ответа

```json
{
  "ok": true,
  "city": "Москва",
  "productName": "К140УД1201 - Операционный усилитель",
  "quantity": 1,
  "senderCity": "Лыткарино",
  "rateCompleted": true,
  "pickup": {
    "name": "Самовывоз",
    "locations": ["Лыткарино", "Москва"],
    "price": 0,
    "priceFormatted": "Бесплатно"
  },
  "deliveries": [
    {
      "operator": "cdek",
      "operatorName": "СДЭК",
      "rateName": "Посылка",
      "periodText": "от 1 раб. дней",
      "deliveryDay": "с 08 апреля, ср",
      "price": 902,
      "priceFormatted": "от 902 ₽"
    }
  ],
  "disclaimer": "Стоимость и сроки доставки являются ориентировочными..."
}
```
