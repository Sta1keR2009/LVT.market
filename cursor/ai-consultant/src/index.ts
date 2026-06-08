import "dotenv/config";
import Fastify from "fastify";
import cors from "@fastify/cors";
import { randomUUID } from "crypto";
import { runAgentTurn } from "./orchestrator.js";
import { resolveLlmFromEnv } from "./llmClient.js";
import type { Channel } from "./types.js";
import { appendMessages, getHistory } from "./session.js";
import {
  recordChatTurn,
  recordEvent,
  getSummary,
  queryEvents,
} from "./analytics.js";
import { telegramFetch } from "./telegramProxy.js";
import {
  buildWidgetMetaFromBody,
  enterWidgetHumanMode,
  forwardWidgetUserMessage,
  leaveWidgetHumanMode,
  pollWidgetMessages,
  pushManagerReply,
  isWidgetHumanMode,
  notifyWidgetChatOpened,
  getWidgetSessionMeta,
  rememberWidgetMeta,
} from "./widgetBridge.js";
import { verifyManagerToken } from "./managerTokens.js";
import {
  appendTranscript,
  getTranscript,
  getTranscriptMeta,
} from "./transcriptStore.js";

function env(name: string, fallback = ""): string {
  return process.env[name]?.trim() ?? fallback;
}

function parseOrigins(): string[] {
  const raw = env("PUBLIC_ORIGIN", "https://lvt.market");
  return raw
    .split(",")
    .map((s) => s.trim())
    .filter(Boolean);
}

function orchestratorEnv() {
  const llm = resolveLlmFromEnv();
  return {
    llmClient: llm.client,
    llmModel: llm.model,
    llmProvider: llm.provider,
    bitrixBaseUrl: env("BITRIX_BASE_URL", "https://lvt.market"),
    catalogApiUrl: env("CATALOG_API_URL") || undefined,
    analogsApiUrl: env("ANALOGS_API_URL") || undefined,
    handoffWebhookUrl: env("HANDOFF_WEBHOOK_URL") || undefined,
  };
}

const app = Fastify({ logger: true });

await app.register(cors, {
  origin: parseOrigins(),
  methods: ["GET", "POST", "OPTIONS"],
  allowedHeaders: ["Content-Type", "X-Session-Id", "X-Internal-Key"],
});

function widgetInternalKeyOk(req: { headers: Record<string, unknown> }): boolean {
  const expected = env("WIDGET_BRIDGE_INTERNAL_KEY");
  if (!expected) return true;
  return req.headers["x-internal-key"] === expected;
}

function managerTokenFromReq(
  req: { query?: Record<string, string>; body?: Record<string, unknown> }
): { sessionId: string; token: string } | null {
  const q = (req.query ?? {}) as Record<string, string>;
  const b = (req.body ?? {}) as Record<string, string>;
  const sessionId = (q.session_id ?? b.session_id ?? q.session ?? b.session ?? "").trim();
  const token = (q.token ?? b.token ?? "").trim();
  if (!sessionId || !token) return null;
  if (!verifyManagerToken(sessionId, token)) return null;
  return { sessionId, token };
}

app.get("/health", async () => ({
  ok: true,
  llm_provider: env("LLM_PROVIDER", "openai"),
  llm_model_hint:
    env("LLM_PROVIDER", "openai").toLowerCase() === "deepseek"
      ? env("DEEPSEEK_MODEL", "deepseek-chat")
      : env("LLM_PROVIDER", "openai").toLowerCase() === "qwen"
        ? env("QWEN_MODEL", "qwen-plus")
        : env("OPENAI_MODEL", "gpt-4o-mini"),
}));

app.get("/v1/analytics/summary", async (req, reply) => {
  const key = req.headers["x-internal-key"];
  if (env("INTERNAL_ANALYTICS_KEY") && key !== env("INTERNAL_ANALYTICS_KEY")) {
    reply.code(403);
    return { ok: false };
  }
  return getSummary();
});

app.get("/v1/analytics/events", async (req, reply) => {
  const key = req.headers["x-internal-key"];
  if (env("INTERNAL_ANALYTICS_KEY") && key !== env("INTERNAL_ANALYTICS_KEY")) {
    reply.code(403);
    return { ok: false };
  }
  const q = req.query as Record<string, string>;
  return {
    events: queryEvents({
      type: q.type as "chat_turn" | "handoff" | undefined,
      session_id: q.session_id,
      limit: q.limit ? Number(q.limit) : 50,
    }),
  };
});

