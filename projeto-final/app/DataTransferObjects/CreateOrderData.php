<?php

namespace App\DataTransferObjects;

final readonly class CreateOrderData
{
    public function __construct(
        public int $restaurantId,
        public string $customerName,
        public array $items,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            restaurantId: $data['restaurant_id'],
            customerName: $data['customer_name'],
            items: $data['items'],
        );
    }
}
