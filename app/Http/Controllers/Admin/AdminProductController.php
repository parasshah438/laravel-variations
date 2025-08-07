<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\ProductVariationImage;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'variations']);
        
        // Apply filters
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        $perPage = $request->get('per_page', 10);
        $products = $query->latest()->paginate($perPage);
        
        // Get all categories for filter dropdown
        $categories = Category::orderBy('name')->get();
        
        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $attributes = Attribute::with('attributeValues')->orderBy('name')->get();
        
        return view('admin.products.create', compact('categories', 'brands', 'attributes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if we have attributes or just a simple product
        $hasAttributes = $request->has('attributes') && count($request->attributes) > 0;
        
        if ($hasAttributes) {
            // Validate for attribute-based product
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'nullable|exists:categories,id',
                'brand_id' => 'nullable|exists:brands,id',
                'status' => 'required|in:active,inactive',
                'general_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'attributes' => 'required|array|min:1',
                'attributes.*' => 'exists:attributes,id',
                'attribute_values' => 'required|array',
                'variations' => 'required|array|min:1',
                'variations.*.price' => 'required|numeric|min:0',
                'variations.*.stock' => 'required|integer|min:0',
                'variations.*.sku' => 'nullable|string|max:100',
                'variations.*.images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);
        } else {
            // Validate for simple product
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'nullable|exists:categories,id',
                'brand_id' => 'nullable|exists:brands,id',
                'status' => 'required|in:active,inactive',
                'general_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'default_price' => 'required|numeric|min:0',
                'default_stock' => 'required|integer|min:0',
                'default_sku' => 'nullable|string|max:100',
            ]);
        }

        DB::transaction(function() use ($request, $hasAttributes) {
            // Create main product (no main image field anymore)
            $product = Product::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'status' => $request->status,
            ]);

            // Handle general product images
            if ($request->hasFile('general_images')) {
                foreach ($request->file('general_images') as $index => $image) {
                    $imagePath = $image->store('products', 'public');
                    
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $imagePath,
                        'is_main' => $index === 0, // First general image is main
                        'sort_order' => $index + 1,
                    ]);
                }
            }

            if ($hasAttributes) {
                // Create variations based on attribute combinations
                foreach ($request->variations as $index => $variationData) {
                    $sku = $variationData['sku'] ?: $this->generateSKU($product, $index);
                    
                    $variation = $product->variations()->create([
                        'price' => $variationData['price'],
                        'stock' => $variationData['stock'],
                        'sku' => $sku,
                        'weight' => $variationData['weight'] ?? null,
                        'is_active' => true,
                    ]);

                    // Link attribute values to this variation
                    if (isset($variationData['attributes'])) {
                        foreach ($variationData['attributes'] as $attribute) {
                            $variation->attributeValues()->attach($attribute['attribute_value_id']);
                        }
                    }

                    // Handle variation-specific images
                    if (isset($variationData['images']) && is_array($variationData['images'])) {
                        foreach ($variationData['images'] as $imageIndex => $image) {
                            if ($image && $image->isValid()) {
                                $imagePath = $image->store('products/variations', 'public');
                                
                                // Create the product image first
                                $productImage = ProductImage::create([
                                    'product_id' => $product->id,
                                    'image_path' => $imagePath,
                                    'is_main' => false,
                                    'sort_order' => $imageIndex + 1,
                                ]);
                                
                                // Link this image to the specific variation via pivot table
                                ProductVariationImage::create([
                                    'product_variation_id' => $variation->id,
                                    'product_image_id' => $productImage->id,
                                    'sort_order' => $imageIndex + 1,
                                ]);
                            }
                        }
                    }
                }
            } else {
                // Create single default variation
                $sku = $request->default_sku ?: $this->generateSKU($product, 0);
                
                $product->variations()->create([
                    'variation_name' => 'Default',
                    'price' => $request->default_price,
                    'stock' => $request->default_stock,
                    'sku' => $sku,
                ]);
            }
        });

        return redirect()->route('admin.products.index')
                        ->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'images', 'variations.attributeValues.attribute']);
        
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $attributes = Attribute::with('attributeValues')->orderBy('name')->get();
        $product->load([
            'category', 
            'brand', 
            'images', 
            'variations.attributeValues.attribute',
            'variations.variationImages.productImage'  // Use correct relationship name
        ]);
        
        return view('admin.products.edit', compact('product', 'categories', 'brands', 'attributes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'status' => 'required|in:active,inactive',
            'general_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'variations.*.price' => 'required|numeric|min:0',
            'variations.*.stock' => 'required|integer|min:0',
            'variations.*.sku' => 'nullable|string|max:100',
            'variations.*.images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'new_variations.*.price' => 'nullable|numeric|min:0',
            'new_variations.*.stock' => 'nullable|integer|min:0',
            'new_variations.*.sku' => 'nullable|string|max:100',
            'new_variations.*.images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        DB::transaction(function() use ($request, $product) {
            // Update basic product information
            $product->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'status' => $request->status,
            ]);

            // Handle new general images
            if ($request->hasFile('general_images')) {
                foreach ($request->file('general_images') as $index => $image) {
                    $imagePath = $image->store('products', 'public');
                    
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $imagePath,
                        'is_main' => $product->images()->whereNull('variations')->count() === 0,
                        'sort_order' => $product->images()->whereNull('variations')->count() + $index + 1,
                    ]);
                }
            }

            // Update existing variations
            if ($request->has('variations')) {
                foreach ($request->variations as $variationId => $variationData) {
                    $variation = ProductVariation::where('id', $variationId)
                                               ->where('product_id', $product->id)
                                               ->first();
                    
                    if ($variation) {
                        $variation->update([
                            'price' => $variationData['price'],
                            'stock' => $variationData['stock'],
                            'sku' => $variationData['sku'] ?: $variation->sku,
                        ]);

                        // Handle variation-specific images
                        if (isset($variationData['images']) && is_array($variationData['images'])) {
                            foreach ($variationData['images'] as $imageIndex => $image) {
                                if ($image && $image->isValid()) {
                                    $imagePath = $image->store('products/variations', 'public');
                                    
                                    // Create the product image first
                                    $productImage = ProductImage::create([
                                        'product_id' => $product->id,
                                        'image_path' => $imagePath,
                                        'is_main' => false,
                                        'sort_order' => $imageIndex + 1,
                                    ]);
                                    
                                    // Link this image to the specific variation via pivot table
                                    ProductVariationImage::create([
                                        'product_variation_id' => $variation->id,
                                        'product_image_id' => $productImage->id,
                                        'sort_order' => $imageIndex + 1,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // Create new variations
            if ($request->has('new_variations')) {
                foreach ($request->new_variations as $index => $variationData) {
                    if (isset($variationData['price']) && isset($variationData['stock'])) {
                        $sku = $variationData['sku'] ?: $this->generateSKU($product, $index + 1000);
                        
                        $variation = $product->variations()->create([
                            'price' => $variationData['price'],
                            'stock' => $variationData['stock'],
                            'sku' => $sku,
                            'weight' => $variationData['weight'] ?? null,
                            'is_active' => true,
                        ]);

                        // Link attribute values to this variation
                        if (isset($variationData['attributes'])) {
                            foreach ($variationData['attributes'] as $attribute) {
                                $variation->attributeValues()->attach($attribute['attribute_value_id']);
                            }
                        }

                        // Handle variation-specific images
                        if (isset($variationData['images']) && is_array($variationData['images'])) {
                            foreach ($variationData['images'] as $imageIndex => $image) {
                                if ($image && $image->isValid()) {
                                    $imagePath = $image->store('products/variations', 'public');
                                    
                                    // Create the product image first
                                    $productImage = ProductImage::create([
                                        'product_id' => $product->id,
                                        'image_path' => $imagePath,
                                        'is_main' => false,
                                        'sort_order' => $imageIndex + 1,
                                    ]);
                                    
                                    // Link this image to the specific variation via pivot table
                                    ProductVariationImage::create([
                                        'product_variation_id' => $variation->id,
                                        'product_image_id' => $productImage->id,
                                        'sort_order' => $imageIndex + 1,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        });

        return redirect()->route('admin.products.index')
                        ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        DB::transaction(function() use ($product) {
            // Delete product images from storage
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }
            
            // Delete the product (cascading will handle relations)
            $product->delete();
        });

        return redirect()->route('admin.products.index')
                        ->with('success', 'Product deleted successfully!');
    }

    public function updateStatus(Request $request, Product $product)
    {
        $request->validate([
            'status' => 'required|in:active,inactive'
        ]);

        $product->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Product status updated successfully!'
        ]);
    }

    public function deleteImage(ProductImage $image)
    {
        // Delete image file from storage
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }
        
        // Delete image record from database
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully!'
        ]);
    }

    private function generateSKU($product, $index)
    {
        $productCode = strtoupper(substr($product->name, 0, 3));
        $randomCode = strtoupper(Str::random(3));
        
        return $productCode . '-' . $product->id . '-' . $index . '-' . $randomCode;
    }

    private function generateAttributeCombinations($attributes)
    {
        $combinations = [[]];
        
        foreach ($attributes as $attributeId => $valueIds) {
            $newCombinations = [];
            foreach ($combinations as $combination) {
                foreach ($valueIds as $valueId) {
                    $newCombination = $combination;
                    $newCombination[$attributeId] = $valueId;
                    $newCombinations[] = $newCombination;
                }
            }
            $combinations = $newCombinations;
        }
        
        // Create keys for each combination
        $keyedCombinations = [];
        foreach ($combinations as $combination) {
            $key = implode('-', array_values($combination));
            $keyedCombinations[$key] = $combination;
        }
        
        return $keyedCombinations;
    }

    /**
     * Bulk update product status
     */
    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
            'status' => 'required|in:active,inactive'
        ]);

        $updatedCount = Product::whereIn('id', $request->ids)
                              ->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => "{$updatedCount} product(s) status updated to " . ucfirst($request->status)
        ]);
    }

    /**
     * Bulk delete products
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id'
        ]);

        DB::transaction(function () use ($request) {
            $products = Product::whereIn('id', $request->ids)
                              ->with(['images', 'variations.variationImages.productImage'])
                              ->get();
            
            foreach ($products as $product) {
                // Delete product images from storage
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                
                // Delete all product images (general and variation-specific)
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image->image_path);
                }
                
                // Delete variation-specific images and pivot entries
                foreach ($product->variations as $variation) {
                    ProductVariationImage::where('product_variation_id', $variation->id)->delete();
                }
                
                // Delete the product (this will cascade delete variations and relations)
                $product->delete();
            }
        });

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' product(s) deleted successfully'
        ]);
    }

    /**
     * Delete a specific product variation
     */
    public function deleteVariation(ProductVariation $variation)
    {
        DB::transaction(function () use ($variation) {
            // Delete variation-specific images and their pivot entries
            $variationImages = ProductVariationImage::where('product_variation_id', $variation->id)->get();
            foreach ($variationImages as $pivotEntry) {
                // Delete the image file from storage
                if ($pivotEntry->productImage && $pivotEntry->productImage->image_path) {
                    Storage::disk('public')->delete($pivotEntry->productImage->image_path);
                }
                
                // Delete the image record
                if ($pivotEntry->productImage) {
                    $pivotEntry->productImage->delete();
                }
                
                // Delete the pivot entry
                $pivotEntry->delete();
            }
            
            // Delete the variation itself (this will also clean up attribute relationships)
            $variation->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Product variation deleted successfully'
        ]);
    }
}
