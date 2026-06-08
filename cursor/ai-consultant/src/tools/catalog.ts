import type {
  AnalogsResult,
  StockPriceResult,
  TechSpecsResult,
} from "./contracts.js";

/**
 * Обертка над HTTP API каталога Bitrix (настроить MOUSER_PROXY_URL или BITRIX_CATALOG_API).
 * Если не задано — возвращает ok:false с подсказкой (демо-режим).
 */
export async function getStockAndPrice(params: {
  catalogApiUrl?: string;
  partNumber: string;
  quantity?: number;
}): Promise<StockPriceResult> {
  const api = params.catalogApiUrl?.trim();
  if (!api) {
    return {
      ok: false,
      part_number: params.partNumber,
      error:
        "Каталог API не настроен (CATALOG_API_URL). Назовите артикул менеджеру или проверьте карточку на сайте.",
    };
  }

  try {
    const url = new URL(api);
    url.searchParams.set("part", params.partNumber);
    url.searchParams.set("qty", String(params.quantity ?? 1));

    const res = await fetch(url.toString(), {
      headers: { Accept: "application/json" },
      signal: AbortSignal.timeout(20000),
    });
    if (!res.ok)
      return {
        ok: false,
        part_number: params.partNumber,
        error: `Каталог: HTTP ${res.status}`,
      };
    const data = (await res.json()) as Record<string, unknown>;
    return mapStockPrice(data, params.partNumber);
  } catch (e) {
    return {
      ok: false,
      part_number: params.partNumber,
      error: e instanceof Error ? e.message : String(e),
    };
  }
}

function mapStockPrice(
  data: Record<string, unknown>,
  fallbackPn: string
): StockPriceResult {
  if (data.ok === false)
    return {
      ok: false,
      part_number: fallbackPn,
      error: String(data.error ?? "unknown"),
    };
  return {
    ok: true,
    part_number: String(data.part_number ?? data.partNumber ?? fallbackPn),
    product_name:
      data.product_name != null ? String(data.product_name) : undefined,
    quantity_requested:
      data.quantity_requested != null
        ? Number(data.quantity_requested)
        : undefined,
    availability_text:
      data.availability_text != null
        ? String(data.availability_text)
        : undefined,
    availability_in_stock:
      data.availability_in_stock != null
        ? String(data.availability_in_stock)
        : undefined,
    lead_time: data.lead_time != null ? String(data.lead_time) : undefined,
    price_breaks: Array.isArray(data.price_breaks)
      ? (data.price_breaks as Record<string, unknown>[]).map((p) => ({
          quantity: Number(p.quantity ?? 1),
          price: String(p.price ?? ""),
          currency: p.currency != null ? String(p.currency) : undefined,
        }))
      : undefined,
    product_url: data.product_url != null ? String(data.product_url) : undefined,
    source: data.source != null ? String(data.source) : undefined,
  };
}

export async function getTechSpecs(params: {
  catalogApiUrl?: string;
  partNumber: string;
}): Promise<TechSpecsResult> {
  const base = params.catalogApiUrl?.trim();
  if (!base) {
    return {
      ok: false,
      part_number: params.partNumber,
      error: "CATALOG_API_URL не задан — характеристики по API недоступны.",
    };
  }
  try {
    const url = new URL(base.replace(/\/$/, "") + "/tech");
    url.searchParams.set("part", params.partNumber);
    const res = await fetch(url.toString(), {
      headers: { Accept: "application/json" },
      signal: AbortSignal.timeout(20000),
    });
    if (!res.ok)
      return {
        ok: false,
        part_number: params.partNumber,
        error: `HTTP ${res.status}`,
      };
    const data = (await res.json()) as Record<string, unknown>;
    return {
      ok: true,
      part_number: String(data.part_number ?? params.partNumber),
      description:
        data.description != null ? String(data.description) : undefined,
      attributes: Array.isArray(data.attributes)
        ? (data.attributes as { name: string; value: string }[])
        : undefined,
      lifecycle:
        data.lifecycle != null ? String(data.lifecycle) : undefined,
      rohs: data.rohs != null ? String(data.rohs) : undefined,
      datasheet_url:
        data.datasheet_url != null ? String(data.datasheet_url) : undefined,
    };
  } catch (e) {
    return {
      ok: false,
      part_number: params.partNumber,
      error: e instanceof Error ? e.message : String(e),
    };
  }
}

export async function findAnalogs(params: {
  analogsApiUrl?: string;
  partNumber: string;
  constraints?: string;
}): Promise<AnalogsResult> {
  const api = params.analogsApiUrl?.trim();
  if (!api) {
    return {
      ok: true,
      query: params.partNumber,
      alternatives: [],
      guidance:
        "Внутренний поиск аналогов не подключён. Предложите клиенту уточнить параметры (напряжение, корпус, температурный диапазон) и передайте менеджеру для подбора.",
    };
  }
  try {
    const url = new URL(api);
    url.searchParams.set("part", params.partNumber);
    if (params.constraints)
      url.searchParams.set("constraints", params.constraints);

    const res = await fetch(url.toString(), {
      headers: { Accept: "application/json" },
      signal: AbortSignal.timeout(25000),
    });
    if (!res.ok)
      return {
        ok: false,
        query: params.partNumber,
        alternatives: [],
        error: `HTTP ${res.status}`,
      };
    const data = (await res.json()) as Record<string, unknown>;
    const alts = Array.isArray(data.alternatives)
      ? (data.alternatives as Record<string, unknown>[]).map((a) => ({
          manufacturer_part_number: String(
            a.manufacturer_part_number ?? a.mpn ?? ""
          ),
          manufacturer:
            a.manufacturer != null ? String(a.manufacturer) : undefined,
          description:
            a.description != null ? String(a.description) : undefined,
          availability:
            a.availability != null ? String(a.availability) : undefined,
          notes: a.notes != null ? String(a.notes) : undefined,
        }))
      : [];
    return {
      ok: true,
      query: params.partNumber,
      alternatives: alts,
      guidance:
        data.guidance != null ? String(data.guidance) : undefined,
    };
  } catch (e) {
    return {
      ok: false,
      query: params.partNumber,
      alternatives: [],
      error: e instanceof Error ? e.message : String(e),
    };
  }
}
