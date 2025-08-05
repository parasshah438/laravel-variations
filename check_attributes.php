<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\AttributeValue;

$product = Product::with(['variations.attributeValues.attribute'])->find(1);

echo "=== Product: " . $product->name . " ===\n";
echo "Total variations: " . $product->variations->count() . "\n\n";

// Find XXL and White attributes
$xxlAttr = AttributeValue::whereHas('attribute', function($q) { 
    $q->where('name', 'Size'); 
})->where('value', 'XXL')->first();

$whiteAttr = AttributeValue::whereHas('attribute', function($q) { 
    $q->where('name', 'Color'); 
})->where('value', 'White')->first();

echo "XXL attribute ID: " . ($xxlAttr ? $xxlAttr->id : 'Not found') . "\n";
echo "White attribute ID: " . ($whiteAttr ? $whiteAttr->id : 'Not found') . "\n\n";

echo "=== Variations with XXL and White ===\n";
foreach ($product->variations as $variation) {
    $attrs = $variation->attributeValues;
    $hasXXL = $attrs->contains('id', $xxlAttr->id ?? -1);
    $hasWhite = $attrs->contains('id', $whiteAttr->id ?? -1);
    
    if ($hasXXL && $hasWhite) {
        echo "✓ Variation " . $variation->id . " (Stock: " . $variation->stock . "): ";
        foreach ($attrs as $attr) {
            echo $attr->attribute->name . "=" . $attr->value . " (ID:" . $attr->id . ") ";
        }
        echo "\n";
    }
}

echo "\n=== All Variations ===\n";
foreach ($product->variations as $variation) {
    echo "Variation " . $variation->id . " (Stock: " . $variation->stock . "): ";
    foreach ($variation->attributeValues as $attr) {
        echo $attr->attribute->name . "=" . $attr->value . " (ID:" . $attr->id . ") ";
    }
    echo "\n";
}