app.post("/v1/chat", async (req, reply) => {
  const body = req.body as {
    message?: string;
    session_id?: string;
    channel?: Channel;
    page_url?: string;
    display_name?: string;
    page_context?: {
      product_id?: number;
      city?: string;
      supplier_min_lead_days?: number;
    };
  };

  const text = (body.message ?? "").trim();
  if (!text || text.length > 8000) {
    reply.code(400);
    return { ok: false, error: "invalid_message" };
  }

  const channel: Channel = body.channel ?? "widget";
  let sessionId =
    (req.headers["x-session-id"] as string) || body.session_id || "";
  if (!sessionId) sessionId = `${channel}:${randomUUID()}`;

  if (channel === "widget" && isWidgetHumanMode(sessionId)) {
    reply.code(409);
    return {
      ok: false,
      error: "human_mode",
      session_id: sessionId,
      human_mode: true,
    };
  }

  const oenv = orchestratorEnv();
  const history = getHistory(sessionId);
  const t0 = Date.now();

  try {
    const result = await runAgentTurn({
      env: oenv,
      channel,
      session_id: sessionId,
      userText: text,
      history,
      pageContext: body.page_context,
    });

    appendMessages(sessionId, [
      { role: "user", content: text },
      { role: "assistant", content: result.reply },
    ]);
    appendTranscript(sessionId, { role: "user", content: text });
    appendTranscript(sessionId, { role: "assistant", content: result.reply });

    if (channel === "widget") {
      const wmeta = {
        pageUrl: body.page_url?.trim() || undefined,
        displayName: body.display_name?.trim() || undefined,
      };
      rememberWidgetMeta(sessionId, wmeta);
      void notifyWidgetChatOpened(sessionId, wmeta);
    }

    recordChatTurn({
      channel,
      session_id: sessionId,
      user_chars: text.length,
      assistant_chars: result.reply.length,
      tool_calls: result.tool_calls.length,
      handoff_triggered: result.handoff,
      latency_ms: Date.now() - t0,
    });

    let humanMode = false;
    if (channel === "widget" && result.handoff) {
      await enterWidgetHumanMode(sessionId, {
        reason: result.handoff_reason ?? "эскалация от ИИ",
      });
      humanMode = true;
    }

    return {
      ok: true,
      session_id: sessionId,
      reply: result.reply,
      handoff: result.handoff,
      handoff_reason: result.handoff_reason,
      human_mode: humanMode,
      tool_calls: result.tool_calls.length,
    };
  } catch (e) {
    recordEvent({
      type: "error",
      channel,
      session_id: sessionId,
      payload: {
        message: e instanceof Error ? e.message : String(e),
      },
    });
    req.log.error(e);
    reply.code(500);
    return {
      ok: false,
      error: e instanceof Error ? e.message : "internal_error",
    };
  }
});

app.post("/v1/widget/human/enter", async (req, reply) => {
  const body = req.body as {
    session_id?: string;
    page_url?: string;
    display_name?: string;
    user_phone?: string;
    user_email?: string;
    reason?: string;
    chat_history?: unknown;
  };
  let sessionId =
    (req.headers["x-session-id"] as string) || body.session_id || "";
  if (!sessionId) {
    reply.code(400);
    return { ok: false, error: "session_id" };
  }
  const meta = buildWidgetMetaFromBody(body);
  const r = await enterWidgetHumanMode(sessionId, meta);
  return { ok: true, session_id: sessionId, human_mode: true, already: r.already };
});

app.post("/v1/widget/human/message", async (req, reply) => {
  const body = req.body as {
    session_id?: string;
    message?: string;
    page_url?: string;
    display_name?: string;
    user_phone?: string;
    user_email?: string;
    chat_history?: unknown;
  };
  let sessionId =
    (req.headers["x-session-id"] as string) || body.session_id || "";
  const text = (body.message ?? "").trim();
  if (!sessionId || !text || text.length > 8000) {
    reply.code(400);
    return { ok: false, error: "invalid" };
  }
  const meta = buildWidgetMetaFromBody(body);
  appendTranscript(sessionId, { role: "user", content: text });
  await forwardWidgetUserMessage(sessionId, text, meta);
  return { ok: true, session_id: sessionId, human_mode: true };
});

app.get("/v1/widget/human/poll", async (req, reply) => {
  const q = req.query as Record<string, string>;
  const sessionId = q.session_id?.trim() ?? "";
  const since = q.since ? Number(q.since) : 0;
  if (!sessionId) {
    reply.code(400);
    return { ok: false, error: "session_id" };
  }
  const messages = pollWidgetMessages(sessionId, since);
  return {
    ok: true,
    session_id: sessionId,
    human_mode: isWidgetHumanMode(sessionId),
    messages,
  };
});

