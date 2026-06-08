#!/usr/bin/env bash
# Smoke-test A: webhook встроенного обработчика оркестратора
set -euo pipefail
cd "$(dirname "$0")/../.."
if [[ -f .env ]]; then set -a; source .env; set +a; fi
TOKEN="${TELEGRAM_BOT_TOKEN:?TELEGRAM_BOT_TOKEN required}"
URL="${TELEGRAM_WEBHOOK_URL:?TELEGRAM_WEBHOOK_URL required, e.g. https://lvt.market/ai-consultant/v1/channels/telegram/webhook}"

echo "setWebhook -> $URL"
curl -sf "https://api.telegram.org/bot${TOKEN}/setWebhook" \
  -d "url=${URL}" \
  -d "allowed_updates=[\"message\"]"

echo ""
echo "getWebhookInfo:"
curl -sf "https://api.telegram.org/bot${TOKEN}/getWebhookInfo"
echo ""
