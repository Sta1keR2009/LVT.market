export type LookupResult = {
  partNumber: string;
  normalizedPartNumber: string;
  source: "bitrix" | "cache" | "external" | "not_found";
  confidence: "high" | "medium" | "low";
  productName?: string;
  manufacturer?: string;
  tnvedCode?: string;
  tnvedDescription?: string;
  dutyRatePercent?: number;
  vatRatePercent?: number;
  honestSignStatus: "required" | "not_required" | "unknown";
  notes: string[];
};

export type JobStatus = {
  id: string;
  status: "queued" | "processing" | "done" | "failed";
  error?: string;
  resultFile?: string;
  pdfFile?: string;
};
