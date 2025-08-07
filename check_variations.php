<?php

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

$product = Product::with('variations.attributeValues.attribute')->find(19);

echo "Product: " . $product->name . "\n";
echo "Available variations:\n";

foreach($product->variations as $variation) {
    $attrs = $variation->attributeValues->map(function($av) {
        return $av->attribute->name . ': ' . $av->value;
    })->join(' | ');
    echo "- " . $attrs . " (Stock: " . $variation->stock . ", Price: " . $variation->price . ")\n";
}
