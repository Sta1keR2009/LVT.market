import OpenAI from "openai";
import type {
  ChatCompletionMessageParam,
  ChatCompletionTool,
} from "openai/resources/chat/completions";
import type { Channel, ChatMessage, ToolCallRecord } from "./types.js";
import { getDeliveryQuote } from "./tools/delivery.js";
import {
  findAnalogs,
  getStockAndPrice,
  getTechSpecs,
} from "./tools/catalog.js";
import { searchCatalog } from "./tools/search.js";
import {
  buildConversationSummary,
  notifyHandoff,
  shouldEscalate,
} from "./handoff.js";
import { recordEvent } from "./analytics.js";
import { loadAgentSystemPrompt } from "./promptLoader.js";

const ORDER_HELP =
  "Шаги: добавьте товары в корзину → оформите заказ → укажите контакты, доставку и оплату. Для счёта юрлицу — запросите менеджера.";

function toolDefs(): ChatCompletionTool[] {
  return [
    {
      type: "function",
      function: {
        name: "get_stock_and_price",
        description: "Stock and price by manufacturer part number.",
        parameters: {
          type: "object",
          properties: {
            part_number: { type: "string" },
            quantity: { type: "integer", minimum: 1 },
          },
          required: ["part_number"],
        },
      },
    },
    {
      type: "function",
      function: {
        name: "get_delivery_quote",
        description: "Shipping quote for Bitrix catalog product_id.",
        parameters: {
          type: "object",
          properties: {
            product_id: { type: "integer" },
            quantity: { type: "integer", minimum: 1 },
            city: { type: "string", description: "Город доставки клиента" },
          },
          required: ["product_id"],
        },
      },
    },
    {
      type: "function",
      function: {
        name: "find_analogs",
        description: "Find replacement/alternate parts.",
        parameters: {
          type: "object",
          properties: {
            part_number: { type: "string" },
            constraints: { type: "string" },
          },
          required: ["part_number"],
        },
      },
    },
    {
      type: "function",
      function: {
        name: "get_tech_specs",
        description: "Technical specs and datasheet URL.",
        parameters: {
          type: "object",
          properties: { part_number: { type: "string" } },
          required: ["part_number"],
        },
      },
    },
    {
      type: "function",
      function: {
        name: "get_order_help",
        description: "How to place an order on the site.",
        parameters: { type: "object", properties: {} },
      },
    },
    {
      type: "function",
      function: {
        name: "search_catalog",
        description: "Full-text search in product catalog by keyword or description. Use for finding products when user asks about availability, types, or categories.",
        parameters: {
          type: "object",
          properties: {
            query: { type: "string", description: "Search query, products or keywords" },
            limit: { type: "integer", minimum: 1, maximum: 10, default: 5 },
          },
          required: ["query"],
        },
      },
    },
  ];
}

const SYSTEM_FALLBACK =
  "You are a sales consultant for lvt.market (electronics). Reply in Russian. " +
  "Be concise. Qualify qty, city, timeline. Upsell only when relevant. " +
  "Use tools for price/stock/shipping; never invent numbers. " +
  "Order timing: max inbound to Lytkarino plus carrier time (explain when relevant). " +
  "If user asks for human or says they want to buy, acknowledge manager handoff.";

export interface OrchestratorEnv {
  llmClient: InstanceType<typeof OpenAI>;
  llmModel: string;
  llmProvider: string;
  bitrixBaseUrl: string;
  catalogApiUrl?: string;
  analogsApiUrl?: string;
  handoffWebhookUrl?: string;
}

export interface PageContext {
  product_id?: number;
  city?: string;
  supplier_min_lead_days?: number;
}

