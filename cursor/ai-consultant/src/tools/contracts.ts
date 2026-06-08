/**
 * Единый контракт ответов tools для LLM и логирования.
 */

export interface StockPriceResult {
  ok: boolean;
  part_number?: string;
  product_name?: string;
  quantity_requested?: number;
  availability_text?: string;
  availability_in_stock?: string;
  lead_time?: string;
  price_breaks?: Array<{ quantity: number; price: string; currency?: string }>;
  product_url?: string;
  source?: string;
  error?: string;
}

export interface DeliveryQuoteResult {
  ok: boolean;
  city?: string;
  product_name?: string;
  quantity?: number;
  pickupDaysShift?: number;
  inboundDaysToLytkarino?: number;
  inboundShiftNote?: string;
  pickup?: {
    name: string;
    locations: string[];
    price: number;
    price_formatted?: string;
  };
  deliveries?: Array<{
    operator: string;
    operator_name?: string;
    rate_name?: string;
    period_text?: string;
    delivery_day?: string;
    price: number;
    price_formatted?: string;
  }>;
  disclaimer?: string;
  error?: string;
}

export interface AnalogRow {
  manufacturer_part_number: string;
  manufacturer?: string;
  description?: string;
  availability?: string;
  notes?: string;
}

export interface AnalogsResult {
  ok: boolean;
  query: string;
  alternatives: AnalogRow[];
  guidance?: string;
  error?: string;
}

export interface TechSpecsResult {
  ok: boolean;
  part_number?: string;
  description?: string;
  attributes?: Array<{ name: string; value: string }>;
  lifecycle?: string;
  rohs?: string;
  datasheet_url?: string;
  error?: string;
}
