#!/bin/bash
echo "=== Запуск обновления цен ==="
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
> /var/www/www-root/data/www/lvtgroup.ru/api/price_update.log
> /var/www/www-root/data/www/lvtgroup.ru/api/price_statistics.log
echo '{"start_time": "'$(date +"%Y-%m-%d %H:%M:%S")'", "total_received": 0, "total_updated": 0, "total_errors": 0, "memory_peak": 0, "end_time": null, "batches_processed": 0}' > /var/www/www-root/data/www/lvtgroup.ru/api/price_update_stats.json

# Запускаем обновление цен
echo "Запуск обновления цен..."
php /var/www/www-root/data/www/lvtgroup.ru/api/update_price.php

echo "Обновление цен завершено"
date