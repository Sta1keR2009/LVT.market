import fs from "node:fs";
import path from "node:path";
import { pipeline } from "node:stream/promises";
import Fastify from "fastify";
import multipart from "@fastify/multipart";
import fastifyStatic from "@fastify/static";
import { z } from "zod";
import { config } from "./config.js";
import { AppDb } from "./db.js";
import { BitrixSource } from "./bitrix-source.js";
import { ExternalSource } from "./external-source.js";
import { LookupService } from "./lookup-service.js";
import { JobProcessor } from "./job-processor.js";

for (const dir of [config.storageDir, config.uploadsDir, config.resultsDir]) {
  fs.mkdirSync(dir, { recursive: true });
}

const app = Fastify({ logger: true });
const db = new AppDb(config.sqlitePath);
const lookupService = new LookupService(db, new BitrixSource(), new ExternalSource());
const jobs = new JobProcessor(db, lookupService, config.resultsDir);

await app.register(multipart, {
  limits: { fileSize: config.maxUploadBytes, files: 1 }
});

await app.register(fastifyStatic, {
  root: config.webDistDir,
  prefix: "/ett/"
});

await app.register(fastifyStatic, {
  root: config.resultsDir,
  prefix: "/ett-api/files/",
  decorateReply: false
});

app.get("/health", async () => ({ ok: true, ts: new Date().toISOString() }));

app.get("/ett-api/version", async () => ({
  name: "ett-service",
  version: "0.1.0"
}));

app.get("/ett-api/lookup", async (req, reply) => {
  const querySchema = z.object({ part: z.string().min(1) });
  const parsed = querySchema.safeParse(req.query);
  if (!parsed.success) return reply.status(400).send({ error: "part is required" });
  const result = await lookupService.lookup(parsed.data.part);
  return reply.send(result);
});

app.post("/ett-api/lookup-batch", async (req, reply) => {
  const bodySchema = z.object({
    parts: z.array(z.string().min(1)).min(1).max(500)
  });
  const parsed = bodySchema.safeParse(req.body);
  if (!parsed.success) {
    return reply.status(400).send({ error: "parts[] is required" });
  }
  const results = [];
  for (const part of parsed.data.parts) {
    // Sequential processing keeps provider/API pressure predictable.
    results.push(await lookupService.lookup(part));
  }
  return reply.send({ count: results.length, results });
});

app.post("/ett-api/jobs", async (req, reply) => {
  const file = await req.file();
  if (!file) return reply.status(400).send({ error: "file is required" });
  const ext = path.extname(file.filename).toLowerCase();
  if (![".xlsx", ".csv"].includes(ext)) {
    return reply.status(400).send({ error: "only .xlsx/.csv supported" });
  }
  const filePath = path.join(config.uploadsDir, `${Date.now()}-${file.filename}`);
  await pipeline(file.file, fs.createWriteStream(filePath));
  const selectedColumnsRaw = file.fields.columns;
  const selectedColumns =
    selectedColumnsRaw && "value" in selectedColumnsRaw
      ? String(selectedColumnsRaw.value)
          .split(",")
          .map((v) => v.trim())
          .filter(Boolean)
      : undefined;
  const id = await jobs.enqueue(filePath, selectedColumns);
  return reply.status(202).send(db.getJob(id));
});

app.get("/ett-api/jobs/:id", async (req, reply) => {
  const params = z.object({ id: z.string() }).safeParse(req.params);
  if (!params.success) return reply.status(400).send({ error: "id is invalid" });
  const job = db.getJob(params.data.id);
  if (!job) return reply.status(404).send({ error: "job not found" });
  return reply.send(job);
});

app.listen({ port: config.port, host: config.host }).catch((error) => {
  app.log.error(error);
  process.exit(1);
});
