#!/bin/bash
# Установка ежедневного мониторинга на сервер lvt.market
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
INSTALL_LIB="/usr/local/lib/lvt-site-monitor"
INSTALL_BIN="/usr/local/sbin/lvt-site-monitor"
ENV_DIR="/etc/lvt"
ENV_FILE="${ENV_DIR}/site-monitor.env"
CRON_FILE="/etc/cron.d/lvt-site-monitor"

if [[ "$(id -u)" -ne 0 ]]; then
  echo "Запустите от root: sudo $0"
  exit 1
fi

install -d -m 0750 "${ENV_DIR}"
install -d -m 0755 "${INSTALL_LIB}"
install -m 0644 "${SCRIPT_DIR}/yandex_webmaster.py" "${INSTALL_LIB}/"
install -m 0755 "${SCRIPT_DIR}/lvt_site_monitor.py" "${INSTALL_LIB}/"
cat > "${INSTALL_BIN}" <<EOF
#!/bin/bash
exec python3 "${INSTALL_LIB}/lvt_site_monitor.py" "\$@"
EOF
chmod 0755 "${INSTALL_BIN}"

LEAD_CFG="/var/www/www-root/data/www/lvtgroup.ru/local/php_interface/telegram_lead_config.php"

if [[ ! -f "${ENV_FILE}" ]]; then
  install -m 0640 "${SCRIPT_DIR}/config.env.example" "${ENV_FILE}"
  if [[ -f "${LEAD_CFG}" ]]; then
    token="$(grep -oP "'bot_token'\s*=>\s*'\K[^']+" "${LEAD_CFG}" 2>/dev/null | head -1 || true)"
    chat="$(grep -oP "'chat_id'\s*=>\s*'\K[^']+" "${LEAD_CFG}" 2>/dev/null | head -1 || true)"
    proxy="$(grep -oP "'proxy_url'\s*=>\s*'\K[^']+" "${LEAD_CFG}" 2>/dev/null | head -1 || true)"
    if [[ -n "${token}" && -n "${chat}" ]]; then
      {
        echo "TELEGRAM_BOT_TOKEN=${token}"
        echo "TELEGRAM_CHAT_ID=${chat}"
        [[ -n "${proxy}" ]] && echo "TELEGRAM_PROXY_URL=${proxy}"
      } >> "${ENV_FILE}"
      echo "Токен Telegram подставлен из telegram_lead_config.php"
    fi
  fi
  echo "Проверьте ${ENV_FILE} (при необходимости укажите отдельного бота для алертов)"
else
  echo "Сохранён существующий ${ENV_FILE}"
fi

install -d -m 0750 /var/lib/lvt-site-monitor

cat > "${CRON_FILE}" <<'EOF'
# LVT мониторинг → Telegram
SHELL=/bin/bash
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
# Мгновенные алерты (только критичное, дедупликация)
*/15 * * * * root flock -n /var/run/lvt-site-monitor-alert.lock -c 'set -a && [ -f /etc/lvt/site-monitor.env ] && . /etc/lvt/site-monitor.env && set +a && /usr/local/sbin/lvt-site-monitor --alert >>/var/log/lvt-site-monitor-alert.log 2>&1'
# Ежедневный полный отчёт (08:05 MSK)
5 8 * * * root flock -n /var/run/lvt-site-monitor-daily.lock -c 'set -a && [ -f /etc/lvt/site-monitor.env ] && . /etc/lvt/site-monitor.env && set +a && /usr/local/sbin/lvt-site-monitor >>/var/log/lvt-site-monitor.log 2>&1'
EOF
chmod 0644 "${CRON_FILE}"

touch /var/log/lvt-site-monitor.log /var/log/lvt-site-monitor-alert.log
chmod 0640 /var/log/lvt-site-monitor.log /var/log/lvt-site-monitor-alert.log

echo "Установлено: ${INSTALL_BIN}"
echo "Cron: алерты каждые 15 мин, полный отчёт 08:05 (см. ${CRON_FILE})"
echo "Тест алертов:  source ${ENV_FILE} && ${INSTALL_BIN} --alert --dry-run"
echo "Тест отчёта:   source ${ENV_FILE} && ${INSTALL_BIN} --dry-run"
