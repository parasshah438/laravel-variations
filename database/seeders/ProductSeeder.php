<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Men's Clothing
            [
                'name' => 'Classic Denim Jeans',
                'description' => 'Premium quality denim jeans with classic fit and comfortable wear',
                'category' => 'mens-clothing',
                'brand' => 'Levi\'s'
            ],
            [
                'name' => 'Cotton Polo T-Shirt',
                'description' => 'Breathable cotton polo shirt perfect for casual and semi-formal occasions',
                'category' => 'mens-clothing',
                'brand' => 'Polo Ralph Lauren'
            ],
            [
                'name' => 'Running Shoes',
                'description' => 'Lightweight running shoes with superior cushioning and grip',
                'category' => 'shoes',
                'brand' => 'Nike'
            ],
            
            // Women's Clothing
            [
                'name' => 'Floral Summer Dress',
                'description' => 'Beautiful floral print summer dress made from breathable fabric',
                'category' => 'womens-clothing',
                'brand' => 'Zara'
            ],
            [
                'name' => 'Skinny Fit Jeans',
                'description' => 'Stylish skinny fit jeans with stretch fabric for comfort',
                'category' => 'womens-clothing',
                'brand' => 'H&M'
            ],
            [
                'name' => 'Casual Blouse',
                'description' => 'Elegant casual blouse suitable for office and daily wear',
                'category' => 'womens-clothing',
                'brand' => 'Uniqlo'
            ],
            
            // Electronics
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Latest iPhone with advanced camera system and powerful A17 chip',
                'category' => 'smartphones',
                'brand' => 'Apple'
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'description' => 'Flagship Android smartphone with exceptional display and camera',
                'category' => 'smartphones',
                'brand' => 'Samsung'
            ],
            [
                'name' => 'Dell XPS 13 Laptop',
                'description' => 'Premium ultrabook with Intel Core processor and stunning display',
                'category' => 'laptops',
                'brand' => 'Dell'
            ],
            [
                'name' => 'Sony WH-1000XM5 Headphones',
                'description' => 'Industry-leading noise canceling wireless headphones',
                'category' => 'audio',
                'brand' => 'Sony'
            ],
            
            // More products for variety
            [
                'name' => 'Wireless Bluetooth Speaker',
                'description' => 'Portable Bluetooth speaker with excellent sound quality',
                'category' => 'audio',
                'brand' => 'Sony'
            ],
            [
                'name' => 'Cotton Bed Sheet Set',
                'description' => 'Soft and comfortable cotton bed sheet set in various colors',
                'category' => 'bedroom',
                'brand' => 'Amazon Basics'
            ],
            [
                'name' => 'LED Table Lamp',
                'description' => 'Modern LED table lamp with adjustable brightness',
                'category' => 'decor',
                'brand' => 'Philips'
            ],
            [
                'name' => 'Sports Water Bottle',
                'description' => 'Insulated stainless steel water bottle for sports and outdoor activities',
                'category' => 'fitness',
                'brand' => 'Nike'
            ],
            [
                'name' => 'Yoga Mat',
                'description' => 'Non-slip yoga mat perfect for home workouts and studio sessions',
                'category' => 'fitness',
                'brand' => 'Adidas'
            ],
            [
                'name' => 'Professional Camera',
                'description' => 'High-quality DSLR camera for professional photography',
                'category' => 'cameras',
                'brand' => 'Canon'
            ],
            [
                'name' => 'Kitchen Knife Set',
                'description' => 'Professional kitchen knife set with sharp stainless steel blades',
                'category' => 'kitchen',
                'brand' => 'Amazon Basics'
            ],
            [
                'name' => 'Moisturizing Face Cream',
                'description' => 'Hydrating face cream for all skin types with natural ingredients',
                'category' => 'skincare',
                'brand' => 'Philips'
            ],
        ];

        foreach ($products as $productData) {
            $category = Category::where('slug', $productData['category'])->first();
            $brand = Brand::where('name', $productData['brand'])->first();
            
            if ($category && $brand) {
                Product::create([
                    'name' => $productData['name'],
                    'slug' => Str::slug($productData['name']),
                    'description' => $productData['description'],
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                ]);
            }
        }
    }
}
