<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Look for products with shirt or men in the name
$products = App\Models\Product::with(['images'])
    ->where(function($query) {
        $query->where('name', 'LIKE', '%shirt%')
              ->orWhere('name', 'LIKE', '%Shirt%')
              ->orWhere('name', 'LIKE', '%men%')
              ->orWhere('name', 'LIKE', '%Men%');
    })
    ->get();

echo "=== SHIRT/MEN PRODUCTS ===\n";
echo "Found " . $products->count() . " products\n\n";

foreach ($products as $product) {
    echo "Product: " . $product->name . " (ID: " . $product->id . ")\n";
    echo "  Slug: " . $product->slug . "\n";
    echo "  Status: " . $product->status . "\n";
    echo "  Images: " . $product->images->count() . "\n";
    
    foreach ($product->images as $image) {
        $fullPath = storage_path('app/public/' . $image->image_path);
        $exists = file_exists($fullPath);
        echo "    - " . $image->image_path . " (exists: " . ($exists ? 'YES' : 'NO') . ")\n";
        
        // Check if this is the specific image from the URL
        if (strpos($image->image_path, '5KoBxjYKzJWtULWuVQBpeWP6dxcrEndTVeR5CqVh.jpg') !== false) {
            echo "      *** THIS IS THE WHITE SHIRT IMAGE! ***\n";
        }
    }
    echo "\n";
}

// Also check if the specific image file exists
$targetImagePath = 'products/5KoBxjYKzJWtULWuVQBpeWP6dxcrEndTVeR5CqVh.jpg';
$fullTargetPath = storage_path('app/public/' . $targetImagePath);
echo "=== TARGET IMAGE CHECK ===\n";
echo "Looking for: " . $targetImagePath . "\n";
echo "Full path: " . $fullTargetPath . "\n";
echo "Exists: " . (file_exists($fullTargetPath) ? 'YES' : 'NO') . "\n";

if (file_exists($fullTargetPath)) {
    echo "Size: " . filesize($fullTargetPath) . " bytes\n";
    echo "Image info: " . print_r(getimagesize($fullTargetPath), true) . "\n";
}
