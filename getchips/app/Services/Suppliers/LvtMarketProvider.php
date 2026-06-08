<?php

namespace App\Services\Suppliers;

use App\Services\Suppliers\Contracts\SupplierSearchProviderInterface;
use App\Services\Suppliers\Data\ComponentSearchQuery;
use App\Services\Suppliers\Data\SupplierOffer;

class LvtMarketProvider implements SupplierSearchProviderInterface
{
    public function __construct(
        private readonly LvtMarketApiClient $client,
    ) {}

    public function name(): string
    {
        return 'lvt.market';
    }

    /**
     * @return array<int, SupplierOffer>
     */
    public function search(ComponentSearchQuery $query): array
    {
        $rows = $this->client->search($query->componentNumber, $query->amount);
        $offers = [];
        foreach ($rows as $row) {
            $partNumber = $this->pickString($row, ['partNumber', 'part_number', 'title'], $query->componentNumber);
            $unitPrice = $this->pickFloat($row, ['unitPrice', 'unit_price', 'price']);
            $offers[] = new SupplierOffer(
                provider: $this->name(),
                supplier: $this->pickString($row, ['supplier', 'vendor', 'donor'], 'LVT Market'),
                partNumber: $partNumber,
                brand: $this->pickString($row, ['brand', 'manufacturer', 'brandName']),
                stock: $this->pickInt($row, ['stock', 'qty', 'quantity']),
                minOrderQty: $this->pickInt($row, ['minOrderQty', 'min_order_qty', 'minq', 'eQuantity']),
                packSize: $this->pickInt($row, ['packSize', 'pack_size', 'sPack']),
                packaging: $this->pickString($row, ['packaging', 'pack']),
                unitPrice: $unitPrice,
                totalPrice: $this->pickFloat($row, ['totalPrice', 'total_price', 'summ']) ?? ($unitPrice !== null ? $unitPrice * $query->amount : null),
                currency: $this->pickString($row, ['currency', 'currencyCode'], 'USD'),
                leadTimeDays: $this->pickInt($row, ['leadTimeDays', 'lead_time_days', 'orderdays']),
                url: $this->pickString($row, ['url', 'link', 'catalogUrl']),
                note: $this->pickString($row, ['note', 'comment']),
                raw: $row,
            );
        }

        return $offers;
    }

    /** @param array<string, mixed> $row */
    private function pickString(array $row, array $keys, ?string $default = null): ?string
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== '') {
                return (string) $row[$key];
            }
        }

        return $default;
    }

    /** @param array<string, mixed> $row */
    private function pickInt(array $row, array $keys): ?int
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && is_numeric($row[$key])) {
                return (int) $row[$key];
            }
        }

        return null;
    }

    /** @param array<string, mixed> $row */
    private function pickFloat(array $row, array $keys): ?float
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && is_numeric($row[$key])) {
                return (float) $row[$key];
            }
        }

        return null;
    }
}
