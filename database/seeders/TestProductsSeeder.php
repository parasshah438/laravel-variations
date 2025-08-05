<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Category;
use App\Models\Brand;

class TestProductsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get or create a category and brand
        $category = Category::first() ?? Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category'
        ]);
        
        $brand = Brand::first() ?? Brand::create([
            'name' => 'Test Brand',
            'slug' => 'test-brand'
        ]);

        // Product 1: Simple product with single variation (no attributes)
        $product1 = Product::create([
            'name' => 'Simple T-Shirt',
            'slug' => 'simple-t-shirt',
            'description' => 'A simple t-shirt with no variations',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'status' => 'active'
        ]);

        ProductVariation::create([
            'product_id' => $product1->id,
            'price' => 25.00,
            'stock' => 100
        ]);

        // Product 2: Simple product with single variation (no attributes)
        $product2 = Product::create([
            'name' => 'Basic Jeans',
            'slug' => 'basic-jeans',
            'description' => 'Basic jeans with no variations',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'status' => 'active'
        ]);

        ProductVariation::create([
            'product_id' => $product2->id,
            'price' => 45.00,
            'stock' => 50
        ]);

        // Product 3: Product with no variations (to test the edge case)
        $product3 = Product::create([
            'name' => 'Coming Soon Product',
            'slug' => 'coming-soon-product', 
            'description' => 'This product has no variations yet',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'status' => 'inactive'
        ]);
        // Intentionally no variations created for this product

        echo "Test products created:\n";
        echo "1. Simple T-Shirt (ID: {$product1->id}) - Single variation\n";
        echo "2. Basic Jeans (ID: {$product2->id}) - Single variation\n";
        echo "3. Coming Soon Product (ID: {$product3->id}) - No variations\n";
    }
}Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
    }
}
