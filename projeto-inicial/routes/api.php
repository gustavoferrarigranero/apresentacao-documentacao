<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', fn (): array => [
    'status' => 'ok',
    'application' => (string) config('app.name'),
]);
