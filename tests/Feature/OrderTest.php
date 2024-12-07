<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Setup the class.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_make_order_with_invalid_data()
    {
        $this->postJson('/api/v1/orders', [])
            ->assertUnprocessable();
    }
}
