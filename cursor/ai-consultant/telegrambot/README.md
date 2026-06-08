# Telegram-бот lvt.market (AI Consultant)

Отдельный сервис: меню, сценарии и заявки в Telegram. Диалог с ИИ — через тот же оркестратор [`../`](../) (`POST /v1/chat`, `channel: "telegram"`).

## Быстрый старт

```bash
cd telegrambot
cp .env.example .env
# TELEGRAM_BOT_TOKEN, AI_BACKEND_URL=http://127.0.0.1:3847
npm install
npm run build
npm start
```

При старте бот вызывает `deleteWebhook` (long polling). В `.env` оркестратора держите `ENABLE_TELEGRAM_WEBHOOK=0`.

## Smoke-test (шаг A)

1. Оркестратор запущен: `curl http://127.0.0.1:3847/health`
2. `./scripts/smoke-test-chat.sh` — проверка `POST /v1/chat` с `channel=telegram`
3. Опционально webhook оркестратора: nginx [`../deploy/nginx-ai-consultant.conf`](../deploy/nginx-ai-consultant.conf), `ENABLE_TELEGRAM_WEBHOOK=1`, `TELEGRAM_WEBHOOK_URL=https://lvt.market/ai-consultant/v1/channels/telegram/webhook`, `./scripts/set-webhook.sh`

## Меню

- Остатки и цена
- Доставка (город + ссылка/product_id)
- Тех. характеристики
- Оператор (эскалация через оркестратор)
- Заказать звонок (форма → `ai_consultant_lead.php`)
- Свободный вопрос

## systemd

```bash
sudo cp ../deploy/lvt-ai-telegrambot.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now lvt-ai-telegrambot
```

## Переменные

| Переменная | Описание |
|------------|----------|
| `TELEGRAM_BOT_TOKEN` | Токен @BotFather |
| `AI_BACKEND_URL` | Оркестратор (127.0.0.1:3847) |
| `LEAD_API_URL` | PHP заявки (по умолчанию lvt.market) |
| `TELEGRAM_PROXY_URL` | Прокси для `api.telegram.org`, напр. `socks5://user:pass@host:64505` |
| `YANDEX_WEBMASTER_OAUTH_TOKEN` | OAuth Яндекс Вебмастер |
| `YANDEX_WEBMASTER_DOMAINS` | Домены через запятую |

Заявки уходят в чат менеджеров через `TELEGRAM_LEAD_*` в php-fpm (см. основной README).  
Для PHP заявок задайте тот же `TELEGRAM_PROXY_URL` в окружении **php-fpm**.

## Диалог менеджера в боте

- `TELEGRAM_MANAGERS_CHAT_ID` — группа менеджеров (из ссылки `t.me/c/3685309637` → `-1003685309637`)
- `TELEGRAM_MANAGER_USER_IDS` — Telegram user id менеджеров (через запятую)

Клиент нажимает **«Оператор»** → сообщения идут в группу. Менеджер отвечает **reply** на сообщение клиента → ответ уходит в бот.  
Закрыть диалог: `/close <chat_id>` или reply `/close` в группе. Клиент может вернуть ИИ: `/menu` или `/ai`.

## Яндекс Вебмастер (менеджеры)

В группе менеджеров или в личном чате с ботом (если ваш `user_id` в `TELEGRAM_MANAGER_USER_IDS`):

- команда `/webmaster` — сводка: ИКС, страницы в поиске, проблемы по сайтам;
- в личном чате у менеджеров в меню есть кнопка **«📊 Вебмастер»**.

Переменные: `YANDEX_WEBMASTER_OAUTH_TOKEN`, `YANDEX_WEBMASTER_DOMAINS` (см. [site-monitor](../../site-monitor/README.md)).
