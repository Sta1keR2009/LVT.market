export type LookupSource = "bitrix" | "cache" | "external" | "not_found";
export type Confidence = "high" | "medium" | "low";
export type HonestSignStatus = "required" | "not_required" | "unknown";

export type LookupResult = {
  partNumber: string;
  normalizedPartNumber: string;
  source: LookupSource;
  confidence: Confidence;
  productName?: string;
  manufacturer?: string;
  tnvedCode?: string;
  tnvedDescription?: string;
  dutyRatePercent?: number;
  vatRatePercent?: number;
  honestSignStatus: HonestSignStatus;
  notes: string[];
};

export type JobStatus = "queued" | "processing" | "done" | "failed";

export type JobRecord = {
  id: string;
  status: JobStatus;
  createdAt: string;
  updatedAt: string;
  error?: string;
  resultFile?: string;
  pdfFile?: string;
};
