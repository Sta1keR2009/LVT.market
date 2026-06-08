import type { ChatSession, PageContext } from "./types.js";
import { askConsultant } from "./orchestratorClient.js";
import { submitLead } from "./lead.js";
import { sendChatAction, sendMessage } from "./telegram.js";
import {
  cancelKeyboard,
  consentKeyboard,
  leadEmailKeyboard,
  mainKeyboard,
  managerMainKeyboard,
  MENU,
  MENU_BUTTONS,
  WELCOME,
} from "./menu.js";
import { sendWebmasterReport } from "./webmaster.js";
import {
  enterHumanMode,
  exitHumanMode,
  forwardClientMessage,
  isHumanMode,
} from "./managers.js";
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

export async function handleStart(
  chatId: number,
  isManager = false
): Promise<void> {
  reset(chatId);
  const kb = isManager ? managerMainKeyboard() : mainKeyboard();
  const intro = isManager
    ? `${WELCOME}\n\nДля менеджеров: «${MENU.WEBMASTER}» или /webmaster — сводка из Яндекс Вебмастера.`
    : WELCOME;
  await sendMessage(chatId, intro, { reply_markup: kb });
}

export async function handleText(
  chatId: number,
  text: string,
  isManager = false
): Promise<void> {
  const trimmed = text.trim();
  if (!trimmed) return;

  if (isHumanMode(chatId)) {
    if (trimmed === "/ai" || trimmed === "/menu") {
      await exitHumanMode(chatId, true);
      return;
    }
    await forwardClientMessage(chatId, trimmed);
    return;
  }

  if (trimmed === MENU.CANCEL) {
    reset(chatId);
    await sendMessage(chatId, "Действие отменено.", {
      reply_markup: mainKeyboard(),
    });
    return;
  }

  const s = session(chatId);

  if (
    trimmed === MENU.WEBMASTER &&
    isManager
  ) {
    await sendWebmasterReport(chatId);
    return;
  }

  if (s.state === "idle" && MENU_BUTTONS.has(trimmed)) {
    await handleMenu(chatId, trimmed, isManager);
    return;
  }

  switch (s.state) {
    case "stock_part":
      s.draft.partNumber = trimmed;
      s.state = "stock_qty";
      await sendMessage(
        chatId,
        "Укажите количество (число) или отправьте «1»:",
        { reply_markup: cancelKeyboard() }
      );
      return;
    case "stock_qty": {
      const qty = parseQuantity(trimmed, 1);
      const part = s.draft.partNumber ?? "";
      reset(chatId);
      await runConsultant(
        chatId,
        `Остатки и цена по артикулу ${part}, количество ${qty}. Вызови get_stock_and_price.`
      );
      return;
    }
    case "specs_part":
      reset(chatId);
      await runConsultant(
        chatId,
        `Технические характеристики и даташит для артикула ${trimmed}. Вызови get_tech_specs.`
      );
      return;
    case "delivery_city":
      s.draft.city = trimmed;
      s.state = "delivery_product";
      await sendMessage(
        chatId,
        "Пришлите ссылку на карточку товара на lvt.market или числовой product_id:",
        { reply_markup: cancelKeyboard() }
      );
      return;
    case "delivery_product": {
      const pid = parseProductId(trimmed);
      if (!pid) {
        await sendMessage(
          chatId,
          "Не удалось определить product_id. Пришлите ссылку на карточку или число ID."
        );
        return;
      }
      s.draft.productId = pid;
      s.state = "delivery_qty";
      await sendMessage(
        chatId,
        "Количество для расчёта доставки (число, по умолчанию 1):",
        { reply_markup: cancelKeyboard() }
      );
      return;
    }
    case "delivery_qty": {
      const qty = parseQuantity(trimmed, 1);
      const city = s.draft.city ?? "";
      const pid = s.draft.productId!;
      reset(chatId);
      const pageContext: PageContext = {
        product_id: pid,
        city,
      };
      await runConsultant(
        chatId,
        `Рассчитай доставку в город ${city} для product_id ${pid}, количество ${qty}. Вызови get_delivery_quote с городом ${city}.`,
        pageContext
      );
      return;
    }
    case "lead_name":
      if (trimmed.length < 2) {
        await sendMessage(chatId, "Введите имя (минимум 2 символа):");
        return;
      }
      s.draft.leadName = trimmed;
      s.state = "lead_phone";
      await sendMessage(
        chatId,
        "Введите телефон (+7...) или отправьте контакт через кнопку «Поделиться контактом» в Telegram:",
        { reply_markup: cancelKeyboard() }
      );
      return;
    case "lead_phone": {
      const phone = normalizePhone(trimmed);
      if (!isValidPhone(phone)) {
        await sendMessage(chatId, "Некорректный телефон. Пример: +79001234567");
        return;
      }
      s.draft.leadPhone = phone;
      s.state = "lead_email";
      await sendMessage(
        chatId,
        "Email (необязательно). Нажмите «Пропустить email» или введите адрес:",
        { reply_markup: leadEmailKeyboard() }
      );
      return;
    }
    case "lead_email":
      if (trimmed === MENU.SKIP_EMAIL) {
        s.draft.leadEmail = "";
      } else if (trimmed.includes("@")) {
        s.draft.leadEmail = trimmed;
      } else {
        await sendMessage(chatId, "Введите корректный email или нажмите «Пропустить email».");
        return;
      }
      s.state = "lead_consent";
      await sendMessage(
        chatId,
        "Для отправки заявки нужно согласие на обработку персональных данных. Нажмите кнопку ниже:",
        { reply_markup: consentKeyboard() }
      );
      return;
    case "lead_consent":
      if (trimmed !== MENU.CONSENT_YES) {
        await sendMessage(chatId, "Нужно подтвердить согласие кнопкой «Согласен на обработку ПДн».", {
          reply_markup: consentKeyboard(),
        });
        return;
      }
      await finishLead(chatId, s);
      return;
    default:
      if (s.state !== "idle") return;
      await runConsultant(chatId, trimmed);
  }
}

