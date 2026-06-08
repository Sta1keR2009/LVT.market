import { getHistory } from "./session.js";
import { buildManagerPanelUrl } from "./managerTokens.js";
import { telegramFetch } from "./telegramProxy.js";

function env(name: string, fallback = ""): string {
  return process.env[name]?.trim() ?? fallback;
}

function parseManagersChatId(): number {
  const raw = env("TELEGRAM_MANAGERS_CHAT_ID");
  if (!raw) return 0;
  const first = raw.split(/[,;\s]+/)[0]?.trim() ?? "";
  const n = Number(first);
  return Number.isFinite(n) ? n : 0;
}

export type ChatHistoryLine = {
  role: "user" | "assistant" | "system";
  content: string;
};

export type WidgetHumanMeta = {
  pageUrl?: string;
  displayName?: string;
  userPhone?: string;
  userEmail?: string;
  reason?: string;
  chatHistory?: ChatHistoryLine[];
};

type SessionState = {
  sessionId: string;
  meta: WidgetHumanMeta;
  enteredAt: number;
};

export type WidgetOutMessage = {
  id: number;
  text: string;
  from: "manager" | "system";
  operator_name?: string;
  ts: number;
};

const humanSessions = new Map<string, SessionState>();
const sessionMetaStore = new Map<string, WidgetHumanMeta>();
const outbox = new Map<string, WidgetOutMessage[]>();
const tgMsgToSession = new Map<number, string>();
const activityNotified = new Set<string>();
let nextOutId = 1;

export function rememberWidgetMeta(
  sessionId: string,
  meta: WidgetHumanMeta
): void {
  const prev = sessionMetaStore.get(sessionId) ?? {};
  sessionMetaStore.set(sessionId, { ...prev, ...meta });
}

export function isWidgetHumanMode(sessionId: string): boolean {
  return humanSessions.has(sessionId);
}

export function getWidgetSessionMeta(
  sessionId: string
): WidgetHumanMeta | undefined {
  const hm = humanSessions.get(sessionId)?.meta;
  const stored = sessionMetaStore.get(sessionId);
  if (!hm && !stored) return undefined;
  return { ...stored, ...hm };
}

export function parseWidgetSessionFromText(text: string): string | undefined {
  const m = /Сессия:\s*(w-[^\s\n]+)/i.exec(text);
  return m?.[1];
}

export function sessionFromTelegramMsgId(msgId: number): string | undefined {
  return tgMsgToSession.get(msgId);
}

function pushOut(
  sessionId: string,
  text: string,
  from: "manager" | "system",
  operatorName?: string
): number {
  const id = nextOutId++;
  const list = outbox.get(sessionId) ?? [];
  const row: WidgetOutMessage = { id, text, from, ts: Date.now() };
  if (operatorName) row.operator_name = operatorName;
  list.push(row);
  if (list.length > 200) list.splice(0, list.length - 200);
  outbox.set(sessionId, list);
  return id;
}

export function pollWidgetMessages(
  sessionId: string,
  since: number
): WidgetOutMessage[] {
  const list = outbox.get(sessionId) ?? [];
  return list.filter((m) => m.id > since);
}

function registerTelegramMsgId(msgId: number, sessionId: string): void {
  tgMsgToSession.set(msgId, sessionId);
}

async function sendTelegram(
  chatId: number,
  text: string
): Promise<number | null> {
  const token = env("TELEGRAM_BOT_TOKEN");
  if (!token || !chatId) return null;
  const res = await telegramFetch(
    `https://api.telegram.org/bot${token}/sendMessage`,
    {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        chat_id: chatId,
        text: text.slice(0, 4000),
        disable_web_page_preview: false,
      }),
    }
  );
  const json = (await res.json()) as {
    ok?: boolean;
    result?: { message_id?: number };
  };
  if (!json.ok) return null;
  return json.result?.message_id ?? null;
}

export function mergeChatHistory(
  ...parts: Array<ChatHistoryLine[] | undefined>
): ChatHistoryLine[] {
  const seen = new Set<string>();
  const out: ChatHistoryLine[] = [];
  for (const arr of parts) {
    if (!arr) continue;
    for (const line of arr) {
      const content = line.content?.trim();
      if (!content) continue;
      const key = `${line.role}:${content}`;
      if (seen.has(key)) continue;
      seen.add(key);
      out.push({
        role: line.role,
        content: content.slice(0, 2000),
      });
    }
  }
  return out.slice(-40);
}

function formatMetaLines(meta: WidgetHumanMeta): string {
  const lines: string[] = [];
  if (meta.pageUrl) lines.push(`Страница: ${meta.pageUrl.slice(0, 500)}`);
  if (meta.displayName) lines.push(`Имя: ${meta.displayName.slice(0, 120)}`);
  if (meta.userPhone) lines.push(`Телефон: ${meta.userPhone.slice(0, 40)}`);
  if (meta.userEmail) lines.push(`Email: ${meta.userEmail.slice(0, 80)}`);
  return lines.length ? lines.join("\n") + "\n" : "";
}

function historySummary(sessionId: string, meta: WidgetHumanMeta): string {
  const lines = mergeChatHistory(
    getHistory(sessionId).map((m) => ({
      role:
        m.role === "user"
          ? ("user" as const)
          : m.role === "assistant"
            ? ("assistant" as const)
            : ("system" as const),
      content: m.content,
    })),
    meta.chatHistory
  );
  if (!lines.length) return "";
  const last = lines.slice(-3);
  const preview = last
    .map((l) => {
      const label = l.role === "user" ? "Клиент" : l.role === "assistant" ? "Бот" : "—";
      const t = l.content.replace(/\s+/g, " ").trim();
      return `${label}: ${t.slice(0, 120)}${t.length > 120 ? "…" : ""}`;
    })
    .join("\n");
  return `\nПоследние реплики:\n${preview}\n`;
}

