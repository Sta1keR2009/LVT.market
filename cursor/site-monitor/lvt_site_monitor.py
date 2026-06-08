#!/usr/bin/env python3
"""
Ежедневный мониторинг lvt.market / lvtgroup.ru:
логи сайта, критичные ошибки, состояние сервисов и краткий отчёт в Telegram.
"""
from __future__ import annotations

import argparse
import json
import os
import re
import socket
import ssl
import subprocess
import sys
import urllib.error
import urllib.request
from collections import Counter
from dataclasses import dataclass, field
from datetime import datetime, timedelta
from pathlib import Path
from typing import Iterable

# Модуль yandex_webmaster.py лежит рядом (см. install.sh)
sys.path.insert(0, str(Path(__file__).resolve().parent))

from yandex_webmaster import (
    HostWebmasterInfo,
    YandexWebmasterClient,
    YandexWebmasterError,
    format_webmaster_section,
)
from zoneinfo import ZoneInfo

TZ = ZoneInfo("Europe/Moscow")
HOSTNAME = socket.gethostname()

# --- Конфиг по умолчанию (переопределяется env) ---

DEFAULT_ENV = Path("/etc/lvt/site-monitor.env")
FALLBACK_ENV = Path(__file__).resolve().parent / ".env"

SITES = ("lvt.market", "lvtgroup.ru", "lvtec.ru", "lvt-ec.ru")
HTTPD_LOG_DIR = Path("/var/www/httpd-logs")
NGINX_ERR = Path("/var/log/nginx/error.log")
PHP_FPM_LOG = Path("/var/log/php8.3-fpm.log")
PHP_SLOW = Path("/var/log/php-fpm/lvtgroup-slow.log")
AI_CONSULTANT_LOG = Path(
    "/var/www/www-root/data/www/lvtgroup.ru/cursor/ai-consultant/logs/telegrambot.log"
)
BITRIX_IMPORT_DIR = Path("/var/log/bitrix-import")
SITE_ROOT = Path("/var/www/www-root/data/www/lvtgroup.ru")

SERVICES = (
    "nginx",
    "mysql",
    "php8.3-fpm",
    "lvt-ai-consultant",
    "lvt-ai-telegrambot",
)

# Шум в nginx error (композит, боты, локальные пробы)
NOISE_PATTERNS = (
    re.compile(r"html_pages/.*index@\.html", re.I),
    re.compile(r"openat\(\).*failed \(2: No such file", re.I),
    re.compile(r"is not found \(2: No such file", re.I),
    re.compile(r"/usr/share/nginx/html/", re.I),
    re.compile(r"stub_status|basic_status|nginx_status|/status HTTP", re.I),
    re.compile(r"buffered to a temporary file", re.I),
)

SIGNIFICANT_PATTERNS: list[tuple[re.Pattern[str], str]] = [
    (re.compile(r"PHP Fatal error", re.I), "php_fatal"),
    (re.compile(r"MySQL server has gone away", re.I), "mysql_gone_away"),
    (re.compile(r"memory size of .* exhausted", re.I), "memory_exhausted"),
    (re.compile(r"502 Bad Gateway|upstream prematurely closed", re.I), "upstream_502"),
    (re.compile(r"504 Gateway|upstream timed out", re.I), "upstream_504"),
    (re.compile(r"connect\(\) failed|Connection refused", re.I), "connect_failed"),
    (re.compile(r"access forbidden by rule", re.I), "forbidden_probe"),
    (re.compile(r"Primary script unknown", re.I), "script_unknown"),
    (re.compile(r"segfault|SIGSEGV", re.I), "segfault"),
]


@dataclass
class Thresholds:
    disk_warn_pct: float = 85.0
    disk_crit_pct: float = 92.0
    swap_warn_mb: int = 1024
    load_warn: float = 12.0
    mysql_conn_warn: int = 250
    ssl_warn_days: int = 14
    slow_php_warn: int = 50


@dataclass
class Config:
    telegram_token: str = ""
    telegram_chat_id: str = ""
    telegram_proxy_url: str = ""
    lookback_hours: int = 24
    thresholds: Thresholds = field(default_factory=Thresholds)
    health_urls: tuple[str, ...] = (
        "https://lvt.market/",
        "https://lvtgroup.ru/",
        "http://127.0.0.1:3847/health",
    )
    yandex_webmaster_token: str = ""
    yandex_webmaster_domains: tuple[str, ...] = ("lvt.market", "lvtgroup.ru")
    # Мгновенные алерты (--alert)
    alert_lookback_hours: int = 1
    alert_cooldown_minutes: int = 60
    alert_count_delta: int = 5
    alert_min_count: int = 3
    alert_state_path: Path = field(
        default_factory=lambda: Path("/var/lib/lvt-site-monitor/alert-state.json")
    )


