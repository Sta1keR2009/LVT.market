<?php

namespace App\Services\Suppliers\Data;

class SupplierOffer
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly string $provider,
        public readonly string $supplier,
        public readonly string $partNumber,
        public readonly ?string $brand = null,
        public readonly ?int $stock = null,
        public readonly ?int $minOrderQty = null,
        public readonly ?int $packSize = null,
        public readonly ?string $packaging = null,
        public readonly ?float $unitPrice = null,
        public readonly ?float $totalPrice = null,
        public readonly ?string $currency = null,
        public readonly ?int $leadTimeDays = null,
        public readonly ?string $url = null,
        public readonly ?string $note = null,
        public readonly array $raw = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'supplier' => $this->supplier,
            'part_number' => $this->partNumber,
            'brand' => $this->brand,
            'stock' => $this->stock,
            'min_order_qty' => $this->minOrderQty,
            'pack_size' => $this->packSize,
            'packaging' => $this->packaging,
            'unit_price' => $this->unitPrice,
            'total_price' => $this->totalPrice,
            'currency' => $this->currency,
            'lead_time_days' => $this->leadTimeDays,
            'url' => $this->url,
            'note' => $this->note,
            'raw' => $this->raw,
        ];
    }
}
