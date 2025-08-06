<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        
        foreach ($products as $product) {
            // Create main image
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'https://www.karkhanawala.in/wp-content/uploads/2020/04/RN-White-1.jpg',
                'is_main' => true,
            ]);
            
            // Create additional images (2-4 per product)
            $additionalImages = rand(2, 4);
            for ($i = 1; $i <= $additionalImages; $i++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => 'https://www.karkhanawala.in/wp-content/uploads/2020/04/RN-White-1.jpg',
                    'is_main' => false,
                ]);
            }
        }
    }
}
