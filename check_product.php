<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$product = Product::with(['images', 'variations.variationImages.productImage'])->find(19);

if ($product) {
    echo "Product: {$product->name}\n";
    echo "Total images in product_images: {$product->images->count()}\n";
    echo "Variations count: {$product->variations->count()}\n";
    
    foreach($product->variations as $variation) {
        echo "Variation {$variation->id} has {$variation->variationImages->count()} specific images\n";
    }
    
    // Find images that are NOT linked to any variation (general images)
    $variationImageIds = $product->variations->flatMap->variationImages->pluck('product_image_id');
    $generalImages = $product->images->whereNotIn('id', $variationImageIds);
    
    echo "Images that are NOT linked to any variation (general images): {$generalImages->count()}\n";
    
    echo "\nAll images:\n";
    foreach($product->images as $image) {
        $isLinkedToVariation = $variationImageIds->contains($image->id);
        echo "Image {$image->id}: {$image->image_path} - " . ($isLinkedToVariation ? 'Variation-specific' : 'General') . "\n";
    }
} else {
    echo "Product not found\n";
}
