<?php
// Test visual search through the main search page
$searchUrl = 'http://127.0.0.1:8000/search?visual=1';
$imagePath = 'storage/app/public/products/5KoBxjYKzJWtULWuVQBpeWP6dxcrEndTVeR5CqVh.jpg';

if (!file_exists($imagePath)) {
    echo "Image file not found: " . $imagePath . "\n";
    exit(1);
}

// First get a CSRF token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');

$homepage = curl_exec($ch);
curl_close($ch);

preg_match('/<meta name="csrf-token" content="([^"]+)"/', $homepage, $matches);
$csrfToken = $matches[1] ?? null;

if (!$csrfToken) {
    echo "Could not extract CSRF token\n";
    exit(1);
}

echo "Testing visual search through main search page\n";
echo "CSRF Token: " . $csrfToken . "\n";
echo "Image: " . $imagePath . " (" . filesize($imagePath) . " bytes)\n";

// Upload image to search page with visual=1
$cfile = new CURLFile($imagePath, 'image/jpeg', 'white_shirt.jpg');
$postData = array(
    'image' => $cfile,
    '_token' => $csrfToken,
    'visual' => '1'
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $searchUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Response Code: " . $httpCode . "\n";

if ($httpCode == 200) {
    // Check if it's JSON response
    $jsonData = json_decode($response, true);
    if ($jsonData) {
        if (isset($jsonData['success']) && $jsonData['success']) {
            echo "SUCCESS! Found results in JSON response\n";
            if (isset($jsonData['query_info'])) {
                echo "Total results: " . $jsonData['query_info']['total_results'] . "\n";
            }
        } else {
            echo "JSON response but no success\n";
            echo "Response: " . json_encode($jsonData, JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        // HTML response - check if it contains products
        if (strpos($response, 'men shirt') !== false) {
            echo "SUCCESS! Found 'men shirt' in HTML response\n";
        } else {
            echo "HTML response but no obvious results\n";
            echo "Response length: " . strlen($response) . " characters\n";
            // Show first 500 chars
            echo "First 500 chars:\n" . substr($response, 0, 500) . "\n";
        }
    }
} else {
    echo "Request failed with HTTP " . $httpCode . "\n";
    echo "Response: " . substr($response, 0, 500) . "\n";
}

// Cleanup
if (file_exists('cookies.txt')) {
    unlink('cookies.txt');
}
