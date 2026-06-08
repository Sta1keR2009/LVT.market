#!/bin/bash
echo "=== Запуск импорта товаров ==="
date

# БЕЗОПАСНОСТЬ: Определяем пользователя для запуска скрипта
# Если скрипт запущен от root, переключаемся на www-root
if [ "$EUID" -eq 0 ]; then
    echo "Запуск от root обнаружен, переключение на пользователя www-root..."
    exec sudo -u www-root "$0" "$@"
    exit $?
fi

# Очищаем старые логи
echo "Очистка старых логов..."
> /var/www/www-root/data/www/lvtgroup.ru/api/product_import.log
> /var/www/www-root/data/www/lvtgroup.ru/api/import_statistics.log
echo '{"start_time": "'$(date +"%Y-%m-%d %H:%M:%S")'", "total_received_from_api": 0, "total_processed": 0, "total_created": 0, "total_updated": 0, "total_errors": 0, "memory_peak": 0, "end_time": null, "batches_processed": 0, "last_item_id": null}' > /var/www/www-root/data/www/lvtgroup.ru/api/import_stats.json

# Запускаем импорт
echo "Запуск импорта..."
php /var/www/www-root/data/www/lvtgroup.ru/api/load.php

echo "Импорт завершен"
date