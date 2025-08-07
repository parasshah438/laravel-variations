<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\VisualSearchService;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VisualSearchTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $category = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        $brand = Brand::create(['name' => 'TestBrand', 'slug' => 'testbrand']);
        
        $product = Product::create([
            'name' => 'Test Red Phone',
            'slug' => 'test-red-phone',
            'description' => 'A red smartphone for testing',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'status' => 'active'
        ]);
        
        // Create a variation with price
        ProductVariation::create([
            'product_id' => $product->id,
            'sku' => 'TEST001',
            'price' => 299.99,
            'stock' => 10
        ]);
    }
    
    public function test_visual_search_basic_functionality()
    {
        $service = new VisualSearchService();
        
        // Create a test image file
        Storage::fake('public');
        $testImage = UploadedFile::fake()->image('test.jpg', 200, 200);
        $imagePath = $testImage->store('temp', 'public');
        $fullPath = storage_path('app/public/' . $imagePath);
        
        // Test the service
        $results = $service->findSimilarProducts($fullPath);
        
        // Should return some results (at least fallback results)
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        
        // Check result structure
        $firstResult = $results[0];
        $this->assertArrayHasKey('id', $firstResult);
        $this->assertArrayHasKey('name', $firstResult);
        $this->assertArrayHasKey('price', $firstResult);
        $this->assertArrayHasKey('image', $firstResult);
        $this->assertArrayHasKey('similarity_score', $firstResult);
        
        // Clean up
        Storage::disk('public')->delete($imagePath);
    }
    
    public function test_visual_search_controller_endpoint()
    {
        Storage::fake('public');
        $testImage = UploadedFile::fake()->image('test.jpg', 200, 200);
        
        $response = $this->postJson('/visual-search/image', [
            'image' => $testImage
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'results',
                    'total'
                ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('results'));
    }
}
