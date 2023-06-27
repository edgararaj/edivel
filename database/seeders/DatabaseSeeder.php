<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(1)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        Product::factory(20)->create();
        Category::factory(5)->create();

        $categories = Category::all();
        Product::all()->each(function ($product) use ($categories) {
            $variants = ProductVariant::factory(10)->make();
            $variants->each(function ($variant) use ($product, $variants) {
                $slug = Str::slug($product->name . ' ' . $variant->color . ' '. $variant->size);
                $variant->slug = $slug;
                $product->variants()->save($variant);
            });
            $product->categories()->attach(
                $categories->random(2)->pluck('id')->toArray()
            );
        });

    }
}
