<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentationRoutesTest extends TestCase
{
    public function test_swagger_ui_is_available(): void
    {
        $this->get('/docs/api')->assertOk();
        $this->get('/docs/api.json')->assertOk();
    }
}
