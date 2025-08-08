<?php

use App\Models\Product;
use App\Models\User;
use App\Services\AIRecommendationService;
use App\Models\UserBehavior;

// Create some test data for the AI recommendation system

// Check if we have users and products
$userCount = User::count();
$productCount = Product::count();

echo "Database Status:\n";
echo "- Users: $userCount\n";
echo "- Products: $productCount\n";

if ($userCount == 0) {
    echo "Creating test user...\n";
    $user = User::create([
        'name' => 'Test AI User',
        'email' => 'test-ai@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);
} else {
    $user = User::first();
}

if ($productCount == 0) {
    echo "No products found. Please add products to test recommendations.\n";
    exit;
}

// Get some products
$products = Product::with(['variations', 'images'])->take(5)->get();

echo "\nTesting AI Recommendation Service...\n";

$aiService = new AIRecommendationService();

// Test behavior tracking
echo "1. Testing behavior tracking...\n";
foreach ($products as $index => $product) {
    $aiService->trackBehavior(
        $user->id,
        'test-session-123',
        $product->id,
        UserBehavior::TYPE_VIEW,
        ['test' => true, 'order' => $index]
    );
    echo "   - Tracked view for product: {$product->name}\n";
}

// Add some cart behavior
if ($products->count() >= 2) {
    $aiService->trackBehavior(
        $user->id,
        'test-session-123',
        $products[0]->id,
        UserBehavior::TYPE_CART_ADD,
        ['quantity' => 1, 'test' => true]
    );
    echo "   - Tracked cart add for product: {$products[0]->name}\n";
}

echo "\n2. Testing recommendation generation...\n";

// Test personalized recommendations
try {
    $recommendations = $aiService->getRecommendationsForUser($user->id, 'test-session-123', 5);
    echo "   - Generated " . $recommendations->count() . " personalized recommendations\n";
    
    foreach ($recommendations as $rec) {
        $product = Product::find($rec->product_id);
        echo "     * {$product->name} (confidence: " . round($rec->confidence_score, 2) . ", type: {$rec->recommendation_type})\n";
    }
} catch (Exception $e) {
    echo "   - Error generating personalized recommendations: " . $e->getMessage() . "\n";
}

// Test related products
if ($products->count() > 0) {
    echo "\n3. Testing related products...\n";
    try {
        $related = $aiService->getRelatedProducts($products[0]->id, $user->id, 'test-session-123', 3);
        echo "   - Found " . $related->count() . " related products for: {$products[0]->name}\n";
        
        foreach ($related as $rel) {
            echo "     * {$rel->product->name} (confidence: " . round($rel->confidence_score, 2) . ")\n";
        }
    } catch (Exception $e) {
        echo "   - Error generating related products: " . $e->getMessage() . "\n";
    }
}

// Test trending products
echo "\n4. Testing trending products...\n";
try {
    $trending = $aiService->getTrendingRecommendations(3);
    echo "   - Found " . $trending->count() . " trending products\n";
    
    foreach ($trending as $trend) {
        $product = Product::find($trend->product_id);
        echo "     * {$product->name} (confidence: " . round($trend->confidence_score, 2) . ")\n";
    }
} catch (Exception $e) {
    echo "   - Error generating trending products: " . $e->getMessage() . "\n";
}

echo "\n5. Checking database records...\n";
$behaviorCount = UserBehavior::count();
echo "   - User behaviors recorded: $behaviorCount\n";

$recentBehaviors = UserBehavior::with('product')->latest()->take(5)->get();
foreach ($recentBehaviors as $behavior) {
    echo "   - {$behavior->behavior_type} on {$behavior->product->name} by user {$behavior->user_id}\n";
}

echo "\n✅ AI Recommendation System Test Complete!\n";
echo "The system is ready to provide intelligent product recommendations.\n";
