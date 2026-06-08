# LVT AI Consultant

Оркестратор для виджета **lvt.market**: chat completions + tool-calling (через OpenAI SDK), доставка через `catapulto_delivery_quote`, эскалация менеджеру, аналитика.

## Провайдер LLM (`.env`)

Переменная **`LLM_PROVIDER`**: `openai` (по умолчанию) | `deepseek` | `qwen`.

- **OpenAI**: `OPENAI_API_KEY`, опционально `OPENAI_BASE_URL`, `OPENAI_MODEL` (по умолчанию `gpt-4o-mini`).
- **DeepSeek**: `DEEPSEEK_API_KEY` или общий `OPENAI_API_KEY`; опционально `DEEPSEEK_BASE_URL` (по умолчанию `https://api.deepseek.com`), `DEEPSEEK_MODEL` (по умолчанию `deepseek-chat`).
- **Qwen (DashScope, OpenAI-compatible)**: `QWEN_API_KEY` или `DASHSCOPE_API_KEY` или `OPENAI_API_KEY`; опционально `QWEN_BASE_URL` (по умолчанию международный compatible-mode), `QWEN_MODEL` (по умолчанию `qwen-plus`).

Перезапуск сервиса после смены провайдера. `GET /health` возвращает `llm_provider` и `llm_model_hint` (без секретов).

## Промпт и заявки

Файл по умолчанию: [`prompts/agent_system.ru.md`](prompts/agent_system.ru.md) (редактируйте инструкции агента здесь). Переменные `AGENT_SYSTEM_PROMPT_PATH`, `AGENT_PROMPT_HOT_RELOAD` — см. [`.env.example`](.env.example).

Заявки с сайта в Telegram (@lvtmarket_bot) — эндпоинт [`/local/api/ai_consultant_lead.php`](/var/www/www-root/data/www/lvtgroup.ru/local/api/ai_consultant_lead.php):

1. **php-fpm** (pool `www`, сайт идёт через Apache:8081): `TELEGRAM_LEAD_BOT_TOKEN`, `TELEGRAM_LEAD_CHAT_ID`, `TELEGRAM_PROXY_URL` — см. [`deploy/php-fpm-telegram-lead.snippet`](deploy/php-fpm-telegram-lead.snippet).
2. **Fallback:** [`local/php_interface/telegram_lead_config.php`](/var/www/www-root/data/www/lvtgroup.ru/local/php_interface/telegram_lead_config.php) (если env в fpm не заданы).

Проверка: `curl -sS -X POST https://lvt.market/local/api/ai_consultant_lead.php -H 'Content-Type: application/json' -d '{"name":"Test","phone":"+79001234567","consent":true}'` → `{"ok":true}`.

## Быстрый старт

```bash
cp .env.example .env
# задайте LLM_PROVIDER и соответствующий API key
npm install
npm run build
node dist/index.js
```

Настройте прокси на сайте: см. [docs/lvt.market-ai-widget-integration.md](../docs/lvt.market-ai-widget-integration.md).

## Telegram

| Режим | Описание |
|-------|----------|
| **Smoke-test (A)** | `ENABLE_TELEGRAM_WEBHOOK=1` + nginx [`deploy/nginx-ai-consultant.conf`](deploy/nginx-ai-consultant.conf) → `POST /v1/channels/telegram/webhook` |
| **Прод (B)** | Сервис [`telegrambot/`](telegrambot/) (long polling, меню, заявки) → `POST /v1/chat` с `channel: "telegram"`. В оркестраторе `ENABLE_TELEGRAM_WEBHOOK=0` |

См. [`telegrambot/README.md`](telegrambot/README.md).

### Прокси для Telegram (РФ)

В `.env` оркестратора и `telegrambot/.env`:

```env
TELEGRAM_PROXY_URL=http://USER:PASS@HOST:64504
```

Для заявок с сайта (`ai_consultant_lead.php`) — тот же `TELEGRAM_PROXY_URL` в **php-fpm** (см. [`deploy/php-fpm-telegram-proxy.snippet`](deploy/php-fpm-telegram-proxy.snippet)). SOCKS5 поддерживается в PHP; для Node используйте **http**-прокси.

## API

- `POST /v1/chat` — тело `{ message, session_id?, channel?, page_context?: { product_id, city } }`
- `GET /health` — `ok`, `llm_provider`, `llm_model_hint`
- `GET /v1/analytics/summary` — заголовок `X-Internal-Key` если задан `INTERNAL_ANALYTICS_KEY`
- `POST /v1/channels/telegram/webhook` — только при `ENABLE_TELEGRAM_WEBHOOK=1`
