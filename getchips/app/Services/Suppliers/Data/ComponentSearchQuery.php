<?php

namespace App\Services\Suppliers\Data;

class ComponentSearchQuery
{
    public function __construct(
        public readonly string $componentNumber,
        public readonly int $amount = 1,
    ) {
    }
}
