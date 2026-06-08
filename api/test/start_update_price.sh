#!/bin/bash
# Запуск обновления остатков и цен через тестовое API (api_url_test).
# Режимы: all — весь каталог, item — один товар (item_id/code), limit N — не более N товаров.
# Пример фонового запуска: ./start_update_price.sh all &

cd "$(dirname "$0")"
SCRIPT_DIR="$(pwd)"
API_ROOT="$(dirname "$SCRIPT_DIR")"
MODE="${1:-all}"
shift
EXTRA_ARGS=("$@")

if [ "$EUID" -eq 0 ]; then
    exec sudo -u www-root "$0" "$MODE" "${EXTRA_ARGS[@]}"
    exit $?
fi

LOG="$SCRIPT_DIR/update_price_test.log"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Start test update_price mode=$MODE ${EXTRA_ARGS[*]}" >> "$LOG"
php "$SCRIPT_DIR/update_price.php" "$MODE" "${EXTRA_ARGS[@]}" >> "$LOG" 2>&1
EXIT=$?
echo "[$(date '+%Y-%m-%d %H:%M:%S')] End test update_price exit=$EXIT" >> "$LOG"
exit $EXIT
