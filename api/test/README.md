# Тестовый запрос к API PromElec

Используется **тестовый endpoint** (`api_url_test`), без ограничений на количество вызовов в день. Боевой обмен не затрагивается.

**Полная документация по режимам запуска (браузер, PHP, .sh, фон):** [LAUNCH_MODES.md](LAUNCH_MODES.md)

## Формат запроса

- `login` — из конфига (lvtgroup2)
- `password` — MD5 пароля (верхний регистр)
- `customer_id` — 148949
- `method` — `item_data_get` (один товар по ID)

## 1. Получить тестовые данные (без записи в Битрикс)

**Веб:**

- `https://lvtgroup.ru/api/test/?item_id=203075`
- `https://lvtgroup.ru/api/test/?code=203075`

**CLI (из папки api/test):**

```bash
php index.php 203075
php index.php --item_id=203075
php index.php --code=203075
```

В ответе — JSON: тело запроса и данные товара из API (остатки, цены, сроки доставки и т.д.).

## 2. Проверить загрузку в склады и цены Битрикс

Скрипт берёт данные из **тестового API** и записывает остатки по складам и цены по типам цен для одного товара (по срокам доставки). Товар должен уже существовать в каталоге (свойство 501 = item_id).

**Веб:**

- `https://lvtgroup.ru/api/test/load_to_bitrix.php?item_id=203075`
- `https://lvtgroup.ru/api/test/load_to_bitrix.php?code=203075`

**CLI:**

```bash
php load_to_bitrix.php 203075
php load_to_bitrix.php --item_id=203075
```

В ответе — JSON: `stores_updated` (какой склад, сколько штук), `prices_by_delivery` (срок доставки → тип цены, количество записей цен), `total_quantity`.

## 3. Обновить остатки и цены по тестовому API (весь каталог или один товар)

Скрипт `update_price.php` использует **тестовое API** (`api_url_test`), обновляет в Битрикс остатки и цены так же, как `load_to_bitrix.php`, но поддерживает режимы:

- **all** — весь каталог (пагинация по API).
- **item** — один товар по `item_id` или `code`.
- **limit** — обновить не более N товаров (параметр `count`).

**Веб:**

- Весь каталог: `https://lvtgroup.ru/api/test/update_price.php?mode=all`
- Один товар: `https://lvtgroup.ru/api/test/update_price.php?mode=item&item_id=838637` или `?mode=item&code=TSX-U-5T`
- До N товаров: `https://lvtgroup.ru/api/test/update_price.php?mode=limit&count=100`

**CLI:**

```bash
# Весь каталог
php update_price.php all

# Один товар
php update_price.php item 838637
php update_price.php item --item_id=838637
php update_price.php item --code=TSX-U-5T

# Не более 100 товаров
php update_price.php limit 100
php update_price.php limit --count=50
```

В ответе — JSON: в режиме **item** данные по одному товару; в режиме **all** / **limit** — `total_received`, `total_updated`, `total_skipped`, `batches`; в **limit** дополнительно `count_requested`.

### Запуск на сервере в фоновом режиме

**Через скрипт (логи в `api/test/update_price_test.log`):**
```bash
cd /var/www/www-root/data/www/lvtgroup.ru/api/test
# Весь каталог — в фоне
./start_update_price.sh all &

# Один товар — в фоне
./start_update_price.sh item 838637 &

# Не более 100 товаров — в фоне
./start_update_price.sh limit 100 &
```

**Напрямую через nohup:**
```bash
cd /var/www/www-root/data/www/lvtgroup.ru/api/test
nohup php update_price.php all > update_price_test.log 2>&1 &
# Или один товар:
nohup php update_price.php item 838637 >> update_price_test.log 2>&1 &
# Или до 100 товаров:
nohup php update_price.php limit 100 >> update_price_test.log 2>&1 &
```

Проверить процесс: `ps aux | grep update_price`. Смотреть лог: `tail -f api/test/update_price_test.log`.
