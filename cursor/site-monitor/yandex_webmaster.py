"""
Клиент API Яндекс Вебмастера v4 (индексация, ИКС, проблемы сайта).
https://yandex.com/dev/webmaster/doc/en/
"""
from __future__ import annotations

import json
import re
import urllib.error
import urllib.request
from dataclasses import dataclass, field
from typing import Any
from urllib.parse import quote

API_BASE = "https://api.webmaster.yandex.net/v4"

PROBLEM_LABELS = {
    "FATAL": "фатальные",
    "CRITICAL": "критичные",
    "POSSIBLE_PROBLEM": "возможные",
    "RECOMMENDATION": "рекомендации",
}


@dataclass
class HostWebmasterInfo:
    domain: str
    host_url: str
    host_id: str
    verified: bool
    sqi: int | None = None
    searchable_pages: int | None = None
    excluded_pages: int | None = None
    problems: dict[str, int] = field(default_factory=dict)
    skip_reason: str | None = None


class YandexWebmasterError(Exception):
    pass


class YandexWebmasterClient:
    def __init__(self, oauth_token: str, timeout: int = 25) -> None:
        self.token = oauth_token.strip()
        self.timeout = timeout
        if not self.token:
            raise YandexWebmasterError("Пустой OAuth-токен")

    def _request(self, path: str) -> Any:
        url = f"{API_BASE}{path}"
        req = urllib.request.Request(
            url,
            headers={
                "Authorization": f"OAuth {self.token}",
                "Accept": "application/json",
                "User-Agent": "LVT-SiteMonitor/1.0",
            },
            method="GET",
        )
        try:
            with urllib.request.urlopen(req, timeout=self.timeout) as resp:
                raw = resp.read().decode("utf-8")
        except urllib.error.HTTPError as e:
            body = ""
            try:
                body = e.read().decode("utf-8", errors="replace")
            except OSError:
                pass
            err = _parse_api_error(body)
            raise YandexWebmasterError(
                f"HTTP {e.code} {path}: {err or e.reason}"
            ) from e
        except urllib.error.URLError as e:
            raise YandexWebmasterError(f"Сеть {path}: {e.reason}") from e
        if not raw:
            return {}
        return json.loads(raw)

    def user_id(self) -> int:
        data = self._request("/user")
        uid = data.get("user_id")
        if uid is None:
            raise YandexWebmasterError("Ответ /user без user_id")
        return int(uid)

    def hosts(self, user_id: int) -> list[dict[str, Any]]:
        data = self._request(f"/user/{user_id}/hosts")
        return list(data.get("hosts") or [])

    def summary(self, user_id: int, host_id: str) -> dict[str, Any]:
        hid = quote(host_id, safe="")
        return self._request(f"/user/{user_id}/hosts/{hid}/summary")

    def collect(
        self,
        domains: tuple[str, ...],
    ) -> list[HostWebmasterInfo]:
        """Сводка по доменам из списка (сопоставление с хостами в Вебмастере)."""
        uid = self.user_id()
        all_hosts = self.hosts(uid)
        by_domain: dict[str, dict[str, Any]] = {}
        for h in all_hosts:
            for key in ("unicode_host_url", "ascii_host_url"):
                url = (h.get(key) or "").strip()
                dom = _domain_from_url(url)
                if dom and dom not in by_domain:
                    by_domain[dom] = h

        result: list[HostWebmasterInfo] = []
        for domain in domains:
            host = by_domain.get(domain)
            if not host:
                result.append(
                    HostWebmasterInfo(
                        domain=domain,
                        host_url="",
                        host_id="",
                        verified=False,
                        skip_reason="не найден в Вебмастере",
                    )
                )
                continue

            host_id = str(host.get("host_id") or "")
            host_url = (
                host.get("unicode_host_url")
                or host.get("ascii_host_url")
                or domain
            )
            verified = bool(host.get("verified"))
            info = HostWebmasterInfo(
                domain=domain,
                host_url=host_url.strip(),
                host_id=host_id,
                verified=verified,
            )
            if not verified:
                info.skip_reason = "права не подтверждены"
                result.append(info)
                continue

            try:
                s = self.summary(uid, host_id)
            except YandexWebmasterError as e:
                msg = str(e)
                if "HOST_NOT_LOADED" in msg:
                    info.skip_reason = "данные ещё не загружены"
                elif "HOST_NOT_VERIFIED" in msg:
                    info.skip_reason = "сайт не верифицирован"
                elif "HOST_NOT_FOUND" in msg:
                    info.skip_reason = "хост не найден"
                else:
                    info.skip_reason = msg[:120]
                result.append(info)
                continue

            info.sqi = _int_or_none(s.get("sqi"))
            info.searchable_pages = _int_or_none(s.get("searchable_pages_count"))
            info.excluded_pages = _int_or_none(s.get("excluded_pages_count"))
            raw_probs = s.get("site_problems") or {}
            if isinstance(raw_probs, dict):
                info.problems = {
                    k: int(v)
                    for k, v in raw_probs.items()
                    if v is not None and int(v) > 0
                }
            result.append(info)
        return result


def format_webmaster_section(items: list[HostWebmasterInfo]) -> tuple[list[str], list[tuple[str, str, int]]]:
    """
    Возвращает (строки HTML для отчёта, список (level, category, message) для findings).
    level: warn | crit
    """
    lines: list[str] = []
    findings: list[tuple[str, str, int]] = []

    if not items:
        return lines, findings

    lines.append("<b>Яндекс Вебмастер</b>")
    for info in items:
        if info.skip_reason:
            lines.append(f"• {info.domain}: {info.skip_reason}")
            if "не найден" not in info.skip_reason:
                findings.append(("warn", "webmaster", f"{info.domain}: {info.skip_reason}", 0))
            continue

        parts = [f"• <b>{info.domain}</b>"]
        if info.sqi is not None:
            parts.append(f"ИКС {info.sqi}")
        if info.searchable_pages is not None:
            parts.append(f"в поиске {info.searchable_pages:,}".replace(",", " "))
        if info.excluded_pages is not None:
            parts.append(f"исключено {info.excluded_pages:,}".replace(",", " "))
        lines.append(", ".join(parts))

        if info.problems:
            prob_parts = []
            for sev in ("FATAL", "CRITICAL", "POSSIBLE_PROBLEM", "RECOMMENDATION"):
                n = info.problems.get(sev, 0)
                if n:
                    prob_parts.append(f"{PROBLEM_LABELS.get(sev, sev)}: {n}")
                    if sev in ("FATAL", "CRITICAL"):
                        findings.append(
                            (
                                "crit" if sev == "FATAL" else "warn",
                                "webmaster",
                                f"{info.domain} — {PROBLEM_LABELS[sev]}: {n}",
                                n,
                            )
                        )
            if prob_parts:
                lines.append(f"  └ проблемы: {', '.join(prob_parts)}")

    lines.append("")
    return lines, findings


def _parse_api_error(body: str) -> str:
    if not body:
        return ""
    try:
        data = json.loads(body)
    except json.JSONDecodeError:
        return body[:200]
    code = data.get("error_code") or data.get("error_message") or ""
    msg = data.get("error_message") or ""
    return f"{code} {msg}".strip() or body[:200]


def _domain_from_url(url: str) -> str:
    url = url.strip().lower()
    if not url:
        return ""
    url = re.sub(r"^https?://", "", url)
    url = url.split("/")[0].split(":")[0]
    if url.startswith("www."):
        url = url[4:]
    return url


def _int_or_none(val: Any) -> int | None:
    if val is None:
        return None
    try:
        return int(val)
    except (TypeError, ValueError):
        return None
