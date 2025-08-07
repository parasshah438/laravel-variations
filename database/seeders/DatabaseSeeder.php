<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Cart;
use App\Models\Wishlist;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
    
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed e-commerce data
        $this->call([
            CategorySeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
            ProductImageSeeder::class,
            ProductVariationSeeder::class,
            CouponSeeder::class,
            AttributeSeeder::class,
            ProductVariationAttributesSeeder::class,
            ProductVariationImageSeeder::class, // Add this for variation-specific images
        ]);
    }
}
