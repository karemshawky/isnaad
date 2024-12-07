<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * Get the product that owns the Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The ingredients that belong to the Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'order_ingredient', 'order_id', 'ingredient_product_id')
            ->withPivot(['used_quantity'])
            ->withTimestamps();
    }

    /**
     * Update the used stock of the ingredients after making an order.
     *
     * @param \App\Models\Order $order
     * @param \Illuminate\Database\Eloquent\Collection $ingredients
     * @param int $quantity
     * @return void
     */
    public static function saveOrderIngredient(Order $order, Collection $ingredients, int $quantity): void
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
    public static function updateIngredientProduct(Collection $ingredients, int $quantity): void
    {
        foreach ($ingredients as $ingredient) {
            $updateUsedStock = $ingredient->pivot->used_stock - $ingredient->pivot->used_ingredient * $quantity;
            $ingredient->pivot->update(['used_stock' => $updateUsedStock]);
            $ingredient->pivot->fresh();
        }
    }

    /**
     * Make an order and update the stock of the ingredients
     *
     * @param array $data
     * @return void
     */
    public static function makeOrder(array $data): void
    {
        DB::transaction(function () use ($data) {

            // 1- Check if the stock is enough
            Log::info('first step');
            $enoughIngredients = Product::isStockEnough((int) $data['product_id'], (int) $data['quantity']);

            // 2- Notify the merchant if the stock is under 50%
            Log::info('second step');
            $notifyIfStockUnderHalf = Product::isStockUnderHalf((int) $data['product_id']);

            // 3- Make the order
            Log::info('third step');
            $order = self::create($data);

            // 4- Update the stock
            // 4.1 - Persists data in order_ingredient table
            Log::info('fourth step');
            self::saveOrderIngredient($order, $enoughIngredients, (int) $data['quantity']);

            // 4.2 - fresh the stock in ingredient_product table by update the used_stock
            Log::info('fifth step');
            self::updateIngredientProduct($enoughIngredients, (int) $data['quantity']);
        });
    }
}
