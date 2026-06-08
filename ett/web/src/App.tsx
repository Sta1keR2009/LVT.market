import { FormEvent, useMemo, useState } from "react";
import type { JobStatus, LookupResult } from "./types";

const API_BASE = "/ett-api";

function renderHonestSign(status: LookupResult["honestSignStatus"]) {
  if (status === "required") return "Да";
  if (status === "not_required") return "Нет";
  return "Неизвестно";
}

export function App() {
  const [part, setPart] = useState("");
  const [lookup, setLookup] = useState<LookupResult | null>(null);
  const [batchLookup, setBatchLookup] = useState<LookupResult[]>([]);
  const [listInput, setListInput] = useState("");
  const [lookupError, setLookupError] = useState("");
  const [loadingLookup, setLoadingLookup] = useState(false);
  const [loadingBatchLookup, setLoadingBatchLookup] = useState(false);
  const [file, setFile] = useState<File | null>(null);
  const [job, setJob] = useState<JobStatus | null>(null);
  const [jobError, setJobError] = useState("");
  const [columns, setColumns] = useState<string[]>([
    "partNumber",
    "tnvedCode",
    "dutyRatePercent",
    "vatRatePercent",
    "honestSignStatus",
    "notes"
  ]);

  const canSubmitLookup = useMemo(() => part.trim().length > 0, [part]);

  async function onLookup(e: FormEvent) {
    e.preventDefault();
    if (!canSubmitLookup) return;
    setLoadingLookup(true);
    setLookupError("");
    try {
      const res = await fetch(`${API_BASE}/lookup?part=${encodeURIComponent(part)}`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      setLookup((await res.json()) as LookupResult);
    } catch (error) {
      setLookupError(error instanceof Error ? error.message : "Lookup failed");
      setLookup(null);
    } finally {
      setLoadingLookup(false);
    }
  }

  async function onBatchLookup(e: FormEvent) {
    e.preventDefault();
    const parts = listInput
      .split(/\r?\n/)
      .map((x) => x.trim())
      .filter(Boolean);
    if (parts.length === 0) return;
    setLoadingBatchLookup(true);
    setLookupError("");
    setBatchLookup([]);
    try {
      const res = await fetch(`${API_BASE}/lookup-batch`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ parts })
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const payload = (await res.json()) as { results: LookupResult[] };
      setBatchLookup(payload.results);
    } catch (error) {
      setLookupError(error instanceof Error ? error.message : "Batch lookup failed");
    } finally {
      setLoadingBatchLookup(false);
    }
  }

  async function onUpload(e: FormEvent) {
    e.preventDefault();
    if (!file) return;
    setJobError("");
    const formData = new FormData();
    formData.append("file", file);
    formData.append("columns", columns.join(","));
    try {
      const res = await fetch(`${API_BASE}/jobs`, { method: "POST", body: formData });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const created = (await res.json()) as JobStatus;
      setJob(created);
      await pollJob(created.id);
    } catch (error) {
      setJobError(error instanceof Error ? error.message : "Upload failed");
    }
  }

  async function pollJob(id: string) {
    while (true) {
      const res = await fetch(`${API_BASE}/jobs/${id}`);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const current = (await res.json()) as JobStatus;
      setJob(current);
      if (current.status === "done" || current.status === "failed") break;
      await new Promise((resolve) => setTimeout(resolve, 1500));
    }
  }

  return (
    <main className="page">
      <h1>ЕТТ / ТН ВЭД</h1>
      <p className="lead">
        Проверка по одному артикулу для тестирования и пакетная обработка XLSX/CSV.
      </p>

      <section className="card">
        <h2>Проверка одного артикула</h2>
        <form onSubmit={onLookup} className="row">
          <input
            value={part}
            onChange={(e) => setPart(e.target.value)}
            placeholder="Введите партномер"
          />
          <button disabled={!canSubmitLookup || loadingLookup}>
            {loadingLookup ? "Проверяем..." : "Найти"}
          </button>
        </form>
        {lookupError && <p className="error">{lookupError}</p>}
        {lookup && (
          <pre className="result">{JSON.stringify(lookup, null, 2)}</pre>
        )}
      </section>

      <section className="card">
        <h2>Проверка списка артикулов</h2>
        <form onSubmit={onBatchLookup}>
          <textarea
            value={listInput}
            onChange={(e) => setListInput(e.target.value)}
            placeholder={"Вставьте список артикулов, по одному в строке"}
            rows={10}
            className="listArea"
          />
          <div className="row">
            <button disabled={loadingBatchLookup}>
              {loadingBatchLookup ? "Проверяем список..." : "Проверить список"}
            </button>
          </div>
        </form>
        {batchLookup.length > 0 && (
          <div className="resultTableWrap">
            <table className="resultTable">
              <thead>
                <tr>
                  <th>Партномер</th>
                  <th>ТН ВЭД</th>
                  <th>Описание</th>
                  <th>% налога</th>
                  <th>Честный знак</th>
                  <th>Заметка</th>
                </tr>
              </thead>
              <tbody>
                {batchLookup.map((row, idx) => (
                  <tr key={`${row.normalizedPartNumber}-${idx}`}>
                    <td>{row.partNumber}</td>
                    <td>{row.tnvedCode ?? ""}</td>
                    <td>{row.tnvedDescription ?? ""}</td>
                    <td>{row.vatRatePercent ?? ""}</td>
                    <td>{renderHonestSign(row.honestSignStatus)}</td>
                    <td>{row.notes?.join("; ") ?? ""}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </section>

      <section className="card">
        <h2>Пакетная обработка</h2>
        <form onSubmit={onUpload} className="row">
          <input
            type="file"
            accept=".xlsx,.csv"
            onChange={(e) => setFile(e.target.files?.[0] ?? null)}
          />
          <button disabled={!file}>Загрузить</button>
        </form>
        <div className="columns">
          {[
            "partNumber",
            "normalizedPartNumber",
            "source",
            "confidence",
            "tnvedCode",
            "dutyRatePercent",
            "vatRatePercent",
            "honestSignStatus",
            "notes"
          ].map((key) => (
            <label key={key}>
              <input
                type="checkbox"
                checked={columns.includes(key)}
                onChange={(e) =>
                  setColumns((prev) =>
                    e.target.checked ? [...prev, key] : prev.filter((x) => x !== key)
                  )
                }
              />
              {key}
            </label>
          ))}
        </div>
        {jobError && <p className="error">{jobError}</p>}
        {job && (
          <div className="status">
            <p>Статус: {job.status}</p>
            {job.error && <p className="error">{job.error}</p>}
            {job.resultFile && (
              <a href={`${API_BASE}/files/${job.resultFile}`} target="_blank">
                Скачать XLSX
              </a>
            )}
            {job.pdfFile && (
              <a href={`${API_BASE}/files/${job.pdfFile}`} target="_blank">
                Скачать PDF
              </a>
            )}
          </div>
        )}
      </section>
    </main>
  );
}
