<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'customer_name' => fake()->name(),
            'status' => OrderStatus::Pending,
            'total_in_cents' => 2590,
        ];
    }
}
