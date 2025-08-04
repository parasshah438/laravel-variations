<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\AttributeValue;

class ProductImagesSeeder extends Seeder
{
    public function run(): void
    {
        // Sample image paths for different colors
        $colorImages = [
            'Red' => [
                'products/red-shirt-1.jpg',
                'products/red-shirt-2.jpg',
                'products/red-shirt-3.jpg',
            ],
            'Blue' => [
                'products/blue-shirt-1.jpg',
                'products/blue-shirt-2.jpg',
                'products/blue-shirt-3.jpg',
            ],
            'Black' => [
                'products/black-shirt-1.jpg',
                'products/black-shirt-2.jpg',
                'products/black-shirt-3.jpg',
            ],
            'White' => [
                'products/white-shirt-1.jpg',
                'products/white-shirt-2.jpg',
                'products/white-shirt-3.jpg',
            ],
        ];

        // Get some products to add variation-specific images
        $products = Product::with(['variations.attributeValues.attribute'])->take(5)->get();

        foreach ($products as $product) {
            // Add general product images first
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'products/general-' . $product->id . '-1.jpg',
                'is_main' => true,
                'sort_order' => 0
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'products/general-' . $product->id . '-2.jpg',
                'is_main' => false,
                'sort_order' => 1
            ]);

            // Add variation-specific images
            foreach ($product->variations as $variation) {
                // Find color attribute if exists
                $colorAttribute = $variation->attributeValues->where('attribute.name', 'Color')->first();
                
                if ($colorAttribute && isset($colorImages[$colorAttribute->value])) {
                    $images = $colorImages[$colorAttribute->value];
                    
                    foreach ($images as $index => $imagePath) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'product_variation_id' => $variation->id,
                            'variation_attribute_value_id' => $colorAttribute->id,
                            'image_path' => $imagePath,
                            'is_main' => $index === 0,
                            'sort_order' => $index
                        ]);
                    }
                }
            }
        }

        $this->command->info('Product images seeded successfully!');
    }
}
