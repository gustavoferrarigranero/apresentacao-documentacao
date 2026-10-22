<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;

class OrderController extends Controller {

    public function index()
    {
        return [
            'data' => [
                OrderStatus::CANCELLED->name => OrderStatus::CANCELLED->value,
            ]
        ];
    }

}
