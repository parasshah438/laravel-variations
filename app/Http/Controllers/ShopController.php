<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        // Get filter data for sidebar
        $categories = Category::withCount(['products' => function($query) {
            $query->where('status', 'active');
        }])->orderBy('name')->get();
        
        $brands = Brand::withCount(['products' => function($query) {
            $query->where('status', 'active');
        }])->orderBy('name')->get();
        
        // Get attributes with their values and product counts
        $attributes = Attribute::with(['attributeValues' => function($query) {
            $query->withCount(['productVariations' => function($subQuery) {
                $subQuery->whereHas('product', function($productQuery) {
                    $productQuery->where('status', 'active');
                });
            }])->orderBy('value');
        }])->orderBy('name')->get();
        
        // Price range
        $priceRange = $this->getPriceRange();
        
        if ($request->ajax()) {
            return $this->getFilteredProducts($request);
        }
        
        return view('shop.index', compact('categories', 'brands', 'attributes', 'priceRange'));
    }
    
    public function getFilteredProducts(Request $request)
    {
        $query = Product::with(['category', 'brand', 'variations' => function($q) {
            $q->orderBy('price');
        }])->where('status', 'active');
        
        // Apply filters
        if ($request->filled('categories')) {
            $query->whereIn('category_id', $request->categories);
        }
        
        if ($request->filled('brands')) {
            $query->whereIn('brand_id', $request->brands);
        }
        
        if ($request->filled('attributes')) {
            foreach ($request->attributes as $attributeId => $valueIds) {
                if (!empty($valueIds)) {
                    $query->whereHas('variations.attributeValues', function($q) use ($valueIds) {
                        $q->whereIn('attribute_value_id', $valueIds);
                    });
                }
            }
        }
        
        if ($request->filled('price_min') || $request->filled('price_max')) {
            $priceMin = $request->get('price_min', 0);
            $priceMax = $request->get('price_max', 999999);
            
            $query->whereHas('variations', function($q) use ($priceMin, $priceMax) {
                $q->whereBetween('price', [$priceMin, $priceMax]);
            });
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'price_low':
                $query->leftJoin('product_variations', 'products.id', '=', 'product_variations.product_id')
                      ->select('products.*', DB::raw('MIN(product_variations.price) as min_price'))
                      ->groupBy('products.id')
                      ->orderBy('min_price');
                break;
            case 'price_high':
                $query->leftJoin('product_variations', 'products.id', '=', 'product_variations.product_id')
                      ->select('products.*', DB::raw('MIN(product_variations.price) as min_price'))
                      ->groupBy('products.id')
                      ->orderByDesc('min_price');
                break;
            case 'name':
                $query->orderBy('name');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }
        
        $perPage = $request->get('per_page', 12);
        $products = $query->paginate($perPage);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('shop.partials.products', compact('products'))->render(),
                'pagination' => view('shop.partials.pagination', compact('products'))->render(),
                'total' => $products->total(),
                'showing' => [
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                    'total' => $products->total()
                ]
            ]);
        }
        
        return view('shop.index', compact('products'));
    }
    
    private function getPriceRange()
    {
        $minPrice = DB::table('product_variations')
            ->join('products', 'product_variations.product_id', '=', 'products.id')
            ->where('products.status', 'active')
            ->min('product_variations.price');
            
        $maxPrice = DB::table('product_variations')
            ->join('products', 'product_variations.product_id', '=', 'products.id')
            ->where('products.status', 'active')
            ->max('product_variations.price');
            
        return [
            'min' => $minPrice ?: 0,
            'max' => $maxPrice ?: 1000
        ];
    }
    
    public function getFilterCounts(Request $request)
    {
        // Return updated filter counts after applying current filters
        $baseQuery = Product::where('status', 'active');
        
        // Apply current filters except the one being updated
        $currentFilters = $request->except(['_token', 'updating_filter']);
        
        foreach ($currentFilters as $filterType => $filterValues) {
            if (empty($filterValues)) continue;
            
            switch ($filterType) {
                case 'categories':
                    if ($request->updating_filter !== 'categories') {
                        $baseQuery->whereIn('category_id', $filterValues);
                    }
                    break;
                case 'brands':
                    if ($request->updating_filter !== 'brands') {
                        $baseQuery->whereIn('brand_id', $filterValues);
                    }
                    break;
                case 'attributes':
                    if ($request->updating_filter !== 'attributes') {
                        foreach ($filterValues as $attributeId => $valueIds) {
                            if (!empty($valueIds)) {
                                $baseQuery->whereHas('variations.attributeValues', function($q) use ($valueIds) {
                                    $q->whereIn('attribute_value_id', $valueIds);
                                });
                            }
                        }
                    }
                    break;
            }
        }
        
        $productIds = $baseQuery->pluck('id');
        
        // Get updated counts
        $updatingFilter = $request->updating_filter;
        $counts = [];
        
        switch ($updatingFilter) {
            case 'categories':
                $counts = Category::withCount(['products' => function($query) use ($productIds) {
                    $query->whereIn('id', $productIds);
                }])->get()->pluck('products_count', 'id');
                break;
            case 'brands':
                $counts = Brand::withCount(['products' => function($query) use ($productIds) {
                    $query->whereIn('id', $productIds);
                }])->get()->pluck('products_count', 'id');
                break;
            case 'attributes':
                // This would be more complex for attributes
                break;
        }
        
        return response()->json(['counts' => $counts]);
    }
}
