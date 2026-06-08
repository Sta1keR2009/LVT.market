# Исправление проблемы "This script cannot be run in root mode"

## Проблема

При запуске скриптов импорта от пользователя `root` появляется ошибка:
```
This script cannot be run in root mode.
```

## Причина

Модуль Bitrix `acrit.core` блокирует выполнение скриптов от пользователя `root` в целях безопасности. Это правильное поведение, так как выполнение скриптов от root может привести к проблемам с правами доступа к файлам.

## Решение

Скрипты должны запускаться от пользователя `www-root`, который является владельцем файлов сайта.

### Вариант 1: Использовать обновленные скрипты запуска (рекомендуется)

Скрипты `start_import.sh` и `start_price_update.sh` автоматически переключаются на пользователя `www-root`, если запущены от root:

```bash
# Можно запускать от root - автоматически переключится на www-root
sudo /var/www/www-root/data/www/lvtgroup.ru/api/start_import.sh
sudo /var/www/www-root/data/www/lvtgroup.ru/api/start_price_update.sh
```

### Вариант 2: Запускать напрямую от www-root

```bash
# Переключиться на пользователя www-root
sudo -u www-root php /var/www/www-root/data/www/lvtgroup.ru/api/load.php
sudo -u www-root php /var/www/www-root/data/www/lvtgroup.ru/api/update_price.php
```

### Вариант 3: Настроить cron задачи от www-root

В crontab пользователя `www-root`:

```bash
# Импорт товаров каждый день в 3:00
0 3 * * * /var/www/www-root/data/www/lvtgroup.ru/api/start_import.sh >> /var/www/www-root/data/www/lvtgroup.ru/api/cron_import.log 2>&1

# Обновление цен каждые 6 часов
0 */6 * * * /var/www/www-root/data/www/lvtgroup.ru/api/start_price_update.sh >> /var/www/www-root/data/www/lvtgroup.ru/api/cron_price_update.log 2>&1
```

## Проверка

После исправления скрипты должны работать:

```bash
# Проверка через скрипт запуска (автоматически переключится на www-root)
sudo /var/www/www-root/data/www/lvtgroup.ru/api/start_import.sh

# Или напрямую от www-root
sudo -u www-root php /var/www/www-root/data/www/lvtgroup.ru/api/load.php
```

## Важно

- ✅ Скрипты защищены от выполнения через HTTP (только CLI)
- ✅ Скрипты автоматически переключаются на правильного пользователя
- ✅ Защита от выполнения от root сохранена (модуль acrit.core)
- ✅ Все файлы создаются с правильными правами доступа
