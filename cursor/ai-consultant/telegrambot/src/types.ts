export type FlowState =
  | "idle"
  | "stock_part"
  | "stock_qty"
  | "specs_part"
  | "delivery_city"
  | "delivery_product"
  | "delivery_qty"
  | "lead_name"
  | "lead_phone"
  | "lead_email"
  | "lead_consent";

export interface ChatSession {
  state: FlowState;
  draft: {
    partNumber?: string;
    qty?: number;
    city?: string;
    productId?: number;
    leadName?: string;
    leadPhone?: string;
    leadEmail?: string;
  };
}

export interface TgUpdate {
  update_id: number;
  message?: TgMessage;
  callback_query?: {
    id: string;
    from: { id: number };
    message?: { chat: { id: number }; message_id: number };
    data?: string;
  };
}

export interface TgMessage {
  message_id: number;
  chat: { id: number; type: string };
  from?: {
    id: number;
    first_name?: string;
    last_name?: string;
    username?: string;
  };
  text?: string;
  contact?: { phone_number?: string };
  reply_to_message?: {
    message_id: number;
    text?: string;
  };
}

export interface PageContext {
  product_id?: number;
  city?: string;
}
