# ИИ-консультант на lvt.market

Домен **lvt.market** указывает на корень сайта [`/var/www/www-root/data/www/lvtgroup.ru`](/var/www/www-root/data/www/lvtgroup.ru) (см. nginx `lvt.market.conf`). Виджет подключён для всех страниц с общим подвалом Aspro Lite.

## Что уже сделано на сервере

| Компонент | Путь |
|-----------|------|
| Статика виджета | [`/local/ext/ai-chat/widget.js`](/var/www/www-root/data/www/lvtgroup.ru/local/ext/ai-chat/widget.js), [`widget.css`](/var/www/www-root/data/www/lvtgroup.ru/local/ext/ai-chat/widget.css) |
| PHP-прокси (без CORS с браузера) | [`/local/api/ai_chat_proxy.php`](/var/www/www-root/data/www/lvtgroup.ru/local/api/ai_chat_proxy.php) |
| Подключение в подвале | [`/include/footer/bottom_footer_custom.php`](/var/www/www-root/data/www/lvtgroup.ru/include/footer/bottom_footer_custom.php) (подключается из `bottom_footer.php`) |
| Расчёт доставки для tool агента | [`/local/api/catapulto_delivery_quote.php`](/var/www/www-root/data/www/lvtgroup.ru/local/api/catapulto_delivery_quote.php) |

Исходники сервиса и виджета в репозитории: [`cursor/ai-consultant/`](/var/www/www-root/data/www/lvtgroup.ru/cursor/ai-consultant/).

## Запуск Node-оркестратора

1. Создайте файл окружения (пример — [`ai-consultant/.env.example`](/var/www/www-root/data/www/lvtgroup.ru/cursor/ai-consultant/.env.example)):

   - `OPENAI_API_KEY` — обязательно  
   - `BITRIX_BASE_URL=https://lvt.market`  
   - `PUBLIC_ORIGIN=https://lvt.market,https://www.lvt.market`  
   - По желанию: `CATALOG_API_URL`, `ANALOGS_API_URL`, `HANDOFF_WEBHOOK_URL`, токены Telegram/WhatsApp  

2. Сборка и старт:

```bash
cd /var/www/www-root/data/www/lvtgroup.ru/cursor/ai-consultant
npm ci
npm run build
export $(grep -v '^#' .env | xargs)
node dist/index.js
```

3. Укажите PHP доступ к бэкенду (пул **php-fpm** или окружение веб-сервера):

```bash
# пример для pool www-root
LVT_AI_BACKEND=http://127.0.0.1:3847
```

Или временно задайте значение по умолчанию уже в [`ai_chat_proxy.php`](/var/www/www-root/data/www/lvtgroup.ru/local/api/ai_chat_proxy.php) (переменная `getenv('LVT_AI_BACKEND')`).

4. Рекомендуется **systemd** (юнит можно положить рядом с проектом и скопировать в `/etc/systemd/system/`):

[`cursor/ai-consultant/deploy/lvt-ai-consultant.service`](/var/www/www-root/data/www/lvtgroup.ru/cursor/ai-consultant/deploy/lvt-ai-consultant.service)

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now lvt-ai-consultant
```

## Поведение виджета

- На карточке товара подхватывается `data-product-id` (в т.ч. из блока Catapulto), чтобы агент мог вызывать расчёт доставки без ручного ввода ID.  
- Сессия: `localStorage` ключ `lvt_ai_session_id`.
- История сообщений: `localStorage` ключ `lvt_ai_msgs_<session_id>` (до 50 сообщений).
- На карточке задаётся `window.LVT_AI_PAGE` (наличие, селектор кнопки «Купить») — для баннера «Добавить в корзину» в чате.
- Заявка менеджеру: [`/local/api/ai_consultant_lead.php`](/var/www/www-root/data/www/lvtgroup.ru/local/api/ai_consultant_lead.php) — `TELEGRAM_LEAD_BOT_TOKEN` (токен @lvtmarket_bot), `TELEGRAM_LEAD_CHAT_ID` (чат менеджера), `TELEGRAM_PROXY_URL` в **php-fpm** pool `www` или в [`local/php_interface/telegram_lead_config.php`](/var/www/www-root/data/www/lvtgroup.ru/local/php_interface/telegram_lead_config.php).

## Системный промпт агента

Файл по умолчанию: [`cursor/ai-consultant/prompts/agent_system.ru.md`](/var/www/www-root/data/www/lvtgroup.ru/cursor/ai-consultant/prompts/agent_system.ru.md). Путь переопределяется переменной `AGENT_SYSTEM_PROMPT_PATH` в `.env` оркестратора. После правок в продакшене перезапустите `node` (или включите `AGENT_PROMPT_HOT_RELOAD=1` для перечитывания по mtime).

## Расчёт доставки и Лыткарино

[`catapulto_delivery_quote.php`](/var/www/www-root/data/www/lvtgroup.ru/local/api/catapulto_delivery_quote.php) вызывает [`LvtProductInboundDays`](/var/www/www-root/data/www/lvtgroup.ru/local/php_interface/classes/LvtProductInboundDays.php) и передаёт сдвиг в Catapulto как `pickup_days_shift` (макс. оценка по складам с остатком из UF_SROK_DOST / строки срока).

## Безопасность

- Ограничьте частоту запросов к `/local/api/ai_chat_proxy.php` в nginx (`limit_req`).  
- Ключ OpenAI хранится только на сервере с Node, не в браузере.
