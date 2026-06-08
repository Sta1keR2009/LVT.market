export type Channel = "widget" | "telegram" | "whatsapp";

export interface ChatMessage {
  role: "user" | "assistant" | "system";
  content: string;
}

export interface ToolCallRecord {
  name: string;
  args: Record<string, unknown>;
  result: unknown;
  ms: number;
}

export interface AnalyticsEvent {
  ts: string;
  type:
    | "chat_turn"
    | "tool_call"
    | "handoff"
    | "session_start"
    | "error";
  channel?: Channel;
  session_id?: string;
  payload?: Record<string, unknown>;
}
