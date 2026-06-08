import "dotenv/config";
import { ProxyAgent, fetch as undiciFetch } from "undici";

const token = process.env.TELEGRAM_BOT_TOKEN?.trim();
if (!token) {
  console.error("TELEGRAM_BOT_TOKEN required");
  process.exit(1);
}

const proxyUrl = process.env.TELEGRAM_PROXY_URL?.trim();
const opts = proxyUrl ? { dispatcher: new ProxyAgent(proxyUrl) } : {};

const res = await undiciFetch(
  `https://api.telegram.org/bot${token}/deleteWebhook`,
  {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ drop_pending_updates: false }),
    ...opts,
  }
);
const data = await res.json();
console.log(data);
