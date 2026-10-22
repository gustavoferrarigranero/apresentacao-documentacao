<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\CreateOrder;
use App\DataTransferObjects\CreateOrderData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, CreateOrder $createOrder): JsonResponse
    {
        $order = $createOrder->execute(CreateOrderData::fromArray($request->validated()));

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->load(['restaurant', 'items']));
    }
}
