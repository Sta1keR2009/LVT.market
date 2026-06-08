# Каналы MVP: виджет сайта + мессенджеры

Единый диалоговый бэкенд — сервис **AI Consultant** (`ai-consultant/`). Все каналы обращаются к одному API `POST /v1/chat`.

## Каналы

| Канал | Назначение | Интеграция |
|-------|------------|------------|
| **Виджет сайта** | Плавающая кнопка + панель чата на страницах каталога, карточки, корзины, оформления | Подключить `/public/widget.js` и `/public/widget.css` из сервиса; скрипт вызывает `POST /v1/chat` с `channel: "widget"` |
| **Telegram** | Бот для клиентов | Прод: сервис [`ai-consultant/telegrambot/`](../ai-consultant/telegrambot/) (long polling + меню) → `POST /v1/chat`. Smoke-test: webhook `POST /v1/channels/telegram/webhook` при `ENABLE_TELEGRAM_WEBHOOK=1` |
| **WhatsApp** | Клиенты в Meta | Webhook `POST /v1/channels/whatsapp/webhook` (Meta Cloud API; верификация через `GET` и `hub.verify_token`) |

## Идентификация сессии

- **Виджет**: в localStorage хранится `lvt_ai_session_id` (UUID); передаётся в заголовке `X-Session-Id` или в теле `session_id`.
- **Telegram**: `session_id` = `telegram:<chat_id>`.
- **WhatsApp**: `session_id` = `whatsapp:<wa_id>`.

История диалога привязана к `session_id` в памяти (MVP) или внешнем хранилище (Redis) при масштабировании.

## Переменные окружения

См. `ai-consultant/.env.example`: `PUBLIC_ORIGIN`, `BITRIX_BASE_URL`, ключи OpenAI, токены Telegram/WhatsApp, URL для эскалации менеджеру.

Развёртывание на боевом сайте **lvt.market**: [lvt.market-ai-widget-integration.md](lvt.market-ai-widget-integration.md).

## Безопасность

- CORS для виджета: задать `PUBLIC_ORIGIN` (домен сайта).
- Секретный заголовок `X-Internal-Key` для админ-эндпоинтов аналитики (опционально).
