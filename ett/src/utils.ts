export function normalizePartNumber(input: string): string {
  return input.trim().toUpperCase().replace(/\s+/g, "").replace(/[^\w.-]/g, "");
}

export function compactPartNumber(input: string): string {
  return normalizePartNumber(input).replace(/[-._]/g, "");
}

export function nowIso(): string {
  return new Date().toISOString();
}

export function isElectronicsCode(code?: string): boolean {
  if (!code) return false;
  return /^85/.test(code) || /^84(71|72|73|74|75|76)/.test(code);
}

export async function retry<T>(fn: () => Promise<T>, retries = 2): Promise<T> {
  let lastError: unknown;
  for (let i = 0; i <= retries; i += 1) {
    try {
      return await fn();
    } catch (error) {
      lastError = error;
      await new Promise((resolve) => setTimeout(resolve, 300 * (i + 1)));
    }
  }
  throw lastError;
}
