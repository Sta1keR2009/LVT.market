import type { LookupResult } from "./types.js";
import { config } from "./config.js";
import { retry } from "./utils.js";

export class ExternalSource {
  async findByPartNumber(partNumber: string): Promise<Partial<LookupResult> | null> {
    if (!config.externalLookupEnabled) return null;
    // Placeholder for Mouser/DigiKey integration.
    // Kept as an async boundary with retry so provider integration can be plugged in safely.
    return retry(async () => {
      void partNumber;
      return null;
    }, 1);
  }
}
