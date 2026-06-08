export type TranscriptRole = "user" | "assistant" | "manager" | "system";

export type TranscriptLine = {
  id: number;
  role: TranscriptRole;
  content: string;
  ts: number;
  operator_name?: string;
};

const store = new Map<string, TranscriptLine[]>();
let nextId = 1;
const MAX_LINES = 200;

export function appendTranscript(
  sessionId: string,
  line: {
    role: TranscriptRole;
    content: string;
    operator_name?: string;
    ts?: number;
  }
): number {
  const content = line.content?.trim();
  if (!content) return 0;

  const id = nextId++;
  const row: TranscriptLine = {
    id,
    role: line.role,
    content: content.slice(0, 4000),
    ts: line.ts ?? Date.now(),
  };
  if (line.operator_name?.trim()) {
    row.operator_name = line.operator_name.trim().slice(0, 120);
  }

  let list = store.get(sessionId) ?? [];
  list.push(row);
  if (list.length > MAX_LINES) list = list.slice(-MAX_LINES);
  store.set(sessionId, list);
  return id;
}

export function getTranscript(
  sessionId: string,
  since = 0
): TranscriptLine[] {
  const list = store.get(sessionId) ?? [];
  if (since <= 0) return [...list];
  return list.filter((l) => l.id > since);
}

export function getTranscriptMeta(sessionId: string): {
  count: number;
  last_id: number;
} {
  const list = store.get(sessionId) ?? [];
  const last = list.length ? list[list.length - 1].id : 0;
  return { count: list.length, last_id: last };
}
