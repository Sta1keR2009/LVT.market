import { config } from "./config.js";

export async function submitLead(params: {
  name: string;
  phone: string;
  email?: string;
  message?: string;
  sessionId: string;
  productId?: number;
  consent: boolean;
}): Promise<{ ok: boolean; error?: string }> {
  const res = await fetch(config.leadApiUrl, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      name: params.name,
      phone: params.phone,
      email: params.email ?? "",
      message: params.message ?? "",
      session_id: params.sessionId,
      product_id: params.productId ?? 0,
      page_url: "telegram://bot",
      consent: params.consent,
    }),
    signal: AbortSignal.timeout(30000),
  });

  const data = (await res.json()) as { ok?: boolean; error?: string };
  if (!res.ok || !data.ok) {
    return { ok: false, error: data.error ?? `HTTP ${res.status}` };
  }
  return { ok: true };
}
