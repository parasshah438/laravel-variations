<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Services\VisualSearchService;

class VisualSearchController extends Controller
{
    protected $visualSearchService;

    public function __construct(VisualSearchService $visualSearchService)
    {
        $this->visualSearchService = $visualSearchService;
    }

    /**
     * Handle visual search by uploaded image
     */
    public function searchByImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        try {
            // Store the uploaded image temporarily
            $image = $request->file('image');
            $imagePath = $image->store('temp/visual-search', 'public');
            $fullImagePath = storage_path('app/public/' . $imagePath);

            // Process the image and find similar products
            $results = $this->visualSearchService->findSimilarProducts($fullImagePath);

            // Clean up temporary file
            Storage::disk('public')->delete($imagePath);

            return response()->json([
                'success' => true,
                'results' => $results,
                'total' => count($results)
            ]);

        } catch (\Exception $e) {
            Log::error('Visual search error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing image. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle visual search by camera capture (base64 image)
     */
    public function searchByCamera(Request $request)
    {
        $request->validate([
            'image_data' => 'required|string',
        ]);

        try {
            // Decode base64 image
            $imageData = $request->input('image_data');
            $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
            $imageData = str_replace(' ', '+', $imageData);
            $decodedImage = base64_decode($imageData);

            // Save temporary file
            $fileName = 'camera_capture_' . time() . '.jpg';
            $tempPath = storage_path('app/temp/' . $fileName);
            
            // Create temp directory if it doesn't exist
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }
            
            file_put_contents($tempPath, $decodedImage);

            // Process the image
            $results = $this->visualSearchService->findSimilarProducts($tempPath);

            // Clean up
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            return response()->json([
                'success' => true,
                'results' => $results,
                'total' => count($results)
            ]);

        } catch (\Exception $e) {
            Log::error('Camera visual search error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing camera image. Please try again.'
            ], 500);
        }
    }

    /**
     * Get visual search analytics
     */
    public function getAnalytics()
    {
        // This could return visual search usage statistics
        return response()->json([
            'success' => true,
            'analytics' => [
                'total_searches' => 0,
                'successful_matches' => 0,
                'popular_categories' => []
            ]
        ]);
    }
}
