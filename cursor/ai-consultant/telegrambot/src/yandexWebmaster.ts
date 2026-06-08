/**
 * API Яндекс Вебмастера v4 — https://yandex.com/dev/webmaster/doc/en/
 */

const API_BASE = "https://api.webmaster.yandex.net/v4";

const PROBLEM_LABELS: Record<string, string> = {
  FATAL: "фатальные",
  CRITICAL: "критичные",
  POSSIBLE_PROBLEM: "возможные",
  RECOMMENDATION: "рекомендации",
};

const PROBLEM_ORDER = [
  "FATAL",
  "CRITICAL",
  "POSSIBLE_PROBLEM",
  "RECOMMENDATION",
] as const;

export interface HostWebmasterInfo {
  domain: string;
  hostUrl: string;
  hostId: string;
  verified: boolean;
  sqi?: number;
  searchablePages?: number;
  excludedPages?: number;
  problems: Record<string, number>;
  skipReason?: string;
}

export class YandexWebmasterError extends Error {
  constructor(message: string) {
    super(message);
    this.name = "YandexWebmasterError";
  }
}

interface YmHost {
  host_id?: string;
  ascii_host_url?: string;
  unicode_host_url?: string;
  verified?: boolean;
}

export class YandexWebmasterClient {
  constructor(
    private readonly token: string,
    private readonly timeoutMs = 25_000
  ) {
    if (!token.trim()) {
      throw new YandexWebmasterError("Пустой OAuth-токен");
    }
  }

  private async request(path: string): Promise<Record<string, unknown>> {
    const url = `${API_BASE}${path}`;
    const ctrl = new AbortController();
    const timer = setTimeout(() => ctrl.abort(), this.timeoutMs);
    try {
      const res = await fetch(url, {
        method: "GET",
        headers: {
          Authorization: `OAuth ${this.token.trim()}`,
          Accept: "application/json",
          "User-Agent": "LVT-TelegramBot/1.0",
        },
        signal: ctrl.signal,
      });
      const raw = await res.text();
      if (!res.ok) {
        throw new YandexWebmasterError(
          `HTTP ${res.status} ${path}: ${parseApiError(raw) || res.statusText}`
        );
      }
      return raw ? (JSON.parse(raw) as Record<string, unknown>) : {};
    } catch (e) {
      if (e instanceof YandexWebmasterError) throw e;
      const msg = e instanceof Error ? e.message : String(e);
      throw new YandexWebmasterError(`Сеть ${path}: ${msg}`);
    } finally {
      clearTimeout(timer);
    }
  }

  async userId(): Promise<number> {
    const data = await this.request("/user");
    const uid = data.user_id;
    if (uid == null) throw new YandexWebmasterError("Ответ /user без user_id");
    return Number(uid);
  }

  async hosts(userId: number): Promise<YmHost[]> {
    const data = await this.request(`/user/${userId}/hosts`);
    return (data.hosts as YmHost[] | undefined) ?? [];
  }

  async summary(userId: number, hostId: string): Promise<Record<string, unknown>> {
    const hid = encodeURIComponent(hostId);
    return this.request(`/user/${userId}/hosts/${hid}/summary`);
  }