@dataclass
class Finding:
    level: str  # ok | warn | crit
    category: str
    message: str
    count: int = 0

    def alert_key(self) -> str:
        """Стабильный ключ для дедупликации алертов."""
        if self.category == "service":
            m = re.search(r"^(\S+):", self.message)
            return f"service:{m.group(1) if m else self.message}"
        if self.category == "disk":
            m = re.search(r"^(/\S+):", self.message)
            return f"disk:{m.group(1) if m else 'root'}"
        if self.category == "http":
            return f"http:{self.message.split(':', 1)[0]}"
        if self.category == "ssl":
            return f"ssl:{self.message.split(':', 1)[0]}"
        if self.category == "logs":
            return f"logs:{self.message.split(':', 1)[0].strip()}"
        return f"{self.category}:{self.message[:80]}"


def load_env_file(path: Path) -> None:
    if not path.is_file():
        return
    for raw in path.read_text(encoding="utf-8", errors="replace").splitlines():
        line = raw.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, _, val = line.partition("=")
        key, val = key.strip(), val.strip().strip("'\"")
        if key and key not in os.environ:
            os.environ[key] = val


def load_config() -> Config:
    for p in (DEFAULT_ENV, FALLBACK_ENV):
        load_env_file(p)
    th = Thresholds(
        disk_warn_pct=float(os.getenv("DISK_WARN_PCT", "85")),
        disk_crit_pct=float(os.getenv("DISK_CRIT_PCT", "92")),
        swap_warn_mb=int(os.getenv("SWAP_WARN_MB", "1024")),
        load_warn=float(os.getenv("LOAD_WARN", "12")),
        mysql_conn_warn=int(os.getenv("MYSQL_CONN_WARN", "250")),
        ssl_warn_days=int(os.getenv("SSL_WARN_DAYS", "14")),
        slow_php_warn=int(os.getenv("SLOW_PHP_WARN", "50")),
    )
    urls = os.getenv("HEALTH_URLS", "")
    health = tuple(u.strip() for u in urls.split(",") if u.strip()) or (
        "https://lvt.market/",
        "https://lvtgroup.ru/",
        "http://127.0.0.1:3847/health",
    )
    wm_domains = os.getenv("YANDEX_WEBMASTER_DOMAINS", "")
    webmaster_domains = tuple(
        d.strip().lower()
        for d in wm_domains.split(",")
        if d.strip()
    ) or ("lvt.market", "lvtgroup.ru")
    return Config(
        telegram_token=os.getenv("TELEGRAM_BOT_TOKEN", "").strip(),
        telegram_chat_id=os.getenv("TELEGRAM_CHAT_ID", "").strip(),
        telegram_proxy_url=os.getenv("TELEGRAM_PROXY_URL", "").strip(),
        lookback_hours=int(os.getenv("LOOKBACK_HOURS", "24")),
        thresholds=th,
        health_urls=health,
        yandex_webmaster_token=os.getenv("YANDEX_WEBMASTER_OAUTH_TOKEN", "").strip(),
        yandex_webmaster_domains=webmaster_domains,
        alert_lookback_hours=int(os.getenv("ALERT_LOOKBACK_HOURS", "1")),
        alert_cooldown_minutes=int(os.getenv("ALERT_COOLDOWN_MINUTES", "60")),
        alert_count_delta=int(os.getenv("ALERT_COUNT_DELTA", "5")),
        alert_min_count=int(os.getenv("ALERT_MIN_COUNT", "3")),
        alert_state_path=Path(
            os.getenv(
                "ALERT_STATE_PATH",
                "/var/lib/lvt-site-monitor/alert-state.json",
            )
        ),
    )


def run(cmd: list[str], timeout: int = 30) -> tuple[int, str]:
    try:
        r = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            timeout=timeout,
            errors="replace",
        )
        out = (r.stdout or "") + (r.stderr or "")
        return r.returncode, out.strip()
    except (subprocess.TimeoutExpired, FileNotFoundError) as e:
        return 1, str(e)


def nginx_dates_in_window(hours: int) -> tuple[set[str], set[str]]:
    """
    Маркеры дат в логах nginx:
    - access: 03/Jun/2026
    - error:  2026/06/03
    - php slow: 03-Jun-2026
    """
    now = datetime.now(TZ)
    start = now - timedelta(hours=hours)
    access_dates: set[str] = set()
    error_dates: set[str] = set()
    slow_dates: set[str] = set()
    cur = start
    while cur.date() <= now.date():
        access_dates.add(cur.strftime("%d/%b/%Y"))
        error_dates.add(cur.strftime("%Y/%m/%d"))
        slow_dates.add(cur.strftime("%d-%b-%Y"))
        cur += timedelta(days=1)
    return access_dates, error_dates, slow_dates


