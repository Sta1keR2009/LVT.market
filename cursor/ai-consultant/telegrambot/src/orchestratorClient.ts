import { config } from "./config.js";
import type { PageContext } from "./types.js";

export interface ChatResponse {
  ok: boolean;
  reply?: string;
  error?: string;
  handoff?: boolean;
  handoff_reason?: string;
}

export async function askConsultant(params: {
  chatId: number;
  message: string;
  pageContext?: PageContext;
}): Promise<ChatResponse> {
  const sessionId = `telegram:${params.chatId}`;
  const res = await fetch(`${config.aiBackendUrl}/v1/chat`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-Session-Id": sessionId,
    },
    body: JSON.stringify({
      message: params.message,
      session_id: sessionId,
      channel: "telegram",
      page_context: params.pageContext,
    }),
    signal: AbortSignal.timeout(120000),
  });

  const data = (await res.json()) as ChatResponse;
  if (!res.ok || !data.ok) {
    return {
      ok: false,
      error: data.error ?? `HTTP ${res.status}`,
    };
  }
  return data;
}
