<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'name' => 'Bowl Laravel',
            'unit_price' => 2590,
            'quantity' => 1,
            'subtotal_in_cents' => 2590,
        ];
    }
}
