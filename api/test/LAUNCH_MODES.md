# Режимы запуска тестового API (api/test)

Документация по способам запуска скриптов: через браузер, PHP в консоли, shell-скрипт и фоновый режим.

**Базовый URL (веб):** `https://lvtgroup.ru/api/test/` или `https://lvt.market/api/test/`  
**Путь на сервере:** `/var/www/www-root/data/www/lvtgroup.ru/api/test/`

---

## 1. Получение тестовых данных (без записи в Битрикс)

Скрипт: **`index.php`** — запрос к тестовому API, ответ только для просмотра.

| Способ        | Команда / URL |
|---------------|----------------|
| **Браузер**   | `https://lvtgroup.ru/api/test/?item_id=203075` |
|               | `https://lvtgroup.ru/api/test/?code=INA118UB`  |
| **PHP (CLI)** | `php index.php 203075` |
|               | `php index.php --item_id=203075` |
|               | `php index.php --code=INA118UB` |

Выполнять из каталога `api/test`.

---

## 2. Загрузка одного товара в склады и цены Битрикс

Скрипт: **`load_to_bitrix.php`** — один товар: остатки по складам и цены по типам цен (по срокам доставки).

| Способ        | Команда / URL |
|---------------|----------------|
| **Браузер**   | `https://lvtgroup.ru/api/test/load_to_bitrix.php?item_id=838637` |
|               | `https://lvtgroup.ru/api/test/load_to_bitrix.php?code=TSX-U-5T`  |
| **PHP (CLI)** | `php load_to_bitrix.php 838637` |
|               | `php load_to_bitrix.php --item_id=838637` |
|               | `php load_to_bitrix.php --code=TSX-U-5T` |

Выполнять из каталога `api/test`. Shell-скрипта для этого режима нет.

---

## 3. Обновление остатков и цен (каталог / один товар / лимит)

Скрипт: **`update_price.php`** — те же правила, что и `load_to_bitrix.php`, но с режимами **all**, **item**, **limit**.

### Режимы

| Режим   | Описание |
|--------|----------|
| **all**  | Весь каталог (пагинация по API). |
| **item** | Один товар по `item_id` или `code`. |
| **limit**| Обновить не более N товаров (параметр `count`). В CLI выводится прогресс в stderr. |

### 3.1 Запуск через браузер

| Режим  | URL |
|--------|-----|
| Весь каталог | `https://lvtgroup.ru/api/test/update_price.php?mode=all` |
| Один товар   | `https://lvtgroup.ru/api/test/update_price.php?mode=item&item_id=838637` |
|             | `https://lvtgroup.ru/api/test/update_price.php?mode=item&code=TSX-U-5T` |
| Лимит N      | `https://lvtgroup.ru/api/test/update_price.php?mode=limit&count=100` |

Ответ — JSON в теле страницы.

### 3.2 Запуск через PHP (CLI)

Выполнять из каталога `api/test`:

```bash
cd /var/www/www-root/data/www/lvtgroup.ru/api/test
```

| Режим  | Команда |
|--------|---------|
| **all**  | `php update_price.php all` |
| **item** | `php update_price.php item 838637` |
|         | `php update_price.php item --item_id=838637` |
|         | `php update_price.php item --code=TSX-U-5T` |
| **limit**| `php update_price.php limit 100` |
|         | `php update_price.php limit --count=50` |

- В режиме **limit** прогресс пишется в **stderr** (в терминале виден счётчик и пакеты).
- Итоговый JSON — в **stdout**. Только JSON: `php update_price.php limit 100 2>/dev/null`.

### 3.3 Запуск через shell-скрипт

Скрипт: **`start_update_price.sh`** — вызывает `update_price.php`, при запуске от root переключается на пользователя `www-root`, пишет вывод в лог.

```bash
cd /var/www/www-root/data/www/lvtgroup.ru/api/test
chmod +x start_update_price.sh   # один раз, если ещё не выполнен
```

| Режим  | Команда (интерактивно) | Команда (в фоне) |
|--------|------------------------|-------------------|
| **all**  | `./start_update_price.sh all` | `./start_update_price.sh all &` |
| **item** | `./start_update_price.sh item 838637` | `./start_update_price.sh item 838637 &` |
| **limit**| `./start_update_price.sh limit 100` | `./start_update_price.sh limit 100 &` |

Лог: **`api/test/update_price_test.log`** (все сообщения и JSON туда попадают при фоновом запуске).

### 3.4 Фоновый запуск без shell-скрипта (nohup)

Из каталога `api/test`:

```bash
# Весь каталог
nohup php update_price.php all >> update_price_test.log 2>&1 &

# Один товар
nohup php update_price.php item 838637 >> update_price_test.log 2>&1 &

# Лимит 100 товаров (прогресс тоже попадёт в лог)
nohup php update_price.php limit 100 >> update_price_test.log 2>&1 &
```

Проверить процесс: `ps aux | grep update_price`  
Смотреть лог: `tail -f api/test/update_price_test.log`

---

## 4. Сводная таблица способов запуска

| Скрипт                 | Браузер | PHP CLI | .sh скрипт | Фон (nohup / .sh &) |
|------------------------|--------|---------|------------|----------------------|
| `index.php`            | ✓      | ✓       | —          | по необходимости     |
| `load_to_bitrix.php`   | ✓      | ✓       | —          | по необходимости     |
| `update_price.php`     | ✓      | ✓       | ✓          | ✓ (рекомендуется .sh) |

Для **update_price.php** в режиме **limit** прогресс в консоли доступен только при запуске через **PHP CLI** (без перенаправления stderr).

---

## 5. Ответы и логи

- **Веб:** ответ — один JSON в теле страницы (Content-Type: application/json).
- **CLI:** JSON в stdout; в режиме **limit** прогресс — в stderr.
- **start_update_price.sh:** весь вывод (stdout + stderr) пишется в `update_price_test.log`, в начале и в конце строки с датой и exit-кодом.

Файлы логов и данных в `api/test/`:
- `update_price_test.log` — лог запусков через `start_update_price.sh` или nohup.