app.post("/v1/widget/human/leave", async (req, reply) => {
  const body = req.body as { session_id?: string };
  let sessionId =
    (req.headers["x-session-id"] as string) || body.session_id || "";
  if (!sessionId) {
    reply.code(400);
    return { ok: false, error: "session_id" };
  }
  leaveWidgetHumanMode(sessionId);
  return { ok: true, session_id: sessionId, human_mode: false };
});

app.post("/v1/internal/widget/manager-reply", async (req, reply) => {
  if (!widgetInternalKeyOk(req)) {
    reply.code(403);
    return { ok: false };
  }
  const body = req.body as {
    session_id?: string;
    text?: string;
    operator_name?: string;
  };
  const sessionId = body.session_id?.trim() ?? "";
  const text = (body.text ?? "").trim();
  if (!sessionId || !text) {
    reply.code(400);
    return { ok: false, error: "invalid" };
  }
  const opName = body.operator_name?.trim();
  const id = pushManagerReply(sessionId, text.slice(0, 4000), opName);
  appendTranscript(sessionId, {
    role: "manager",
    content: text.slice(0, 4000),
    operator_name: opName,
  });
  return { ok: true, session_id: sessionId, message_id: id };
});

app.get("/v1/manager/session", async (req, reply) => {
  const auth = managerTokenFromReq({ query: req.query as Record<string, string> });
  if (!auth) {
    reply.code(403);
    return { ok: false, error: "forbidden" };
  }
  const { sessionId } = auth;
  const meta = getWidgetSessionMeta(sessionId) ?? {};
  const transcript = getTranscript(sessionId);
  const tm = getTranscriptMeta(sessionId);
  return {
    ok: true,
    session_id: sessionId,
    human_mode: isWidgetHumanMode(sessionId),
    meta: {
      page_url: meta.pageUrl,
      display_name: meta.displayName,
      user_phone: meta.userPhone,
      user_email: meta.userEmail,
    },
    messages: transcript,
    last_id: tm.last_id,
  };
});

app.get("/v1/manager/poll", async (req, reply) => {
  const q = req.query as Record<string, string>;
  const auth = managerTokenFromReq({ query: q });
  if (!auth) {
    reply.code(403);
    return { ok: false, error: "forbidden" };
  }
  const since = q.since ? Number(q.since) : 0;
  const messages = getTranscript(auth.sessionId, since);
  return {
    ok: true,
    session_id: auth.sessionId,
    human_mode: isWidgetHumanMode(auth.sessionId),
    messages,
    last_id: getTranscriptMeta(auth.sessionId).last_id,
  };
});

app.post("/v1/manager/takeover", async (req, reply) => {
  const body = req.body as {
    session_id?: string;
    token?: string;
    operator_name?: string;
  };
  const auth = managerTokenFromReq({
    query: {},
    body: body as Record<string, unknown>,
  });
  if (!auth) {
    reply.code(403);
    return { ok: false, error: "forbidden" };
  }
  const opName = body.operator_name?.trim() || "Менеджер";
  await enterWidgetHumanMode(auth.sessionId, {
    reason: `оператор ${opName} (веб-панель)`,
  });
  appendTranscript(auth.sessionId, {
    role: "system",
    content: `${opName} подключился к чату.`,
  });
  return {
    ok: true,
    session_id: auth.sessionId,
    human_mode: true,
  };
});

app.post("/v1/manager/reply", async (req, reply) => {
  const body = req.body as {
    session_id?: string;
    token?: string;
    text?: string;
    operator_name?: string;
  };
  const auth = managerTokenFromReq({
    query: {},
    body: body as Record<string, unknown>,
  });
  if (!auth) {
    reply.code(403);
    return { ok: false, error: "forbidden" };
  }
  const text = (body.text ?? "").trim();
  if (!text) {
    reply.code(400);
    return { ok: false, error: "invalid" };
  }
  const opName = body.operator_name?.trim() || "Менеджер";
  if (!isWidgetHumanMode(auth.sessionId)) {
    await enterWidgetHumanMode(auth.sessionId, {
      reason: `ответ от ${opName} (веб-панель)`,
    });
  }
  const id = pushManagerReply(auth.sessionId, text.slice(0, 4000), opName);
  appendTranscript(auth.sessionId, {
    role: "manager",
    content: text.slice(0, 4000),
    operator_name: opName,
  });
  return {
    ok: true,
    session_id: auth.sessionId,
    message_id: id,
    human_mode: true,
  };
});

