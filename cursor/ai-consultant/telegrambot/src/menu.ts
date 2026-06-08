import type { ReplyKeyboard } from "./telegram.js";

export const MENU = {
  WEBMASTER: "📊 Вебмастер",
  STOCK: "📦 Остатки и цена",
  DELIVERY: "🚚 Доставка",
  SPECS: "📋 Тех. характеристики",
  OPERATOR: "👤 Оператор",
  LEAD: "📞 Заказать звонок",
  FREE: "💬 Свободный вопрос",
  CANCEL: "❌ Отмена",
  SKIP_EMAIL: "Пропустить email",
  CONSENT_YES: "✅ Согласен на обработку ПДн",
} as const;

export const MENU_BUTTONS = new Set<string>(Object.values(MENU));

export function mainKeyboard(): ReplyKeyboard {
  return {
    keyboard: [
      [MENU.STOCK, MENU.DELIVERY],
      [MENU.SPECS, MENU.OPERATOR],
      [MENU.LEAD, MENU.FREE],
    ],
    resize_keyboard: true,
  };
}

/** Клавиатура для менеджеров (личный чат с ботом) */
export function managerMainKeyboard(): ReplyKeyboard {
  return {
    keyboard: [
      [MENU.WEBMASTER],
      [MENU.STOCK, MENU.DELIVERY],
      [MENU.SPECS, MENU.OPERATOR],
      [MENU.LEAD, MENU.FREE],
    ],
    resize_keyboard: true,
  };
}

export function cancelKeyboard(): ReplyKeyboard {
  return {
    keyboard: [[MENU.CANCEL]],
    resize_keyboard: true,
    one_time_keyboard: true,
  };
}

export function consentKeyboard(): ReplyKeyboard {
  return {
    keyboard: [[MENU.CONSENT_YES], [MENU.CANCEL]],
    resize_keyboard: true,
    one_time_keyboard: true,
  };
}

export function leadEmailKeyboard(): ReplyKeyboard {
  return {
    keyboard: [[MENU.SKIP_EMAIL], [MENU.CANCEL]],
    resize_keyboard: true,
    one_time_keyboard: true,
  };
}

export const WELCOME =
  "Здравствуйте! Я консультант lvt.market — помогу с остатками, доставкой и характеристиками.\n\n" +
  "Выберите пункт меню или задайте вопрос текстом.";