def line_in_window(
    line: str,
    access_dates: set[str],
    error_dates: set[str],
    slow_dates: set[str] | None = None,
) -> bool:
    for d in access_dates:
        if d in line:
            return True
    for d in error_dates:
        if d in line:
            return True
    if slow_dates:
        for d in slow_dates:
            if d in line:
                return True
    return False


def is_noise(line: str) -> bool:
    return any(p.search(line) for p in NOISE_PATTERNS)


def classify_significant(line: str) -> str | None:
    for pat, label in SIGNIFICANT_PATTERNS:
        if pat.search(line):
            return label
    if "[error]" in line and not is_noise(line):
        if "FastCGI" in line or "crit" in line.lower():
            return "nginx_error"
    return None


def tail_lines(path: Path, max_lines: int = 200_000) -> Iterable[str]:
    if not path.is_file():
        return
    try:
        with path.open("r", encoding="utf-8", errors="replace") as f:
            buf: list[str] = []
            for line in f:
                buf.append(line)
                if len(buf) > max_lines:
                    buf.pop(0)
            yield from buf
    except OSError:
        return


def scan_error_log(
    path: Path,
    access_dates: set[str],
    error_dates: set[str],
) -> tuple[Counter[str], int, int]:
    sig: Counter[str] = Counter()
    total_err = 0
    noise = 0
    for line in tail_lines(path):
        if "[error]" not in line and "[crit]" not in line and "[alert]" not in line:
            continue
        if not line_in_window(line, access_dates, error_dates):
            continue
        total_err += 1
        if is_noise(line):
            noise += 1
            continue
        label = classify_significant(line)
        if label:
            sig[label] += 1
        elif "[error]" in line:
            sig["other_error"] += 1
    return sig, total_err, noise


def scan_access_5xx(
    path: Path, access_dates: set[str], error_dates: set[str]
) -> Counter[str]:
    codes: Counter[str] = Counter()
    for line in tail_lines(path, 100_000):
        if not line_in_window(line, access_dates, error_dates):
            continue
        m = re.search(r'" (\d{3}) ', line)
        if not m:
            continue
        code = m.group(1)
        if code.startswith("5"):
            codes[code] += 1
    return codes


def count_slow_php(
    path: Path,
    access_dates: set[str],
    error_dates: set[str],
    slow_dates: set[str],
) -> int:
    n = 0
    for line in tail_lines(path, 50_000):
        if not line.startswith("["):
            continue
        if not line_in_window(line, access_dates, error_dates, slow_dates):
            continue
        if "pid" in line or "executing too slow" in line:
            n += 1
    return n


def scan_php_fpm_log(
    path: Path, access_dates: set[str], error_dates: set[str]
) -> Counter[str]:
    c: Counter[str] = Counter()
    for line in tail_lines(path, 30_000):
        if not line_in_window(line, access_dates, error_dates):
            continue
        low = line.lower()
        if "fatal" in low or "segfault" in low or "error" in low:
            if "NOTICE" in line and "error" not in low:
                continue
            c["php_fpm_issue"] += 1
    return c


def scan_bitrix_import_logs(
    access_dates: set[str], error_dates: set[str]
) -> list[Finding]:
    findings: list[Finding] = []
    if not BITRIX_IMPORT_DIR.is_dir():
        return findings
    for log in sorted(BITRIX_IMPORT_DIR.glob("*.log"))[-10:]:
        err = 0
        for line in tail_lines(log, 5000):
            if not line_in_window(line, access_dates, error_dates):
                continue
            if re.search(r"\b(ERROR|FATAL|Exception|failed)\b", line, re.I):
                err += 1
        if err:
            findings.append(
                Finding("warn", "import", f"{log.name}: {err} ошибок за период", err)
            )
    return findings


def check_raid() -> Finding:
    try:
        text = Path("/proc/mdstat").read_text(encoding="utf-8")
    except OSError as e:
        return Finding("crit", "raid", f"Не читается /proc/mdstat: {e}")
    degraded = "_U" in text or "U_" in text or "[1/2]" in text
    if degraded:
        return Finding("crit", "raid", "RAID деградирован — проверьте mdadm")
    if "[UU]" not in text and "active" in text:
        return Finding("warn", "raid", "RAID: неясный статус, см. /proc/mdstat")
    return Finding("ok", "raid", "RAID: оба диска в строю [UU]")


def check_disk(th: Thresholds) -> list[Finding]:
    out: list[Finding] = []
    code, text = run(["df", "-P", "/"])
    if code != 0:
        out.append(Finding("warn", "disk", f"df failed: {text[:120]}"))
        return out
    for line in text.splitlines()[1:]:
        parts = line.split()
        if len(parts) < 6:
            continue
        pct = int(parts[4].rstrip("%"))
        mount = parts[5]
        if pct >= th.disk_crit_pct:
            out.append(Finding("crit", "disk", f"{mount}: {pct}% занято"))
        elif pct >= th.disk_warn_pct:
            out.append(Finding("warn", "disk", f"{mount}: {pct}% занято"))
        else:
            out.append(Finding("ok", "disk", f"{mount}: {pct}%"))
    return out


