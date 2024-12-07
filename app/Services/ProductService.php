<?php

namespace App\Services;

use App\Models\Product;
use App\Mail\NotifyLowStockEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class ProductService
{
    /**
     * Check if the stock is enough for a product to make order.
     *
     * @param int $id
     * @param int $quantity
     * @return mixed
     */
    public function isStockEnough(int $id, int $quantity): mixed
    {
        $ingredients = Product::getIngredients($id)->get();

        foreach ($ingredients as $ingredient) {
            if ($ingredient->pivot->used_stock < $ingredient->pivot->used_ingredient * $quantity) {
                abort(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $ingredient->name . ' stock is not Enough ot make order');
            }
        }

        return $ingredients;
    }

    /**
     * Notify the merchant via email if the stock of any ingredient of the product
     * with the given id is under 50% and hasn't been notified before.
     *
     * @param int $id
     * @return void
     */
    public function isStockUnderHalf(int $id): void
    {
        $ingredients = Product::getIngredients($id)->where('low_stock', false)->get();

        foreach ($ingredients as $ingredient) {
            $ingredientStockPercentage = $ingredient->pivot->used_stock / $ingredient->pivot->main_stock * 100;

            if ($ingredientStockPercentage <= (float) 50) {

                $ingredient->pivot->update(['low_stock' => true]);
                $ingredient->pivot->fresh();

                Mail::to('merchant@example.com')->send(new NotifyLowStockEmail($ingredient));
            }
        }
    }
}
