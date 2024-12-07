<?php

namespace App\Models;

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
}
