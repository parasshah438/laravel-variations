<?php
$url = 'http://127.0.0.1:8000/visual-search/debug';
$response = file_get_contents($url);
$data = json_decode($response, true);

echo "=== VISUAL SEARCH DEBUG INFO ===\n";
echo "Total Products: " . $data['total_products'] . "\n";
echo "Active Products: " . $data['active_products'] . "\n";
echo "Products with Images: " . $data['products_with_images'] . "\n";
echo "Storage Path: " . $data['storage_path'] . "\n";
echo "Storage Exists: " . ($data['storage_exists'] ? 'YES' : 'NO') . "\n\n";

echo "=== PRODUCTS ===\n";
foreach ($data['products'] as $product) {
    echo "Product: " . $product['name'] . " (ID: " . $product['id'] . ")\n";
    echo "  Has white/shirt keywords: " . ($product['has_white_or_shirt'] ? 'YES' : 'NO') . "\n";
    
    if (!empty($product['images'])) {
        foreach ($product['images'] as $image) {
            echo "  Image: " . $image['path'] . "\n";
            echo "    Exists: " . ($image['exists'] ? 'YES' : 'NO') . "\n";
            if ($image['exists']) {
                echo "    Size: " . $image['size'] . " bytes\n";
            }
        }
    } else {
        echo "  No images\n";
    }
    echo "\n";
}
