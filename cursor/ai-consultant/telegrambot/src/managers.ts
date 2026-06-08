import { config } from "./config.js";
import { sendMessage, sendMessageReturnId } from "./telegram.js";
import type { TgMessage } from "./types.js";

type TgUser = NonNullable<TgMessage["from"]>;

export function formatOperatorName(from?: TgUser): string | undefined {
  if (!from) return undefined;
  const name = [from.first_name, from.last_name]
    .filter(Boolean)
    .join(" ")
    .trim();
  if (name) return name;
  if (from.username) {
    const u = from.username.replace(/^@/, "");
    return u ? `@${u}` : undefined;
  }
  return undefined;
}
import { mainKeyboard } from "./menu.js";
import { isWebmasterCommand, sendWebmasterReport } from "./webmaster.js";

/** Клиенты в режиме диалога с менеджером (ИИ отключён) */
const humanClients = new Set<number>();

/** message_id в чате менеджеров → chat_id клиента */
const managerReplyMap = new Map<number, number>();

/** message_id в чате менеджеров → session_id виджета (w-…) */
const widgetReplyMap = new Map<number, string>();

function parseWidgetSessionFromText(text: string): string | undefined {
  const m = /Сессия:\s*(w-[^\s\n]+)/i.exec(text);
  return m?.[1];
}

function widgetBridgeKey(): string {
  return process.env.WIDGET_BRIDGE_INTERNAL_KEY?.trim() ?? "";
}

export function isHumanMode(clientChatId: number): boolean {
  return humanClients.has(clientChatId);
}

export function isManagersChat(chatId: number): boolean {
  return (
    config.managersChatId !== 0 && chatId === config.managersChatId
  );
}

export function isManagerUser(userId: number | undefined): boolean {
  if (userId == null) return false;
  return config.managerUserIds.includes(userId);
}

/** Включить режим менеджера и уведомить группу */
export async function enterHumanMode(
  clientChatId: number,
  reason: string
): Promise<void> {
  if (!config.managersChatId) {
    console.warn("[managers] TELEGRAM_MANAGERS_CHAT_ID not set");
    return;
  }

  if (humanClients.has(clientChatId)) {
    await forwardClientMessage(
      clientChatId,
      `[эскалация: ${reason}]`
    );
    return;
  }

  humanClients.add(clientChatId);

  const header =
    `🟡 Запрос менеджера\n` +
    `Клиент: ${clientChatId}\n` +
    `Сессия: telegram:${clientChatId}\n` +
    `Причина: ${reason}\n\n` +
    `Ответьте reply на сообщения клиента ниже.\n` +
    `Закрыть диалог: /close ${clientChatId}`;

  const headerId = await sendMessageReturnId(
    config.managersChatId,
    header
  );
  managerReplyMap.set(headerId, clientChatId);

  await sendMessage(
    clientChatId,
    "Перевожу на менеджера. Опишите вопрос — ответит живой специалист в этом чате. Ожидайте, пожалуйста."
  );
}

export async function exitHumanMode(
  clientChatId: number,
  notifyClient = true
): Promise<void> {
  humanClients.delete(clientChatId);
  if (notifyClient) {
    await sendMessage(
      clientChatId,
      "Диалог с менеджером завершён. Снова могу помочь как консультант lvt.market — выберите пункт меню или задайте вопрос.",
      { reply_markup: mainKeyboard() }
    );
  }
}

/** Переслать сообщение клиента в чат менеджеров */
export async function forwardClientMessage(
  clientChatId: number,
  text: string
): Promise<void> {
  if (!config.managersChatId) return;

  const body =
    `👤 Клиент ${clientChatId}:\n${text.slice(0, 3500)}`;
  const msgId = await sendMessageReturnId(config.managersChatId, body);
  managerReplyMap.set(msgId, clientChatId);
}

/**
 * Сообщение из группы менеджеров.
 * @returns true если обработано
 */
