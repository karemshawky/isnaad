<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class IngredientService
{
    /**
     * Update the used stock of the ingredients after making an order.
     *
     * @param \App\Models\Order $order
     * @param \Illuminate\Database\Eloquent\Collection $ingredients
     * @param int $quantity
     * @return void
     */
    public function saveOrderIngredient(Order $order, Collection $ingredients, int $quantity): void
    {
        $orderIngredient = [];

        foreach ($ingredients as $ingredient) {
            $orderIngredient[] = [
                'ingredient_product_id' => $ingredient->id,
                'used_quantity' => $ingredient->pivot->used_ingredient * $quantity
            ];
        }

        $order->ingredients()->attach($orderIngredient);
    }

    /**
     * Update the used stock of the ingredients after making an order.
     *
     * @param \Illuminate\Database\Eloquent\Collection $ingredients
     * @param int $quantity
     * @return void
     */
    public function updateIngredientProduct(Collection $ingredients, int $quantity): void
    {
        foreach ($ingredients as $ingredient) {
            $updateUsedStock = $ingredient->pivot->used_stock - $ingredient->pivot->used_ingredient * $quantity;
            $ingredient->pivot->update(['used_stock' => $updateUsedStock]);
            $ingredient->pivot->fresh();
        }
    }

}
