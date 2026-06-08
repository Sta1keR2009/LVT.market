import fs from "node:fs";
import path from "node:path";
import { AppDb } from "../src/db.js";
import { config } from "../src/config.js";

const input = process.argv[2] ?? path.resolve(process.cwd(), "data/honest-sign.csv");
if (!fs.existsSync(input)) {
  console.error(`File not found: ${input}`);
  process.exit(1);
}

const db = new AppDb(config.sqlitePath);
const lines = fs.readFileSync(input, "utf8").split(/\r?\n/).filter(Boolean);
for (const line of lines.slice(1)) {
  const [code, status] = line.split(",");
  if (!code) continue;
  const normalizedStatus =
    status?.trim() === "required" || status?.trim() === "not_required"
      ? (status.trim() as "required" | "not_required")
      : "unknown";
  db.upsertHonestSign(code.trim(), normalizedStatus);
}
console.log(`Imported honest sign map from ${input}`);
