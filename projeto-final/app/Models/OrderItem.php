<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit_price',
        'quantity',
        'subtotal_in_cents',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
