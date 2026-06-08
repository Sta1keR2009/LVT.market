import type { ChatMessage } from "./types.js";
import { recordEvent } from "./analytics.js";

const OPERATOR_PATTERNS =
  /оператор|менеджер|живой\s+человек|переведи|свяжи\s+с\s+сотрудником|хочу\s+поговорить\s+с/i;

const BUY_SIGNAL_PATTERNS =
  /оформить\s+заказ|готов\s+купить|выставьте\s+сч[её]т|резерв|куплю|беру\s+все|оформляйте/i;

export interface HandoffCheckInput {
  lastUserMessage: string;
  assistantDraft?: string;
  /** 0..1 from model or heuristic */
  confidence?: number;
}

export interface HandoffResult {
  should_handoff: boolean;
  reason:
    | "user_requested_operator"
    | "buy_signal"
    | "low_confidence"
    | "none";
  summary_lines: string[];
}

export function buildConversationSummary(
  history: ChatMessage[],
  maxMessages = 12
): string[] {
  const slice = history.slice(-maxMessages);
  return slice.map((m) => `${m.role}: ${m.content}`.slice(0, 500));
}

export function shouldEscalate(input: HandoffCheckInput): HandoffResult {
  const u = input.lastUserMessage ?? "";
  if (OPERATOR_PATTERNS.test(u)) {
    return {
      should_handoff: true,
      reason: "user_requested_operator",
      summary_lines: [],
    };
  }
  if (BUY_SIGNAL_PATTERNS.test(u)) {
    return {
      should_handoff: true,
      reason: "buy_signal",
      summary_lines: [],
    };
  }
  const conf = input.confidence;
  if (typeof conf === "number" && conf < 0.35) {
    return {
      should_handoff: true,
      reason: "low_confidence",
      summary_lines: [],
    };
  }
  return { should_handoff: false, reason: "none", summary_lines: [] };
}

export async function notifyHandoff(params: {
  webhook_url?: string;
  session_id: string;
  reason: HandoffResult["reason"];
  summary: string[];
  channel: string;
}): Promise<{ ok: boolean; error?: string }> {
  recordEvent({
    type: "handoff",
    session_id: params.session_id,
    channel: params.channel as "widget" | "telegram" | "whatsapp",
    payload: { reason: params.reason, summary_lines: params.summary.length },
  });

  const url = params.webhook_url;
  if (!url) return { ok: true };

  try {
    const res = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        session_id: params.session_id,
        channel: params.channel,
        reason: params.reason,
        summary: params.summary,
        ts: new Date().toISOString(),
      }),
    });
    if (!res.ok)
      return { ok: false, error: `HTTP ${res.status}` };
    return { ok: true };
  } catch (e) {
    return {
      ok: false,
      error: e instanceof Error ? e.message : String(e),
    };
  }
}
