import { config } from "./config.js";
import { sendChatAction, sendMessage } from "./telegram.js";
import {
  YandexWebmasterClient,
  YandexWebmasterError,
  formatWebmasterReport,
} from "./yandexWebmaster.js";

function tokenLooksLikeClientId(token: string): boolean {
  const t = token.trim();
  // OAuth access token обычно y0_…; Client ID — 32 hex-символа без префикса
  return /^[a-f0-9]{32}$/i.test(t) && !t.startsWith("y0_");
}

export async function sendWebmasterReport(chatId: number): Promise<void> {
  const token = config.yandexWebmasterToken;
  if (!token) {
    await sendMessage(
      chatId,
      "Яндекс Вебмастер не настроен: задайте YANDEX_WEBMASTER_OAUTH_TOKEN в .env бота."
    );
    return;
  }
  if (tokenLooksLikeClientId(token)) {
    await sendMessage(
      chatId,
      "В .env указан Client ID приложения, а нужен access_token из браузера.\n\n" +
        "1. Откройте:\n" +
        "https://oauth.yandex.ru/authorize?response_type=token&client_id=5821c0045bee40f7a8ee7b0de1688484\n\n" +
        "2. Разрешите доступ.\n" +
        "3. Скопируйте значение после access_token= (обычно начинается с y0_…).\n" +
        "4. Вставьте в YANDEX_WEBMASTER_OAUTH_TOKEN и перезапустите бота."
    );
    return;
  }

  await sendChatAction(chatId);
  try {
    const client = new YandexWebmasterClient(config.yandexWebmasterToken);
    const items = await client.collect(config.yandexWebmasterDomains);
    await sendMessage(chatId, formatWebmasterReport(items));
  } catch (e) {
    const msg =
      e instanceof YandexWebmasterError
        ? e.message
        : e instanceof Error
          ? e.message
          : String(e);
    await sendMessage(chatId, `Ошибка Вебмастера: ${msg.slice(0, 500)}`);
  }
}

export function isWebmasterCommand(text: string): boolean {
  return /^\/webmaster(?:@\w+)?$/i.test(text.trim());
}
