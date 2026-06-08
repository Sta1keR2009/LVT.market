import "dotenv/config";

function env(name: string, fallback = ""): string {
  return process.env[name]?.trim() ?? fallback;
}

function buildProxyUrl(): string {
  const direct = env("TELEGRAM_PROXY_URL");
  if (direct) return direct;
  const type = env("TELEGRAM_PROXY_TYPE", "socks5").toLowerCase();
  const host = env("TELEGRAM_PROXY_HOST");
  const port = env("TELEGRAM_PROXY_PORT");
  const user = env("TELEGRAM_PROXY_USER");
  const pass = env("TELEGRAM_PROXY_PASS");
  if (!host || !port) return "";
  const auth =
    user && pass
      ? `${encodeURIComponent(user)}:${encodeURIComponent(pass)}@`
      : "";
  const scheme = type === "http" || type === "https" ? "http" : "socks5";
  return `${scheme}://${auth}${host}:${port}`;
}

function parseWebmasterDomains(): string[] {
  const raw = env(
    "YANDEX_WEBMASTER_DOMAINS",
    "lvt.market,lvtgroup.ru"
  );
  return raw
    .split(/[,;\s]+/)
    .map((s) => s.trim().toLowerCase())
    .filter(Boolean);
}

function parseManagerIds(): number[] {
  const raw = env("TELEGRAM_MANAGER_USER_IDS", "399334751");
  return raw
    .split(/[,;\s]+/)
    .map((s) => Number(s.trim()))
    .filter((n) => Number.isFinite(n) && n > 0);
}

/** t.me/c/3685309637 → -1003685309637 */
function parseManagersChatId(): number {
  const direct = env("TELEGRAM_MANAGERS_CHAT_ID");
  if (direct) return Number(direct);
  const linkId = env("TELEGRAM_MANAGERS_CHAT_LINK_ID");
  if (linkId) {
    const n = Number(linkId);
    if (Number.isFinite(n)) return n > 0 ? -1_000_000_000_000 - n : n;
  }
  return -1003685309637;
}

export const config = {
  token: env("TELEGRAM_BOT_TOKEN"),
  proxyUrl: buildProxyUrl(),
  managersChatId: parseManagersChatId(),
  managerUserIds: parseManagerIds(),
  aiBackendUrl: env("AI_BACKEND_URL", "http://127.0.0.1:3847").replace(
    /\/$/,
    ""
  ),
  leadApiUrl: env(
    "LEAD_API_URL",
    "https://lvt.market/local/api/ai_consultant_lead.php"
  ),
  useWebhook: env("TELEGRAM_USE_WEBHOOK", "0") === "1",
  webhookPort: Number(env("TELEGRAM_WEBHOOK_PORT", "3848")),
  webhookPath: env("TELEGRAM_WEBHOOK_PATH", "/telegram-webhook"),
  pollTimeoutSec: Number(env("TELEGRAM_POLL_TIMEOUT", "50")),
  yandexWebmasterToken: env("YANDEX_WEBMASTER_OAUTH_TOKEN"),
  yandexWebmasterDomains: parseWebmasterDomains(),
};

export function assertConfig(): void {
  if (!config.token) {
    throw new Error("TELEGRAM_BOT_TOKEN is required");
  }
}
