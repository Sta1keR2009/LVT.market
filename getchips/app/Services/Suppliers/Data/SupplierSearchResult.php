<?php

namespace App\Services\Suppliers\Data;

class SupplierSearchResult
{
    /**
     * @param array<int, SupplierOffer> $offers
     * @param array<int, string> $errors
     * @param array<string, float> $timingsMs
     */
    public function __construct(
        public readonly array $offers,
        public readonly array $errors = [],
        public readonly array $timingsMs = [],
    ) {
    }
}
