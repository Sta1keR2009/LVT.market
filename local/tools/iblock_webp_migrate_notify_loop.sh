#!/bin/bash
# Периодические отчёты в Telegram (работает параллельно с migrate_loop.sh).
#   NOTIFY_EVERY_SEC=1800 sudo -u www-root nohup bash local/tools/iblock_webp_migrate_notify_loop.sh >> upload/_ORIGINALIMG/migrate_notify.log 2>&1 &
set -uo pipefail

DOCROOT="/var/www/www-root/data/www/lvtgroup.ru"
PHP="php"
NOTIFY_SCRIPT="${DOCROOT}/local/tools/iblock_webp_migrate_notify_telegram.php"
PIDFILE="${DOCROOT}/upload/_ORIGINALIMG/migrate_notify_loop.pid"
NOTIFY_EVERY_SEC="${NOTIFY_EVERY_SEC:-14400}"

cd "$DOCROOT"

if [[ -f "$PIDFILE" ]]; then
  OLD="$(cat "$PIDFILE" 2>/dev/null || true)"
  if [[ -n "$OLD" ]] && kill -0 "$OLD" 2>/dev/null; then
    echo "$(date -Is) notify loop already running pid=${OLD}"
    exit 0
  fi
fi
echo $$ > "$PIDFILE"
trap 'rm -f "$PIDFILE"' EXIT

echo "$(date -Is) notify loop start interval=${NOTIFY_EVERY_SEC}s"

while true; do
  $PHP "$NOTIFY_SCRIPT" 2>&1 || echo "$(date -Is) notify failed"
  sleep "$NOTIFY_EVERY_SEC"
done
