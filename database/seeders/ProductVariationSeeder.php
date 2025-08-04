<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Seeder;

class ProductVariationSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'];
        $colors = ['Black', 'White', 'Red', 'Blue', 'Green', 'Yellow', 'Pink', 'Purple', 'Gray', 'Brown'];
        $fabrics = ['Cotton', 'Polyester', 'Denim', 'Silk', 'Wool', 'Linen', 'Viscose'];
        
        foreach ($products as $product) {
            $categoryName = $product->category->name;
            
            // Different variation logic based on product category
            if (in_array($categoryName, ['Men\'s Clothing', 'Women\'s Clothing'])) {
                // Clothing items - size, color, fabric variations
                $productSizes = array_slice($sizes, 0, rand(3, 5));
                $productColors = array_slice($colors, 0, rand(2, 4));
                $productFabrics = array_slice($fabrics, 0, rand(1, 2));
                
                foreach ($productSizes as $size) {
                    foreach ($productColors as $color) {
                        foreach ($productFabrics as $fabric) {
                            ProductVariation::create([
                                'product_id' => $product->id,
                                'size' => $size,
                                'color' => $color,
                                'fabric' => $fabric,
                                'price' => rand(25, 150) + (rand(0, 99) / 100),
                                'stock' => rand(0, 100),
                            ]);
                        }
                    }
                }
            } elseif ($categoryName === 'Electronics') {
                // Electronics - color and storage/model variations
                $productColors = array_slice(['Black', 'White', 'Silver', 'Gold', 'Blue'], 0, rand(2, 3));
                $storageOptions = ['64GB', '128GB', '256GB', '512GB', '1TB'];
                
                if (str_contains($product->name, 'iPhone') || str_contains($product->name, 'Samsung')) {
                    // Phone variations
                    foreach ($productColors as $color) {
                        foreach (array_slice($storageOptions, 0, 3) as $storage) {
                            ProductVariation::create([
                                'product_id' => $product->id,
                                'size' => $storage,
                                'color' => $color,
                                'fabric' => null,
                                'price' => rand(500, 1200) + (rand(0, 99) / 100),
                                'stock' => rand(5, 50),
                            ]);
                        }
                    }
                } else {
                    // Other electronics
                    foreach ($productColors as $color) {
                        ProductVariation::create([
                            'product_id' => $product->id,
                            'size' => null,
                            'color' => $color,
                            'fabric' => null,
                            'price' => rand(50, 800) + (rand(0, 99) / 100),
                            'stock' => rand(10, 100),
                        ]);
                    }
                }
            } else {
                // Other categories - simple variations
                $productColors = array_slice($colors, 0, rand(2, 4));
                
                foreach ($productColors as $color) {
                    ProductVariation::create([
                        'product_id' => $product->id,
                        'size' => null,
                        'color' => $color,
                        'fabric' => null,
                        'price' => rand(15, 200) + (rand(0, 99) / 100),
                        'stock' => rand(0, 150),
                    ]);
                }
            }
        }
    }
}