app.post("/v1/manager/close", async (req, reply) => {
  const body = req.body as { session_id?: string; token?: string };
  const auth = managerTokenFromReq({
    query: {},
    body: body as Record<string, unknown>,
  });
  if (!auth) {
    reply.code(403);
    return { ok: false, error: "forbidden" };
  }
  leaveWidgetHumanMode(auth.sessionId);
  appendTranscript(auth.sessionId, {
    role: "system",
    content: "Диалог с менеджером завершён.",
  });
  return { ok: true, session_id: auth.sessionId, human_mode: false };
});

app.post("/v1/internal/widget/close", async (req, reply) => {
  if (!widgetInternalKeyOk(req)) {
    reply.code(403);
    return { ok: false };
  }
  const body = req.body as { session_id?: string };
  const sessionId = body.session_id?.trim() ?? "";
  if (!sessionId) {
    reply.code(400);
    return { ok: false, error: "session_id" };
  }
  leaveWidgetHumanMode(sessionId);
  return { ok: true, session_id: sessionId, human_mode: false };
});

app.post("/v1/channels/telegram/webhook", async (req, reply) => {
  if (env("ENABLE_TELEGRAM_WEBHOOK", "0") !== "1") {
    reply.code(404);
    return { ok: false, error: "telegram_webhook_disabled" };
  }
  const token = env("TELEGRAM_BOT_TOKEN");
  const update = req.body as {
    message?: { chat?: { id: number }; text?: string };
  };
  const chatId = update.message?.chat?.id;
  const text = update.message?.text?.trim();
  if (!token || !chatId || !text) return { ok: true };

  const sessionId = `telegram:${chatId}`;
  const oenv = orchestratorEnv();
  const history = getHistory(sessionId);
  const result = await runAgentTurn({
    env: oenv,
    channel: "telegram",
    session_id: sessionId,
    userText: text,
    history,
  });
  appendMessages(sessionId, [
    { role: "user", content: text },
    { role: "assistant", content: result.reply },
  ]);
  const tgUrl = `https://api.telegram.org/bot${token}/sendMessage`;
  await telegramFetch(tgUrl, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      chat_id: chatId,
      text: result.reply.slice(0, 4000),
    }),
  });
  return { ok: true };
});

app.get("/v1/channels/whatsapp/webhook", async (req, reply) => {
  const q = req.query as Record<string, string>;
  const verify = env("WHATSAPP_VERIFY_TOKEN");
  if (q["hub.mode"] === "subscribe" && q["hub.verify_token"] === verify) {
    reply.code(200);
    return Number(q["hub.challenge"] ?? "0");
  }
  reply.code(403);
  return "Forbidden";
});

app.post("/v1/channels/whatsapp/webhook", async (req) => {
  const body = req.body as {
    entry?: Array<{
      changes?: Array<{
        value?: {
          messages?: Array<{ from?: string; text?: { body?: string } }>;
        };
      }>;
    }>;
  };
  const msg = body.entry?.[0]?.changes?.[0]?.value?.messages?.[0];
  const from = msg?.from;
  const text = msg?.text?.body?.trim();
  if (!from || !text) return { ok: true };

  const sessionId = `whatsapp:${from}`;
  const oenv = orchestratorEnv();
  const history = getHistory(sessionId);
  const result = await runAgentTurn({
    env: oenv,
    channel: "whatsapp",
    session_id: sessionId,
    userText: text,
    history,
  });
  appendMessages(sessionId, [
    { role: "user", content: text },
    { role: "assistant", content: result.reply },
  ]);

  const phoneId = env("WHATSAPP_PHONE_NUMBER_ID");
  const wtoken = env("WHATSAPP_ACCESS_TOKEN");
  if (phoneId && wtoken) {
    await fetch(`https://graph.facebook.com/v21.0/${phoneId}/messages`, {
      method: "POST",
      headers: {
        Authorization: `Bearer ${wtoken}`,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        messaging_product: "whatsapp",
        to: from,
        type: "text",
        text: { body: result.reply.slice(0, 4096) },
      }),
    });
  }
  return { ok: true };
});

const port = Number(env("PORT", "3847"));
const host = env("HOST", "127.0.0.1");

try {
  await app.listen({ port, host });
  app.log.info(`listening http://${host}:${port}`);
} catch (e) {
  app.log.error(e);
  process.exit(1);
}
