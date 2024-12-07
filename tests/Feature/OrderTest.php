<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Ingredient;
use App\Mail\NotifyLowStockEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public $product, $ingredient;

    /**
     * Setup the test environment.
     *
     * This function is called before each test case. It calls the parent
     * implementation to setup the environment, and then enables exception
     * handling for the test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create();
        $this->ingredient = Ingredient::factory()->create();
    }

    /**
     * This test case tests that the order creation endpoint returns a 422
     * response when making a POST call with invalid data.
     */
    public function test_make_order_with_invalid_data(): void
    {
        $response = $this->postJson(route('orders.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'product_id' => 'The product id field is required.',
                'quantity' => 'The quantity field is required.'
            ]);
    }

    /**
     * This test case tests that the order creation endpoint returns a 422
     * response when making a POST call with valid data but the stock of one
     * of the product's ingredients is not enough to make the order.
     */
    public function test_check_if_stock_is_enough_for_make_order(): void
    {
        $productIngredients = $this->product->ingredients()->attach([
            $this->ingredient->id => [
                'used_ingredient' => fake()->numberBetween(50, 100),
                'main_stock' => fake()->numberBetween(1000, 5000),
                'used_stock' => fake()->numberBetween(100, 500),
                'low_stock' => false
            ]
        ]);

        $orderDetails = [
            'product_id' => $this->product->id,
            'quantity' => fake()->numberBetween(10, 20)
        ];

        $response = $this->postJson(route('orders.store'), $orderDetails);

        $response->assertUnprocessable()
            ->assertJson([
                'message' => $this->ingredient->name . ' stock is not Enough ot make order',
            ]);
    }

    /**
     * This test case tests that the order creation endpoint sends a notification
     * email when one of the product's ingredients stock level is under 50% and
     * hasn't been notified before.
     */
    public function test_check_is_stock_under_half(): void
    {
        Mail::fake();

        $productIngredients = $this->product->ingredients()->attach([
            $this->ingredient->id => [
                'used_ingredient' => fake()->numberBetween(10, 20),
                'main_stock' => fake()->numberBetween(800, 1000),
                'used_stock' => fake()->numberBetween(300, 400),
                'low_stock' => false
            ]
        ]);

        $orderDetails = [
            'product_id' => $this->product->id,
            'quantity' => fake()->numberBetween(10, 20)
        ];

        $response = $this->postJson(route('orders.store'), $orderDetails);

        $response->assertCreated();

        $this->assertTrue((bool) $this->product->ingredients()->find($this->ingredient->id)->pivot->low_stock);

        Mail::assertSent(NotifyLowStockEmail::class, function ($mail) {
            return $mail->hasTo('merchant@example.com');
        });
    }

    /**
     * This test case tests that the order creation endpoint returns a 201
     * response, the order is stored in the database, and the stock of the
     * ingredients is correctly updated.
     */
    public function test_make_order_successfully(): void
    {
        $productIngredients = $this->product->ingredients()->attach([
            $this->ingredient->id => [
                'used_ingredient' => fake()->numberBetween(10, 20),
                'main_stock' => fake()->numberBetween(800, 1000),
                'used_stock' => fake()->numberBetween(500, 800),
                'low_stock' => false
            ]
        ]);

        $ingredient = $this->product->ingredients()->find($this->ingredient->id);

        $orderDetails = [
            'product_id' => $this->product->id,
            'quantity' => fake()->numberBetween(10, 20)
        ];

        $response = $this->postJson(route('orders.store'), $orderDetails);

        $response->assertCreated()
            ->assertJson([
                'message' => 'Order created successfully.',
            ]);

        $this->assertDatabaseHas('orders', [
            'product_id' => $orderDetails['product_id'],
            'quantity' => $orderDetails['quantity'],
        ]);

        $this->assertDatabaseHas('order_ingredient', [
            'ingredient_product_id' => $ingredient->id,
            'used_quantity' => $orderDetails['quantity'] * $ingredient->pivot->used_ingredient,
        ]);
    }
}
