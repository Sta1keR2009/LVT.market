import { createHmac, timingSafeEqual } from "crypto";

function env(name: string, fallback = ""): string {
  return process.env[name]?.trim() ?? fallback;
}

export function managerPanelSecret(): string {
  return (
    env("MANAGER_PANEL_SECRET") || env("WIDGET_BRIDGE_INTERNAL_KEY") || ""
  );
}

export function managerTokenTtlSec(): number {
  const raw = env("MANAGER_TOKEN_TTL_SEC", "604800");
  const n = Number(raw);
  return Number.isFinite(n) && n > 60 ? Math.floor(n) : 604800;
}

export function managerPanelBaseUrl(): string {
  return env(
    "MANAGER_PANEL_BASE_URL",
    "https://lvt.market/local/api/ai_manager_chat.php"
  ).replace(/\/$/, "");
}

function signPayload(sessionId: string, exp: number): string {
  const secret = managerPanelSecret();
  if (!secret) return "";
  const payload = `${sessionId}.${exp}`;
  return createHmac("sha256", secret).update(payload).digest("base64url");
}

export function createManagerToken(
  sessionId: string,
  ttlSec = managerTokenTtlSec()
): string {
  const exp = Math.floor(Date.now() / 1000) + ttlSec;
  const sig = signPayload(sessionId, exp);
  return `${exp}.${sig}`;
}

export function verifyManagerToken(
  sessionId: string,
  token: string
): boolean {
  const secret = managerPanelSecret();
  if (!secret || !sessionId || !token) return false;
  const parts = token.split(".");
  if (parts.length !== 2) return false;
  const exp = Number(parts[0]);
  const sig = parts[1];
  if (!Number.isFinite(exp) || !sig) return false;
  if (exp < Math.floor(Date.now() / 1000)) return false;

  const expected = signPayload(sessionId, exp);
  if (expected.length !== sig.length) return false;
  try {
    return timingSafeEqual(Buffer.from(expected), Buffer.from(sig));
  } catch {
    return false;
  }
}

export function buildManagerPanelUrl(sessionId: string): string {
  const token = createManagerToken(sessionId);
  const base = managerPanelBaseUrl();
  return `${base}?session=${encodeURIComponent(sessionId)}&token=${encodeURIComponent(token)}`;
}
