interface SearchResult {
  ok: boolean;
  query?: string;
  total?: number;
  products?: Array<{
    id: number;
    title: string;
    description: string;
    url: string;
    image_url?: string;
    article?: string;
    brand?: string;
    score: number;
  }>;
  error?: string;
}

export async function searchCatalog(params: {
  siteBaseUrl: string;
  query: string;
  limit?: number;
}): Promise<SearchResult> {
  const q = params.query?.trim();
  if (!q) return { ok: false, error: "Пустой поисковый запрос" };

  const url = "http://127.0.0.1:9308/search";

  try {
    const res = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        index: "bitrix_enriched",
        query: { match: { "*": q } },
        limit: params.limit ?? 5,
        options: { ranker: "bm25" },
      }),
      signal: AbortSignal.timeout(5000),
    });

    if (!res.ok) return { ok: false, error: "Manticore: HTTP " + res.status };
    const data = await res.json() as any;
    const hits = data?.hits?.hits ?? [];
    
    const products = hits.map((h: any) => {
      const itemId = h._source?.item_id ?? h._id;
      const bitrixId = h._source?.item ?? String(itemId);
      const article = h._source?.article || '';
      const brand = h._source?.brand || '';
      const cleanId = String(bitrixId).replace(/[^0-9]/g, "") || String(itemId);
      return {
        id: itemId,
        title: h._source?.title ?? "",
        description: (h._source?.body ?? "").replace(/<[^>]*>/g, "").substring(0, 300),
        url: "",
        image_url: "",
        article: article,
        brand: brand,
        score: h._score ?? 0,
        _bitrixId: cleanId,
      } as any;
    });

    // Fetch proper URLs and images from Bitrix
    const productIds = products.map((p: any) => p._bitrixId).filter(Boolean);
    if (productIds.length > 0) {
      try {
        const imgApiUrl = params.siteBaseUrl.replace(/\/$/, "") + "/local/api/product_images.php?ids=" + productIds.join(",");
        const imgRes = await fetch(imgApiUrl, { signal: AbortSignal.timeout(3000) });
        if (imgRes.ok) {
          const imgData = await imgRes.json() as any;
          if (imgData.ok && imgData.images) {
            for (const p of products) {
              const info = imgData.images[p._bitrixId];
              if (info) {
                p.url = params.siteBaseUrl.replace(/\/$/, "") + info.url;
                if (info.image) {
                  p.image_url = params.siteBaseUrl.replace(/\/$/, "") + info.image.url;
                }
              }
            }
          }
        }
      } catch (_) {
        // Continue without images
      }
      for (const p of products) {
        if (!p.url) {
          p.url = params.siteBaseUrl.replace(/\/$/, "") + "/catalog/" + p._bitrixId + "/";
        }
        delete p._bitrixId;
      }
    }

    return { ok: true, query: q, total: data?.hits?.total ?? products.length, products };
  } catch (e) {
    return { ok: false, error: e instanceof Error ? e.message : String(e) };
  }
}
