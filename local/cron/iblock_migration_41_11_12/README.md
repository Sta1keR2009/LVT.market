# Миграция 41 -> 11 и SKU в 12

Скрипты выполняются только в CLI и используют `prolog_before.php`.

## Файлы

- `config.php` — общие настройки.
- `mapping_41_to_11.php` — маппинг свойств из 41 в 11.
- `mapping_41_to_12.php` — маппинг свойств из 41 в 12.
- `step1_clean_empty_properties_41.php` — удаление пустых свойств в 41.
- `step2_export_properties.php` — экспорт свойств 11/12/41 в CSV.
- `step3_migrate_41_to_11_and_12.php` — перенос элементов в 11 и создание SKU в 12.

## Важно перед запуском

1. Заполните в `config.php`:
   - `manufacturer_code_property_11`,
   - `manufacturer_code_property_41`,
   - `target_section` (ID или CODE раздела "Электротехника").
2. Заполните маппинги:
   - `mapping_41_to_11.php`,
   - `mapping_41_to_12.php`.
3. Проверьте `sku.link_property_id`:
   - если 0, скрипт пытается определить автоматически.

## Команды

```bash
php /var/www/www-root/data/www/lvtgroup.ru/local/cron/iblock_migration_41_11_12/step1_clean_empty_properties_41.php --dry-run=1
php /var/www/www-root/data/www/lvtgroup.ru/local/cron/iblock_migration_41_11_12/step1_clean_empty_properties_41.php --dry-run=0

php /var/www/www-root/data/www/lvtgroup.ru/local/cron/iblock_migration_41_11_12/step2_export_properties.php

php /var/www/www-root/data/www/lvtgroup.ru/local/cron/iblock_migration_41_11_12/step3_migrate_41_to_11_and_12.php --dry-run=1 --limit=50 --offset=0
php /var/www/www-root/data/www/lvtgroup.ru/local/cron/iblock_migration_41_11_12/step3_migrate_41_to_11_and_12.php --dry-run=0 --limit=50 --offset=0
```

Точечный тест на одном элементе:

```bash
php /var/www/www-root/data/www/lvtgroup.ru/local/cron/iblock_migration_41_11_12/step3_migrate_41_to_11_and_12.php --dry-run=1 --element-id=12345
```

## Логи и выгрузки

- Логи: `local/cron/iblock_migration_41_11_12/logs/`
- CSV: `local/cron/iblock_migration_41_11_12/export/`
