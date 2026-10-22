<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn (): array => [
    'status' => 'ok',
    'application' => (string) config('app.name'),
]);

Route::get('order', [OrderController::class, 'index']);
