<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\DB;

class ProductVariationAttributesSeeder extends Seeder
{
    public function run(): void
    {
        // First, let's create some basic attributes if they don't exist
        $sizeAttribute = Attribute::firstOrCreate(['name' => 'Size', 'type' => 'select']);
        $colorAttribute = Attribute::firstOrCreate(['name' => 'Color', 'type' => 'color']);
        $materialAttribute = Attribute::firstOrCreate(['name' => 'Material', 'type' => 'select']);

        // Create attribute values for Size
        $sizeValues = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        foreach ($sizeValues as $size) {
            AttributeValue::firstOrCreate([
                'attribute_id' => $sizeAttribute->id,
                'value' => $size
            ]);
        }

        // Create attribute values for Color
        $colorValues = [
            ['Red', '#FF0000'],
            ['Blue', '#0000FF'],
            ['Green', '#00FF00'],
            ['Black', '#000000'],
            ['White', '#FFFFFF'],
            ['Gray', '#808080'],
            ['Navy', '#000080'],
            ['Pink', '#FFC0CB'],
            ['Yellow', '#FFFF00'],
            ['Orange', '#FFA500'],
            ['Purple', '#800080'],
            ['Brown', '#A52A2A']
        ];
        foreach ($colorValues as $color) {
            AttributeValue::firstOrCreate([
                'attribute_id' => $colorAttribute->id,
                'value' => $color[0],
                'color_code' => $color[1] ?? null
            ]);
        }

        // Create attribute values for Material
        $materialValues = ['Cotton', 'Polyester', 'Cotton Blend', 'Denim', 'Silk', 'Wool', 'Linen', 'Leather'];
        foreach ($materialValues as $material) {
            AttributeValue::firstOrCreate([
                'attribute_id' => $materialAttribute->id,
                'value' => $material
            ]);
        }

        // Now let's assign attributes to existing product variations
        $product = Product::with('variations')->find(2); // Cotton Polo T-Shirt
        
        if ($product && $product->variations->count() > 0) {
            echo "Assigning attributes to {$product->variations->count()} variations of '{$product->name}'\n";
            
            $sizeAttributeValues = AttributeValue::where('attribute_id', $sizeAttribute->id)->get();
            $colorAttributeValues = AttributeValue::where('attribute_id', $colorAttribute->id)->get();
            $materialAttributeValues = AttributeValue::where('attribute_id', $materialAttribute->id)->get();
            
            foreach ($product->variations as $index => $variation) {
                // Clear existing attribute values
                DB::table('product_variation_attribute_values')
                    ->where('product_variation_id', $variation->id)
                    ->delete();
                
                // Assign size (cycle through sizes)
                $sizeIndex = $index % $sizeAttributeValues->count();
                $selectedSize = $sizeAttributeValues[$sizeIndex];
                
                // Assign color (cycle through colors)
                $colorIndex = $index % $colorAttributeValues->count();
                $selectedColor = $colorAttributeValues[$colorIndex];
                
                // Assign material (cycle through materials)
                $materialIndex = $index % $materialAttributeValues->count();
                $selectedMaterial = $materialAttributeValues[$materialIndex];
                
                // Insert into pivot table
                DB::table('product_variation_attribute_values')->insert([
                    ['product_variation_id' => $variation->id, 'attribute_value_id' => $selectedSize->id],
                    ['product_variation_id' => $variation->id, 'attribute_value_id' => $selectedColor->id],
                    ['product_variation_id' => $variation->id, 'attribute_value_id' => $selectedMaterial->id],
                ]);
                
                echo "Variation {$variation->id}: {$selectedSize->value}, {$selectedColor->value}, {$selectedMaterial->value}\n";
            }
        }
        
        // Also assign to other products if they exist
        $otherProducts = Product::with('variations')->where('id', '!=', 2)->take(5)->get();
        
        foreach ($otherProducts as $product) {
            if ($product->variations->count() > 0) {
                echo "\nAssigning attributes to {$product->variations->count()} variations of '{$product->name}'\n";
                
                foreach ($product->variations as $index => $variation) {
                    // Clear existing attribute values
                    DB::table('product_variation_attribute_values')
                        ->where('product_variation_id', $variation->id)
                        ->delete();
                    
                    // Assign random attributes
                    $sizeIndex = $index % $sizeAttributeValues->count();
                    $colorIndex = $index % $colorAttributeValues->count();
                    $materialIndex = $index % $materialAttributeValues->count();
                    
                    $selectedSize = $sizeAttributeValues[$sizeIndex];
                    $selectedColor = $colorAttributeValues[$colorIndex];
                    $selectedMaterial = $materialAttributeValues[$materialIndex];
                    
                    // Insert into pivot table
                    DB::table('product_variation_attribute_values')->insert([
                        ['product_variation_id' => $variation->id, 'attribute_value_id' => $selectedSize->id],
                        ['product_variation_id' => $variation->id, 'attribute_value_id' => $selectedColor->id],
                        ['product_variation_id' => $variation->id, 'attribute_value_id' => $selectedMaterial->id],
                    ]);
                    
                    echo "Variation {$variation->id}: {$selectedSize->value}, {$selectedColor->value}, {$selectedMaterial->value}\n";
                }
            }
        }
        
        echo "\nAttribute assignment completed!\n";
    }
}
