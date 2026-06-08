import {
  fetch as undiciFetch,
  ProxyAgent,
  type RequestInit as UndiciRequestInit,
} from "undici";
import { config } from "./config.js";

let cachedAgent: ProxyAgent | undefined;

function getProxyAgent(): ProxyAgent | undefined {
  const url = config.proxyUrl;
  if (!url) return undefined;
  const scheme = new URL(url).protocol;
  if (scheme !== "http:" && scheme !== "https:") {
    throw new Error(
      `TELEGRAM_PROXY_URL must be http:// or https:// (got ${scheme}). ` +
        "For SOCKS5 use http-прокси на том же хосте или укажите http://user:pass@host:64504"
    );
  }
  if (!cachedAgent) {
    cachedAgent = new ProxyAgent(url);
  }
  return cachedAgent;
}

/** fetch с прокси (TELEGRAM_PROXY_URL) для api.telegram.org */
export function proxyFetch(url: string, init?: UndiciRequestInit) {
  const agent = getProxyAgent();
  if (agent) {
    return undiciFetch(url, { ...init, dispatcher: agent });
  }
  return undiciFetch(url, init);
}
