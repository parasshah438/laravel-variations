<?php
// Test visual search by uploading the image file
$uploadUrl = 'http://127.0.0.1:8000/visual-search/image';
$imagePath = 'storage/app/public/products/5KoBxjYKzJWtULWuVQBpeWP6dxcrEndTVeR5CqVh.jpg';

if (!file_exists($imagePath)) {
    echo "Image file not found: " . $imagePath . "\n";
    exit(1);
}

echo "Testing visual search with image: " . $imagePath . "\n";
echo "Image size: " . filesize($imagePath) . " bytes\n";

// Create a POST request with the image
$cfile = new CURLFile($imagePath, 'image/jpeg', 'test_image.jpg');
$postData = array('image' => $cfile);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $uploadUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Response Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";

if ($httpCode == 200) {
    $data = json_decode($response, true);
    if ($data && isset($data['results'])) {
        echo "Found " . count($data['results']) . " results:\n";
        foreach ($data['results'] as $result) {
            echo "  - " . $result['name'] . " (Score: " . $result['similarity_score'] . ")\n";
        }
    } else {
        echo "No results found in response\n";
    }
} else {
    echo "Request failed\n";
}
