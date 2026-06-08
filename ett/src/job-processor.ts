import fs from "node:fs";
import path from "node:path";
import crypto from "node:crypto";
import ExcelJS from "exceljs";
import PDFDocument from "pdfkit";
import { AppDb } from "./db.js";
import type { LookupService } from "./lookup-service.js";
import { normalizePartNumber } from "./utils.js";

type RowRecord = Record<string, string>;

export class JobProcessor {
  constructor(
    private readonly db: AppDb,
    private readonly lookupService: LookupService,
    private readonly resultsDir: string
  ) {}

  async enqueue(filePath: string, selectedColumns?: string[]): Promise<string> {
    const id = crypto.randomUUID();
    this.db.createJob(id);
    setImmediate(async () => {
      try {
        this.db.updateJob(id, { status: "processing" });
        const rows = await this.readRows(filePath);
        const output = await this.processRows(rows);
        const xlsxFile = `${id}.xlsx`;
        const pdfFile = `${id}.pdf`;
        await this.writeXlsx(path.join(this.resultsDir, xlsxFile), output, selectedColumns);
        await this.writePdf(path.join(this.resultsDir, pdfFile), output);
        this.db.updateJob(id, { status: "done", resultFile: xlsxFile, pdfFile });
      } catch (error) {
        this.db.updateJob(id, {
          status: "failed",
          error: error instanceof Error ? error.message : String(error)
        });
      }
    });
    return id;
  }

  private async readRows(filePath: string): Promise<RowRecord[]> {
    const wb = new ExcelJS.Workbook();
    if (filePath.endsWith(".csv")) {
      await wb.csv.readFile(filePath);
    } else {
      await wb.xlsx.readFile(filePath);
    }
    const ws = wb.worksheets[0];
    if (!ws) return [];
    const header = ws.getRow(1).values as (string | number | undefined)[];
    const headers = header.map((v) => String(v ?? "").trim().toLowerCase());
    const partIndex = headers.findIndex((h) =>
      ["партномер", "partnumber", "part", "артикул", "articul"].includes(h)
    );
    if (partIndex <= 0) {
      throw new Error("Column 'партномер' not found");
    }
    const result: RowRecord[] = [];
    ws.eachRow((row, index) => {
      if (index === 1) return;
      const part = String(row.getCell(partIndex).value ?? "").trim();
      if (!part) return;
      result.push({ partNumber: part });
    });
    return result;
  }

  private async processRows(rows: RowRecord[]) {
    const out: Array<RowRecord> = [];
    for (const row of rows) {
      const normalized = normalizePartNumber(row.partNumber);
      const lookup = await this.lookupService.lookup(normalized);
      out.push({
        partNumber: row.partNumber,
        normalizedPartNumber: normalized,
        source: lookup.source,
        confidence: lookup.confidence,
        tnvedCode: lookup.tnvedCode ?? "",
        dutyRatePercent: String(lookup.dutyRatePercent ?? ""),
        vatRatePercent: String(lookup.vatRatePercent ?? ""),
        honestSignStatus: lookup.honestSignStatus,
        notes: lookup.notes.join("; ")
      });
    }
    return out;
  }

  private async writeXlsx(filePath: string, rows: RowRecord[], selectedColumns?: string[]) {
    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet("result");
    const allColumns = [
      { header: "partNumber", key: "partNumber", width: 26 },
      { header: "normalizedPartNumber", key: "normalizedPartNumber", width: 26 },
      { header: "source", key: "source", width: 14 },
      { header: "confidence", key: "confidence", width: 14 },
      { header: "tnvedCode", key: "tnvedCode", width: 16 },
      { header: "dutyRatePercent", key: "dutyRatePercent", width: 16 },
      { header: "vatRatePercent", key: "vatRatePercent", width: 16 },
      { header: "honestSignStatus", key: "honestSignStatus", width: 18 },
      { header: "notes", key: "notes", width: 60 }
    ];
    ws.columns =
      selectedColumns && selectedColumns.length > 0
        ? allColumns.filter((c) => selectedColumns.includes(String(c.key)))
        : allColumns;
    rows.forEach((r) => ws.addRow(r));
    await wb.xlsx.writeFile(filePath);
  }

  private async writePdf(filePath: string, rows: RowRecord[]) {
    await new Promise<void>((resolve, reject) => {
      const doc = new PDFDocument({ margin: 24 });
      const stream = fs.createWriteStream(filePath);
      doc.pipe(stream);
      doc.fontSize(16).text("ETT/TN VED summary");
      doc.moveDown(0.5);
      rows.slice(0, 50).forEach((row, idx) => {
        doc
          .fontSize(10)
          .text(
            `${idx + 1}. ${row.partNumber} -> ${row.tnvedCode || "N/A"}, duty ${
              row.dutyRatePercent || "-"
            }%, CZ: ${row.honestSignStatus}`
          );
      });
      if (rows.length > 50) {
        doc.moveDown(1).text(`Only first 50 rows are shown. Full export is in XLSX.`);
      }
      doc.end();
      stream.on("finish", () => resolve());
      stream.on("error", (err) => reject(err));
    });
  }
}
