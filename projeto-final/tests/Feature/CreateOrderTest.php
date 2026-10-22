<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_an_order_and_calculates_its_total(): void
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->postJson('/api/orders', [
            'restaurant_id' => $restaurant->id,
            'customer_name' => 'Ana Silva',
            'items' => [
                ['name' => 'Bowl Laravel', 'unit_price' => 2590, 'quantity' => 2],
                ['name' => 'Suco PHP', 'unit_price' => 790, 'quantity' => 1],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total_in_cents', 5970)
            ->assertJsonCount(2, 'data.items');

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Ana Silva',
            'total_in_cents' => 5970,
        ]);
    }

    public function test_rejects_an_order_without_items(): void
    {
        $restaurant = Restaurant::factory()->create();

        $this->postJson('/api/orders', [
            'restaurant_id' => $restaurant->id,
            'customer_name' => 'Ana Silva',
            'items' => [],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }

    public function test_shows_an_existing_order(): void
    {
        $restaurant = Restaurant::factory()->create();
        $order = $restaurant->orders()->create([
            'customer_name' => 'Ana Silva',
            'status' => 'paid',
            'total_in_cents' => 2590,
        ]);
        $order->items()->create([
            'name' => 'Bowl Laravel',
            'unit_price' => 2590,
            'quantity' => 1,
            'subtotal_in_cents' => 2590,
        ]);

        $this->getJson("/api/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.restaurant.name', $restaurant->name)
            ->assertJsonPath('data.status', 'paid');
    }
}
