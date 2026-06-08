import type { ChatMessage } from "./types.js";

const store = new Map<string, ChatMessage[]>();
const MAX_MESSAGES = 40;

export function getHistory(sessionId: string): ChatMessage[] {
  return store.get(sessionId) ?? [];
}

export function appendMessages(
  sessionId: string,
  messages: ChatMessage[]
): void {
  let h = store.get(sessionId) ?? [];
  h = [...h, ...messages];
  if (h.length > MAX_MESSAGES) h = h.slice(-MAX_MESSAGES);
  store.set(sessionId, h);
}

export function setHistory(sessionId: string, messages: ChatMessage[]): void {
  store.set(
    sessionId,
    messages.length > MAX_MESSAGES ? messages.slice(-MAX_MESSAGES) : messages
  );
}
