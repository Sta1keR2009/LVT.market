import {
  fetch as undiciFetch,
  ProxyAgent,
  type RequestInit as UndiciRequestInit,
} from "undici";

function env(name: string, fallback = ""): string {
  return process.env[name]?.trim() ?? fallback;
}

function buildProxyUrl(): string {
  const direct = env("TELEGRAM_PROXY_URL");
  if (direct) return direct;
  const type = env("TELEGRAM_PROXY_TYPE", "http").toLowerCase();
  const host = env("TELEGRAM_PROXY_HOST");
  const port = env("TELEGRAM_PROXY_PORT");
  const user = env("TELEGRAM_PROXY_USER");
  const pass = env("TELEGRAM_PROXY_PASS");
  if (!host || !port) return "";
  const auth =
    user && pass
      ? `${encodeURIComponent(user)}:${encodeURIComponent(pass)}@`
      : "";
  const scheme = type === "socks5" ? "socks5" : "http";
  return `${scheme}://${auth}${host}:${port}`;
}

let cachedAgent: ProxyAgent | undefined;

function getProxyAgent(): ProxyAgent | undefined {
  const proxyUrl = buildProxyUrl();
  if (!proxyUrl) return undefined;
  const scheme = new URL(proxyUrl).protocol;
  if (scheme !== "http:" && scheme !== "https:") {
    throw new Error(
      `TELEGRAM_PROXY_URL must be http:// for Node (use http://user:pass@host:64504)`
    );
  }
  if (!cachedAgent) {
    cachedAgent = new ProxyAgent(proxyUrl);
  }
  return cachedAgent;
}

export function telegramFetch(url: string, init?: UndiciRequestInit) {
  const agent = getProxyAgent();
  if (agent) {
    return undiciFetch(url, { ...init, dispatcher: agent });
  }
  return undiciFetch(url, init);
}
