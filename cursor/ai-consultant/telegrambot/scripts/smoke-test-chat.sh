#!/usr/bin/env bash
# Smoke-test: оркестратор отвечает с channel=telegram
set -euo pipefail
BACKEND="${AI_BACKEND_URL:-http://127.0.0.1:3847}"
SESSION="telegram:smoke-test-$(date +%s)"

echo "GET $BACKEND/health"
curl -sf "$BACKEND/health" | head -c 200
echo ""

echo "POST $BACKEND/v1/chat (channel=telegram)"
curl -sf -X POST "$BACKEND/v1/chat" \
  -H "Content-Type: application/json" \
  -d "{\"message\":\"Привет, это smoke-test Telegram\",\"session_id\":\"$SESSION\",\"channel\":\"telegram\"}" \
  | head -c 500
echo ""
