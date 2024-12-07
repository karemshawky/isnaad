<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $burger = Product::create([
            'name' => 'Burger',
            'description' => 'beef-burger'
        ]);

        $beef = Ingredient::create([
            'name' => 'Beef',
            'description' => 'beef',
        ]);

        $cheese = Ingredient::create([
            'name' => 'Cheese',
            'description' => 'cheese',
        ]);

        $onion = Ingredient::create([
            'name' => 'Onion',
            'description' => 'Onion',
        ]);

        $burger->ingredients()->attach([
            $beef->id => ['used_ingredient' => 150, 'main_stock' => 20000, 'used_stock' => 12000],
            $cheese->id => ['used_ingredient' => 30, 'main_stock' => 5000, 'used_stock' => 3500],
            $onion->id => ['used_ingredient' => 20, 'main_stock' => 1000, 'used_stock' => 800],
        ]);
    }
}
