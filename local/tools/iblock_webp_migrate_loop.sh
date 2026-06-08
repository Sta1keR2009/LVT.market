#!/bin/bash
# Непрерывная миграция iblock → WebP. Запуск:
#   sudo -u www-root nohup bash local/tools/iblock_webp_migrate_loop.sh >> upload/_ORIGINALIMG/migrate_loop.log 2>&1 &
set -uo pipefail

DOCROOT="/var/www/www-root/data/www/lvtgroup.ru"
PHP="php"
SCRIPT="${DOCROOT}/local/tools/iblock_webp_migrate.php"
STATE="${DOCROOT}/upload/_ORIGINALIMG/migrate_last_id.txt"
LOG="${DOCROOT}/upload/_ORIGINALIMG/migrate_loop.log"
PIDFILE="${DOCROOT}/upload/_ORIGINALIMG/migrate_loop.pid"
NOTIFY_SCRIPT="${DOCROOT}/local/tools/iblock_webp_migrate_notify_telegram.php"
NOTIFY_STAMP="${DOCROOT}/upload/_ORIGINALIMG/migrate_notify_last.txt"
LIMIT="${LIMIT:-500}"
# Интервал уведомлений в Telegram (секунды), по умолчанию 30 мин
NOTIFY_EVERY_SEC="${NOTIFY_EVERY_SEC:-14400}"

cd "$DOCROOT"

notify_tg() {
  $PHP "$NOTIFY_SCRIPT" "$@" 2>/dev/null || true
}

should_notify() {
  local now last
  now="$(date +%s)"
  if [[ ! -f "$NOTIFY_STAMP" ]]; then
    echo "$now" > "$NOTIFY_STAMP"
    return 0
  fi
  last="$(cat "$NOTIFY_STAMP" 2>/dev/null || echo 0)"
  if [[ $((now - last)) -ge $NOTIFY_EVERY_SEC ]]; then
    echo "$now" > "$NOTIFY_STAMP"
    return 0
  fi
  return 1
}

if [[ -f "$PIDFILE" ]]; then
  OLD_PID="$(cat "$PIDFILE" 2>/dev/null || true)"
  if [[ -n "$OLD_PID" ]] && kill -0 "$OLD_PID" 2>/dev/null; then
    echo "$(date -Is) already running pid=${OLD_PID}" >> "$LOG"
    exit 0
  fi
fi
echo $$ > "$PIDFILE"

if [[ -f "$STATE" ]]; then
  LAST_ID="$(tr -d '[:space:]' < "$STATE")"
else
  LAST_ID=0
fi

echo "$(date -Is) start last-id=${LAST_ID} limit=${LIMIT}" >> "$LOG"
notify_tg --text="Старт миграции iblock → WebP
Курсор: ${LAST_ID}
Батч: ${LIMIT} файлов
Интервал отчётов: каждые $((NOTIFY_EVERY_SEC / 60)) мин"

cleanup() {
  rm -f "$PIDFILE"
}
trap cleanup EXIT

while true; do
  OUT="$($PHP "$SCRIPT" migrate --limit="$LIMIT" --last-id="$LAST_ID" 2>&1)" || true
  echo "$OUT" >> "$LOG"

  if echo "$OUT" | grep -qE 'RedisException|MISCONF|Fatal error|Uncaught Error|При выполнении скрипта'; then
    echo "$(date -Is) error at last-id=${LAST_ID}, retry in 60s" >> "$LOG"
    sleep 60
    continue
  fi

  NEW_ID="$(echo "$OUT" | grep -oP 'last-id=\K[0-9]+' | tail -1)"
  OK="$(echo "$OUT" | grep -oP 'ok=\K[0-9]+' | tail -1)"
  SKIP="$(echo "$OUT" | grep -oP 'skip=\K[0-9]+' | tail -1)"

  if [[ -z "$NEW_ID" ]]; then
    echo "$(date -Is) no last-id in output, retry in 30s (cursor=${LAST_ID})" >> "$LOG"
    sleep 30
    continue
  fi

  if [[ "$NEW_ID" == "$LAST_ID" ]]; then
    echo "$(date -Is) done last-id=${LAST_ID} ok=${OK:-0} skip=${SKIP:-0}" >> "$LOG"
    break
  fi

  echo "$NEW_ID" > "$STATE"
  LAST_ID="$NEW_ID"
  echo "$(date -Is) batch ok=${OK:-0} skip=${SKIP:-0} last-id=${LAST_ID}" >> "$LOG"

  if should_notify; then
    notify_tg
  fi

  sleep 1
done

echo "$(date -Is) finished last-id=${LAST_ID}" >> "$LOG"
notify_tg --text="Миграция iblock → WebP завершена
Курсор: ${LAST_ID}"
