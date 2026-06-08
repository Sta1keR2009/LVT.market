import type { ChatSession, TgMessage } from "./types.js";
import { askConsultant } from "./orchestratorClient.js";
import { submitLead } from "./lead.js";
import {
  cancelKeyboard,
  consentKeyboard,
  leadEmailKeyboard,
  mainKeyboard,
  MENU,
  MENU_BUTTONS,
  WELCOME,
} from "./menu.js";
import { sendChatAction, sendMessage } from "./telegram.js";
import {
  isValidPhone,
  normalizePhone,
  parseProductId,
  parseQuantity,
} from "./utils.js";

const sessions = new Map<number, ChatSession>();

function session(chatId: number): ChatSession {
  let s = sessions.get(chatId);
  if (!s) {
    s = { state: "idle", draft: {} };
    sessions.set(chatId, s);
  }
  return s;
}

function reset(chatId: number): void {
  sessions.set(chatId, { state: "idle", draft: {} });
}

async function replyWithAi(
  chatId: number,
  message: string,
  pageContext?: { product_id?: number; city?: string }
): Promise<void> {
  await sendChatAction(chatId);
  const result = await askConsultant({ chatId, message, pageContext });
  if (!result.ok || !result.reply) {
    await sendMessage(
      chatId,
      `Не удалось получить ответ консультанта${result.error ? `: ${result.error}` : ""}. Попробуйте позже или нажмите «${MENU.OPERATOR}».`,
      { reply_markup: mainKeyboard() }
    );
    return;
  }
  await sendMessage(chatId, result.reply, { reply_markup: mainKeyboard() });
}