def parse_mem() -> tuple[int, int]:
    avail_mb = 0
    swap_used_mb = 0
    try:
        with Path("/proc/meminfo").open(encoding="utf-8") as f:
            mem: dict[str, int] = {}
            for line in f:
                k, v = line.split(":", 1)
                mem[k.strip()] = int(v.split()[0])
        avail_mb = mem.get("MemAvailable", 0) // 1024
        swap_total = mem.get("SwapTotal", 0)
        swap_free = mem.get("SwapFree", 0)
        swap_used_mb = max(0, (swap_total - swap_free) // 1024)
    except OSError:
        pass
    return avail_mb, swap_used_mb


def check_load(th: Thresholds) -> Finding:
    try:
        load1, load5, _ = os.getloadavg()
    except OSError:
        return Finding("warn", "load", "loadavg недоступен")
    msg = f"Load: {load1:.1f} / {load5:.1f} (5m)"
    if load5 >= th.load_warn:
        return Finding("warn", "load", msg)
    return Finding("ok", "load", msg)


def check_services() -> list[Finding]:
    out: list[Finding] = []
    for svc in SERVICES:
        code, text = run(
            ["systemctl", "is-active", svc],
            timeout=10,
        )
        state = text.strip() or "unknown"
        if code != 0 or state != "active":
            out.append(Finding("crit", "service", f"{svc}: {state}"))
        else:
            out.append(Finding("ok", "service", f"{svc}: active"))
    return out


def check_mysql(th: Thresholds) -> list[Finding]:
    out: list[Finding] = []
    code, _ = run(["mysqladmin", "ping"], timeout=10)
    if code != 0:
        out.append(Finding("crit", "mysql", "mysqladmin ping failed"))
        return out
    out.append(Finding("ok", "mysql", "MySQL отвечает"))
    code, text = run(
        ["mysql", "-N", "-e", "SHOW GLOBAL STATUS LIKE 'Threads_connected';"],
        timeout=15,
    )
    if code == 0:
        m = re.search(r"(\d+)", text)
        if m:
            conn = int(m.group(1))
            if conn >= th.mysql_conn_warn:
                out.append(
                    Finding(
                        "warn",
                        "mysql",
                        f"Подключений: {conn} (порог {th.mysql_conn_warn})",
                        conn,
                    )
                )
            else:
                out.append(Finding("ok", "mysql", f"Подключений: {conn}", conn))
    return out


def ssl_days_left(host: str, port: int = 443) -> int | None:
    ctx = ssl.create_default_context()
    try:
        with socket.create_connection((host, port), timeout=8) as sock:
            with ctx.wrap_socket(sock, server_hostname=host) as ssock:
                cert = ssock.getpeercert()
        exp = cert.get("notAfter")
        if not exp:
            return None
        # e.g. 'Jun  3 12:00:00 2026 GMT'
        dt = datetime.strptime(exp, "%b %d %H:%M:%S %Y %Z").replace(tzinfo=ZoneInfo("UTC"))
        return (dt.astimezone(TZ) - datetime.now(TZ)).days
    except Exception:
        return None


def check_ssl(th: Thresholds) -> list[Finding]:
    out: list[Finding] = []
    for host in SITES:
        days = ssl_days_left(host)
        if days is None:
            out.append(Finding("warn", "ssl", f"{host}: не удалось проверить сертификат"))
        elif days < 0:
            out.append(Finding("crit", "ssl", f"{host}: сертификат просрочен"))
        elif days <= th.ssl_warn_days:
            out.append(Finding("warn", "ssl", f"{host}: истекает через {days} дн."))
        else:
            out.append(Finding("ok", "ssl", f"{host}: {days} дн."))
    return out


def check_http(urls: Iterable[str]) -> list[Finding]:
    out: list[Finding] = []
    for url in urls:
        try:
            req = urllib.request.Request(url, method="GET")
            req.add_header("User-Agent", "LVT-SiteMonitor/1.0")
            with urllib.request.urlopen(req, timeout=15) as resp:
                code = resp.status
        except urllib.error.HTTPError as e:
            code = e.code
        except Exception as e:
            out.append(Finding("crit", "http", f"{url}: {e}"))
            continue
        if code >= 500:
            out.append(Finding("crit", "http", f"{url}: HTTP {code}"))
        elif code >= 400:
            out.append(Finding("warn", "http", f"{url}: HTTP {code}"))
        else:
            out.append(Finding("ok", "http", f"{url}: HTTP {code}"))
    return out


def fetch_yandex_webmaster(cfg: Config) -> tuple[list[str], list[Finding]]:
    """Данные из API Яндекс Вебмастера для отчёта в Telegram."""
    if not cfg.yandex_webmaster_token:
        return [], []
    try:
        client = YandexWebmasterClient(cfg.yandex_webmaster_token)
        items: list[HostWebmasterInfo] = client.collect(cfg.yandex_webmaster_domains)
    except YandexWebmasterError as e:
        return [], [
            Finding("warn", "webmaster", f"Вебмастер API: {e}"),
        ]
    lines, raw_findings = format_webmaster_section(items)
    findings = [
        Finding(level, cat, msg, cnt)
        for level, cat, msg, cnt in raw_findings
    ]
    return lines, findings


def journal_errors(hours: int) -> Counter[str]:
    since = f"{hours} hours ago"
    code, text = run(
        [
            "journalctl",
            "-u",
            "nginx",
            "-u",
            "mysql",
            "-u",
            "php8.3-fpm",
            "-u",
            "lvt-ai-consultant",
            "-u",
            "lvt-ai-telegrambot",
            "--since",
            since,
            "-p",
            "err",
            "--no-pager",
            "-n",
            "200",
        ],
        timeout=60,
    )
    c: Counter[str] = Counter()
    if code != 0:
        return c
    for line in text.splitlines():
        if line.startswith("-- ") or not line.strip():
            continue
        c["journal_err"] += 1
    return c


CRIT_LOG_KEYS = frozenset(
    {
        "php_fatal",
        "mysql_gone_away",
        "memory_exhausted",
        "segfault",
        "upstream_502",
        "connect_failed",
    }
)

INSTANT_ALERT_CATEGORIES = frozenset({"service", "raid", "mysql", "http", "ssl", "disk"})


def gather_critical_findings(cfg: Config, hours: int) -> list[Finding]:
    """Быстрая выборка только критичных событий за короткое окно."""
    access_dates, error_dates, _slow_dates = nginx_dates_in_window(hours)
    findings: list[Finding] = []

    raid = check_raid()
    if raid.level == "crit":
        findings.append(raid)

    for f in check_disk(cfg.thresholds):
        if f.level == "crit":
            findings.append(f)

    for svc in SERVICES:
        code, text = run(["systemctl", "is-active", svc], timeout=10)
        state = text.strip() or "unknown"
        if code != 0 or state != "active":
            findings.append(Finding("crit", "service", f"{svc}: {state}"))

    mysql_findings = check_mysql(cfg.thresholds)
    for f in mysql_findings:
        if f.level == "crit":
            findings.append(f)

    for f in check_ssl(cfg.thresholds):
        if "просрочен" in f.message:
            findings.append(f)

    for f in check_http(cfg.health_urls):
        if f.level == "crit":
            findings.append(f)

    label_ru = {
        "php_fatal": "PHP Fatal",
        "mysql_gone_away": "MySQL gone away",
        "memory_exhausted": "Memory exhausted",
        "upstream_502": "502/upstream",
        "connect_failed": "connect failed",
        "segfault": "segfault",
    }
    all_sig: Counter[str] = Counter()
    if HTTPD_LOG_DIR.is_dir():
        for site in ("lvt.market", "lvtgroup.ru"):
            err_log = HTTPD_LOG_DIR / f"{site}.error.log"
            acc_log = HTTPD_LOG_DIR / f"{site}.access.log"
            if err_log.is_file():
                sig, _, _ = scan_error_log(err_log, access_dates, error_dates)
                for k in CRIT_LOG_KEYS:
                    all_sig[k] += sig.get(k, 0)
            if acc_log.is_file():
                c5 = scan_access_5xx(acc_log, access_dates, error_dates)
                n5 = sum(c5.values())
                if n5 >= cfg.alert_min_count:
                    all_sig["http_5xx"] += n5

    for key, cnt in all_sig.items():
        if cnt <= 0:
            continue
        findings.append(
            Finding("crit", "logs", f"{label_ru.get(key, key)}: {cnt}", cnt)
        )

    return findings


def load_alert_state(path: Path) -> dict:
    if not path.is_file():
        return {"keys": {}}
    try:
        data = json.loads(path.read_text(encoding="utf-8"))
        if isinstance(data, dict) and "keys" in data:
            return data
    except (json.JSONDecodeError, OSError):
        pass
    return {"keys": {}}


def save_alert_state(path: Path, state: dict) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    tmp = path.with_suffix(".tmp")
    tmp.write_text(
        json.dumps(state, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )
    tmp.replace(path)


def _parse_ts(iso: str) -> datetime | None:
    try:
        return datetime.fromisoformat(iso).astimezone(TZ)
    except ValueError:
        return None


def should_fire_alert(
    finding: Finding,
    state: dict,
    cfg: Config,
) -> bool:
    if finding.level != "crit":
        return False
    key = finding.alert_key()
    prev = state["keys"].get(key)
    now = datetime.now(TZ)

    if prev is None:
        if finding.category in INSTANT_ALERT_CATEGORIES:
            return True
        return finding.count >= cfg.alert_min_count

    if finding.category in INSTANT_ALERT_CATEGORIES:
        if prev.get("level") != "crit":
            return True
        last = _parse_ts(prev.get("last_alert", ""))
        if not last:
            return True
        elapsed = (now - last).total_seconds()
        return elapsed >= cfg.alert_cooldown_minutes * 60

    prev_count = int(prev.get("count", 0))
    if finding.count >= prev_count + cfg.alert_count_delta:
        return True
    last = _parse_ts(prev.get("last_alert", ""))
    if last and (now - last).total_seconds() < cfg.alert_cooldown_minutes * 60:
        return False
    return finding.count > prev_count


def detect_recoveries(
    current: list[Finding],
    state: dict,
) -> list[str]:
    current_keys = {f.alert_key() for f in current if f.level == "crit"}
    messages: list[str] = []
    for key, prev in list(state.get("keys", {}).items()):
        if prev.get("level") == "crit" and key not in current_keys:
            label = prev.get("message", key)
            messages.append(f"✅ {label}")
    return messages


def process_instant_alerts(
    cfg: Config,
    dry_run: bool = False,
) -> tuple[int, str | None]:
    """
    Мгновенные алерты: только критичное, дедупликация, восстановление.
    Возвращает (число отправленных сообщений, статус для лога).
    """
    state = load_alert_state(cfg.alert_state_path)
    current = gather_critical_findings(cfg, cfg.alert_lookback_hours)
    to_alert = [f for f in current if should_fire_alert(f, state, cfg)]
    recoveries = detect_recoveries(current, state)

    now = datetime.now(TZ)
    now_iso = now.isoformat()

    for f in current:
        key = f.alert_key()
        state["keys"][key] = {
            "level": f.level,
            "count": f.count,
            "message": f.message,
            "last_seen": now_iso,
            "last_alert": state["keys"].get(key, {}).get("last_alert"),
        }
    for f in to_alert:
        state["keys"][f.alert_key()]["last_alert"] = now_iso
        state["keys"][f.alert_key()]["level"] = "crit"

    if not to_alert and not recoveries:
        save_alert_state(cfg.alert_state_path, state)
        return 0, "ok (no new alerts)"

    lines = [
        f"<b>🚨 LVT алерт</b> — {now.strftime('%d.%m.%Y %H:%M')} MSK",
        f"{HOSTNAME} · окно {cfg.alert_lookback_hours} ч",
        "",
    ]
    if to_alert:
        lines.append("<b>Критично</b>")
        for f in to_alert[:12]:
            extra = f" ({f.count})" if f.count else ""
            lines.append(f"• {f.message}{extra}")
        lines.append("")
    if recoveries:
        lines.append("<b>Восстановлено</b>")
        lines.extend(recoveries[:8])
        lines.append("")

    text = "\n".join(lines).strip()
    if len(text) > 4000:
        text = text[:3950] + "\n…"

    if dry_run:
        print(text.replace("<b>", "").replace("</b>", ""))
        print(f"\n--- alerts: {len(to_alert)}, recoveries: {len(recoveries)} ---")
        return len(to_alert) + bool(recoveries), "dry-run"

    send_telegram(cfg, text)
    save_alert_state(cfg.alert_state_path, state)
    return len(to_alert) + bool(recoveries), f"sent ({len(to_alert)} crit, {len(recoveries)} ok)"


def build_report(cfg: Config) -> tuple[str, str]:
    """Возвращает (уровень_итога, текст_отчёта)."""
    access_dates, error_dates, slow_dates = nginx_dates_in_window(
        cfg.lookback_hours
    )
    now = datetime.now(TZ)
    findings: list[Finding] = []
    log_stats: list[str] = []

    # --- Логи nginx per-site ---
    all_sig: Counter[str] = Counter()
    if HTTPD_LOG_DIR.is_dir():
        for site in ("lvt.market", "lvtgroup.ru", "lvtec.ru", "lvt-ec.ru"):
            err_log = HTTPD_LOG_DIR / f"{site}.error.log"
            acc_log = HTTPD_LOG_DIR / f"{site}.access.log"
            if err_log.is_file():
                sig, total, noise = scan_error_log(
                    err_log, access_dates, error_dates
                )
                all_sig.update(sig)
                if total:
                    log_stats.append(
                        f"• {site} error: {total} строк ({noise} шум отфильтровано)"
                    )
            if acc_log.is_file():
                c5 = scan_access_5xx(acc_log, access_dates, error_dates)
                if c5:
                    all_sig["http_5xx"] += sum(c5.values())
                    log_stats.append(
                        f"• {site} HTTP 5xx: "
                        + ", ".join(f"{k}×{v}" for k, v in sorted(c5.items()))
                    )

    if NGINX_ERR.is_file():
        sig, total, noise = scan_error_log(
            NGINX_ERR, access_dates, error_dates
        )
        all_sig.update(sig)
        if total:
            log_stats.append(f"• nginx global: {total} ошибок ({noise} шум)")

    slow = count_slow_php(PHP_SLOW, access_dates, error_dates, slow_dates)
    if slow >= cfg.thresholds.slow_php_warn:
        findings.append(
            Finding(
                "warn",
                "php_slow",
                f"Медленных PHP-запросов: {slow}",
                slow,
            )
        )
    elif slow:
        log_stats.append(f"• PHP slow log: {slow} записей")

    pf = scan_php_fpm_log(PHP_FPM_LOG, access_dates, error_dates)
    if pf.get("php_fpm_issue"):
        all_sig["php_fpm"] += pf["php_fpm_issue"]

    if AI_CONSULTANT_LOG.is_file():
        ai_err = 0
        for line in tail_lines(AI_CONSULTANT_LOG, 10_000):
            if not line_in_window(line, access_dates, error_dates):
                continue
            if re.search(r"\b(ERROR|error|Exception|failed)\b", line):
                ai_err += 1
        if ai_err:
            findings.append(
                Finding("warn", "ai", f"AI consultant log: {ai_err} событий", ai_err)
            )

    findings.extend(scan_bitrix_import_logs(access_dates, error_dates))

    label_ru = {
        "php_fatal": "PHP Fatal",
        "mysql_gone_away": "MySQL gone away",
        "memory_exhausted": "Memory exhausted",
        "upstream_502": "502/upstream",
        "upstream_504": "504/timeout",
        "connect_failed": "connect failed",
        "forbidden_probe": "запрет доступа (скан)",
        "script_unknown": "script unknown",
        "segfault": "segfault",
        "nginx_error": "nginx/FastCGI",
        "other_error": "прочие nginx error",
        "http_5xx": "HTTP 5xx (access)",
        "php_fpm": "php-fpm log",
    }
    for key, cnt in all_sig.most_common(12):
        if cnt == 0:
            continue
        lvl = "warn"
        if key in ("php_fatal", "mysql_gone_away", "memory_exhausted", "segfault"):
            lvl = "crit"
        findings.append(
            Finding(lvl, "logs", f"{label_ru.get(key, key)}: {cnt}", cnt)
        )

    j = journal_errors(cfg.lookback_hours)
    if j.get("journal_err", 0) > 5:
        findings.append(
            Finding(
                "warn",
                "journal",
                f"systemd journal (err): {j['journal_err']} записей",
                j["journal_err"],
            )
        )

    # --- Система ---
    raid = check_raid()
    findings.append(raid)
    findings.extend(check_disk(cfg.thresholds))
    avail_mb, swap_mb = parse_mem()
    findings.append(Finding("ok", "mem", f"RAM available: {avail_mb} MB"))
    if swap_mb >= cfg.thresholds.swap_warn_mb:
        findings.append(
            Finding("warn", "mem", f"Swap used: {swap_mb} MB", swap_mb)
        )
    else:
        findings.append(Finding("ok", "mem", f"Swap: {swap_mb} MB"))
    findings.append(check_load(cfg.thresholds))
    findings.extend(check_services())
    findings.extend(check_mysql(cfg.thresholds))
    findings.extend(check_ssl(cfg.thresholds))
    findings.extend(check_http(cfg.health_urls))

    wm_lines, wm_findings = fetch_yandex_webmaster(cfg)
    findings.extend(wm_findings)

    # --- Сборка текста ---
    crit_n = sum(1 for f in findings if f.level == "crit")
    warn_n = sum(1 for f in findings if f.level == "warn")
    if crit_n:
        overall = "crit"
        status = "🔴 Требует внимания"
    elif warn_n:
        overall = "warn"
        status = "🟡 Есть предупреждения"
    else:
        overall = "ok"
        status = "🟢 В норме"

    lines = [
        f"<b>LVT мониторинг</b> — {now.strftime('%d.%m.%Y %H:%M')} MSK",
        f"{status} · {HOSTNAME}",
        f"Период логов: {cfg.lookback_hours} ч",
        "",
    ]
    if log_stats:
        lines.append("<b>Логи</b>")
        lines.extend(log_stats[:8])
        lines.append("")

    if wm_lines:
        lines.extend(wm_lines)

    groups = ("crit", "warn", "ok")
    titles = {"crit": "Критично", "warn": "Предупреждения", "ok": "ОК"}
    for g in groups:
        items = [f for f in findings if f.level == g and f.category != "service"]
        if g == "ok":
            items = [f for f in items if f.category in ("raid", "disk", "load", "mysql", "ssl")]
        if not items:
            continue
        lines.append(f"<b>{titles[g]}</b>")
        for f in items[:15]:
            extra = f" ({f.count})" if f.count else ""
            lines.append(f"• {f.message}{extra}")
        lines.append("")

    svc = [f for f in findings if f.category == "service"]
    if svc:
        lines.append("<b>Сервисы</b>")
        for f in svc:
            icon = "✅" if f.level == "ok" else "❌"
            lines.append(f"{icon} {f.message}")
        lines.append("")

    lines.append(
        "<i>Шум (композит-кэш 404, боты) отфильтрован. Полные логи: /var/www/httpd-logs/</i>"
    )
    text = "\n".join(lines)
    if len(text) > 4000:
        text = text[:3950] + "\n… (обрезано)"
    return overall, text


def send_telegram(cfg: Config, text: str) -> None:
    if not cfg.telegram_token or not cfg.telegram_chat_id:
        raise SystemExit(
            "Задайте TELEGRAM_BOT_TOKEN и TELEGRAM_CHAT_ID в /etc/lvt/site-monitor.env"
        )
    url = f"https://api.telegram.org/bot{cfg.telegram_token}/sendMessage"
    body = json.dumps(
        {
            "chat_id": cfg.telegram_chat_id,
            "text": text,
            "parse_mode": "HTML",
            "disable_web_page_preview": True,
        }
    ).encode("utf-8")
    handlers: list[urllib.request.BaseHandler] = []
    if cfg.telegram_proxy_url:
        handlers.append(
            urllib.request.ProxyHandler(
                {"http": cfg.telegram_proxy_url, "https": cfg.telegram_proxy_url}
            )
        )
    opener = urllib.request.build_opener(*handlers)
    req = urllib.request.Request(
        url,
        data=body,
        headers={"Content-Type": "application/json"},
        method="POST",
    )
    try:
        with opener.open(req, timeout=30) as resp:
            data = json.loads(resp.read().decode())
        if not data.get("ok"):
            raise RuntimeError(data.get("description", "Telegram API error"))
    except urllib.error.URLError as e:
        raise SystemExit(f"Telegram send failed: {e}") from e


def main() -> None:
    parser = argparse.ArgumentParser(description="LVT site & server daily monitor")
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Только вывести отчёт в stdout, не слать в Telegram",
    )
    parser.add_argument(
        "--hours",
        type=int,
        default=None,
        help="Окно анализа логов в часах",
    )
    parser.add_argument(
        "--webmaster-only",
        action="store_true",
        help="Только блок Яндекс Вебмастер (dry-run или отправка в Telegram)",
    )
    parser.add_argument(
        "--alert",
        action="store_true",
        help="Мгновенный алерт: только критичное за ALERT_LOOKBACK_HOURS (cron каждые 15 мин)",
    )
    parser.add_argument(
        "--reset-alert-state",
        action="store_true",
        help="Сбросить состояние дедупликации алертов и выйти",
    )
    args = parser.parse_args()
    cfg = load_config()
    if args.hours is not None:
        cfg.lookback_hours = args.hours

    if args.reset_alert_state:
        p = cfg.alert_state_path
        if p.is_file():
            p.unlink()
            print(f"Removed {p}")
        else:
            print(f"No state file at {p}")
        return

    if args.alert:
        _, status = process_instant_alerts(cfg, dry_run=args.dry_run)
        print(status)
        return

    if args.webmaster_only:
        wm_lines, wm_findings = fetch_yandex_webmaster(cfg)
        if not cfg.yandex_webmaster_token:
            raise SystemExit(
                "Задайте YANDEX_WEBMASTER_OAUTH_TOKEN в /etc/lvt/site-monitor.env"
            )
        crit_n = sum(1 for f in wm_findings if f.level == "crit")
        warn_n = sum(1 for f in wm_findings if f.level == "warn")
        overall = "crit" if crit_n else ("warn" if warn_n else "ok")
        text = "\n".join(wm_lines).strip() or "<b>Яндекс Вебмастер</b>\n(нет данных)"
        if args.dry_run:
            print(
                text.replace("<b>", "")
                .replace("</b>", "")
                .replace("<i>", "")
                .replace("</i>", "")
            )
            print(f"\n--- overall: {overall} ---")
            return
        send_telegram(cfg, text)
        print(f"Webmaster report sent ({overall})")
        return

    overall, text = build_report(cfg)
    if args.dry_run:
        print(text.replace("<b>", "").replace("</b>", "").replace("<i>", "").replace("</i>", ""))
        print(f"\n--- overall: {overall} ---")
        return

    send_telegram(cfg, text)
    print(f"Report sent ({overall})")


if __name__ == "__main__":
    main()
