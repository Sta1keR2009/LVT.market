import http from "http";
import { config, assertConfig } from "./config.js";
import { deleteWebhook, getUpdates } from "./telegram.js";
import { handleContact, handleStart, handleText } from "./flows.js";
import {
  handleManagerMessage,
  isManagersChat,
  isManagerUser,
} from "./managers.js";
import { isWebmasterCommand, sendWebmasterReport } from "./webmaster.js";
import type { TgUpdate } from "./types.js";

assertConfig();

async function bootstrap(): Promise<void> {
  console.info("[telegrambot] deleting orchestrator webhook (use long polling)");
  try {
    await deleteWebhook();
  } catch (e) {
    console.warn("[telegrambot] deleteWebhook:", e);
  }

  if (config.useWebhook) {
    startWebhookServer();
    return;
  }

  startPolling();
}

function startPolling(): void {
  let offset = 0;
  console.info("[telegrambot] long polling started");

  const loop = async (): Promise<void> => {
    try {
      const updates = await getUpdates({
        offset: offset > 0 ? offset : undefined,
        timeout: config.pollTimeoutSec,
      });
      for (const u of updates) {
        offset = Math.max(offset, u.update_id + 1);
        await processUpdate(u).catch((err) => {
          console.error("[telegrambot] update error:", err);
        });
      }
    } catch (e) {
      console.error("[telegrambot] poll error:", e);
      await sleep(3000);
    }
    setImmediate(loop);
  };

  void loop();
}

function startWebhookServer(): void {
  const server = http.createServer(async (req, res) => {
    if (req.method !== "POST" || req.url !== config.webhookPath) {
      res.writeHead(404);
      res.end();
      return;
    }
    let body = "";
    req.on("data", (chunk) => {
      body += chunk;
    });
    req.on("end", async () => {
      try {
        const update = JSON.parse(body) as TgUpdate;
        await processUpdate(update);
        res.writeHead(200, { "Content-Type": "application/json" });
        res.end(JSON.stringify({ ok: true }));
      } catch (e) {
        console.error("[telegrambot] webhook error:", e);
        res.writeHead(500);
        res.end();
      }
    });
  });

  server.listen(config.webhookPort, "127.0.0.1", () => {
    console.info(
      `[telegrambot] webhook listening http://127.0.0.1:${config.webhookPort}${config.webhookPath}`
    );
  });
}

async function processUpdate(update: TgUpdate): Promise<void> {
  const msg = update.message;
  if (!msg?.chat?.id) return;

  if (isManagersChat(msg.chat.id)) {
    await handleManagerMessage(msg);
    return;
  }

  const chatId = msg.chat.id;
  const text = msg.text?.trim();

  if (text && isWebmasterCommand(text)) {
    if (isManagerUser(msg.from?.id)) {
      await sendWebmasterReport(chatId);
    }
    return;
  }

  if (text === "/start" || text === "/menu") {
    await handleStart(chatId, isManagerUser(msg.from?.id));
    return;
  }

  if (msg.contact?.phone_number) {
    await handleContact(chatId, msg.contact.phone_number);
    return;
  }

  if (text) {
    await handleText(chatId, text, isManagerUser(msg.from?.id));
  }
}

function sleep(ms: number): Promise<void> {
  return new Promise((r) => setTimeout(r, ms));
}

void bootstrap();
