import { BitrixSource } from "./bitrix-source.js";
import { AppDb } from "./db.js";
import { ExternalSource } from "./external-source.js";
import { Classifier } from "./classifier.js";
import type { LookupResult } from "./types.js";
import { normalizePartNumber } from "./utils.js";

export class LookupService {
  private readonly classifier: Classifier;

  constructor(
    private readonly db: AppDb,
    private readonly bitrix: BitrixSource,
    private readonly external: ExternalSource
  ) {
    this.classifier = new Classifier(db);
  }

  async lookup(partNumberRaw: string): Promise<LookupResult> {
    const normalized = normalizePartNumber(partNumberRaw);
    if (!normalized) {
      return {
        partNumber: partNumberRaw,
        normalizedPartNumber: "",
        source: "not_found",
        confidence: "low",
        honestSignStatus: "unknown",
        notes: ["Part number is empty"]
      };
    }

    const cached = this.db.getLookup(normalized);
    if (cached) {
      const hasLegacyNoMapping = cached.notes.some((n) =>
        n.startsWith("No confident TN VED mapping for")
      );
      const official = this.db.getTnvedByCode(cached.tnvedCode);
      const hasOutdatedDescription =
        Boolean(cached.tnvedCode) &&
        Boolean(official?.description) &&
        cached.tnvedDescription !== official?.description;
      const isAuthoritativeCache = cached.source === "bitrix" && cached.confidence === "high";
      if (!hasLegacyNoMapping && !hasOutdatedDescription && isAuthoritativeCache) {
        return { ...cached, source: "cache" };
      }
    }

    const bitrixFound = await this.bitrix.findByPartNumber(normalized);
    const externalFound = bitrixFound ? null : await this.external.findByPartNumber(normalized);
    const base: LookupResult = {
      partNumber: partNumberRaw,
      normalizedPartNumber: normalized,
      source: bitrixFound ? "bitrix" : externalFound ? "external" : "not_found",
      confidence: bitrixFound ? "high" : externalFound ? "medium" : "low",
      honestSignStatus: "unknown",
      notes: []
    };

    const classified = this.classifier.classify(
      { ...base, ...(bitrixFound ?? {}), ...(externalFound ?? {}) },
      normalized
    );
    const result: LookupResult = {
      ...base,
      ...classified,
      notes: classified.notes ?? base.notes
    };
    this.db.saveLookup(result);
    return result;
  }
}
