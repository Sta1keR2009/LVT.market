# Мониторинг сайта LVT (логи + сервер → Telegram)

Скрипт `lvt_site_monitor.py` раз в сутки (или по запросу) собирает краткий отчёт и отправляет его в Telegram.

## Что проверяется

### Логи сайта (по умолчанию за 24 ч)

- `/var/www/httpd-logs/*.error.log` — lvt.market, lvtgroup.ru, lvtec.ru, lvt-ec.ru
- `/var/www/httpd-logs/*.access.log` — счётчик HTTP 5xx
- `/var/log/nginx/error.log`
- `/var/log/php-fpm/lvtgroup-slow.log` — медленные запросы PHP
- `/var/log/php8.3-fpm.log`
- Лог AI-консультанта и `/var/log/bitrix-import/*.log` (если есть)

**Фильтр шума:** типовые 404 композитного кэша (`html_pages/...index@.html`), боты, пробы localhost — не засоряют отчёт.

**Важные события:** PHP Fatal, MySQL gone away, исчерпание памяти, 502/504, сканирование `/bitrix/backup.*`, прочие значимые nginx/FastCGI ошибки.

### Состояние сервера

- RAID (`/proc/mdstat`)
- Диск `/`
- RAM / swap
- Load average
- Сервисы: nginx, mysql, php8.3-fpm, lvt-ai-consultant, lvt-ai-telegrambot
- MySQL ping и число подключений
- Срок действия SSL для основных доменов
- HTTP-проверки главных страниц и `http://127.0.0.1:3847/health`
- Ошибки в journalctl за период

### Яндекс Вебмастер (если задан `YANDEX_WEBMASTER_OAUTH_TOKEN`)

- ИКС (индекс качества сайта)
- Число страниц в поиске и исключённых
- Проблемы по категориям: фатальные, критичные, возможные, рекомендации

Данные попадают в ежедневный отчёт в Telegram. Фатальные и критичные проблемы повышают уровень алерта.

## Установка

```bash
cd /var/www/www-root/data/www/lvtgroup.ru/cursor/site-monitor
sudo ./install.sh
sudo nano /etc/lvt/site-monitor.env   # TELEGRAM_BOT_TOKEN, TELEGRAM_CHAT_ID
```

Рекомендуется **отдельный бот** только для алертов (через [@BotFather](https://t.me/BotFather)), chat_id — ваш личный или группа админов.

Если Telegram доступен только через прокси (как на этом сервере), добавьте в env:

```
TELEGRAM_PROXY_URL=http://user:pass@host:port
```

## Запуск

```bash
# Просмотр отчёта без отправки
sudo bash -c 'set -a; source /etc/lvt/site-monitor.env; set +a; /usr/local/sbin/lvt-site-monitor --dry-run'

# Отправка в Telegram
sudo bash -c 'set -a; source /etc/lvt/site-monitor.env; set +a; /usr/local/sbin/lvt-site-monitor'
```

Cron после `install.sh`:

| Расписание | Режим | Лог |
|------------|-------|-----|
| **каждые 15 мин** | `--alert` (только критичное) | `/var/log/lvt-site-monitor-alert.log` |
| **08:05 ежедневно** | полный отчёт | `/var/log/lvt-site-monitor.log` |

### Мгновенные алерты (`--alert`)

Отдельные короткие сообщения в Telegram, без дублирования:

- Падение сервиса, RAID, диск ≥ критического порога, MySQL недоступен, HTTP 5xx на health-URL, просроченный SSL
- В логах за последний час: PHP Fatal, MySQL gone away, OOM, 502, segfault (порог `ALERT_MIN_COUNT`, по умолчанию 3)
- Повтор того же счётчика — только при росте на `ALERT_COUNT_DELTA` (5) или после `ALERT_COOLDOWN_MINUTES` (60)
- При восстановлении — строка «✅ …» в том же сообщении

```bash
# Просмотр без отправки
sudo bash -c 'set -a; source /etc/lvt/site-monitor.env; set +a; /usr/local/sbin/lvt-site-monitor --alert --dry-run'

# Сброс дедупликации (после тестов)
sudo /usr/local/sbin/lvt-site-monitor --reset-alert-state
```

Дополнительно можно вызывать полный отчёт после импортов или деплоя вручную.

## Параметры

| Переменная | По умолчанию | Описание |
|------------|--------------|----------|
| `LOOKBACK_HOURS` | 24 | Окно анализа логов |
| `DISK_WARN_PCT` / `DISK_CRIT_PCT` | 85 / 92 | Занятость диска |
| `SWAP_WARN_MB` | 1024 | Порог swap |
| `LOAD_WARN` | 12 | Load (5 min) |
| `MYSQL_CONN_WARN` | 250 | Подключения MySQL |
| `SSL_WARN_DAYS` | 14 | Скоро истекает сертификат |
| `SLOW_PHP_WARN` | 50 | Медленных PHP за период |
| `YANDEX_WEBMASTER_OAUTH_TOKEN` | — | OAuth для API Вебмастера |
| `YANDEX_WEBMASTER_DOMAINS` | lvt.market,lvtgroup.ru | Домены в отчёте |

| `ALERT_LOOKBACK_HOURS` | 1 | Окно для мгновенных алертов |
| `ALERT_COOLDOWN_MINUTES` | 60 | Пауза перед повтором того же алерта |
| `ALERT_COUNT_DELTA` | 5 | Повтор при росте счётчика (PHP Fatal и т.п.) |
| `ALERT_MIN_COUNT` | 3 | Минимум событий для первого алерта по логам |

CLI: `--hours 12`, `--dry-run`, `--alert`, `--reset-alert-state`, `--webmaster-only`.

### OAuth-токен Яндекс Вебмастера

1. Зарегистрируйте приложение на [oauth.yandex.ru](https://oauth.yandex.ru/) (тип «Веб-сервисы» или «Другое»).
2. В правах укажите доступ к **Яндекс Вебмастер** (чтение).
3. Получите OAuth-токен пользователя, у которого в [webmaster.yandex.ru](https://webmaster.yandex.ru/) добавлены нужные сайты.
4. Пропишите в `/etc/lvt/site-monitor.env`:

   ```
   YANDEX_WEBMASTER_OAUTH_TOKEN=y0_AgAAAA...
   YANDEX_WEBMASTER_DOMAINS=lvt.market,lvtgroup.ru
   ```

Проверка без отправки в Telegram:

```bash
sudo bash -c 'set -a; source /etc/lvt/site-monitor.env; set +a; /usr/local/sbin/lvt-site-monitor --webmaster-only --dry-run'
```

## Безопасность

- Не коммитьте `/etc/lvt/site-monitor.env` и токены в git.
- Файл env на сервере: `chmod 640`, владелец root.
