import type { DeliveryQuoteResult } from "./contracts.js";

export async function getDeliveryQuote(params: {
  bitrixBaseUrl: string;
  productId: number;
  quantity?: number;
  city?: string;
}): Promise<DeliveryQuoteResult> {
  const qty = params.quantity ?? 1;
  const base = params.bitrixBaseUrl.replace(/\/$/, "");
  const url = new URL(`${base}/local/api/catapulto_delivery_quote.php`);
  url.searchParams.set("product_id", String(params.productId));
  url.searchParams.set("quantity", String(qty));
  if (params.city?.trim()) {
    url.searchParams.set("city", params.city.trim());
  }

  try {
    const res = await fetch(url.toString(), {
      headers: { Accept: "application/json" },
      signal: AbortSignal.timeout(45000),
    });
    const raw = (await res.json()) as Record<string, unknown>;
    if (!raw.ok)
      return {
        ok: false,
        error:
          typeof raw.error === "string"
            ? raw.error
            : `Доставка: HTTP ${res.status}`,
      };

    const deliveries = Array.isArray(raw.deliveries)
      ? (raw.deliveries as Record<string, unknown>[]).map((d) => ({
          operator: String(d.operator ?? ""),
          operator_name: d.operatorName != null ? String(d.operatorName) : undefined,
          rate_name: d.rateName != null ? String(d.rateName) : undefined,
          period_text: d.periodText != null ? String(d.periodText) : undefined,
          delivery_day: d.deliveryDay != null ? String(d.deliveryDay) : undefined,
          price: Number(d.price ?? 0),
          price_formatted:
            d.priceFormatted != null ? String(d.priceFormatted) : undefined,
        }))
      : [];

    const pickup = raw.pickup as Record<string, unknown> | undefined;

    return {
      ok: true,
      city: raw.city != null ? String(raw.city) : undefined,
      product_name:
        raw.productName != null ? String(raw.productName) : undefined,
      quantity: raw.quantity != null ? Number(raw.quantity) : qty,
      pickupDaysShift:
        raw.pickupDaysShift != null ? Number(raw.pickupDaysShift) : undefined,
      inboundDaysToLytkarino:
        raw.inboundDaysToLytkarino != null
          ? Number(raw.inboundDaysToLytkarino)
          : undefined,
      inboundShiftNote:
        raw.inboundShiftNote != null ? String(raw.inboundShiftNote) : undefined,
      pickup: pickup
        ? {
            name: String(pickup.name ?? "Самовывоз"),
            locations: Array.isArray(pickup.locations)
              ? (pickup.locations as unknown[]).map(String)
              : [],
            price: Number(pickup.price ?? 0),
            price_formatted:
              pickup.priceFormatted != null
                ? String(pickup.priceFormatted)
                : undefined,
          }
        : undefined,
      deliveries,
      disclaimer:
        raw.disclaimer != null ? String(raw.disclaimer) : undefined,
    };
  } catch (e) {
    return {
      ok: false,
      error: e instanceof Error ? e.message : String(e),
    };
  }
}
