<?php

namespace Tests\Feature;

use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    public function test_project_starts_without_order_examples(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok');
    }
}
