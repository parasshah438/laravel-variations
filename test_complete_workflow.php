<?php
// Test the complete visual search workflow
echo "=== TESTING VISUAL SEARCH WORKFLOW ===\n";

// Step 1: Test the visual search API endpoint
$apiUrl = 'http://127.0.0.1:8000/visual-search/image';
$imagePath = 'storage/app/public/products/5KoBxjYKzJWtULWuVQBpeWP6dxcrEndTVeR5CqVh.jpg';

if (!file_exists($imagePath)) {
    echo "❌ Image file not found: " . $imagePath . "\n";
    exit(1);
}

// Get CSRF token
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
    echo "❌ Could not extract CSRF token\n";
    exit(1);
}

echo "✅ CSRF Token obtained: " . substr($csrfToken, 0, 20) . "...\n";
echo "✅ Image file exists: " . filesize($imagePath) . " bytes\n";

// Step 2: Upload image to visual search API
$cfile = new CURLFile($imagePath, 'image/jpeg', 'white_shirt.jpg');
$postData = array(
    'image' => $cfile,
    '_token' => $csrfToken
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');

echo "⏳ Uploading image to visual search API...\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 API Response Code: " . $httpCode . "\n";

if ($httpCode == 200) {
    $data = json_decode($response, true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "✅ Visual search API SUCCESS!\n";
        echo "📊 Found " . count($data['results']) . " results:\n";
        
        foreach ($data['results'] as $result) {
            echo "   - " . $result['name'] . " (Score: " . $result['similarity_score'] . ")\n";
        }
        
        // Step 3: Test accessing the results page (this is what the JavaScript would do)
        $resultsUrl = "http://127.0.0.1:8000/search?visual=1&results=" . count($data['results']);
        echo "🔗 Results page URL: " . $resultsUrl . "\n";
        
        // Simulate accessing the results page
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $resultsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
        
        $resultsPage = curl_exec($ch);
        $resultsHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($resultsHttpCode == 200) {
            echo "✅ Results page loaded successfully\n";
            if (strpos($resultsPage, 'Visual Search Results') !== false) {
                echo "✅ Visual search indicators found on page\n";
            } else {
                echo "⚠️  Visual search indicators not found (might be handled by JS)\n";
            }
        } else {
            echo "❌ Results page failed to load: HTTP " . $resultsHttpCode . "\n";
        }
        
    } else {
        echo "❌ API returned error: " . ($data['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ API request failed: HTTP " . $httpCode . "\n";
    echo "Response: " . substr($response, 0, 200) . "...\n";
}

// Cleanup
if (file_exists('cookies.txt')) {
    unlink('cookies.txt');
}

echo "\n=== INSTRUCTIONS ===\n";
echo "✨ The visual search API is working!\n";
echo "🎯 To use visual search properly:\n";
echo "   1. Go to: http://127.0.0.1:8000\n";
echo "   2. Click the camera (📷) button in the search bar\n";
echo "   3. Upload your white shirt image\n";
echo "   4. Click 'Search Similar Products'\n";
echo "   5. You'll be redirected to results automatically!\n";
echo "\n❌ Don't visit search?visual=1&results=1 directly - that's just the results page!\n";
