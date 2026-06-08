import "dotenv/config";
import path from "node:path";

export const config = {
  port: Number(process.env.PORT ?? 3217),
  host: process.env.HOST ?? "127.0.0.1",
  nodeEnv: process.env.NODE_ENV ?? "development",
  webDistDir: path.resolve(process.cwd(), "web-dist"),
  storageDir: path.resolve(process.cwd(), "storage"),
  uploadsDir: path.resolve(process.cwd(), "storage/uploads"),
  resultsDir: path.resolve(process.cwd(), "storage/results"),
  sqlitePath: process.env.SQLITE_PATH ?? path.resolve(process.cwd(), "storage/ett.sqlite"),
  bitrixDbHost: process.env.BITRIX_DB_HOST ?? "",
  bitrixDbSocket: process.env.BITRIX_DB_SOCKET ?? "",
  bitrixDbPort: Number(process.env.BITRIX_DB_PORT ?? 3306),
  bitrixDbName: process.env.BITRIX_DB_NAME ?? "",
  bitrixDbUser: process.env.BITRIX_DB_USER ?? "",
  bitrixDbPassword: process.env.BITRIX_DB_PASSWORD ?? "",
  externalLookupEnabled: process.env.EXTERNAL_LOOKUP_ENABLED === "1",
  maxUploadBytes: Number(process.env.MAX_UPLOAD_BYTES ?? 10_000_000)
};