export async function handleManagerMessage(msg: TgMessage): Promise<boolean> {
  if (!isManagersChat(msg.chat.id)) return false;

  const fromId = msg.from?.id;
  if (!isManagerUser(fromId)) {
    return true;
  }

  const text = (msg.text ?? "").trim();
  if (!text) return true;

  if (isWebmasterCommand(text)) {
    await sendWebmasterReport(config.managersChatId);
    return true;
  }

  const closeW = /^\/closew(?:@\w+)?\s+(w-\S+)/i.exec(text);
  if (closeW) {
    const sessionId = closeW[1];
    const ok = await closeWidgetSession(sessionId);
    await sendMessageReturnId(
      config.managersChatId,
      ok
        ? `✅ Чат на сайте закрыт (${sessionId}).`
        : `Не удалось закрыть чат на сайте (${sessionId}).`
    );
    return true;
  }

  const closeMatch = /^\/close(?:@\w+)?(?:\s+(-?\d+))?$/i.exec(text);
  if (closeMatch) {
    let clientId = closeMatch[1] ? Number(closeMatch[1]) : undefined;
    if (clientId == null && msg.reply_to_message?.message_id) {
      clientId = managerReplyMap.get(msg.reply_to_message.message_id);
    }
    if (clientId != null && humanClients.has(clientId)) {
      await exitHumanMode(clientId, true);
      await sendMessageReturnId(
        config.managersChatId,
        `✅ Диалог с клиентом ${clientId} закрыт, включён ИИ-консультант.`
      );
    } else {
      await sendMessageReturnId(
        config.managersChatId,
        "Укажите: /close <chat_id> или reply на сообщение клиента."
      );
    }
    return true;
  }

  const replyTo = msg.reply_to_message;
  const replyId = replyTo?.message_id;
  if (replyId == null) {
    await sendMessageReturnId(
      config.managersChatId,
      "Ответьте reply на сообщение клиента, чтобы отправить ответ в бот."
    );
    return true;
  }

  let widgetSession = widgetReplyMap.get(replyId);
  if (!widgetSession) {
    widgetSession = parseWidgetSessionFromText(replyTo?.text ?? "");
  }
  if (widgetSession) {
    await relayManagerToWidget(widgetSession, text, msg.from);
    return true;
  }

  const clientChatId = managerReplyMap.get(replyId);
  if (clientChatId == null) {
    const parsed = /Клиент[:\s]+(-?\d+)/i.exec(replyTo?.text ?? "");
    if (parsed) {
      const id = Number(parsed[1]);
      if (Number.isFinite(id)) {
        await relayManagerToClient(id, text, fromId);
        return true;
      }
    }
    await sendMessageReturnId(
      config.managersChatId,
      "Не найден клиент для этого reply. Используйте reply на сообщение с текстом клиента или с сайта."
    );
    return true;
  }

  await relayManagerToClient(clientChatId, text, fromId);
  return true;
}

async function relayManagerToWidget(
  sessionId: string,
  text: string,
  from?: TgUser
): Promise<void> {
  const operatorName = formatOperatorName(from);
  const headers: Record<string, string> = {
    "Content-Type": "application/json",
  };
  const key = widgetBridgeKey();
  if (key) headers["X-Internal-Key"] = key;

  try {
    const res = await fetch(
      `${config.aiBackendUrl}/v1/internal/widget/manager-reply`,
      {
        method: "POST",
        headers,
        body: JSON.stringify({
          session_id: sessionId,
          text,
          operator_name: operatorName,
        }),
      }
    );
    if (!res.ok) {
      await sendMessageReturnId(
        config.managersChatId,
        `Ошибка доставки на сайт (${sessionId}): HTTP ${res.status}`
      );
      return;
    }
    await sendMessageReturnId(
      config.managersChatId,
      `✓ Отправлено на сайт (${sessionId})`
    );
  } catch (e) {
    await sendMessageReturnId(
      config.managersChatId,
      `Ошибка доставки на сайт (${sessionId}): ${e instanceof Error ? e.message : String(e)}`
    );
  }
}

async function closeWidgetSession(sessionId: string): Promise<boolean> {
  const headers: Record<string, string> = {
    "Content-Type": "application/json",
  };
  const key = widgetBridgeKey();
  if (key) headers["X-Internal-Key"] = key;
  try {
    const res = await fetch(`${config.aiBackendUrl}/v1/internal/widget/close`, {
      method: "POST",
      headers,
      body: JSON.stringify({ session_id: sessionId }),
    });
    return res.ok;
  } catch {
    return false;
  }
}

async function relayManagerToClient(
  clientChatId: number,
  text: string,
  managerId?: number
): Promise<void> {
  if (!humanClients.has(clientChatId)) {
    humanClients.add(clientChatId);
  }

  void managerId;
  await sendMessage(
    clientChatId,
    `👤 Менеджер:\n${text.slice(0, 3900)}`
  );

  await sendMessageReturnId(
    config.managersChatId,
    `✓ Отправлено клиенту ${clientChatId}`
  );
}
