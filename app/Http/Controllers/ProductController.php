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
                    'attributeValues.attribute',
                    'images' => function($imgQuery) {
                        $imgQuery->orderBy('sort_order')->orderBy('is_main', 'desc');
                    }
                ])->orderBy('price');
            }
        ])->where('slug', $slug)->where('status', 'active')->firstOrFail();
        
        // Add to recently viewed
        $this->addToRecentlyViewed($product->id);
        
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
    
    public function getVariations(Request $request, $productId)
    {
        $product = Product::with([
            'variations' => function($q) {
                $q->with([
                    'attributeValues.attribute',
                    'images' => function($imgQuery) {
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
        if ($firstVariation && $firstVariation->images->count() > 0) {
            $images = $firstVariation->images->map(function($image) {
                return [
                    'url' => asset('storage/' . $image->image_path),
                    'id' => $image->id
                ];
            })->toArray();
        } else {
            // Fallback to product images
            $images = $product->images->map(function($image) {
                return [
                    'url' => asset('storage/' . $image->image_path),
                    'id' => $image->id
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
                    'images' => function($imgQuery) {
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
