import { readFileSync, existsSync, statSync } from "fs";
import { join } from "path";

const FALLBACK =
  "You are a sales consultant for lvt.market (electronics). Reply in Russian. " +
  "Be concise. Use tools for price/stock/shipping; never invent numbers. " +
  "If user asks for human or wants to buy, acknowledge manager handoff.";

let cachedText = "";
let cachedPath = "";
let cachedMtime = 0;

function env(name: string, fallback = ""): string {
  return process.env[name]?.trim() ?? fallback;
}

export function loadAgentSystemPrompt(): string {
  const rel = env("AGENT_SYSTEM_PROMPT_PATH", "prompts/agent_system.ru.md");
  const path = join(process.cwd(), rel);

  const hot = env("AGENT_PROMPT_HOT_RELOAD", "") === "1";

  if (!existsSync(path)) {
    return FALLBACK;
  }

  try {
    const st = statSync(path);
    if (!hot && cachedPath === path && st.mtimeMs === cachedMtime && cachedText) {
      return cachedText;
    }
    cachedText = readFileSync(path, "utf8").trim();
    cachedPath = path;
    cachedMtime = st.mtimeMs;
    return cachedText || FALLBACK;
  } catch {
    return FALLBACK;
  }
}
