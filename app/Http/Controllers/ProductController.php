<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\RecentlyViewedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function show($slug)
    {
        $product = Product::with([
            'category', 
            'brand', 
            'images' => function($q) {
                $q->orderBy('sort_order')->orderBy('is_main', 'desc');
            },
            'variations' => function($q) {
                $q->with([
                    'attributeValues.attribute'
                    // Don't load variation images on initial page load
                ])->orderBy('price');
            }
        ])->where('slug', $slug)->where('status', 'active')->firstOrFail();
        
        // Add to recently viewed
        $this->addToRecentlyViewed($product->id);
        
        // Get all unique attribute values for this product - INCLUDE zero stock items
        $availableAttributes = [];
        $allVariations = $product->variations; // Include ALL variations, even out of stock
        
        foreach ($allVariations as $variation) {
            foreach ($variation->attributeValues as $attributeValue) {
                $attributeName = $attributeValue->attribute->name;
                
                // Initialize array if not exists
                if (!isset($availableAttributes[$attributeName])) {
                    $availableAttributes[$attributeName] = [];
                }
                
                // Check if this attribute value already exists
                $exists = collect($availableAttributes[$attributeName])->contains('id', $attributeValue->id);
                
                if (!$exists) {
                    $stockCount = $allVariations->filter(function($v) use ($attributeValue) {
                        return $v->attributeValues->contains('id', $attributeValue->id);
                    })->sum('stock');
                    
                    $availableAttributes[$attributeName][] = [
                        'id' => $attributeValue->id,
                        'value' => $attributeValue->value,
                        'attribute_name' => $attributeName,
                        'stock_count' => $stockCount,
                        'in_stock' => $stockCount > 0
                    ];
                }
            }
        }
        
        // Get related products
        $relatedProducts = Product::with(['images', 'variations' => function($q) {
            $q->orderBy('price');
        }])
        ->where('category_id', $product->category_id)
        ->where('id', '!=', $product->id)
        ->where('status', 'active')
        ->limit(4)
        ->get();
        
        return view('products.show', compact('product', 'relatedProducts', 'availableAttributes'));
    }
    
    public function getFilteredAttributes(Request $request, $productId)
    {
        $product = Product::with(['variations.attributeValues.attribute'])->findOrFail($productId);
        $selectedAttributes = $request->input('selected', []);
        
        // Get variations that match the selected attributes
        $availableVariations = $product->variations->filter(function($variation) use ($selectedAttributes) {
            if (empty($selectedAttributes)) {
                return $variation->stock > 0; // Only in-stock variations
            }
            
            $variationAttributeIds = $variation->attributeValues->pluck('id')->toArray();
            
            // Check if this variation contains all selected attributes
            foreach ($selectedAttributes as $selectedId) {
                if (!in_array($selectedId, $variationAttributeIds)) {
                    return false;
                }
            }
            
            return $variation->stock > 0; // Only in-stock variations
        });
        
        // Build available attributes based on filtered variations
        $filteredAttributes = [];
        foreach ($availableVariations as $variation) {
            foreach ($variation->attributeValues as $attributeValue) {
                $attributeName = $attributeValue->attribute->name;
                
                if (!isset($filteredAttributes[$attributeName])) {
                    $filteredAttributes[$attributeName] = [];
                }
                
                // Check if this attribute value already exists
                $exists = collect($filteredAttributes[$attributeName])->contains('id', $attributeValue->id);
                
                if (!$exists) {
                    // Only include if not already selected (to prevent deselection issues)
                    $isAlreadySelected = in_array($attributeValue->id, $selectedAttributes);
                    
                    $filteredAttributes[$attributeName][] = [
                        'id' => $attributeValue->id,
                        'value' => $attributeValue->value,
                        'attribute_name' => $attributeName,
                        'available' => true,
                        'already_selected' => $isAlreadySelected,
                        'stock_count' => $availableVariations->filter(function($v) use ($attributeValue) {
                            return $v->attributeValues->contains('id', $attributeValue->id);
                        })->sum('stock')
                    ];
                }
            }
        }
        
        // Also get all possible attributes to mark unavailable ones
        $allPossibleAttributes = [];
        foreach ($product->variations as $variation) {
            foreach ($variation->attributeValues as $attributeValue) {
                $attributeName = $attributeValue->attribute->name;
                
                if (!isset($allPossibleAttributes[$attributeName])) {
                    $allPossibleAttributes[$attributeName] = [];
                }
                
                $exists = collect($allPossibleAttributes[$attributeName])->contains('id', $attributeValue->id);
                if (!$exists) {
                    $isAvailable = isset($filteredAttributes[$attributeName]) && 
                                  collect($filteredAttributes[$attributeName])->contains('id', $attributeValue->id);
                    
                    $allPossibleAttributes[$attributeName][] = [
                        'id' => $attributeValue->id,
                        'value' => $attributeValue->value,
                        'attribute_name' => $attributeName,
                        'available' => $isAvailable,
                        'already_selected' => in_array($attributeValue->id, $selectedAttributes),
                        'stock_count' => $isAvailable ? 
                            $availableVariations->filter(function($v) use ($attributeValue) {
                                return $v->attributeValues->contains('id', $attributeValue->id);
                            })->sum('stock') : 0
                    ];
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'attributes' => $allPossibleAttributes, // Show all, but mark availability
            'available_variations_count' => $availableVariations->count(),
            'debug' => [
                'selected_attributes' => $selectedAttributes,
                'available_variations' => $availableVariations->pluck('id')->toArray()
            ]
        ]);
    }
    
    public function getVariations(Request $request, $productId)
    {
        $product = Product::with([
            'variations' => function($q) {
                $q->with([
                    'attributeValues.attribute',
                    'variationImages.productImage' => function($imgQuery) {
                        $imgQuery->orderBy('sort_order')->orderBy('is_main', 'desc');
                    }
                ])->orderBy('price');
            }
        ])->findOrFail($productId);
        
        $selectedAttributes = $request->get('attributes', []);
        
        // Filter variations based on selected attributes
        $matchingVariations = $product->variations->filter(function($variation) use ($selectedAttributes) {
            if (empty($selectedAttributes)) return true;
            
            $variationAttributeIds = $variation->attributeValues->pluck('id')->toArray();
            
            // Check if all selected attributes are present in this variation
            foreach ($selectedAttributes as $attributeId) {
                if (!in_array($attributeId, $variationAttributeIds)) {
                    return false;
                }
            }
            return true;
        });
        
        if ($matchingVariations->isEmpty()) {
            return response()->json([
                'variations' => [],
                'stock' => 0,
                'price_range' => ['min' => 0, 'max' => 0],
                'images' => []
            ]);
        }
        
        $stock = $matchingVariations->sum('stock');
        $minPrice = $matchingVariations->min('price');
        $maxPrice = $matchingVariations->max('price');
        
        // Get images from the first matching variation if available
        $images = [];
        $firstVariation = $matchingVariations->first();
        if ($firstVariation && $firstVariation->variationImages->count() > 0) {
            // Use variation-specific images through pivot table
            $images = $firstVariation->variationImages->map(function($pivotEntry) {
                return [
                    'url' => asset('storage/' . $pivotEntry->productImage->image_path),
                    'id' => $pivotEntry->productImage->id,
                    'is_main' => $pivotEntry->productImage->is_main ?? false
                ];
            })->toArray();
        } else {
            // Fallback to general product images if no variation-specific images
            $images = $product->images->map(function($image) {
                return [
                    'url' => asset('storage/' . $image->image_path),
                    'id' => $image->id,
                    'is_main' => $image->is_main ?? false
                ];
            })->toArray();
        }
             
        return response()->json([
            'variations' => $matchingVariations->values(),
            'stock' => $stock,
            'price_range' => [
                'min' => $minPrice,
                'max' => $maxPrice
            ],
            'images' => $images
        ]);
    }
    
    public function quickView($slug)
    {
        $product = Product::with([
            'category', 
            'brand', 
            'images' => function($q) {
                $q->orderBy('sort_order')->orderBy('is_main', 'desc');
            },
            'variations' => function($q) {
                $q->with([
                    'attributeValues.attribute',
                    'variationImages.productImage' => function($imgQuery) {
                        $imgQuery->orderBy('sort_order')->orderBy('is_main', 'desc');
                    }
                ])->orderBy('price');
            }
        ])->where('slug', $slug)->where('status', 'active')->firstOrFail();
        
        // Get all unique attribute values for this product
        $availableAttributes = [];
        foreach ($product->variations as $variation) {
            foreach ($variation->attributeValues as $attributeValue) {
                $attributeName = $attributeValue->attribute->name;
                
                // Initialize array if not exists
                if (!isset($availableAttributes[$attributeName])) {
                    $availableAttributes[$attributeName] = [];
                }
                
                $availableAttributes[$attributeName][] = [
                    'id' => $attributeValue->id,
                    'value' => $attributeValue->value,
                    'variations' => $product->variations->filter(function($v) use ($attributeValue) {
                        return $v->attributeValues->contains('id', $attributeValue->id);
                    })->values()
                ];
            }
        }
        
        // Remove duplicates and organize
        foreach ($availableAttributes as $attributeName => $values) {
            $availableAttributes[$attributeName] = collect($values)
                ->unique('id')
                ->values()
                ->toArray();
        }
        
        return view('products.quick-view', compact('product', 'availableAttributes'));
    }
    
    public function getVariation($productId, $variationId)
    {
        $product = Product::findOrFail($productId);
        $variation = $product->variations()->findOrFail($variationId);
        
        return response()->json([
            'id' => $variation->id,
            'price' => $variation->price,
            'stock' => $variation->stock,
            'sku' => $variation->sku,
            'attributes' => $variation->attributeValues->map(function($attr) {
                return [
                    'name' => $attr->attribute->name,
                    'value' => $attr->value
                ];
            })
        ]);
    }
    

    
    private function addToRecentlyViewed($productId)
    {
        $data = ['product_id' => $productId];
        
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
            
            // Remove if already exists and add new
            RecentlyViewedProduct::where('user_id', Auth::id())
                                ->where('product_id', $productId)
                                ->delete();
        } else {
            $guestToken = session('guest_token', session()->getId());
            session(['guest_token' => $guestToken]);
            $data['guest_token'] = $guestToken;
            
            // Remove if already exists and add new
            RecentlyViewedProduct::where('guest_token', $guestToken)
                                ->where('product_id', $productId)
                                ->delete();
        }
        
        RecentlyViewedProduct::create($data);
        
        // Keep only last 10 items
        $query = RecentlyViewedProduct::where('product_id', '!=', $productId);
        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('guest_token', session('guest_token'));
        }
        
        $oldItems = $query->orderBy('created_at', 'desc')->skip(9)->take(100)->get();
        foreach ($oldItems as $item) {
            $item->delete();
        }
    }
}
