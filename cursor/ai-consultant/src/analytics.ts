import type { AnalyticsEvent, Channel } from "./types.js";

const events: AnalyticsEvent[] = [];
const MAX = 5000;

export function recordEvent(e: Omit<AnalyticsEvent, "ts">): AnalyticsEvent {
  const full: AnalyticsEvent = { ...e, ts: new Date().toISOString() };
  events.push(full);
  if (events.length > MAX) events.splice(0, events.length - MAX);
  return full;
}

export function recordChatTurn(params: {
  channel: Channel;
  session_id: string;
  user_chars: number;
  assistant_chars: number;
  tool_calls: number;
  handoff_triggered: boolean;
  latency_ms: number;
}): void {
  recordEvent({
    type: "chat_turn",
    channel: params.channel,
    session_id: params.session_id,
    payload: {
      user_chars: params.user_chars,
      assistant_chars: params.assistant_chars,
      tool_calls: params.tool_calls,
      handoff_triggered: params.handoff_triggered,
      latency_ms: params.latency_ms,
    },
  });
}

export function getSummary(): {
  total_turns: number;
  handoffs: number;
  by_channel: Record<string, number>;
  avg_latency_ms: number;
} {
  const turns = events.filter((e) => e.type === "chat_turn");
  const handoffs = events.filter((e) => e.type === "handoff").length;
  const by_channel: Record<string, number> = {};
  let sumLat = 0;
  for (const t of turns) {
    const ch = t.channel ?? "unknown";
    by_channel[ch] = (by_channel[ch] ?? 0) + 1;
    const lat = (t.payload?.latency_ms as number) ?? 0;
    sumLat += lat;
  }
  return {
    total_turns: turns.length,
    handoffs,
    by_channel,
    avg_latency_ms:
      turns.length === 0 ? 0 : Math.round(sumLat / turns.length),
  };
}

export function queryEvents(filter?: {
  type?: AnalyticsEvent["type"];
  session_id?: string;
  limit?: number;
}): AnalyticsEvent[] {
  let list = [...events].reverse();
  if (filter?.type) list = list.filter((e) => e.type === filter.type);
  if (filter?.session_id)
    list = list.filter((e) => e.session_id === filter.session_id);
  const lim = filter?.limit ?? 100;
  return list.slice(0, lim);
}
