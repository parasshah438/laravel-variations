<?php
// First get a CSRF token from the page
$homeUrl = 'http://127.0.0.1:8000';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $homeUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');

$homepage = curl_exec($ch);
curl_close($ch);

// Extract CSRF token
preg_match('/<meta name="csrf-token" content="([^"]+)"/', $homepage, $matches);
$csrfToken = $matches[1] ?? null;

if (!$csrfToken) {
    echo "Could not extract CSRF token\n";
    exit(1);
}

echo "CSRF Token: " . $csrfToken . "\n";

// Now test visual search with the token
$uploadUrl = 'http://127.0.0.1:8000/visual-search/image';
$imagePath = 'storage/app/public/products/5KoBxjYKzJWtULWuVQBpeWP6dxcrEndTVeR5CqVh.jpg';

if (!file_exists($imagePath)) {
    echo "Image file not found: " . $imagePath . "\n";
    exit(1);
}

echo "Testing visual search with image: " . $imagePath . "\n";

// Create a POST request with the image and CSRF token
$cfile = new CURLFile($imagePath, 'image/jpeg', 'test_image.jpg');
$postData = array(
    'image' => $cfile,
    '_token' => $csrfToken
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $uploadUrl);
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
    $data = json_decode($response, true);
    if ($data && isset($data['results'])) {
        echo "Found " . count($data['results']) . " results:\n";
        foreach ($data['results'] as $result) {
            echo "  - " . $result['name'] . " (Score: " . $result['similarity_score'] . ")\n";
        }
    } else {
        echo "Response: " . $response . "\n";
    }
} else {
    echo "Request failed. Response: " . substr($response, 0, 500) . "...\n";
}

// Cleanup
if (file_exists('cookies.txt')) {
    unlink('cookies.txt');
}
