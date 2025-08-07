<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ProductImage;
use App\Models\ProductVariationImage;
use App\Models\AttributeValue;

class ProductVariationImageSeeder extends Seeder
{
    public function run()
    {
        // Get products with variations and images
        $products = Product::with(['variations.attributeValues.attribute', 'images'])
                          ->whereHas('variations')
                          ->whereHas('images')
                          ->get();

        foreach ($products as $product) {
            echo "Processing product: {$product->name}\n";
            
            // Group variations by color attribute if exists
            $colorAttribute = null;
            $colorVariations = [];
            
            foreach ($product->variations as $variation) {
                foreach ($variation->attributeValues as $attributeValue) {
                    if (strtolower($attributeValue->attribute->name) === 'color') {
                        $colorAttribute = $attributeValue->attribute;
                        $colorVariations[$attributeValue->value][] = $variation;
                    }
                }
            }
            
            if (empty($colorVariations)) {
                // No color variations, assign all images to all variations
                echo "  No color variations found, assigning all images to all variations\n";
                foreach ($product->variations as $variation) {
                    foreach ($product->images as $index => $image) {
                        ProductVariationImage::updateOrCreate([
                            'product_variation_id' => $variation->id,
                            'product_image_id' => $image->id,
                        ], [
                            'sort_order' => $index
                        ]);
                    }
                }
            } else {
                // Assign images based on color
                echo "  Found color variations: " . implode(', ', array_keys($colorVariations)) . "\n";
                
                $imageGroups = $this->groupImagesByColor($product->images, array_keys($colorVariations));
                
                foreach ($colorVariations as $colorName => $variations) {
                    $colorImages = $imageGroups[$colorName] ?? $imageGroups['general'] ?? $product->images->take(2);
                    
                    echo "    Assigning " . $colorImages->count() . " images to {$colorName} variations\n";
                    
                    foreach ($variations as $variation) {
                        foreach ($colorImages as $index => $image) {
                            ProductVariationImage::updateOrCreate([
                                'product_variation_id' => $variation->id,
                                'product_image_id' => $image->id,
                            ], [
                                'sort_order' => $index
                            ]);
                        }
                    }
                }
            }
            
            echo "  ✅ Completed {$product->name}\n\n";
        }
        
        echo "🎉 Product variation images seeded successfully!\n";
    }
    
    /**
     * Group images by color based on filename or distribute evenly
     */
    private function groupImagesByColor($images, $colors)
    {
        $groups = ['general' => collect()];
        
        // Initialize groups for each color
        foreach ($colors as $color) {
            $groups[strtolower($color)] = collect();
        }
        
        // Try to assign images based on filename patterns
        foreach ($images as $image) {
            $filename = strtolower($image->image_path);
            $assigned = false;
            
            foreach ($colors as $color) {
                if (strpos($filename, strtolower($color)) !== false) {
                    $groups[strtolower($color)]->push($image);
                    $assigned = true;
                    break;
                }
            }
            
            if (!$assigned) {
                $groups['general']->push($image);
            }
        }
        
        // If some colors don't have images, distribute general images
        foreach ($colors as $color) {
            if ($groups[strtolower($color)]->isEmpty() && $groups['general']->isNotEmpty()) {
                // Assign first 2 images from general to this color
                $groups[strtolower($color)] = $groups['general']->take(2);
            }
        }
        
        return $groups;
    }
}
