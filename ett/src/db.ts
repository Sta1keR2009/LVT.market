import fs from "node:fs";
import path from "node:path";
import Database from "better-sqlite3";
import type { JobRecord, JobStatus, LookupResult } from "./types.js";
import { nowIso } from "./utils.js";

type CachedLookup = LookupResult & { updatedAt: string };

export class AppDb {
  private db: Database.Database;

  constructor(private readonly sqlitePath: string) {
    fs.mkdirSync(path.dirname(sqlitePath), { recursive: true });
    this.db = new Database(sqlitePath);
    this.init();
  }

  private init() {
    this.db.exec(`
      CREATE TABLE IF NOT EXISTS jobs (
        id TEXT PRIMARY KEY,
        status TEXT NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        error TEXT,
        result_file TEXT,
        pdf_file TEXT
      );

      CREATE TABLE IF NOT EXISTS lookup_cache (
        part_number TEXT PRIMARY KEY,
        payload TEXT NOT NULL,
        updated_at TEXT NOT NULL
      );

      CREATE TABLE IF NOT EXISTS tnved_codes (
        code TEXT PRIMARY KEY,
        description TEXT NOT NULL,
        active INTEGER NOT NULL DEFAULT 1
      );

      CREATE TABLE IF NOT EXISTS duty_rates (
        code TEXT PRIMARY KEY,
        duty_rate_percent REAL,
        vat_rate_percent REAL
      );

      CREATE TABLE IF NOT EXISTS honest_sign_map (
        code TEXT PRIMARY KEY,
        status TEXT NOT NULL
      );
    `);
  }

  saveLookup(result: LookupResult) {
    const stmt = this.db.prepare(
      "INSERT INTO lookup_cache(part_number,payload,updated_at) VALUES(?,?,?) ON CONFLICT(part_number) DO UPDATE SET payload=excluded.payload, updated_at=excluded.updated_at"
    );
    stmt.run(result.normalizedPartNumber, JSON.stringify(result), nowIso());
  }

  getLookup(normalizedPart: string): CachedLookup | null {
    const row = this.db
      .prepare("SELECT payload, updated_at FROM lookup_cache WHERE part_number = ?")
      .get(normalizedPart) as { payload: string; updated_at: string } | undefined;
    if (!row) return null;
    return { ...(JSON.parse(row.payload) as LookupResult), updatedAt: row.updated_at };
  }

  createJob(id: string): JobRecord {
    const now = nowIso();
    this.db
      .prepare(
        "INSERT INTO jobs(id,status,created_at,updated_at) VALUES(?,?,?,?)"
      )
      .run(id, "queued", now, now);
    return this.getJob(id)!;
  }

  updateJob(id: string, patch: Partial<JobRecord>) {
    const current = this.getJob(id);
    if (!current) return;
    const next: JobRecord = {
      ...current,
      ...patch,
      updatedAt: nowIso()
    };
    this.db
      .prepare(
        "UPDATE jobs SET status=?, updated_at=?, error=?, result_file=?, pdf_file=? WHERE id=?"
      )
      .run(next.status, next.updatedAt, next.error, next.resultFile, next.pdfFile, id);
  }

  getJob(id: string): JobRecord | null {
    const row = this.db
      .prepare("SELECT * FROM jobs WHERE id=?")
      .get(id) as
      | {
          id: string;
          status: JobStatus;
          created_at: string;
          updated_at: string;
          error?: string;
          result_file?: string;
          pdf_file?: string;
        }
      | undefined;
    if (!row) return null;
    return {
      id: row.id,
      status: row.status,
      createdAt: row.created_at,
      updatedAt: row.updated_at,
      error: row.error ?? undefined,
      resultFile: row.result_file ?? undefined,
      pdfFile: row.pdf_file ?? undefined
    };
  }

  getDuty(code?: string): { dutyRatePercent?: number; vatRatePercent?: number } {
    if (!code) return {};
    const row = this.db
      .prepare("SELECT duty_rate_percent, vat_rate_percent FROM duty_rates WHERE code = ?")
      .get(code) as { duty_rate_percent: number | null; vat_rate_percent: number | null } | undefined;
    return {
      dutyRatePercent: row?.duty_rate_percent ?? undefined,
      vatRatePercent: row?.vat_rate_percent ?? undefined
    };
  }

  getHonestSign(code?: string): "required" | "not_required" | "unknown" {
    if (!code) return "unknown";
    const row = this.db
      .prepare("SELECT status FROM honest_sign_map WHERE code = ?")
      .get(code) as { status: string } | undefined;
    if (!row) return "unknown";
    if (row.status === "required" || row.status === "not_required") return row.status;
    return "unknown";
  }

  findTnvedByPrefix(prefix6: string): { code: string; description: string }[] {
    return this.db
      .prepare("SELECT code, description FROM tnved_codes WHERE code LIKE ? AND active = 1 LIMIT 5")
      .all(`${prefix6}%`) as { code: string; description: string }[];
  }

  getTnvedByCode(code?: string): { code: string; description: string } | null {
    if (!code) return null;
    const row = this.db
      .prepare("SELECT code, description FROM tnved_codes WHERE code = ? AND active = 1")
      .get(code) as { code: string; description: string } | undefined;
    return row ?? null;
  }

  upsertTnved(code: string, description: string) {
    this.db
      .prepare(
        "INSERT INTO tnved_codes(code,description,active) VALUES(?,?,1) ON CONFLICT(code) DO UPDATE SET description=excluded.description, active=1"
      )
      .run(code, description);
  }

  upsertDuty(code: string, duty: number | null, vat: number | null) {
    this.db
      .prepare(
        "INSERT INTO duty_rates(code,duty_rate_percent,vat_rate_percent) VALUES(?,?,?) ON CONFLICT(code) DO UPDATE SET duty_rate_percent=excluded.duty_rate_percent, vat_rate_percent=excluded.vat_rate_percent"
      )
      .run(code, duty, vat);
  }

  upsertHonestSign(code: string, status: "required" | "not_required" | "unknown") {
    this.db
      .prepare(
        "INSERT INTO honest_sign_map(code,status) VALUES(?,?) ON CONFLICT(code) DO UPDATE SET status=excluded.status"
      )
      .run(code, status);
  }
}
