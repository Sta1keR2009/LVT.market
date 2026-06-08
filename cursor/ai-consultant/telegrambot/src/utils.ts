/** Извлечь product_id из ссылки lvt.market или числового ввода */
export function parseProductId(input: string): number | null {
  const t = input.trim();
  const direct = /^\d{1,10}$/.exec(t);
  if (direct) {
    const n = Number(direct[0]);
    return Number.isFinite(n) && n > 0 ? n : null;
  }

  const patterns = [
    /[?&]product_id=(\d+)/i,
    /[?&]ELEMENT_ID=(\d+)/i,
    /[?&]id=(\d+)/i,
    /\/catalog\/[^/]+\/(\d+)\/?/i,
    /\/product\/(\d+)\/?/i,
    /\/(\d{3,10})\/?(?:\?|$)/,
  ];

  for (const re of patterns) {
    const m = t.match(re);
    if (m?.[1]) {
      const n = Number(m[1]);
      if (Number.isFinite(n) && n > 0) return n;
    }
  }
  return null;
}

export function parseQuantity(text: string, defaultQty = 1): number {
  const m = text.match(/\b(\d{1,6})\b/);
  if (!m) return defaultQty;
  const n = Number(m[1]);
  return Number.isFinite(n) && n > 0 ? n : defaultQty;
}

export function normalizePhone(text: string): string {
  const digits = text.replace(/\D/g, "");
  if (digits.length === 11 && digits.startsWith("8")) {
    return "+7" + digits.slice(1);
  }
  if (digits.length === 11 && digits.startsWith("7")) {
    return "+" + digits;
  }
  if (digits.length === 10) {
    return "+7" + digits;
  }
  return text.trim();
}

export function isValidPhone(text: string): boolean {
  const d = text.replace(/\D/g, "");
  return d.length >= 10 && d.length <= 15;
}
