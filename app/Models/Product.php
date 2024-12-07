<?php

namespace App\Models;

use Illuminate\Http\JsonResponse;
use App\Mail\NotifyLowStockEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * The ingredients that belong to the Product
     *
     * @return BelongsToMany
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_product', 'product_id', 'ingredient_id')
            ->withPivot(['used_ingredient', 'main_stock', 'used_stock', 'low_stock'])
            ->withTimestamps();
    }

    /**
     * The orders that belong to the Product
     *
     * @return BelongsToMany
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_product', 'product_id', 'order_id')
            ->withPivot(['used_quantity'])
            ->withTimestamps();
    }

    /**
     * Get the ingredients that belong to the Product
     *
     * @param int $id
     * @return mixed
     */
    public static function getIngredients(int $id)
    {
        return self::whereId($id)->firstOrFail()->ingredients();
    }

    /**
     * Check if the stock is enough for a product to make order.
     *
     * @param int $id
     * @param int $quantity
     * @return mixed
     */
    public static function isStockEnough(int $id, int $quantity): mixed
    {
        $ingredients = self::getIngredients($id)->get();

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
    public static function isStockUnderHalf(int $id): void
    {
        $ingredients = self::getIngredients($id)->where('low_stock', false)->get();

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
