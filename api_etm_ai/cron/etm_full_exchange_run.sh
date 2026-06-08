#!/bin/bash
# Полный обмен ETM для IB41: enrich → цены → остатки.
#
#   sudo -u www-root bash api_etm_ai/cron/etm_full_exchange_run.sh
#   sudo -u www-root bash api_etm_ai/cron/etm_full_exchange_run.sh --no-reset-enrich
#
# Лог: api_etm_ai/logs/full_exchange_YYYY-MM-DD_HH-MM-SS.log
# PID: api_etm_ai/logs/full_exchange.pid

set -euo pipefail

DOCROOT="/var/www/www-root/data/www/lvtgroup.ru"
BATCHES=37
MAX=3600
REMAINS_CYCLES=74
RESET_ENRICH=1

for arg in "$@"; do
  case "$arg" in
    --batches=*) BATCHES="${arg#*=}" ;;
    --max=*) MAX="${arg#*=}" ;;
    --remains-cycles=*) REMAINS_CYCLES="${arg#*=}" ;;
    --no-reset-enrich) RESET_ENRICH=0 ;;
  esac
done

LOG="$DOCROOT/api_etm_ai/logs/full_exchange_$(date +%Y-%m-%d_%H-%M-%S).log"
PIDFILE="$DOCROOT/api_etm_ai/logs/full_exchange.pid"
PHP="php"
RUN="$PHP"

cd "$DOCROOT"

if [[ -f "$PIDFILE" ]]; then
  old_pid="$(cat "$PIDFILE" 2>/dev/null || true)"
  if [[ -n "$old_pid" ]] && kill -0 "$old_pid" 2>/dev/null; then
    echo "Уже выполняется full exchange, PID=$old_pid"
    exit 1
  fi
fi

echo $$ > "$PIDFILE"
trap 'rm -f "$PIDFILE"' EXIT

notify_tg() {
  $RUN api_etm_ai/cron/etm_exchange_notify_telegram.php "$@" 2>/dev/null || true
}

{
  echo "[full_exchange] start $(date -Is) batches=$BATCHES max=$MAX remains_cycles=$REMAINS_CYCLES reset_enrich=$RESET_ENRICH"
  echo "[full_exchange] log=$LOG"
  notify_tg --text="Старт полного обмена ETM
Этап 1/3: enrich ($BATCHES батчей × $MAX SKU)
Остатки: $REMAINS_CYCLES циклов
Лог: $(basename "$LOG")"

  echo "[full_exchange] === 1/3 ENRICH (без --skip-enriched) ==="
  if [[ "$RESET_ENRICH" == "1" ]]; then
    $RUN api_etm_ai/cron/enrich_chars.php --reset --max="$MAX"
  fi
  for ((i = 1; i <= BATCHES; i++)); do
    echo "[full_exchange] enrich batch $i/$BATCHES $(date -Is)"
    notify_tg
    $RUN api_etm_ai/cron/enrich_chars.php --max="$MAX"
    if [[ -f api_etm_ai/logs/enrich_chars_state.json ]]; then
      cat api_etm_ai/logs/enrich_chars_state.json
    fi
  done

  echo "[full_exchange] === 2/3 PRICES ==="
  notify_tg --text="ETM обмен: этап 2/3 — обновление цен"
  $RUN api_etm_ai/cron/update_prices_ib40.php

  echo "[full_exchange] === 3/3 REMAINS ($REMAINS_CYCLES циклов по 1800 SKU) ==="
  notify_tg --text="ETM обмен: этап 3/3 — остатки ($REMAINS_CYCLES циклов)"
  printf '{"offset":0,"total":0,"last_run":"%s","last_range":"reset"}\n' "$(date '+%Y-%m-%d %H:%M:%S')" \
    > api_etm_ai/logs/remains_offset_ib40.json
  for ((i = 1; i <= REMAINS_CYCLES; i++)); do
    echo "[full_exchange] remains cycle $i/$REMAINS_CYCLES $(date -Is)"
    if (( i == 1 || i % 10 == 0 || i == REMAINS_CYCLES )); then
      notify_tg
    fi
    $RUN api_etm_ai/cron/update_remains_ib40.php
  done

  echo "[full_exchange] finished $(date -Is)"
  notify_tg --text="✅ Полный обмен ETM завершён ($(date '+%Y-%m-%d %H:%M:%S'))"
} >> "$LOG" 2>&1

echo "$LOG"
