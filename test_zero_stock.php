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
    
    echo "=== ZERO STOCK TESTING ===\n";
    echo "Product: " . $product->name . "\n\n";
    
    echo "All variations with stock info:\n";
    $totalStock = 0;
    foreach($product->variations as $variation) {
        $attrs = $variation->attributeValues->map(function($av) {
            return $av->attribute->name . ': ' . $av->value;
        })->join(' | ');
        echo "- " . $attrs . " (Stock: " . $variation->stock . ")\n";
        $totalStock += $variation->stock;
    }
    
    echo "\nTotal Stock: " . $totalStock . "\n";
    
    echo "\n=== NEW BEHAVIOR (Show all, disable out-of-stock) ===\n";
    
    // Simulate new controller logic
    $availableAttributes = [];
    $allVariations = $product->variations; // Include ALL variations
    
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
                    'stock_count' => $stockCount,
                    'in_stock' => $stockCount > 0,
                    'status' => $stockCount > 0 ? 'AVAILABLE' : 'OUT OF STOCK'
                ];
            }
        }
    }
    
    echo "\nAttribute Options with Stock Status:\n";
    foreach($availableAttributes as $attrName => $attrValues) {
        echo "\n{$attrName}:\n";
        foreach($attrValues as $option) {
            $status = $option['in_stock'] ? '✅' : '❌';
            echo "  {$status} {$option['value']} (Stock: {$option['stock_count']}) - {$option['status']}\n";
        }
    }
    
    // Check if all are out of stock
    $allOutOfStock = true;
    foreach($availableAttributes as $attrName => $attrValues) {
        foreach($attrValues as $option) {
            if ($option['stock_count'] > 0) {
                $allOutOfStock = false;
                break 2;
            }
        }
    }
    
    echo "\n=== RESULT ===\n";
    if ($allOutOfStock) {
        echo "🚫 ALL VARIATIONS OUT OF STOCK\n";
        echo "- Will show warning: 'Currently Out of Stock - All variations are temporarily unavailable'\n";
        echo "- All buttons will be disabled with crossed-out text\n";
        echo "- Add to cart button will be disabled\n";
    } else {
        echo "✅ Some variations in stock\n";
        echo "- Will show normal interface\n";
        echo "- Out of stock options will be disabled\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
