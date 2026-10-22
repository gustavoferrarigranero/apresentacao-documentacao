<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::query()->create([
            'name' => 'ClinicFlow Kitchen',
            'active' => true,
        ]);

        $order = $restaurant->orders()->create([
            'customer_name' => 'Ana Silva',
            'status' => OrderStatus::Paid,
            'total_in_cents' => 2590,
        ]);

        $order->items()->create([
            'name' => 'Bowl Laravel',
            'unit_price' => 2590,
            'quantity' => 1,
            'subtotal_in_cents' => 2590,
        ]);
    }
}
