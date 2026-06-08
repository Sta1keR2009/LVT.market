import { AppDb } from "./db.js";
import type { LookupResult } from "./types.js";
import { isElectronicsCode } from "./utils.js";

export class Classifier {
  constructor(private readonly db: AppDb) {}

  classify(partial: Partial<LookupResult>, partNumber: string): Partial<LookupResult> {
    if (partial.tnvedCode && isElectronicsCode(partial.tnvedCode)) {
      const duty = this.db.getDuty(partial.tnvedCode);
      const official = this.db.getTnvedByCode(partial.tnvedCode);
      return {
        ...partial,
        tnvedDescription: official?.description ?? partial.tnvedDescription,
        ...duty,
        honestSignStatus: this.db.getHonestSign(partial.tnvedCode)
      };
    }

    const hs6 = partial.tnvedCode?.slice(0, 6);
    if (hs6) {
      const candidates = this.db.findTnvedByPrefix(hs6);
      const best = candidates.find((c) => isElectronicsCode(c.code));
      if (best) {
        const duty = this.db.getDuty(best.code);
        return {
          ...partial,
          tnvedCode: best.code,
          tnvedDescription: best.description,
          confidence: "medium",
          ...duty,
          honestSignStatus: this.db.getHonestSign(best.code),
          notes: [...(partial.notes ?? []), `Mapped by HS6 prefix ${hs6}`]
        };
      }
    }

    const guessedByPart = this.guessByPartNumber(partNumber);
    if (guessedByPart) {
      const official = this.db.getTnvedByCode(guessedByPart.code);
      const duty = this.db.getDuty(guessedByPart.code);
      return {
        ...partial,
        tnvedCode: guessedByPart.code,
        tnvedDescription: official?.description,
        confidence: guessedByPart.confidence,
        ...duty,
        honestSignStatus: this.db.getHonestSign(guessedByPart.code),
        notes: [
          ...(partial.notes ?? []),
          guessedByPart.note,
          official
            ? "Description loaded from official TN VED dictionary"
            : "Official TN VED description not found in local dictionary"
        ]
      };
    }

    return {
      ...partial,
      confidence: partial.confidence ?? "low",
      honestSignStatus: partial.honestSignStatus ?? "unknown",
      notes: [...(partial.notes ?? []), `No confident TN VED mapping for ${partNumber}`]
    };
  }

  private guessByPartNumber(
    partNumber: string
  ): { code: string; confidence: "low" | "medium"; note: string } | null {
    const pn = partNumber.toUpperCase();

    // Broad component-class heuristics for MVP (electronics-only scope).
    if (
      /^(RT|TL|LM|LT|TPS|AP|MP|STM|AD|MAX|PIC|ATMEGA|ESP|CH|INA|MCP|SN)/.test(pn) ||
      /(QFN|BGA|TSSOP|MSOP|SOIC|LQFP)/.test(pn)
    ) {
      return {
        code: "8542399000",
        confidence: "medium",
        note: "Mapped by component part-number heuristic (IC class)"
      };
    }

    if (/(OSC|CRYSTAL|XTAL|MHZ)/.test(pn)) {
      return {
        code: "8541600000",
        confidence: "medium",
        note: "Mapped by component part-number heuristic (oscillator/crystal)"
      };
    }

    if (/(CONN|TERMINAL|HEADER|SOCKET|JST|MOLEX)/.test(pn)) {
      return {
        code: "8536901000",
        confidence: "low",
        note: "Mapped by component part-number heuristic (connector class)"
      };
    }

    // Default electronics fallback to avoid empty result in MVP.
    return {
      code: "8542399000",
      confidence: "low",
      note: "Mapped by default electronics fallback (manual verification required)"
    };
  }
}
