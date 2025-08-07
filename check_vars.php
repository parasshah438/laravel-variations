#!/usr/bin/env php
<?php

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

use App\Models\Product;

try {
    $product = Product::with('variations.attributeValues.attribute')->find(19);
    
    if (!$product) {
        echo "Product not found\n";
        exit;
    }
    
    echo "Product: " . $product->name . "\n";
    echo "Available variations:\n";
    
    foreach($product->variations as $variation) {
        $attrs = $variation->attributeValues->map(function($av) {
            return $av->attribute->name . ': ' . $av->value;
        })->join(' | ');
        echo "- " . $attrs . " (Stock: " . $variation->stock . ", Price: " . $variation->price . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