export async function handleContact(
  chatId: number,
  phone: string
): Promise<void> {
  const s = session(chatId);
  if (s.state !== "lead_phone") return;
  const normalized = normalizePhone(phone);
  if (!isValidPhone(normalized)) {
    await sendMessage(chatId, "Не удалось прочитать номер из контакта.");
    return;
  }
  s.draft.leadPhone = normalized;
  s.state = "lead_email";
  await sendMessage(
    chatId,
    "Email (необязательно). Нажмите «Пропустить email» или введите адрес:",
    { reply_markup: leadEmailKeyboard() }
  );
}

async function handleMenu(
  chatId: number,
  action: string,
  isManager = false
): Promise<void> {
  const s = session(chatId);

  switch (action) {
    case MENU.WEBMASTER:
      if (!isManager) break;
      await sendWebmasterReport(chatId);
      return;
    case MENU.STOCK:
      s.state = "stock_part";
      s.draft = {};
      await sendMessage(chatId, "Введите артикул (part number):", {
        reply_markup: cancelKeyboard(),
      });
      break;
    case MENU.SPECS:
      s.state = "specs_part";
      s.draft = {};
      await sendMessage(chatId, "Введите артикул для тех. характеристик:", {
        reply_markup: cancelKeyboard(),
      });
      break;
    case MENU.DELIVERY:
      s.state = "delivery_city";
      s.draft = {};
      await sendMessage(chatId, "В какой город доставки?", {
        reply_markup: cancelKeyboard(),
      });
      break;
    case MENU.OPERATOR:
      reset(chatId);
      await enterHumanMode(chatId, "кнопка «Оператор»");
      break;
    case MENU.LEAD:
      s.state = "lead_name";
      s.draft = {};
      await sendMessage(chatId, "Заявка на звонок менеджера.\n\nКак вас зовут?", {
        reply_markup: cancelKeyboard(),
      });
      break;
    case MENU.FREE:
      reset(chatId);
      await sendMessage(
        chatId,
        "Напишите ваш вопрос — отвечу как консультант lvt.market:",
        { reply_markup: mainKeyboard() }
      );
      break;
    default:
      break;
  }
}

async function finishLead(chatId: number, s: ChatSession): Promise<void> {
  const name = s.draft.leadName ?? "";
  const phone = s.draft.leadPhone ?? "";
  const email = s.draft.leadEmail ?? "";
  const sessionId = `telegram:${chatId}`;

  await sendChatAction(chatId);
  const result = await submitLead({
    name,
    phone,
    email: email || undefined,
    sessionId,
    productId: s.draft.productId,
    consent: true,
    message: "Заявка из Telegram-бота",
  });

  reset(chatId);
  if (result.ok) {
    await sendMessage(
      chatId,
      "Заявка отправлена. Менеджер свяжется с вами в ближайшее время.",
      { reply_markup: mainKeyboard() }
    );
  } else {
    await sendMessage(
      chatId,
      `Не удалось отправить заявку (${result.error ?? "ошибка"}). Позвоните: +7 (495) 260-13-69`,
      { reply_markup: mainKeyboard() }
    );
  }
}

async function runConsultant(
  chatId: number,
  message: string,
  pageContext?: PageContext
): Promise<void> {
  await sendChatAction(chatId);
  const result = await askConsultant({ chatId, message, pageContext });
  if (!result.ok || !result.reply) {
    await sendMessage(
      chatId,
      `Сервис временно недоступен. Попробуйте позже или позвоните +7 (495) 260-13-69.`,
      { reply_markup: mainKeyboard() }
    );
    return;
  }
  await sendMessage(chatId, result.reply, { reply_markup: mainKeyboard() });

  if (result.handoff) {
    await enterHumanMode(
      chatId,
      result.handoff_reason ?? "эскалация от ИИ"
    );
  }
}
