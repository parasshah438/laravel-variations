<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class VisualSearchService
{
    protected $googleVisionApiKey;
    protected $searchThreshold = 0.7; // Similarity threshold

    public function __construct()
    {
        $this->googleVisionApiKey = config('services.google.vision_api_key');
    }

    /**
     * Find similar products based on uploaded image
     * 
     * @param string $imagePath Path to the uploaded image
     * @return array Array of similar products
     */
    public function findSimilarProducts($imagePath)
    {
        try {
            // Method 1: Use Google Vision API (if configured)
            if ($this->googleVisionApiKey) {
                return $this->searchWithGoogleVision($imagePath);
            }
            
            // Method 2: Use basic image analysis (fallback)
            return $this->searchWithBasicAnalysis($imagePath);
            
        } catch (\Exception $e) {
            Log::error('Visual search service error: ' . $e->getMessage());
            return $this->getFallbackResults();
        }
    }

    /**
     * Search using Google Vision API
     */
    protected function searchWithGoogleVision($imagePath)
    {
        try {
            // Read and encode image
            $imageContent = base64_encode(file_get_contents($imagePath));
            
            // Google Vision API request
            $response = Http::post("https://vision.googleapis.com/v1/images:annotate?key={$this->googleVisionApiKey}", [
                'requests' => [
                    [
                        'image' => [
                            'content' => $imageContent
                        ],
                        'features' => [
                            ['type' => 'PRODUCT_SEARCH', 'maxResults' => 20],
                            ['type' => 'LABEL_DETECTION', 'maxResults' => 10],
                            ['type' => 'OBJECT_LOCALIZATION', 'maxResults' => 10]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->processGoogleVisionResults($data);
            }
            
            // Fallback if API call fails
            return $this->searchWithBasicAnalysis($imagePath);
            
        } catch (\Exception $e) {
            Log::error('Google Vision API error: ' . $e->getMessage());
            return $this->searchWithBasicAnalysis($imagePath);
        }
    }

    /**
     * Process Google Vision API results
     */
    protected function processGoogleVisionResults($apiResponse)
    {
        $detectedLabels = [];
        $searchTerms = [];

        // Extract labels and objects
        if (isset($apiResponse['responses'][0]['labelAnnotations'])) {
            foreach ($apiResponse['responses'][0]['labelAnnotations'] as $label) {
                if ($label['score'] > 0.7) {
                    $detectedLabels[] = strtolower($label['description']);
                }
            }
        }

        if (isset($apiResponse['responses'][0]['localizedObjectAnnotations'])) {
            foreach ($apiResponse['responses'][0]['localizedObjectAnnotations'] as $object) {
                if ($object['score'] > 0.7) {
                    $detectedLabels[] = strtolower($object['name']);
                }
            }
        }

        // Convert labels to search terms
        $searchTerms = $this->convertLabelsToSearchTerms($detectedLabels);
        
        // Search products based on detected terms
        return $this->searchProductsByTerms($searchTerms);
    }

    /**
     * Basic image analysis (fallback method)
     */
    protected function searchWithBasicAnalysis($imagePath)
    {
        try {
            // Get image properties
            $imageInfo = getimagesize($imagePath);
            $dominantColors = $this->extractDominantColors($imagePath);
            
            // First, try to find products with similar images
            $imageResults = $this->searchBySimilarImages($imagePath);
            if (!empty($imageResults)) {
                return $imageResults;
            }
            
            // If no similar images found, use color-based search
            $searchTerms = $this->analyzeImageBasic($imagePath, $dominantColors);
            
            return $this->searchProductsByTerms($searchTerms);
            
        } catch (\Exception $e) {
            Log::error('Basic image analysis error: ' . $e->getMessage());
            return $this->getFallbackResults();
        }
    }

    /**
     * Search by similar images using basic image comparison
     */
    protected function searchBySimilarImages($uploadedImagePath)
    {
        try {
            $products = Product::with(['images', 'category', 'brand', 'variations'])
                ->where('status', 'active')
                ->get();

            $matches = [];
            
            foreach ($products as $product) {
                foreach ($product->images as $productImage) {
                    $productImagePath = storage_path('app/public/' . $productImage->image_path);
                    
                    if (file_exists($productImagePath)) {
                        $similarity = $this->compareImages($uploadedImagePath, $productImagePath);
                        
                        if ($similarity > 0.6) { // 60% similarity threshold
                            $matches[] = [
                                'product' => $product,
                                'similarity' => $similarity
                            ];
                            break; // Found a match for this product
                        }
                    }
                }
            }
            
            // Sort by similarity
            usort($matches, function($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });
            
            if (!empty($matches)) {
                $products = collect($matches)->pluck('product');
                return $this->formatResults($products, [], $matches);
            }
            
            return [];
            
        } catch (\Exception $e) {
            Log::error('Image comparison error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Compare two images using basic pixel comparison
     */
    protected function compareImages($image1Path, $image2Path)
    {
        try {
            // Load images
            $img1 = $this->loadAndResizeImage($image1Path, 100, 100);
            $img2 = $this->loadAndResizeImage($image2Path, 100, 100);
            
            if (!$img1 || !$img2) {
                return 0;
            }
            
            $width = imagesx($img1);
            $height = imagesy($img1);
            
            $totalPixels = $width * $height;
            $matchingPixels = 0;
            $colorTolerance = 30; // RGB tolerance
            
            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $rgb1 = imagecolorat($img1, $x, $y);
                    $rgb2 = imagecolorat($img2, $x, $y);
                    
                    $r1 = ($rgb1 >> 16) & 0xFF;
                    $g1 = ($rgb1 >> 8) & 0xFF;
                    $b1 = $rgb1 & 0xFF;
                    
                    $r2 = ($rgb2 >> 16) & 0xFF;
                    $g2 = ($rgb2 >> 8) & 0xFF;
                    $b2 = $rgb2 & 0xFF;
                    
                    // Calculate color difference
                    $diff = sqrt(pow($r1 - $r2, 2) + pow($g1 - $g2, 2) + pow($b1 - $b2, 2));
                    
                    if ($diff <= $colorTolerance) {
                        $matchingPixels++;
                    }
                }
            }
            
            // Clean up
            imagedestroy($img1);
            imagedestroy($img2);
            
            return $matchingPixels / $totalPixels;
            
        } catch (\Exception $e) {
            Log::error('Image comparison failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Load and resize image for comparison
     */
    protected function loadAndResizeImage($imagePath, $width, $height)
    {
        try {
            $imageInfo = getimagesize($imagePath);
            if (!$imageInfo) {
                return false;
            }
            
            $imageType = $imageInfo[2];
            $sourceImage = false;
            
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($imagePath);
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($imagePath);
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($imagePath);
                    break;
                default:
                    return false;
            }
            
            if (!$sourceImage) {
                return false;
            }
            
            // Create resized image
            $resizedImage = imagecreatetruecolor($width, $height);
            imagecopyresampled(
                $resizedImage, $sourceImage,
                0, 0, 0, 0,
                $width, $height,
                imagesx($sourceImage), imagesy($sourceImage)
            );
            
            imagedestroy($sourceImage);
            
            return $resizedImage;
            
        } catch (\Exception $e) {
            Log::error('Image loading failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract dominant colors from image
     */
    protected function extractDominantColors($imagePath)
    {
        try {
            $image = imagecreatefromstring(file_get_contents($imagePath));
            $colors = [];
            
            if ($image) {
                $width = imagesx($image);
                $height = imagesy($image);
                
                // Sample colors from a grid
                for ($x = 0; $x < $width; $x += 10) {
                    for ($y = 0; $y < $height; $y += 10) {
                        $rgb = imagecolorat($image, $x, $y);
                        $r = ($rgb >> 16) & 0xFF;
                        $g = ($rgb >> 8) & 0xFF;
                        $b = $rgb & 0xFF;
                        
                        $colors[] = $this->getColorName($r, $g, $b);
                    }
                }
                
                imagedestroy($image);
            }
            
            // Return most common colors
            return array_slice(array_keys(array_count_values($colors)), 0, 3);
            
        } catch (\Exception $e) {
            return ['red', 'blue', 'black']; // Default colors
        }
    }

    /**
     * Convert RGB to color name
     */
    protected function getColorName($r, $g, $b)
    {
        $colors = [
            'white' => [255, 255, 255],
            'black' => [0, 0, 0],
            'red' => [255, 0, 0],
            'green' => [0, 255, 0],
            'blue' => [0, 0, 255],
            'yellow' => [255, 255, 0],
            'purple' => [128, 0, 128],
            'orange' => [255, 165, 0],
            'pink' => [255, 192, 203],
            'gray' => [128, 128, 128],
            'light-gray' => [211, 211, 211],
            'cream' => [255, 253, 208],
            'beige' => [245, 245, 220],
            'brown' => [165, 42, 42]
        ];

        $minDistance = PHP_INT_MAX;
        $closestColor = 'white';

        foreach ($colors as $name => $rgb) {
            $distance = sqrt(pow($r - $rgb[0], 2) + pow($g - $rgb[1], 2) + pow($b - $rgb[2], 2));
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestColor = $name;
            }
        }

        // Special handling for white/light colors
        $brightness = ($r + $g + $b) / 3;
        if ($brightness > 200) {
            return 'white';
        } elseif ($brightness < 50) {
            return 'black';
        }

        return $closestColor;
    }

    /**
     * Analyze image using basic heuristics
     */
    protected function analyzeImageBasic($imagePath, $dominantColors)
    {
        $searchTerms = [];
        
        // Add color-based search terms
        foreach ($dominantColors as $color) {
            $searchTerms[] = $color;
        }
        
        // Try to detect clothing/fashion items based on image properties
        $imageInfo = getimagesize($imagePath);
        if ($imageInfo) {
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $aspectRatio = $width / $height;
            
            // Clothing items are often portrait-oriented or square
            if ($aspectRatio >= 0.7 && $aspectRatio <= 1.3) {
                $searchTerms = array_merge($searchTerms, ['shirt', 'clothing', 'apparel', 'fashion']);
            }
            
            // If predominantly white/light colored, add clothing terms
            if (in_array('white', $dominantColors) || in_array('gray', $dominantColors)) {
                $searchTerms = array_merge($searchTerms, ['white', 'shirt', 't-shirt', 'top', 'blouse']);
            }
            
            // If dark colors, add different clothing terms
            if (in_array('black', $dominantColors) || in_array('blue', $dominantColors)) {
                $searchTerms = array_merge($searchTerms, ['shirt', 'jacket', 'pants', 'dress']);
            }
        }
        
        return array_unique($searchTerms);
    }

    /**
     * Convert detected labels to meaningful search terms
     */
    protected function convertLabelsToSearchTerms($labels)
    {
        $termMapping = [
            'clothing' => ['shirt', 'dress', 'pants', 'jacket'],
            'footwear' => ['shoes', 'boots', 'sneakers'],
            'bag' => ['handbag', 'backpack', 'purse'],
            'accessory' => ['jewelry', 'watch', 'belt'],
            'furniture' => ['chair', 'table', 'sofa'],
            'electronics' => ['phone', 'laptop', 'tablet']
        ];

        $searchTerms = [];
        
        foreach ($labels as $label) {
            $searchTerms[] = $label;
            
            // Add related terms
            foreach ($termMapping as $category => $terms) {
                if (in_array($label, $terms) || $label === $category) {
                    $searchTerms = array_merge($searchTerms, $terms);
                }
            }
        }

        return array_unique($searchTerms);
    }

    /**
     * Search products based on detected terms
     */
    protected function searchProductsByTerms($searchTerms)
    {
        if (empty($searchTerms)) {
            return $this->getFallbackResults();
        }

        $query = Product::with(['images', 'category', 'brand', 'variations'])
            ->where('status', 'active');

        // Build search query
        $query->where(function ($q) use ($searchTerms) {
            foreach ($searchTerms as $term) {
                $q->orWhere('name', 'LIKE', "%{$term}%")
                  ->orWhere('description', 'LIKE', "%{$term}%")
                  ->orWhereHas('category', function ($categoryQuery) use ($term) {
                      $categoryQuery->where('name', 'LIKE', "%{$term}%");
                  })
                  ->orWhereHas('brand', function ($brandQuery) use ($term) {
                      $brandQuery->where('name', 'LIKE', "%{$term}%");
                  });
            }
        });

        // Order by most recent first as relevance proxy
        $results = $query->latest()->limit(20)->get();

        return $this->formatResults($results, $searchTerms);
    }

    /**
     * Format search results
     */
    protected function formatResults($products, $searchTerms = [], $imageMatches = [])
    {
        return $products->map(function ($product, $index) use ($searchTerms, $imageMatches) {
            // Get price from variations
            $minPrice = $product->variations->min('price') ?? 0;
            $maxPrice = $product->variations->max('price') ?? 0;
            $displayPrice = $minPrice == $maxPrice ? $minPrice : $minPrice;
            
            // Get similarity score from image matches if available
            $similarityScore = 0;
            if (!empty($imageMatches)) {
                $match = collect($imageMatches)->firstWhere('product.id', $product->id);
                $similarityScore = $match ? $match['similarity'] : $this->calculateSimilarityScore($product, $searchTerms);
            } else {
                $similarityScore = $this->calculateSimilarityScore($product, $searchTerms);
            }
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $displayPrice,
                'sale_price' => null, // Can be implemented if needed
                'image' => $product->images->first() ? 
                    asset('storage/' . $product->images->first()->image_path) : 
                    asset('images/no-image.jpg'),
                'category' => $product->category?->name,
                'brand' => $product->brand?->name,
                'url' => route('products.show', $product->slug),
                'similarity_score' => $similarityScore
            ];
        })->sortByDesc('similarity_score')->values();
    }

    /**
     * Calculate similarity score (basic implementation)
     */
    protected function calculateSimilarityScore($product, $searchTerms)
    {
        if (empty($searchTerms)) {
            return 0.5;
        }

        $score = 0;
        $productText = strtolower($product->name . ' ' . $product->description);
        
        foreach ($searchTerms as $term) {
            if (strpos($productText, strtolower($term)) !== false) {
                $score += 1;
            }
        }
        
        return min($score / count($searchTerms), 1.0);
    }

    /**
     * Get fallback results when search fails
     */
    protected function getFallbackResults()
    {
        // Return recent or random products as fallback
        $fallbackProducts = Product::with(['images', 'category', 'brand', 'variations'])
            ->where('status', 'active')
            ->latest()
            ->limit(10)
            ->get();

        return $this->formatResults($fallbackProducts);
    }

    /**
     * Log visual search for analytics
     */
    public function logVisualSearch($searchTerms, $resultsCount, $userId = null)
    {
        try {
            DB::table('search_logs')->insert([
                'user_id' => $userId,
                'search_type' => 'visual',
                'search_query' => json_encode($searchTerms),
                'results_count' => $resultsCount,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log visual search: ' . $e->getMessage());
        }
    }
}
