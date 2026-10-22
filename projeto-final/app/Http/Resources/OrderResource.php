<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant' => [
                'id' => $this->restaurant->id,
                'name' => $this->restaurant->name,
            ],
            'customer_name' => $this->customer_name,
            'status' => $this->status->value,
            'total_in_cents' => $this->total_in_cents,
            'items' => $this->items->map(fn ($item): array => [
                'name' => $item->name,
                'unit_price' => $item->unit_price,
                'quantity' => $item->quantity,
                'subtotal_in_cents' => $item->subtotal_in_cents,
            ]),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
