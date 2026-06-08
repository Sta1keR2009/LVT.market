#!/bin/bash
# Повторный enrich IB41: цикл батчей без --skip-enriched.
#   sudo -u www-root bash api_etm_ai/cron/enrich_chars_run_batches.sh
#   sudo -u www-root bash api_etm_ai/cron/enrich_chars_run_batches.sh --batches=37 --max=3600

set -euo pipefail

DOCROOT="/var/www/www-root/data/www/lvtgroup.ru"
BATCHES=37
MAX=3600
RESET=0

for arg in "$@"; do
  case "$arg" in
    --batches=*) BATCHES="${arg#*=}" ;;
    --max=*) MAX="${arg#*=}" ;;
    --reset) RESET=1 ;;
  esac
done

LOG="$DOCROOT/api_etm_ai/logs/re_enrich_$(date +%Y-%m-%d_%H-%M-%S).log"
PHP="php"
if command -v sudo >/dev/null 2>&1; then
  RUN="sudo -u www-root $PHP"
else
  RUN="$PHP"
fi

cd "$DOCROOT"

{
  echo "[re_enrich] start $(date -Is) batches=$BATCHES max=$MAX reset=$RESET"
  if [[ "$RESET" == "1" ]]; then
    $RUN api_etm_ai/cron/enrich_chars.php --reset --max="$MAX"
  fi
  for ((i = 1; i <= BATCHES; i++)); do
    echo "[re_enrich] batch $i/$BATCHES $(date -Is)"
    $RUN api_etm_ai/cron/enrich_chars.php --max="$MAX"
    echo "[re_enrich] batch $i done $(date -Is)"
    if [[ -f api_etm_ai/logs/enrich_chars_state.json ]]; then
      cat api_etm_ai/logs/enrich_chars_state.json
    fi
  done
  echo "[re_enrich] finished $(date -Is)"
} >> "$LOG" 2>&1

echo "$LOG"
