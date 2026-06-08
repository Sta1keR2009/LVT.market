<?php

namespace App\Services\Suppliers\Contracts;

use App\Services\Suppliers\Data\ComponentSearchQuery;
use App\Services\Suppliers\Data\SupplierOffer;

interface SupplierSearchProviderInterface
{
    public function name(): string;

    /**
     * @return array<int, SupplierOffer>
     */
    public function search(ComponentSearchQuery $query): array;
}