  async collect(domains: string[]): Promise<HostWebmasterInfo[]> {
    const uid = await this.userId();
    const allHosts = await this.hosts(uid);
    const byDomain = new Map<string, YmHost>();
    for (const h of allHosts) {
      for (const key of ["unicode_host_url", "ascii_host_url"] as const) {
        const url = (h[key] ?? "").trim();
        const dom = domainFromUrl(url);
        if (dom && !byDomain.has(dom)) byDomain.set(dom, h);
      }
    }

    const result: HostWebmasterInfo[] = [];
    for (const domain of domains) {
      const host = byDomain.get(domain.toLowerCase());
      if (!host) {
        result.push({
          domain,
          hostUrl: "",
          hostId: "",
          verified: false,
          problems: {},
          skipReason: "не найден в Вебмастере",
        });
        continue;
      }

      const hostId = String(host.host_id ?? "");
      const hostUrl =
        (host.unicode_host_url ?? host.ascii_host_url ?? domain).trim();
      const verified = Boolean(host.verified);
      const info: HostWebmasterInfo = {
        domain,
        hostUrl,
        hostId,
        verified,
        problems: {},
      };

      if (!verified) {
        info.skipReason = "права не подтверждены";
        result.push(info);
        continue;
      }

      try {
        const s = await this.summary(uid, hostId);
        info.sqi = intOrUndefined(s.sqi);
        info.searchablePages = intOrUndefined(s.searchable_pages_count);
        info.excludedPages = intOrUndefined(s.excluded_pages_count);
        const raw = s.site_problems;
        if (raw && typeof raw === "object") {
          for (const [k, v] of Object.entries(raw as Record<string, unknown>)) {
            const n = Number(v);
            if (Number.isFinite(n) && n > 0) info.problems[k] = n;
          }
        }
      } catch (e) {
        const msg = e instanceof Error ? e.message : String(e);
        if (msg.includes("HOST_NOT_LOADED")) info.skipReason = "данные ещё не загружены";
        else if (msg.includes("HOST_NOT_VERIFIED"))
          info.skipReason = "сайт не верифицирован";
        else if (msg.includes("HOST_NOT_FOUND")) info.skipReason = "хост не найден";
        else info.skipReason = msg.slice(0, 120);
      }
      result.push(info);
    }
    return result;
  }
}

export function formatWebmasterReport(items: HostWebmasterInfo[]): string {
  const now = new Date().toLocaleString("ru-RU", {
    timeZone: "Europe/Moscow",
    dateStyle: "short",
    timeStyle: "short",
  });
  const lines: string[] = [`📊 Яндекс Вебмастер · ${now} MSK`, ""];

  if (!items.length) {
    lines.push("Нет данных.");
    return lines.join("\n");
  }

  for (const info of items) {
    if (info.skipReason) {
      lines.push(`• ${info.domain}: ${info.skipReason}`);
      continue;
    }
    const parts: string[] = [`• ${info.domain}`];
    if (info.sqi != null) parts.push(`ИКС ${info.sqi}`);
    if (info.searchablePages != null) {
      parts.push(`в поиске ${formatNum(info.searchablePages)}`);
    }
    if (info.excludedPages != null) {
      parts.push(`исключено ${formatNum(info.excludedPages)}`);
    }
    lines.push(parts.join(", "));

    const probParts: string[] = [];
    for (const sev of PROBLEM_ORDER) {
      const n = info.problems[sev];
      if (n) probParts.push(`${PROBLEM_LABELS[sev] ?? sev}: ${n}`);
    }
    if (probParts.length) {
      lines.push(`  └ ${probParts.join(", ")}`);
    }
  }

  lines.push("", "Команда: /webmaster");
  return lines.join("\n");
}

function formatNum(n: number): string {
  return n.toLocaleString("ru-RU");
}

function parseApiError(body: string): string {
  if (!body) return "";
  try {
    const data = JSON.parse(body) as Record<string, string>;
    const code = data.error_code ?? "";
    const msg = data.error_message ?? "";
    return `${code} ${msg}`.trim() || body.slice(0, 200);
  } catch {
    return body.slice(0, 200);
  }
}

function domainFromUrl(url: string): string {
  let u = url.trim().toLowerCase();
  if (!u) return "";
  u = u.replace(/^https?:\/\//, "");
  u = u.split("/")[0]?.split(":")[0] ?? "";
  if (u.startsWith("www.")) u = u.slice(4);
  return u;
}

function intOrUndefined(val: unknown): number | undefined {
  if (val == null) return undefined;
  const n = Number(val);
  return Number.isFinite(n) ? n : undefined;
}