export async function runAgentTurn(params: {
  env: OrchestratorEnv;
  channel: Channel;
  session_id: string;
  userText: string;
  history: ChatMessage[];
  pageContext?: PageContext;
}): Promise<{
  reply: string;
  handoff: boolean;
  handoff_reason?: string;
  tool_calls: ToolCallRecord[];
}> {
  const client = params.env.llmClient;
  const ctxParts: string[] = [];
  if (params.pageContext?.product_id != null) {
    ctxParts.push(`product_id=${params.pageContext.product_id}`);
  }
  if (params.pageContext?.city?.trim()) {
    ctxParts.push(`city=${params.pageContext.city.trim()}`);
  }
  if (
    params.pageContext?.supplier_min_lead_days != null &&
    Number.isFinite(params.pageContext.supplier_min_lead_days)
  ) {
    ctxParts.push(
      `supplier_min_lead_days=${params.pageContext.supplier_min_lead_days} (минимальный срок по строкам поставщиков на карточке; формулировать «от N дней» к поставке на Лыткарино, плюс ТК по get_delivery_quote)`
    );
  }
  const ctx = ctxParts.length ? ` Page context: ${ctxParts.join("; ")}.` : "";

  const systemBase = loadAgentSystemPrompt() || SYSTEM_FALLBACK;

  const messages: ChatCompletionMessageParam[] = [
    { role: "system", content: systemBase + ctx },
    ...params.history.map(
      (m): ChatCompletionMessageParam => ({
        role: m.role === "assistant" ? "assistant" : "user",
        content: m.content,
      })
    ),
    { role: "user", content: params.userText },
  ];

  const toolRecords: ToolCallRecord[] = [];
  let rounds = 0;

  while (rounds < 6) {
    rounds++;
    const completion = await client.chat.completions.create({
      model: params.env.llmModel,
      messages,
      tools: toolDefs(),
      tool_choice: "auto",
      temperature: 0.5,
    });

    const choice = completion.choices[0];
    if (!choice?.message) break;
    const msg = choice.message;

    if (msg.tool_calls?.length) {
      messages.push(msg);
      for (const tc of msg.tool_calls) {
        if (tc.type !== "function") continue;
        const name = tc.function.name;
        let args: Record<string, unknown> = {};
        try {
          args = JSON.parse(tc.function.arguments || "{}") as Record<
            string,
            unknown
          >;
        } catch {
          args = {};
        }
        const t0 = Date.now();
        const result = await dispatchTool(name, args, params.env, {
          page_product_id: params.pageContext?.product_id,
          page_city: params.pageContext?.city,
        });
        toolRecords.push({
          name,
          args,
          result,
          ms: Date.now() - t0,
        });
        recordEvent({
          type: "tool_call",
          channel: params.channel,
          session_id: params.session_id,
          payload: { name, ms: Date.now() - t0 },
        });
        messages.push({
          role: "tool",
          tool_call_id: tc.id,
          content: JSON.stringify(result),
        });
      }
      continue;
    }

    let reply =
      typeof msg.content === "string"
        ? msg.content
        : "Извините, не удалось сформировать ответ.";

    const esc = shouldEscalate({
      lastUserMessage: params.userText,
      assistantDraft: reply,
    });
    const handoff = esc.should_handoff;
    if (handoff) {
      const summary = buildConversationSummary([
        ...params.history,
        { role: "user", content: params.userText },
        { role: "assistant", content: reply },
      ]);
      await notifyHandoff({
        webhook_url: params.env.handoffWebhookUrl,
        session_id: params.session_id,
        reason: esc.reason,
        summary,
        channel: params.channel,
      });
      if (esc.reason === "user_requested_operator") {
        reply += "\n\nЯ передал запрос менеджеру — с вами скоро свяжутся.";
      } else if (esc.reason === "buy_signal") {
        reply += "\n\nОтлично — передаю менеджеру для оформления и счёта.";
      } else if (esc.reason === "low_confidence") {
        reply =
          "Чтобы не ошибиться с условиями, перевожу вас на менеджера. Он ответит в ближайшее время.";
      }
    }

    return {
      reply,
      handoff,
      handoff_reason: handoff ? esc.reason : undefined,
      tool_calls: toolRecords,
    };
  }

  return {
    reply: "Сервис временно занят. Попробуйте ещё раз.",
    handoff: false,
    tool_calls: toolRecords,
  };
}

async function dispatchTool(
  name: string,
  args: Record<string, unknown>,
  env: OrchestratorEnv,
  ctx: { page_product_id?: number; page_city?: string }
): Promise<unknown> {
  switch (name) {
    case "get_stock_and_price":
      return getStockAndPrice({
        catalogApiUrl: env.catalogApiUrl,
        partNumber: String(args.part_number ?? ""),
        quantity:
          args.quantity != null ? Number(args.quantity) : undefined,
      });
    case "get_delivery_quote": {
      let pid = args.product_id != null ? Number(args.product_id) : NaN;
      if (!Number.isFinite(pid) && ctx.page_product_id != null)
        pid = ctx.page_product_id;
      if (!Number.isFinite(pid))
        return {
          ok: false,
          error: "Нужен product_id (откройте карточку товара на сайте).",
        };
      const cityArg =
        args.city != null
          ? String(args.city)
          : ctx.page_city?.trim() || undefined;
      return getDeliveryQuote({
        bitrixBaseUrl: env.bitrixBaseUrl,
        productId: pid,
        quantity:
          args.quantity != null ? Number(args.quantity) : undefined,
        city: cityArg,
      });
    }
    case "find_analogs":
      return findAnalogs({
        analogsApiUrl: env.analogsApiUrl,
        partNumber: String(args.part_number ?? ""),
        constraints:
          args.constraints != null ? String(args.constraints) : undefined,
      });
    case "get_tech_specs":
      return getTechSpecs({
        catalogApiUrl: env.catalogApiUrl,
        partNumber: String(args.part_number ?? ""),
      });
    case "search_catalog":
      return searchCatalog({
        siteBaseUrl: env.bitrixBaseUrl,
        query: String(args.query ?? ""),
        limit:
          args.limit != null ? Number(args.limit) : 5,
      });
    case "get_order_help":
      return { ok: true, steps: ORDER_HELP };
    default:
      return { ok: false, error: "unknown_tool" };
  }
}
