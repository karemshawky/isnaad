<?php

namespace App\Services;

use App\Models\Order;
use App\Services\ProductService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Create a new MakeOrderService instance.
     *
     * @param  \App\Services\ProductService  $productService
     * @param  \App\Services\IngredientService  $ingredientService
     * @return void
     */
    public function __construct(
        protected ProductService $productService,
        protected IngredientService $ingredientService
    ) {}

    /**
     * Make an order and update the stock of the ingredients
     *
     * @param array $data
     * @return void
     */
    public function makeOrder(array $data): void
    {
        DB::transaction(function () use ($data) {

            // 1- Check if the stock is enough
            Log::info('first step');
            $enoughIngredients = $this->productService->isStockEnough((int) $data['product_id'], (int) $data['quantity']);

            // 2- Notify the merchant if the stock is under 50%
            Log::info('second step');
            $notifyIfStockUnderHalf = $this->productService->isStockUnderHalf((int) $data['product_id']);

            // 3- Make the order
            Log::info('third step');
            $order = Order::create($data);

            // 4- Update the stock
            // 4.1 - Persists data in order_ingredient table
            Log::info('fourth step');
            $this->ingredientService->saveOrderIngredient($order, $enoughIngredients, (int) $data['quantity']);

            // 4.2 - fresh the stock in ingredient_product table by update the used_stock
            Log::info('fifth step');
            $this->ingredientService->updateIngredientProduct($enoughIngredients, (int) $data['quantity']);
        });
    }
}
