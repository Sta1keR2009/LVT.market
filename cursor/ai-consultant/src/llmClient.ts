import OpenAI from "openai";

export type LlmProviderId = "openai" | "deepseek" | "qwen";

function env(name: string, fallback = ""): string {
  return process.env[name]?.trim() ?? fallback;
}

function normalizeProvider(raw: string): LlmProviderId {
  const p = raw.toLowerCase();
  if (p === "deepseek") return "deepseek";
  if (p === "qwen" || p === "dashscope") return "qwen";
  return "openai";
}

export interface ResolvedLlm {
  client: OpenAI;
  model: string;
  provider: LlmProviderId;
  baseURL: string | undefined;
}

/**
 * Единая точка: OpenAI SDK + совместимые эндпоинты DeepSeek и Qwen (DashScope).
 */
export function resolveLlmFromEnv(): ResolvedLlm {
  const provider = normalizeProvider(env("LLM_PROVIDER", "openai"));

  if (provider === "deepseek") {
    const apiKey = env("DEEPSEEK_API_KEY") || env("OPENAI_API_KEY");
    if (!apiKey)
      throw new Error(
        "DEEPSEEK_API_KEY or OPENAI_API_KEY is required when LLM_PROVIDER=deepseek"
      );
    const baseURL = env("DEEPSEEK_BASE_URL", "https://api.deepseek.com");
    const model = env("DEEPSEEK_MODEL", "deepseek-chat");
    const client = new OpenAI({ apiKey, baseURL });
    return { client, model, provider, baseURL };
  }

  if (provider === "qwen") {
    const apiKey =
      env("QWEN_API_KEY") || env("DASHSCOPE_API_KEY") || env("OPENAI_API_KEY");
    if (!apiKey)
      throw new Error(
        "QWEN_API_KEY, DASHSCOPE_API_KEY or OPENAI_API_KEY is required when LLM_PROVIDER=qwen"
      );
    const baseURL = env(
      "QWEN_BASE_URL",
      "https://dashscope-intl.aliyuncs.com/compatible-mode/v1"
    );
    const model = env("QWEN_MODEL", "qwen-plus");
    const client = new OpenAI({ apiKey, baseURL });
    return { client, model, provider, baseURL };
  }

  const apiKey = env("OPENAI_API_KEY");
  if (!apiKey) throw new Error("OPENAI_API_KEY is required when LLM_PROVIDER=openai");
  const baseURLOverride = env("OPENAI_BASE_URL");
  const baseURL = baseURLOverride || undefined;
  const model = env("OPENAI_MODEL", "gpt-4o-mini");
  const client = new OpenAI({
    apiKey,
    ...(baseURL ? { baseURL } : {}),
  });
  return {
    client,
    model,
    provider: "openai",
    baseURL,
  };
}
