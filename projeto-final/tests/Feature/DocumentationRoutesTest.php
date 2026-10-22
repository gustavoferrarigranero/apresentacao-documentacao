<?php

namespace Tests\Feature;

use Tests\TestCase;

final class DocumentationRoutesTest extends TestCase
{
    public function test_swagger_ui_and_openapi_contract_are_available(): void
    {
        $this->get('/docs/api')->assertOk();
        $this->getJson('/docs/api.json')
            ->assertOk()
            ->assertJsonPath('openapi', '3.1.2')
            ->assertJsonStructure(['paths' => ['/orders']]);
    }
}
