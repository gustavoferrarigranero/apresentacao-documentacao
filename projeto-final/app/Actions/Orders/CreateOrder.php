<?php

namespace App\Actions\Orders;

use App\DataTransferObjects\CreateOrderData;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;

final class CreateOrder
{
    public function execute(CreateOrderData $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $restaurant = Restaurant::query()
                ->whereKey($data->restaurantId)
                ->where('active', true)
                ->firstOrFail();

            $total = collect($data->items)
                ->sum(fn (array $item): int => $item['unit_price'] * $item['quantity']);

            $order = $restaurant->orders()->create([
                'customer_name' => $data->customerName,
                'status' => OrderStatus::Pending,
                'total_in_cents' => $total,
            ]);

            $order->items()->createMany(
                collect($data->items)
                    ->map(fn (array $item): array => [
                        ...$item,
                        'subtotal_in_cents' => $item['unit_price'] * $item['quantity'],
                    ])
                    ->all(),
            );

            return $order->load(['restaurant', 'items']);
        });
    }
}
