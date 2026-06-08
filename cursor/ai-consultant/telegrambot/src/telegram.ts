import { config } from "./config.js";
import { proxyFetch } from "./proxyFetch.js";

const API = `https://api.telegram.org/bot${config.token}`;

export interface ReplyKeyboard {
  keyboard: string[][];
  resize_keyboard?: boolean;
  one_time_keyboard?: boolean;
}

export async function tgApi<T>(
  method: string,
  body: Record<string, unknown>
): Promise<T> {
  const res = await proxyFetch(`${API}/${method}`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  });
  const data = (await res.json()) as { ok: boolean; description?: string; result?: T };
  if (!data.ok) {
    throw new Error(data.description ?? `Telegram API ${method} failed`);
  }
  return data.result as T;
}

export async function sendMessageReturnId(
  chatId: number,
  text: string,
  opts?: { reply_markup?: ReplyKeyboard | { inline_keyboard: unknown[][] } }
): Promise<number> {
  const chunks = splitMessage(text, 4000);
  let lastId = 0;
  for (let i = 0; i < chunks.length; i++) {
    const result = await tgApi<{ message_id: number }>("sendMessage", {
      chat_id: chatId,
      text: chunks[i],
      ...(i === chunks.length - 1 && opts?.reply_markup
        ? { reply_markup: opts.reply_markup }
        : {}),
    });
    lastId = result.message_id;
  }
  return lastId;
}

export async function sendMessage(
  chatId: number,
  text: string,
  opts?: { reply_markup?: ReplyKeyboard | { inline_keyboard: unknown[][] } }
): Promise<void> {
  await sendMessageReturnId(chatId, text, opts);
}

export async function sendChatAction(
  chatId: number,
  action: "typing" = "typing"
): Promise<void> {
  await tgApi("sendChatAction", { chat_id: chatId, action });
}

export async function getUpdates(params: {
  offset?: number;
  timeout?: number;
}): Promise<import("./types.js").TgUpdate[]> {
  const result = await tgApi<import("./types.js").TgUpdate[]>("getUpdates", {
    offset: params.offset,
    timeout: params.timeout ?? 50,
    allowed_updates: ["message", "callback_query"],
  });
  return result ?? [];
}

export async function deleteWebhook(): Promise<void> {
  await tgApi("deleteWebhook", { drop_pending_updates: false });
}

function splitMessage(text: string, max: number): string[] {
  if (text.length <= max) return [text];
  const parts: string[] = [];
  let rest = text;
  while (rest.length > max) {
    let cut = rest.lastIndexOf("\n", max);
    if (cut < max * 0.5) cut = max;
    parts.push(rest.slice(0, cut));
    rest = rest.slice(cut).trimStart();
  }
  if (rest) parts.push(rest);
  return parts;
}
