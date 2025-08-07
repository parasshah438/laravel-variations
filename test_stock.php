<?php

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

use App\Models\Product;

try {
    $product = Product::with(['variations.attributeValues.attribute'])->find(19);
    
    if (!$product) {
        echo "Product not found\n";
        exit;
    }
    
    echo "=== STOCK TESTING ===\n";
    echo "Product: " . $product->name . "\n\n";
    
    echo "All variations with stock info:\n";
    foreach($product->variations as $variation) {
        $attrs = $variation->attributeValues->map(function($av) {
            return $av->attribute->name . ': ' . $av->value;
        })->join(' | ');
        echo "- " . $attrs . " (Stock: " . $variation->stock . ", Price: " . $variation->price . ")\n";
    }
    
    echo "\n=== TESTING STOCK CONDITIONS ===\n";
    
    // Simulate what your controller does
    $availableAttributes = [];
    $allVariations = $product->variations->where('stock', '>', 0); // Only in-stock
    
    echo "In-stock variations count: " . $allVariations->count() . "\n";
    echo "Out-of-stock variations will be excluded from frontend\n\n";
    
    foreach ($allVariations as $variation) {
        foreach ($variation->attributeValues as $attributeValue) {
            $attributeName = $attributeValue->attribute->name;
            
            if (!isset($availableAttributes[$attributeName])) {
                $availableAttributes[$attributeName] = [];
            }
            
            $exists = collect($availableAttributes[$attributeName])->contains('id', $attributeValue->id);
            
            if (!$exists) {
                $stockCount = $allVariations->filter(function($v) use ($attributeValue) {
                    return $v->attributeValues->contains('id', $attributeValue->id);
                })->sum('stock');
                
                $availableAttributes[$attributeName][] = [
                    'id' => $attributeValue->id,
                    'value' => $attributeValue->value,
                    'stock_count' => $stockCount
                ];
                
                echo "✅ {$attributeName}: {$attributeValue->value} (Stock: {$stockCount})\n";
            }
        }
    }
    
    echo "\n=== FINAL RESULT ===\n";
    echo "Variations with 0 stock will NOT appear in dropdown/buttons\n";
    echo "Variations with >0 stock will appear and be selectable\n";
    echo "Low stock (1-5) will show warning tooltip\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
