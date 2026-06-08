import mysql from "mysql2/promise";
import type { RowDataPacket } from "mysql2";
import type { LookupResult } from "./types.js";
import { config } from "./config.js";
import { compactPartNumber, normalizePartNumber } from "./utils.js";

export class BitrixSource {
  private pool: mysql.Pool | null = null;

  constructor() {
    if ((config.bitrixDbHost || config.bitrixDbSocket) && config.bitrixDbName && config.bitrixDbUser) {
      this.pool = mysql.createPool({
        host: config.bitrixDbSocket ? undefined : config.bitrixDbHost,
        socketPath: config.bitrixDbSocket || undefined,
        port: config.bitrixDbSocket ? undefined : config.bitrixDbPort,
        user: config.bitrixDbUser,
        password: config.bitrixDbPassword,
        database: config.bitrixDbName,
        connectionLimit: 4,
        supportBigNumbers: true
      });
    }
  }

  async findByPartNumber(partNumber: string): Promise<Partial<LookupResult> | null> {
    if (!this.pool) return null;
    const normalized = normalizePartNumber(partNumber);
    const compact = compactPartNumber(partNumber);
    if (!normalized) return null;

    const sql = `
      SELECT
        e.ID as element_id,
        e.NAME as product_name,
        p.IBLOCK_PROPERTY_ID as property_id,
        p.VALUE as matched_value
      FROM b_iblock_element e
      JOIN b_iblock_element_property p ON p.IBLOCK_ELEMENT_ID = e.ID
      WHERE e.IBLOCK_ID = 11
        AND e.ACTIVE = 'Y'
        AND p.IBLOCK_PROPERTY_ID IN (627, 103, 512)
        AND (
          UPPER(REPLACE(REPLACE(TRIM(COALESCE(p.VALUE, '')), ' ', ''), '/', '')) = ?
          OR UPPER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(COALESCE(p.VALUE, '')), ' ', ''), '/', ''), '-', ''), '.', '')) = ?
        )
      ORDER BY FIELD(p.IBLOCK_PROPERTY_ID, 627, 103, 512), e.ID DESC
      LIMIT 1
    `;
    const [rows] = await this.pool.query<RowDataPacket[]>(sql, [
      normalized,
      compact
    ]);
    const row = rows[0] as
      | {
          element_id: number;
          product_name: string;
          property_id: number;
          matched_value: string;
        }
      | undefined;
    if (!row) return null;
    return {
      source: "bitrix",
      confidence: "high",
      productName: row.product_name,
      notes: [
        `Matched in Bitrix iblock 11 by property ${row.property_id}`,
        `Matched value: ${row.matched_value}`
      ]
    };
  }
}
