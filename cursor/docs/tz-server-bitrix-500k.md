# Техническое задание: тюнинг сервера lvt.market под 1С-Битрикс (каталог 500k товаров)

| Параметр | Значение |
|---|---|
| Объект | Выделенный сервер `lvt.market` (94.198.50.146) |
| Назначение | Хостинг 3 интернет-магазинов на 1С-Битрикс + вспомогательные сервисы |
| Главный сайт | `lvtgroup.ru` — Битрикс 26.250.100, БД `lvtmarket`, ~500 000 товаров |
| Дата аудита | 27.05.2026 |
| Версия документа | 1.0 |
| Тип работ | Тюнинг существующей инсталляции (без миграции на новое железо) |
| Окно работ | Любое (пиковая нагрузка низкая) |
| Ограничение | Файловую структуру сайтов не трогаем |

## Оглавление

1. [Аппаратная конфигурация и ОС](#1-аппаратная-конфигурация-и-ос)
2. [Текущее состояние (AS-IS)](#2-текущее-состояние-as-is)
3. [Целевое состояние (TO-BE)](#3-целевое-состояние-to-be)
4. [План работ — приоритеты P0…P3](#4-план-работ--приоритеты-p0p3)
5. [Конфигурации (готовые шаблоны)](#5-конфигурации-готовые-шаблоны)
6. [Безопасность](#6-безопасность)
7. [Резервное копирование (Яндекс.Диск)](#7-резервное-копирование-яндексдиск)
8. [Мониторинг](#8-мониторинг)
9. [Критерии приёмки](#9-критерии-приёмки)
10. [Что не входит в ТЗ](#10-что-не-входит-в-тз)

---

## 1. Аппаратная конфигурация и ОС

| Компонент | Параметры |
|---|---|
| Платформа | ASUS PRIME B550M-A (consumer-плата, без IPMI/ECC) |
| Процессор | AMD Ryzen 7 5700G, 8 ядер / 16 потоков, до 4.67 GHz |
| RAM | 128 GB |
| Диск 1 | Samsung NVMe MZQLB960HAJR-00007, 960 GB (datacenter) |
| Диск 2 | Samsung NVMe MZQL2960HCJR-00A07, 960 GB (datacenter) |
| RAID | Программный RAID 1 (mdadm) — md0 (EFI 512 MB) + md2 (root 894 GB) |
| LVM | Отсутствует, ext4 поверх mdadm |
| ОС | Ubuntu Server 24.04.4 LTS, kernel 6.8.0-100 |
| Панель управления | ISPmanager 6 Lite |
| PHP (минимум) | 8.3.6 |

### Целевая нагрузка

- До 500 онлайн-пользователей, до 50 RPS пиково.
- API-импорт от поставщиков — фоновые задания (по расписанию, через cron + flock).
- SLA: TTFB ≤ 200 мс для кэшированных страниц каталога, доступность 99.9%.

---

## 2. Текущее состояние (AS-IS)

### 2.1 Сводка критичных проблем

| # | Приоритет | Проблема |
|---|---|---|
| 1 | P0 | **RAID 1 деградировал**: md0 и md2 работают на одном диске nvme1n1, второй nvme0n1 жив, но выпал из массива |
| 2 | P0 | **Кэш Битрикса в БД** (`'type' => 'db'`) во всех трёх сайтах — удвоенная нагрузка на MySQL |
| 3 | P1 | `join_buffer_size = 256M` per-connection в MySQL — потенциальный OOM (теоретически 125 GB при 500 коннектах) |
| 4 | P1 | `innodb_buffer_pool_size = 16G` при RAM 128 GB и БД 23 GB — сильное недоиспользование RAM |
| 5 | P1 | `innodb_flush_log_at_trx_commit = 0` — риск потери до 1 сек транзакций при крэше |
| 6 | P1 | Apache MPM prefork + mod_php (не FPM!) — неэффективно по памяти |
| 7 | P1 | `vm.swappiness = 60` (дефолт), активно используется swap 4.3/6 GB при 113 GB в buff/cache |
| 8 | P2 | TLS 1.0/1.1 включены на 4 vhost (`lvt-ec.ru`, `lvtec.ru`, `find.lvt.market`, `docs.lvt.market`) и `manager.conf` |
| 9 | P2 | SSH (22) и панель (1500/1501) открыты всем IP, UFW не активен |
| 10 | P2 | fail2ban покрывает только `sshd/dovecot/exim/proftpd` — без `nginx-*`, `apache-*`, `ispmanager` |
| 11 | P2 | Telegram bot token в `env[]` пула PHP-FPM `www` — секрет в незашифрованном конфиге |
| 12 | P2 | Cron Битрикса `lvtgroup.ru` использует `/opt/php84/bin/php` (PHP 8.4 — вне зоны поддержки Битрикса) |
| 13 | P3 | Нет внешних бэкапов (есть только `rclone` как транспорт) |
| 14 | P3 | Нет системного мониторинга (netdata/zabbix/prometheus) |
| 15 | P3 | Нет SMART/mdadm-алертов |

### 2.2 Состояние RAID

```
md0 : active raid1 nvme1n1p1[1]         [2/1] [_U]   degraded
md2 : active raid1 nvme1n1p2[1]         [2/1] [_U]   degraded
```

- `nvme0n1`: SMART OK, износ 1 %, температура 32 °C, ошибок 0 — диск **исправен**, просто выпал из массива.
- `nvme1n1`: SMART OK, износ 2 %, температура 31 °C, ошибок 0.
- **Все данные на одном диске** — любой аппаратный сбой = полная потеря.

### 2.3 Состояние веб-стека

| Слой | Реальное состояние |
|---|---|
| Frontend | Nginx 1.28.2 — слушает 80/443, проксирует на Apache 127.0.0.1:8081 |
| Backend HTTP | Apache 2.4.58, **MPM prefork + mod_php** (по 1 PHP-процессу на запрос) |
| PHP main | 8.3.6 — через Apache mod_php |
| PHP alt | 8.4 в `/opt/php84/bin/php` — используется в cron `lvtgroup.ru` |
| PHP-FPM 8.3 | Работает, но обслуживает только `ai-consultant` и Roundcube |
| MySQL | 8.0.45 (НЕ MariaDB), на 127.0.0.1:3306, БД 23 GB |
| Redis | Только в Docker-контейнере `findlvtmarket-redis-1` — Битриксом не используется |
| Поиск | Штатный Битрикс (b_search_content_stem 419 MB / 4.1 M строк) |

### 2.4 Состояние Битрикс `.settings.php`

Все три сайта (`lvtgroup.ru`, `lvt-ec.ru`, `lvtec.ru`):

```php
'cache' => [
    'value' => [
        'type' => 'db',            // ← КЭШ В БД, надо переключить на Redis
        'host' => 'localhost',
    ],
],
```

Сессии — файловые по умолчанию (через mod_php).

### 2.5 Топ-таблицы БД `lvtmarket`

| Таблица | Размер данных | Размер индексов | Строки |
|---|---:|---:|---:|
| `apriori_optimizer_seo_links` | 790 MB | 0 | 3 474 558 |
| `b_iblock_element_property` | 486 MB | 954 MB | 3 860 385 |
| `b_iblock_11_index` | 423 MB | 615 MB | 7 484 845 |
| `b_search_content_stem` | 419 MB | 213 MB | 4 175 480 |
| `b_iblock_element_iprop` | 406 MB | 65 MB | 1 346 535 |
| `b_iblock_element` | 387 MB | 184 MB | 373 818 |
| `b_search_content_title` | 314 MB | 202 MB | 4 175 501 |
| `b_search_content` | 285 MB | 41 MB | 443 212 |

Поисковые таблицы (`b_search_content*`) суммарно ≈ 1.5 GB — серьёзная нагрузка на штатный поиск.

### 2.6 Текущий my.cnf — основные параметры

```ini
[mysqld]
bind-address = 127.0.0.1
max_connections = 500
innodb_buffer_pool_size = 16G          # МАЛО (RAM 128G, БД 23G)
innodb_flush_log_at_trx_commit = 0     # РИСК потери транзакций
innodb_flush_method = O_DIRECT         # ok
transaction-isolation = READ-COMMITTED # ok (рекомендация Битрикса)
join_buffer_size = 256M                # ОПАСНО (per-connection × max_conn)
tmp_table_size = 256M                  # OK
table_open_cache = 40960               # OK
skip-name-resolve                      # OK
default-authentication-plugin = mysql_native_password  # deprecated в MySQL 8
```

### 2.7 Sysctl — текущие значения

```
vm.swappiness = 60                   # надо 10
net.core.somaxconn = 4096            # надо 65535
net.ipv4.tcp_max_syn_backlog = 4096  # надо 8192
vm.dirty_ratio = 20                  # надо 10
vm.dirty_background_ratio = 10       # надо 5
```

CPU governor: драйвер `cpufreq` не загружен — управление частотой отдано BIOS.

I/O scheduler для NVMe: не зафиксирован (нужно `none`).

---

## 3. Целевое состояние (TO-BE)

### 3.1 Дисковая подсистема

- RAID 1 в состоянии `clean [UU]` на обоих массивах.
- Загрузчик GRUB установлен на оба диска (EFI-запись продублирована через `efibootmgr`).
- Резервный диск автоматически собирается при сбое — настроен `mdmonitor` с email-оповещениями.

LVM поверх mdadm — **не делаем** в рамках этого ТЗ (требует переразметки/простоя). Фиксируется как пожелание для следующего апгрейда.

### 3.2 Веб-стек — Nginx → PHP-FPM 8.3 напрямую (Apache выводится из эксплуатации)

```
                       ┌──────────────┐
   HTTPS 443  ─────►   │  Nginx 1.28  │  ──► статика (open_file_cache)
                       │              │  ──► композит /bitrix/html_pages/
                       └──────┬───────┘
                              │ fastcgi unix socket
                              ▼
                    ┌─────────────────────┐
                    │  PHP-FPM 8.3        │
                    │  пул lvtgroup-web   │  user=www-root
                    │  пул lvt-ec-web     │  user=www-root
                    │  пул lvtec-web      │  user=www-root
                    │  пул bitrix-cli     │  для cron/импортов
                    └─────────┬───────────┘
                              ▼
                    ┌─────────────────────┐
                    │  MySQL 8.0.45       │  innodb_buffer_pool=48G
                    │  Redis (cache+sess) │  unix-socket
                    │  Manticore Search   │  127.0.0.1:9306
                    └─────────────────────┘
```

- Apache 2.4 — `systemctl disable --now apache2` (пакет остаётся установленным на случай отката).
- Подготовка: конвертация `.htaccess` в nginx-include для каждого сайта (Битрикс предоставляет готовый шаблон в `bitrix/modules/main/install/htaccess/`).
- Отдельный FPM-пул на сайт с `user = www-root` — сохраняет изоляцию между сайтами (взамен `mpm_itk`).

### 3.3 Кэш и сессии Битрикса — Redis

- Установить `redis-server` (системный) + `php8.3-redis` + `php8.3-igbinary`.
- Слушать на unix-socket `/run/redis/redis-server.sock` (быстрее TCP).
- `maxmemory = 8gb`, `maxmemory-policy = allkeys-lru`, persistence отключена.
- `.settings.php` всех трёх сайтов перевести на `'type' => 'redis'` + сессии через redis-handler.

### 3.4 Поиск по каталогу — Manticore Search

- Установить из официального APT-репозитория Manticore.
- Real-time индекс по `b_search_content` с морфологией `stem_ru, stem_en`.
- Слушает на 127.0.0.1:9306 (MySQL protocol для Битрикса) и 9308 (HTTP).
- Подключение через штатный модуль `search.sphinx` Битрикса.
- Дельта-индексация раз в 15 минут, полный ребилд — ночью.

### 3.5 MySQL — целевые параметры

- `innodb_buffer_pool_size = 48G` (вмещает БД 23 GB целиком + запас на рост).
- `join_buffer_size = 4M` (безопасно).
- `innodb_flush_log_at_trx_commit = 2` (компромисс).
- `max_connections = 300` (с учётом реальной нагрузки 50 RPS).
- Slow query log `long_query_time = 0.5`, ротация через logrotate.

### 3.6 ОС — целевой профиль

- `vm.swappiness = 10`, NVMe scheduler = `none`, лимиты `nofile = 200000`.
- CPU governor = `performance` через `cpupower` (после установки `linux-tools-generic`).
- Swap уменьшить до 4 GB (insurance, не использовать в нормальном режиме).
- `unattended-upgrades` для security-патчей (уже включён).

---

## 4. План работ — приоритеты P0…P3

### P0 — критично, выполнить в первую очередь

**P0a. Полный бэкап до любых работ** (см. раздел 7)

- `mysqldump --single-transaction --routines --triggers --hex-blob --all-databases | gzip | rclone rcat yadisk:backup/pre-raid-$(date +%F-%H%M).sql.gz`
- `tar czf - /etc /usr/local/mgr5/etc /root | rclone rcat yadisk:backup/pre-raid-etc-$(date +%F-%H%M).tar.gz`
- Опционально: критичные подкаталоги `/var/www/www-root/data/www/lvtgroup.ru/{upload,local,bitrix/php_interface}` — отдельным архивом.
- **Контрольная точка**: проверить листинг через `rclone ls yadisk:backup/` и тестово скачать один dump обратно.

**P0b. Восстановление RAID 1** (см. раздел 5.1)

Процедура:

1. `mdadm --examine /dev/nvme0n1p1 /dev/nvme0n1p2` — сохранить вывод.
2. `mdadm --zero-superblock /dev/nvme0n1p1 /dev/nvme0n1p2`.
3. `sgdisk -R=/dev/nvme0n1 /dev/nvme1n1 && sgdisk -G /dev/nvme0n1` — синхронизация таблицы разделов.
4. `echo 100000 > /proc/sys/dev/raid/speed_limit_max` (дроссель до 100 MB/s).
5. `mdadm --add /dev/md0 /dev/nvme0n1p1`.
6. `mdadm --add /dev/md2 /dev/nvme0n1p2`.
7. Мониторить: `watch -n 5 cat /proc/mdstat`. Ожидаемое время — 1.5–3 часа.
8. После завершения: `grub-install /dev/nvme0n1`, `efibootmgr --create --disk /dev/nvme0n1 --part 1 --label "ubuntu-mirror" --loader '\EFI\ubuntu\shimx64.efi'`.
9. Настроить `/etc/mdadm/mdadm.conf` → `MAILADDR <admin@email>` для алертов о деградации.

**Сервер остаётся в боевом режиме без downtime. Данные не теряются** при условии:
- категорически не использовать `mdadm --create` (только `--add`);
- проверять имя устройства перед каждой командой (`lsblk`, `mdadm --detail`);
- бэкап P0a выполнен.

**P0c. Redis для Битрикса** (см. раздел 5.3)

1. `apt install redis-server php8.3-redis php8.3-igbinary`.
2. Применить конфиг `/etc/redis/redis.conf` (раздел 5.3).
3. `usermod -aG redis www-data && usermod -aG redis www-root`.
4. `systemctl restart redis-server php8.3-fpm`.
5. Для каждого из трёх `.settings.php` — заменить секцию `cache` и `session` на Redis (см. 5.3).
6. Сброс кэша Битрикса через админку → «Настройки → Производительность → Очистить файлы кэша».
7. **Контрольная точка**: `redis-cli -s /run/redis/redis-server.sock INFO keyspace` — растёт количество ключей, страницы каталога 2-й раз TTFB ≤ 200 мс.

### P1 — высокий приоритет, 1–2 недели

**P1a. Тюнинг MySQL** (см. раздел 5.4)

1. Сделать `mysqldump` перед изменениями (отдельно от P0a, чтобы был «свежак»).
2. Обновить `/etc/mysql/mysql.conf.d/mysqld.cnf` параметрами из раздела 5.4.
3. `systemctl restart mysql` (downtime ~5–10 сек на момент рестарта).
4. `SHOW ENGINE INNODB STATUS\G` — проверить отсутствие ошибок.
5. **Контрольная точка**: через сутки в slow.log не больше 50 медленных запросов, max_used_connections < 200.

**P1b. Подготовка Nginx → PHP-FPM (staging)** (см. раздел 5.2)

1. Создать staging-домен (например, `staging.lvtgroup.ru` или временный поддомен).
2. Создать новые FPM-пулы `lvtgroup-web`, `lvt-ec-web`, `lvtec-web`, `bitrix-cli` (раздел 5.2).
3. Сконвертировать `.htaccess` основного сайта в nginx-include (раздел 5.2).
4. Прогнать `bitrix_server_test.php` на staging.
5. Прогнать нагрузочный тест: `wrk -t8 -c100 -d60s https://staging.lvtgroup.ru/catalog/`.

**P1c. Переключение боевых сайтов** на nginx+FPM

- По одному сайту с интервалом 1–2 дня (`lvtec.ru` → `lvt-ec.ru` → `lvtgroup.ru`).
- `systemctl disable --now apache2` после переключения всех трёх.
- **Контрольная точка**: bitrix_server_test.php все зелёные, RPS вырос ≥ 2× относительно prefork+mod_php.

**P1d. ОС sysctl/limits/NVMe** (см. раздел 5.5)

1. Создать `/etc/sysctl.d/99-bitrix.conf` (раздел 5.5).
2. Создать `/etc/security/limits.d/99-bitrix.conf`.
3. Создать `/etc/udev/rules.d/60-nvme.rules` для NVMe scheduler.
4. `sysctl --system && udevadm trigger`.
5. Установить `linux-tools-generic` + `cpupower frequency-set -g performance`.
6. **Контрольная точка**: `swap used` ≤ 100 MB при нагрузке, `cat /sys/block/nvme0n1/queue/scheduler` показывает `[none]`.

**P1e. Cron Битрикса на php8.3-cli**

- В crontab пользователя `www-root` заменить `/opt/php84/bin/php` на `/usr/bin/php8.3` для скрипта `cron_events.php` и других.

### P2 — средний приоритет, в течение месяца

**P2a. TLS hardening** (см. раздел 6.1)

В пяти файлах заменить `ssl_protocols TLSv1 TLSv1.1 TLSv1.2 TLSv1.3;` на `ssl_protocols TLSv1.2 TLSv1.3;`:

- `/etc/nginx/vhosts/www-root/lvt-ec.ru.conf:47`
- `/etc/nginx/vhosts/www-root/lvtec.ru.conf:49`
- `/etc/nginx/vhosts/www-root/find.lvt.market.conf:46`
- `/etc/nginx/vhosts/www-root/docs.lvt.market.conf:45`
- `/etc/nginx/ssl_cert_servers/manager.conf:11`

Добавить modern cipher suite + HSTS (раздел 6.1).

**Контрольная точка**: `testssl.sh https://lvtgroup.ru` оценка A+.

**P2b. UFW + fail2ban** (см. раздел 6.2)

- UFW базовый профиль с whitelist для 22/1500/1501.
- Добавить fail2ban jails: `nginx-http-auth`, `nginx-botsearch`, `nginx-limit-req`, `apache-auth`, `ispmanager`.
- Удалить Telegram bot token из `/etc/php/8.3/fpm/pool.d/www.conf` (`env[TELEGRAM_LEAD_BOT_TOKEN]`), перенести в Битрикс-настройку модуля.

**P2c. Manticore Search** (см. раздел 5.6)

**P2d. Битрикс «Композит» + «Проактивный фильтр»**

- В админке `/bitrix/admin/composite_options.php` — включить для всех анонимных пользователей.
- В админке `/bitrix/admin/security_filter.php` — включить Проактивный фильтр.
- Проверить `bitrix_server_test.php` на наличие зелёных пунктов «Композитный сайт» и «Проактивный фильтр».

### P3 — постоянная гигиена

**P3a. Внешние бэкапы** (см. раздел 7)

- Настроить rclone remote `yadisk:` (webdav, https://webdav.yandex.ru).
- Установить `borgbackup`.
- Скрипт ежедневного бэкапа в `/usr/local/sbin/lvt-backup.sh` + запись в cron.

**P3b. Netdata** (см. раздел 8)

- `apt install netdata` или официальный installer.
- Прокси через nginx с basic-auth на поддомене `monitor.lvtgroup.ru`.
- Алерты на email/Telegram по критичным метрикам.

**P3c. План замены железа** (на следующий апгрейд)

- Серверная плата с IPMI/BMC (Supermicro, ASRock Rack).
- ECC RAM.
- Hot-swap корпус для замены NVMe без выключения.

---

## 5. Конфигурации (готовые шаблоны)

### 5.1 RAID — процедура восстановления

```bash
# 1. Аудит состояния второго диска
mdadm --examine /dev/nvme0n1p1 /dev/nvme0n1p2 | tee /root/raid-restore-examine.log

# 2. Очистка старых метаданных RAID
mdadm --zero-superblock /dev/nvme0n1p1
mdadm --zero-superblock /dev/nvme0n1p2

# 3. Перенос таблицы разделов с живого на новый
sgdisk -R=/dev/nvme0n1 /dev/nvme1n1
sgdisk -G /dev/nvme0n1     # новый GUID

# 4. Дроссель ресинка (защита боевого IO)
echo 100000 > /proc/sys/dev/raid/speed_limit_max

# 5. Возврат разделов в массив
mdadm --add /dev/md0 /dev/nvme0n1p1
mdadm --add /dev/md2 /dev/nvme0n1p2

# 6. Наблюдение
watch -n 5 cat /proc/mdstat

# 7. После завершения — дублируем загрузчик
grub-install /dev/nvme0n1
update-grub
# EFI запись на второй диск:
efibootmgr --create --disk /dev/nvme0n1 --part 1 \
  --label "ubuntu-mirror" --loader '\EFI\ubuntu\shimx64.efi'

# 8. Сохраняем актуальную конфигурацию mdadm
mdadm --detail --scan >> /etc/mdadm/mdadm.conf
update-initramfs -u
```

`/etc/mdadm/mdadm.conf` — добавить строку для алертов:
```
MAILADDR admin@lvt.market
```

### 5.2 Nginx + PHP-FPM 8.3

`/etc/nginx/nginx.conf` (фрагмент):

```nginx
user www-data;
worker_processes auto;
worker_rlimit_nofile 200000;
worker_cpu_affinity auto;

events {
    worker_connections 8192;
    use epoll;
    multi_accept on;
}

http {
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 30;
    keepalive_requests 1000;
    server_tokens off;

    client_max_body_size 256m;
    client_body_buffer_size 256k;
    large_client_header_buffers 8 16k;

    open_file_cache max=200000 inactive=60s;
    open_file_cache_valid 120s;
    open_file_cache_min_uses 2;

    gzip on;
    gzip_comp_level 5;
    gzip_min_length 1000;
    gzip_proxied any;
    gzip_vary on;
    gzip_types text/plain text/css text/xml text/javascript application/json
               application/javascript application/xml application/xml+rss
               application/x-javascript image/svg+xml font/woff font/woff2;

    fastcgi_buffers 16 32k;
    fastcgi_buffer_size 64k;
    fastcgi_busy_buffers_size 128k;
    fastcgi_temp_file_write_size 256k;
    fastcgi_read_timeout 90;
    fastcgi_connect_timeout 10;

    include /etc/nginx/conf.d/*.conf;
    include /etc/nginx/vhosts/*/*.conf;
}
```

Vhost-шаблон для Битрикс-сайта (на примере `lvtgroup.ru`):

```nginx
upstream lvtgroup_php {
    server unix:/run/php/php8.3-fpm-lvtgroup.sock;
    keepalive 32;
}

server {
    listen 80;
    server_name lvtgroup.ru www.lvtgroup.ru;
    return 301 https://lvtgroup.ru$request_uri;
}

server {
    listen 443 ssl;
    http2 on;
    server_name lvtgroup.ru www.lvtgroup.ru;
    root /var/www/www-root/data/www/lvtgroup.ru;
    index index.php index.html;

    ssl_certificate     /etc/letsencrypt/live/lvtgroup.ru/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/lvtgroup.ru/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:50m;
    ssl_session_timeout 1d;
    ssl_session_tickets off;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options SAMEORIGIN;

    # Композит Битрикса
    set $usefilecache 0;
    if ($request_method = GET)              { set $usefilecache 1; }
    if ($http_cookie ~ "BITRIX_SM_LOGIN")   { set $usefilecache 0; }
    if ($http_cookie ~ "BX_USER_ID")        { set $usefilecache 0; }

    location / {
        try_files /bitrix/html_pages/$host$uri@$args.html
                  $uri $uri/ /bitrix/urlrewrite.php?$args;
    }

    # Блокировки
    location ~* /\.ht           { deny all; }
    location ~* /\.git          { deny all; }
    location ~* /bitrix/backup  { deny all; }
    location ~* /upload/.*\.(php|phtml|phar|pl|py|cgi|sh)$  { deny all; }
    location ~* /bitrix/(cache|managed_cache|stack_cache|tmp)/.*\.(php|phtml|phar)$ { deny all; }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass lvtgroup_php;
        fastcgi_index index.php;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param HTTPS on;
        fastcgi_keep_conn on;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|webp|avif|css|js|woff2|woff|ttf|svg)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    error_log  /var/log/nginx/lvtgroup.ru.error.log;
    access_log /var/log/nginx/lvtgroup.ru.access.log;
}
```

PHP-FPM пул `/etc/php/8.3/fpm/pool.d/lvtgroup-web.conf`:

```ini
[lvtgroup-web]
user = www-root
group = www-root
listen = /run/php/php8.3-fpm-lvtgroup.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 40
pm.start_servers = 8
pm.min_spare_servers = 4
pm.max_spare_servers = 16
pm.max_requests = 500
pm.process_idle_timeout = 30s

request_terminate_timeout = 90s
slowlog = /var/log/php-fpm/lvtgroup-slow.log
request_slowlog_timeout = 5s

php_admin_value[memory_limit] = 512M
php_admin_value[max_execution_time] = 60
php_admin_value[post_max_size] = 256M
php_admin_value[upload_max_filesize] = 256M
php_admin_value[short_open_tag] = On
php_admin_value[error_log] = /var/log/php-fpm/lvtgroup-error.log
```

PHP-FPM пул для CLI/cron `/etc/php/8.3/fpm/pool.d/bitrix-cli.conf`:

```ini
[bitrix-cli]
user = www-root
group = www-root
listen = /run/php/php8.3-fpm-bitrix-cli.sock
pm = ondemand
pm.max_children = 8
pm.process_idle_timeout = 60s
php_admin_value[memory_limit] = 2048M
php_admin_value[max_execution_time] = 0
```

`/etc/php/8.3/fpm/php.ini` ключевые параметры:

```ini
memory_limit = 512M
max_execution_time = 60
max_input_vars = 10000
realpath_cache_size = 4096K
realpath_cache_ttl = 600
post_max_size = 256M
upload_max_filesize = 256M
expose_php = Off
short_open_tag = On

[opcache]
opcache.enable = 1
opcache.enable_cli = 0
opcache.memory_consumption = 512
opcache.interned_strings_buffer = 64
opcache.max_accelerated_files = 200000
opcache.validate_timestamps = 1
opcache.revalidate_freq = 60
opcache.save_comments = 1
opcache.jit = tracing
opcache.jit_buffer_size = 128M
```

> `opcache.save_comments = 1` — **обязательно** для Битрикса (использует DocBlock-аннотации в ORM).

### 5.3 Redis для Битрикса

`/etc/redis/redis.conf` — ключевые параметры:

```
bind 127.0.0.1 -::1
port 6379
unixsocket /run/redis/redis-server.sock
unixsocketperm 770
maxmemory 8gb
maxmemory-policy allkeys-lru
tcp-backlog 511
timeout 0
tcp-keepalive 60
save ""
appendonly no
io-threads 4
io-threads-do-reads yes
```

`/var/www/www-root/data/www/lvtgroup.ru/bitrix/.settings.php` — секции `cache` и `session`:

```php
'cache' => [
    'value' => [
        'type' => 'redis',
        'redis' => [
            'host' => '/run/redis/redis-server.sock',
            'port' => 0,
            'persistent' => true,
            'serializer' => \Bitrix\Main\Data\RedisConnection::SERIALIZER_IGBINARY,
            'persistent_id' => 'bx-lvtgroup',
            'failover' => 'none',
        ],
    ],
    'readonly' => false,
],
'session' => [
    'value' => [
        'mode' => 'default',
        'handlers' => [
            'general' => [
                'type' => 'redis',
                'host' => '/run/redis/redis-server.sock',
                'port' => 0,
                'serializer' => \Bitrix\Main\Data\RedisConnection::SERIALIZER_IGBINARY,
            ],
        ],
    ],
    'readonly' => false,
],
```

> Для `lvt-ec.ru` и `lvtec.ru` — повторить с уникальными `persistent_id` (`bx-lvtec`, `bx-lvtgroupec`).

### 5.4 MySQL 8.0 — целевой my.cnf

`/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
[mysqld]
user = mysql
bind-address = 127.0.0.1
mysqlx-bind-address = 127.0.0.1
skip-name-resolve = ON

max_connections = 300
back_log = 1024
wait_timeout = 600
interactive_timeout = 600
connect_timeout = 30
max_allowed_packet = 256M

# Per-connection буферы — безопасные
join_buffer_size = 4M
sort_buffer_size = 4M
read_buffer_size = 2M
read_rnd_buffer_size = 4M
tmp_table_size = 64M
max_heap_table_size = 64M

# InnoDB
innodb_buffer_pool_size = 48G
innodb_buffer_pool_instances = 16
innodb_log_file_size = 2G
innodb_log_buffer_size = 64M
innodb_flush_method = O_DIRECT
innodb_flush_log_at_trx_commit = 2
innodb_flush_neighbors = 0
innodb_io_capacity = 4000
innodb_io_capacity_max = 8000
innodb_read_io_threads = 8
innodb_write_io_threads = 8
innodb_stats_on_metadata = OFF
innodb_file_per_table = 1

# Совместимость Битрикс
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
transaction-isolation = READ-COMMITTED
sql_mode = ""
default-authentication-plugin = caching_sha2_password

# Кэши
table_open_cache = 16000
table_definition_cache = 4000
thread_cache_size = 128
open_files_limit = 200000

# Лимиты
local-infile = 0
skip-log-bin
disable-log-bin

# Логи
log_error = /var/log/mysql/error.log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 0.5
log_queries_not_using_indexes = 0
```

### 5.5 ОС — тюнинг ядра

`/etc/sysctl.d/99-bitrix.conf`:

```
# Память
vm.swappiness = 10
vm.dirty_ratio = 10
vm.dirty_background_ratio = 5
vm.overcommit_memory = 1

# Сеть
net.core.somaxconn = 65535
net.core.netdev_max_backlog = 16384
net.ipv4.tcp_max_syn_backlog = 8192
net.ipv4.tcp_fin_timeout = 15
net.ipv4.tcp_tw_reuse = 1
net.ipv4.tcp_keepalive_time = 300
net.ipv4.ip_local_port_range = 10240 65535

# Файлы
fs.file-max = 2097152
fs.aio-max-nr = 1048576
```

`/etc/security/limits.d/99-bitrix.conf`:

```
* soft nofile 200000
* hard nofile 200000
* soft nproc 65535
* hard nproc 65535
www-data soft nofile 200000
www-root soft nofile 200000
mysql    soft nofile 200000
mysql    hard nofile 200000
```

`/etc/udev/rules.d/60-nvme.rules`:

```
ACTION=="add|change", KERNEL=="nvme[0-9]n[0-9]", ATTR{queue/scheduler}="none", ATTR{queue/nr_requests}="1024", ATTR{queue/read_ahead_kb}="128"
```

CPU governor:

```bash
apt install linux-tools-generic linux-tools-$(uname -r)
cpupower frequency-set -g performance
# Закрепить в systemd:
cat > /etc/systemd/system/cpupower.service <<EOF
[Unit]
Description=CPU governor performance
[Service]
Type=oneshot
ExecStart=/usr/bin/cpupower frequency-set -g performance
[Install]
WantedBy=multi-user.target
EOF
systemctl enable cpupower.service
```

### 5.6 Manticore Search

Установка:

```bash
wget https://repo.manticoresearch.com/manticore-repo.noarch.deb
dpkg -i manticore-repo.noarch.deb
apt update
apt install manticore manticore-extra
systemctl enable --now manticore
```

`/etc/manticoresearch/manticore.conf` (фрагмент для Битрикса):

```
index bitrix_lvtgroup {
    type            = rt
    path            = /var/lib/manticore/bitrix_lvtgroup

    rt_field        = title
    rt_field        = body
    rt_attr_uint    = iblock_id
    rt_attr_uint    = item_id
    rt_attr_string  = url

    morphology      = stem_ru, stem_en
    min_word_len    = 2
    min_infix_len   = 2
    html_strip      = 1
    charset_table   = non_cjk
    blend_chars     = +, &, U+23, ., -, /, U+5F
}

searchd {
    listen          = 127.0.0.1:9306:mysql41
    listen          = 127.0.0.1:9308:http
    log             = /var/log/manticore/searchd.log
    query_log       = /var/log/manticore/query.log
    query_log_format = sphinxql
    pid_file        = /var/run/manticore/searchd.pid
    data_dir        = /var/lib/manticore
    binlog_path     = /var/lib/manticore/binlog
}
```

Подключение к Битриксу — в админке Битрикса «Настройки → Поиск → Поиск Sphinx» прописать:
- Хост: `127.0.0.1`
- Порт: `9306`
- Индекс: `bitrix_lvtgroup`

Cron индексации (`crontab -u www-root -e`):

```cron
*/15 * * * * /usr/bin/php8.3 -f /var/www/www-root/data/www/lvtgroup.ru/bitrix/modules/search/cli/sphinx_reindex.php >> /var/log/bitrix-search.log 2>&1
```

---

## 6. Безопасность

### 6.1 TLS hardening

В 5 файлах nginx заменить старую строку:

```
ssl_protocols TLSv1 TLSv1.1 TLSv1.2 TLSv1.3;
```

на:

```
ssl_protocols TLSv1.2 TLSv1.3;
ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384';
ssl_prefer_server_ciphers on;
ssl_session_cache shared:SSL:50m;
ssl_session_timeout 1d;
ssl_session_tickets off;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```

Файлы (по результатам аудита):
- `/etc/nginx/vhosts/www-root/lvt-ec.ru.conf:47`
- `/etc/nginx/vhosts/www-root/lvtec.ru.conf:49`
- `/etc/nginx/vhosts/www-root/find.lvt.market.conf:46`
- `/etc/nginx/vhosts/www-root/docs.lvt.market.conf:45`
- `/etc/nginx/ssl_cert_servers/manager.conf:11`

После применения — `nginx -t && systemctl reload nginx`, проверка `testssl.sh https://<host>`.

### 6.2 UFW

```bash
ufw default deny incoming
ufw default allow outgoing

# HTTP/HTTPS — открыто всем
ufw allow 80/tcp
ufw allow 443/tcp

# Mail (если шлём наружу или принимаем)
ufw allow 25/tcp
ufw allow 587/tcp
ufw allow 465/tcp
ufw allow 993/tcp
ufw allow 995/tcp

# Админ-доступ — только с IP администратора (ЗАПОЛНИТЬ!)
ADMIN_IP=<заполнить_перед_применением>
ufw allow from $ADMIN_IP to any port 22 proto tcp
ufw allow from $ADMIN_IP to any port 1500 proto tcp
ufw allow from $ADMIN_IP to any port 1501 proto tcp

ufw enable
ufw status verbose
```

> **Внимание**: перед `ufw enable` убедиться, что SSH доступ с указанного IP работает (иначе можно потерять доступ к серверу).

### 6.3 SSH

`/etc/ssh/sshd_config.d/99-hardening.conf`:

```
PermitRootLogin prohibit-password
PasswordAuthentication no
PubkeyAuthentication yes
MaxAuthTries 3
LoginGraceTime 20
ClientAliveInterval 60
ClientAliveCountMax 3
```

После применения — `systemctl reload sshd`, **не закрывая активную SSH-сессию** до проверки нового подключения.

### 6.4 fail2ban — дополнительные jails

`/etc/fail2ban/jail.local`:

```ini
[nginx-http-auth]
enabled = true
filter = nginx-http-auth
logpath = /var/log/nginx/*error.log
maxretry = 3
findtime = 600
bantime = 3600

[nginx-botsearch]
enabled = true
filter = nginx-botsearch
logpath = /var/log/nginx/*access.log
maxretry = 2
findtime = 600
bantime = 86400

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
logpath = /var/log/nginx/*error.log
maxretry = 10
findtime = 600
bantime = 3600

[ispmanager]
enabled = true
filter = ispmanager
logpath = /usr/local/mgr5/var/ihttpd.log
maxretry = 5
findtime = 600
bantime = 86400
```

Кастомный фильтр `/etc/fail2ban/filter.d/ispmanager.conf` — для логов панели управления (создаётся на основе формата логов конкретной версии ISPmanager).

### 6.5 Битрикс — встроенная защита

- Включить **Проактивный фильтр** во всех трёх сайтах: `/bitrix/admin/security_filter.php` → «Включить».
- Включить **Контроль активности** для критичных страниц (логин, оплата): `/bitrix/admin/security_redirect.php`.
- Включить **Защита админки**: уровень «Стандарт» как минимум.

### 6.6 Удаление секрета из FPM

В `/etc/php/8.3/fpm/pool.d/www.conf` присутствует:

```
env[TELEGRAM_LEAD_BOT_TOKEN] = 8856124640:AAGn2qt9v9DyWr-ijDJ_lgE4H7UjXUfSR6M
env[TELEGRAM_PROXY_URL] = http://LM8Efc73:7cvVyzHi@153.80.171.52:64504
```

**Действия**:
1. **Отозвать токен** в @BotFather (заново выпустить).
2. Перенести новый токен в Битрикс-настройку соответствующего модуля (через интерфейс администратора).
3. Удалить строки `env[TELEGRAM_*]` из конфига пула.
4. Перезапустить `systemctl reload php8.3-fpm`.

### 6.7 Внешний WAF/DDoS

Для интернет-магазина в публичном интернете однократного хостинга недостаточно. Рекомендация (организационная):

- Cloudflare (бесплатный план или Pro) — DNS-проксирование, базовый WAF, защита от L7-DDoS.
- DDoS-Guard / Qrator — для российской аудитории, отдельный кейс.

Указывается в ТЗ как обязательная мера; реализация — вне рамок этого ТЗ.

---

## 7. Резервное копирование (Яндекс.Диск)

### 7.1 Подключение rclone к Яндекс.Диску

```bash
rclone config
# new remote → name: yadisk → type: webdav
#   url: https://webdav.yandex.ru
#   vendor: other
#   user: <login>
#   pass: <app_password из настроек Яндекса>
```

Проверка:

```bash
rclone lsd yadisk:
rclone mkdir yadisk:backup
```

### 7.2 Сценарий ежедневного бэкапа

`/usr/local/sbin/lvt-backup.sh`:

```bash
#!/bin/bash
set -euo pipefail

DATE=$(date +%F)
BACKUP_DIR=/var/backups/lvt
REMOTE=yadisk:backup

mkdir -p "$BACKUP_DIR/mysql" "$BACKUP_DIR/etc"

# 1. MySQL — все базы
mysqldump --defaults-file=/root/.my.cnf \
    --single-transaction --routines --triggers --hex-blob \
    --all-databases \
    | gzip -9 > "$BACKUP_DIR/mysql/all-$DATE.sql.gz"

# 2. Конфиги
tar czf "$BACKUP_DIR/etc/etc-$DATE.tar.gz" \
    /etc /usr/local/mgr5/etc /root 2>/dev/null

# 3. Borg — файлы сайтов (с дедупликацией)
export BORG_PASSPHRASE="<задать>"
borg create --stats --compression zstd,3 \
    /var/backups/borg::www-$DATE \
    /var/www/www-root/data/www

# 4. Заливка в Я.Диск
rclone copy "$BACKUP_DIR/mysql/all-$DATE.sql.gz" "$REMOTE/mysql/" \
    --bwlimit 50M --transfers 2
rclone copy "$BACKUP_DIR/etc/etc-$DATE.tar.gz" "$REMOTE/etc/" \
    --bwlimit 50M
rclone sync /var/backups/borg "$REMOTE/borg/" \
    --bwlimit 50M --transfers 2

# 5. Локальная ротация
find "$BACKUP_DIR/mysql" -name '*.sql.gz' -mtime +7 -delete
find "$BACKUP_DIR/etc"   -name '*.tar.gz' -mtime +7 -delete
borg prune --keep-daily 7 --keep-weekly 4 --keep-monthly 3 /var/backups/borg

echo "Backup completed: $DATE"
```

Cron (`crontab -e` от root):

```
0 3 * * * /usr/local/sbin/lvt-backup.sh >> /var/log/lvt-backup.log 2>&1
```

### 7.3 Pre-backup перед RAID-работами (разовая операция)

```bash
TS=$(date +%F-%H%M)
mysqldump --defaults-file=/root/.my.cnf \
    --single-transaction --routines --triggers --hex-blob \
    --all-databases \
  | gzip -9 \
  | rclone rcat yadisk:backup/pre-raid-$TS.sql.gz

tar czf - /etc /usr/local/mgr5/etc /root \
  | rclone rcat yadisk:backup/pre-raid-etc-$TS.tar.gz

# Проверка
rclone ls yadisk:backup/ | grep pre-raid
```

### 7.4 Регулярная проверка восстановления

- Раз в квартал: на тестовой VM/директории распаковать последний дамп и стартануть MySQL-инстанс, проверить наличие критичных таблиц.
- Раз в полгода: проверить восстановление borg-репозитория (`borg extract` в /tmp).

### 7.5 ISPmanager как второй канал

ISPmanager 6 Lite штатно умеет создавать бэкапы и заливать на S3/FTP. Настроить параллельно в панели управления как страховочный канал — на случай проблем с borg или rclone.

---

## 8. Мониторинг

### 8.1 Netdata

```bash
bash <(curl -Ss https://my-netdata.io/kickstart.sh) --stable-channel
```

После установки — слушает на 127.0.0.1:19999. Проксировать через nginx с basic-auth и IP-whitelist:

```nginx
server {
    listen 443 ssl;
    server_name monitor.lvtgroup.ru;
    # ... ssl ...
    allow <admin_ip>;
    deny all;
    auth_basic "Restricted";
    auth_basic_user_file /etc/nginx/.netdata-htpasswd;

    location / {
        proxy_pass http://127.0.0.1:19999;
        proxy_set_header Host $host;
        proxy_http_version 1.1;
        proxy_set_header Connection "";
    }
}
```

### 8.2 Алерты

Настроить через `health_alarm_notify.conf` Netdata:

| Метрика | Порог | Канал |
|---|---|---|
| `mdadm` degraded | любая деградация | email + Telegram |
| SMART `available_spare` < 30 % | warning | email |
| SMART `percentage_used` > 80 % | critical | email + Telegram |
| Disk free `/` | < 15 % | email |
| Swap used | > 1 GB | email |
| Load avg (5min) | > 12 | email |
| MySQL connections | > 250 (из 300) | email |
| Nginx 5xx rate | > 5/sec за 5 мин | email + Telegram |
| HTTPS cert expiry | < 14 дней | email |

### 8.3 Логи

- `logrotate` для bitrix-import: создать `/etc/logrotate.d/bitrix-import`:

```
/var/log/bitrix-import/*.log {
    daily
    rotate 14
    compress
    delaycompress
    missingok
    notifempty
    create 640 www-root www-root
}
```

- Slow query log MySQL — анализировать раз в неделю через `pt-query-digest /var/log/mysql/slow.log | less`.
- Лог импортов хранить в `/var/log/bitrix-import/<feed>.log`.

---

## 9. Критерии приёмки

| # | Критерий | Способ проверки |
|---|---|---|
| 1 | RAID 1 восстановлен | `cat /proc/mdstat` → `[UU]` на md0 и md2 |
| 2 | SMART обоих NVMe — без ошибок после resync | `smartctl -a /dev/nvme0n1`, `smartctl -a /dev/nvme1n1` |
| 3 | GRUB установлен на оба диска | `efibootmgr -v` показывает 2 записи Ubuntu |
| 4 | Битрикс — все тесты в зелёном | `https://lvtgroup.ru/bitrix/admin/site_checker.php` |
| 5 | TTFB кэшированной страницы каталога | curl-замер с прогретым кэшем ≤ 200 мс |
| 6 | Redis активно используется | `redis-cli -s /run/redis/redis-server.sock INFO keyspace` — растёт `keys` |
| 7 | InnoDB buffer pool не «голодает» | `Innodb_buffer_pool_pages_free` > 0 при нагрузочном тесте `wrk -t8 -c100 -d60s` |
| 8 | Swap не используется в боевом режиме | `free -m` показывает `Swap used` ≤ 100 MB |
| 9 | TLS только 1.2/1.3 | `testssl.sh https://lvtgroup.ru` оценка ≥ A |
| 10 | fail2ban активные jails | `fail2ban-client status` — не менее 8 jails |
| 11 | UFW активен с whitelist | `ufw status verbose` — 22/1500/1501 только с admin IP |
| 12 | Apache отключён | `systemctl is-active apache2` → `inactive` |
| 13 | Nginx обслуживает PHP через FPM | `ss -ulnp \| grep php-fpm` — пулы слушают на сокетах |
| 14 | Manticore работает | `mysql -h127.0.0.1 -P9306 -e 'SHOW TABLES'` |
| 15 | Поиск Битрикса использует Manticore | `/bitrix/admin/search_reindex.php` — индексация через Sphinx |
| 16 | Композит включён | `/bitrix/html_pages/` содержит закэшированные страницы |
| 17 | Проактивный фильтр Битрикса | в админке статус — Включен |
| 18 | Бэкап в Я.Диск | `rclone ls yadisk:backup/mysql/` показывает свежие dumps |
| 19 | Восстановление БД проверено | тестовое восстановление dump на staging — успешно |
| 20 | Netdata доступна и алерты работают | https://monitor.lvtgroup.ru с basic-auth, тестовый алерт пришёл |

---

## 10. Что не входит в ТЗ

- Изменение файловой структуры сайтов `/var/www/www-root/data/www/<site>/...` — по требованию заказчика.
- Программная доработка модулей и шаблонов Битрикса.
- Миграция на другую CMS или инфраструктуру.
- Замена «железа» (consumer-плата ASUS B550M-A → серверная с IPMI/ECC) — отдельный кейс на следующий апгрейд.
- Настройка внешнего CDN / WAF (Cloudflare, Qrator) — даётся только рекомендация.
- Перенос/настройка почтового сервера — exim4/dovecot оставляем как есть.
- Настройка LVM поверх mdadm (требует переразметки/простоя).
- Промышленное нагрузочное тестирование (jMeter-сценарии на полную каталог-логику с корзиной/оформлением) — отдельный кейс.

---

## Приложение А. Контакты и ответственные

| Роль | Ответственный | Контакт |
|---|---|---|
| Заказчик | _заполнить_ | _заполнить_ |
| Системный администратор | _заполнить_ | _заполнить_ |
| Доступ к Яндекс.Диску | _заполнить_ | _заполнить_ |
| Доступ к ISPmanager | _заполнить_ | _заполнить_ |
| Доступ к админке Битрикса | _заполнить_ | _заполнить_ |

## Приложение Б. Окружение работ

- Все операции выполняются по SSH под root или sudo-пользователем с правом эскалации.
- Перед любой работой, помеченной P0/P1, обязательно подтверждается наличие свежего бэкапа в Я.Диске.
- После каждой серии работ — короткий smoke-test: главная, страница категории, страница товара, корзина, оформление заказа (тестовый), `bitrix_server_test.php`.

## Приложение В. Журнал изменений документа

| Версия | Дата | Изменения |
|---|---|---|
| 1.0 | 27.05.2026 | Первая редакция по результатам аудита сервера lvt.market |