export async function handleMessage(msg: TgMessage): Promise<void> {
  const chatId = msg.chat.id;
  const text = (msg.text ?? "").trim();
  if (!text) return;

  const s = session(chatId);

  if (text === MENU.CANCEL) {
    reset(chatId);
    await sendMessage(chatId, "Действие отменено.", {
      reply_markup: mainKeyboard(),
    });
    return;
  }

  if (text === "/start" || text === "/menu") {
    reset(chatId);
    await sendMessage(chatId, WELCOME, { reply_markup: mainKeyboard() });
    return;
  }

  if (text === MENU.STOCK) {
    s.state = "stock_part";
    s.draft = {};
    await sendMessage(
      chatId,
      "Укажите артикул (part number), например STM32F103C8T6:",
      { reply_markup: cancelKeyboard() }
    );
    return;
  }

  if (text === MENU.SPECS) {
    s.state = "specs_part";
    s.draft = {};
    await sendMessage(chatId, "Укажите артикул для технических характеристик:", {
      reply_markup: cancelKeyboard(),
    });
    return;
  }

  if (text === MENU.DELIVERY) {
    s.state = "delivery_city";
    s.draft = {};
    await sendMessage(chatId, "В какой город нужна доставка?", {
      reply_markup: cancelKeyboard(),
    });
    return;
  }

  if (text === MENU.OPERATOR) {
    reset(chatId);
    await replyWithAi(
      chatId,
      "Клиент просит связаться с менеджером-оператором. Передайте запрос менеджеру."
    );
    return;
  }

  if (text === MENU.LEAD) {
    s.state = "lead_name";
    s.draft = {};
    await sendMessage(chatId, "Как к вам обращаться? (имя)", {
      reply_markup: cancelKeyboard(),
    });
    return;
  }

  if (text === MENU.FREE) {
    reset(chatId);
    await sendMessage(
      chatId,
      "Напишите ваш вопрос — отвечу как консультант lvt.market:",
      { reply_markup: mainKeyboard() }
    );
    return;
  }

  switch (s.state) {
    case "stock_part": {
      s.draft.partNumber = text;
      s.state = "stock_qty";
      await sendMessage(
        chatId,
        "Укажите количество (число) или отправьте «1»:",
        { reply_markup: cancelKeyboard() }
      );
      return;
    }
    case "stock_qty": {
      const qty = parseQuantity(text, 1);
      const part = s.draft.partNumber ?? "";
      reset(chatId);
      await replyWithAi(
        chatId,
        `Проверь остатки и цену для артикула ${part}, количество ${qty}. Используй инструмент get_stock_and_price.`
      );
      return;
    }
    case "specs_part": {
      const part = text;
      reset(chatId);
      await replyWithAi(
        chatId,
        `Дай технические характеристики и ссылку на даташит для артикула ${part}. Используй get_tech_specs.`
      );
      return;
    }
    case "delivery_city": {
      s.draft.city = text;
      s.state = "delivery_product";
      await sendMessage(
        chatId,
        "Пришлите ссылку на карточку товара на lvt.market или числовой product_id:",
        { reply_markup: cancelKeyboard() }
      );
      return;
    }
    case "delivery_product": {
      const pid = parseProductId(text);
      if (!pid) {
        await sendMessage(
          chatId,
          "Не удалось определить product_id. Пришлите ссылку на товар или числовой ID.",
          { reply_markup: cancelKeyboard() }
        );
        return;
      }
      s.draft.productId = pid;
      s.state = "delivery_qty";
      await sendMessage(
        chatId,
        "Количество для расчёта доставки (по умолчанию 1):",
        { reply_markup: cancelKeyboard() }
      );
      return;
    }
    case "delivery_qty": {
      const qty = parseQuantity(text, 1);
      const city = s.draft.city ?? "";
      const pid = s.draft.productId;
      reset(chatId);
      if (!pid) {
        await sendMessage(chatId, "Ошибка: не указан товар.", {
          reply_markup: mainKeyboard(),
        });
        return;
      }
      await replyWithAi(
        chatId,
        `Рассчитай доставку в город ${city} для product_id ${pid}, количество ${qty}. Используй get_delivery_quote с городом ${city}.`,
        { product_id: pid, city }
      );
      return;
    }
    case "lead_name": {
      if (text.length < 2 || text.length > 200) {
        await sendMessage(chatId, "Имя должно быть от 2 до 200 символов.", {
          reply_markup: cancelKeyboard(),
        });
        return;
      }
      s.draft.leadName = text;
      s.state = "lead_phone";
      await sendMessage(
        chatId,
        "Укажите телефон для связи (например +79001234567):",
        { reply_markup: cancelKeyboard() }
      );
      return;
    }
    case "lead_phone": {
      const phone = msg.contact?.phone_number
        ? normalizePhone(msg.contact.phone_number)
        : normalizePhone(text);
      if (!isValidPhone(phone)) {
        await sendMessage(
          chatId,
          "Некорректный телефон. Введите номер в формате +7XXXXXXXXXX:",
          { reply_markup: cancelKeyboard() }
        );
        return;
      }
      s.draft.leadPhone = phone;
      s.state = "lead_email";
      await sendMessage(
        chatId,
        "Email (необязательно). Нажмите «Пропустить email», если не нужен:",
        { reply_markup: leadEmailKeyboard() }
      );
      return;
    }
    case "lead_email": {
      if (text === MENU.SKIP_EMAIL) {
        s.draft.leadEmail = "";
      } else if (text.includes("@")) {
        s.draft.leadEmail = text;
      } else {
        await sendMessage(
          chatId,
          "Введите корректный email или нажмите «Пропустить email»:",
          { reply_markup: leadEmailKeyboard() }
        );
        return;
      }
      s.state = "lead_consent";
      await sendMessage(
        chatId,
        "Для отправки заявки необходимо согласие на обработку персональных данных. Нажмите кнопку ниже:",
        { reply_markup: consentKeyboard() }
      );
      return;
    }
    case "lead_consent": {
      if (text !== MENU.CONSENT_YES) {
        await sendMessage(
          chatId,
          "Без согласия на обработку ПДн заявку отправить нельзя.",
          { reply_markup: consentKeyboard() }
        );
        return;
      }
      const name = s.draft.leadName ?? "";
      const phone = s.draft.leadPhone ?? "";
      const email = s.draft.leadEmail;
      const productId = s.draft.productId;
      const sessionId = `telegram:${chatId}`;
      reset(chatId);

      const lead = await submitLead({
        name,
        phone,
        email: email || undefined,
        message: "Заявка из Telegram-бота консультанта",
        sessionId,
        productId,
        consent: true,
      });

      if (!lead.ok) {
        await sendMessage(
          chatId,
          `Не удалось отправить заявку${lead.error ? `: ${lead.error}` : ""}. Позвоните +7 (495) 260-13-69.`,
          { reply_markup: mainKeyboard() }
        );
        return;
      }

      await sendMessage(
        chatId,
        "Заявка отправлена менеджеру. Мы свяжемся с вами в ближайшее время.",
        { reply_markup: mainKeyboard() }
      );
      return;
    }
    default:
      break;
  }

  if (s.state === "idle" && !MENU_BUTTONS.has(text) && !text.startsWith("/")) {
    await replyWithAi(chatId, text);
  }
}