/** Компактное уведомление в Telegram с ссылкой на веб-панель (без полной истории). */
export async function notifyManagersCompact(
  sessionId: string,
  meta: WidgetHumanMeta,
  opts: { title: string; reason?: string; force?: boolean } = {
    title: "💬 Чат на сайте lvt.market",
  }
): Promise<void> {
  const chatId = parseManagersChatId();
  if (!chatId) return;
  if (!opts.force && activityNotified.has(sessionId)) return;
  activityNotified.add(sessionId);

  const link = buildManagerPanelUrl(sessionId);
  const reason = opts.reason ? `Причина: ${opts.reason}\n` : "";
  const text =
    `${opts.title}\n` +
    `Сессия: ${sessionId}\n` +
    formatMetaLines(meta) +
    reason +
    historySummary(sessionId, meta) +
    `\n📋 Открыть переписку:\n${link}\n\n` +
    `Ответьте reply на сообщения клиента ниже или в панели.\n` +
    `Закрыть: /closew ${sessionId}`;

  const msgId = await sendTelegram(chatId, text);
  if (msgId != null) registerTelegramMsgId(msgId, sessionId);
}

/** Первое сообщение клиента в виджете (режим ИИ). */
export async function notifyWidgetChatOpened(
  sessionId: string,
  meta: WidgetHumanMeta = {}
): Promise<void> {
  rememberWidgetMeta(sessionId, meta);
  await notifyManagersCompact(sessionId, meta, {
    title: "💬 Открыт чат на сайте lvt.market",
  });
}

export async function enterWidgetHumanMode(
  sessionId: string,
  meta: WidgetHumanMeta = {}
): Promise<{ ok: true; already: boolean }> {
  const already = humanSessions.has(sessionId);
  if (!already) {
    humanSessions.set(sessionId, {
      sessionId,
      meta,
      enteredAt: Date.now(),
    });
  } else {
    const st = humanSessions.get(sessionId);
    if (st) st.meta = { ...st.meta, ...meta };
  }

  if (!already) {
    await notifyManagersCompact(
      sessionId,
      humanSessions.get(sessionId)?.meta ?? meta,
      {
        title: "🟢 Чат с сайта — режим оператора",
        reason: meta.reason,
        force: true,
      }
    );
  }

  if (!already) {
    pushOut(
      sessionId,
      "Вы на связи с менеджером. Напишите вопрос — ответ придёт в этот чат.",
      "system"
    );
  }

  return { ok: true, already };
}

export async function forwardWidgetUserMessage(
  sessionId: string,
  text: string,
  meta: WidgetHumanMeta = {}
): Promise<void> {
  if (!humanSessions.has(sessionId)) {
    await enterWidgetHumanMode(sessionId, meta);
  } else {
    const st = humanSessions.get(sessionId);
    if (st) st.meta = { ...st.meta, ...meta };
  }

  const chatId = parseManagersChatId();
  if (!chatId) return;

  const st = humanSessions.get(sessionId);
  const metaLines = formatMetaLines(st?.meta ?? meta);
  const link = buildManagerPanelUrl(sessionId);
  const body =
    `🌐 Сообщение с сайта\n` +
    `Сессия: ${sessionId}\n` +
    metaLines +
    `---\n` +
    `👤 Клиент:\n${text.slice(0, 3500)}\n\n` +
    `📋 Панель: ${link}`;

  const msgId = await sendTelegram(chatId, body);
  if (msgId != null) registerTelegramMsgId(msgId, sessionId);
}

export function pushManagerReply(
  sessionId: string,
  text: string,
  operatorName?: string
): number {
  if (!humanSessions.has(sessionId)) {
    humanSessions.set(sessionId, {
      sessionId,
      meta: {},
      enteredAt: Date.now(),
    });
  }
  const name = operatorName?.trim().slice(0, 120) || undefined;
  return pushOut(sessionId, text, "manager", name);
}

export function leaveWidgetHumanMode(sessionId: string): void {
  humanSessions.delete(sessionId);
  pushOut(
    sessionId,
    "Диалог с менеджером завершён. Можете снова задать вопрос ИИ-консультанту.",
    "system"
  );
}

function parseChatHistoryFromBody(
  raw: unknown
): ChatHistoryLine[] | undefined {
  if (!Array.isArray(raw)) return undefined;
  const out: ChatHistoryLine[] = [];
  for (const item of raw) {
    if (!item || typeof item !== "object") continue;
    const row = item as { role?: string; content?: string };
    const content = row.content?.trim();
    if (!content) continue;
    const role =
      row.role === "user"
        ? "user"
        : row.role === "assistant"
          ? "assistant"
          : row.role === "system"
            ? "system"
            : null;
    if (!role) continue;
    out.push({ role, content: content.slice(0, 2000) });
  }
  return out.length ? out : undefined;
}

export function buildWidgetMetaFromBody(body: {
  page_url?: string;
  display_name?: string;
  user_phone?: string;
  user_email?: string;
  reason?: string;
  chat_history?: unknown;
}): WidgetHumanMeta {
  return {
    pageUrl: body.page_url?.trim() || undefined,
    displayName: body.display_name?.trim() || undefined,
    userPhone: body.user_phone?.trim() || undefined,
    userEmail: body.user_email?.trim() || undefined,
    reason: body.reason?.trim() || undefined,
    chatHistory: parseChatHistoryFromBody(body.chat_history),
  };
}
