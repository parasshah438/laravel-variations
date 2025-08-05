<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductCategory;
use App\Models\ProductCollection; 
use App\Models\DiamondClarityColorMaster;
use App\Models\DiamondShapeMaster;
use App\Models\DiamondWeightMaster;
use App\Models\GoldQualityMaster;
use App\Models\GoldColorMaster;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

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

    public function newShopPage(Request $request)
{
    $country = session('country', 'inr');
    $req_category = trim(strip_tags($request->category));
    $req_collection = trim(strip_tags($request->collection));
    $product_viewed = trim(strip_tags($request->product_viewed));
    $gold_weight = trim(strip_tags($request->gold_weight));
    $added_to_collection = trim(strip_tags($request->added_to_collection));
    $store = trim(strip_tags($request->brand));
    $min_price = $request->min_price ?? 0;
    $max_price = $request->max_price ?? 0;
    $minutes = 60 * 24 * 7; 

    // Handle filter arrays from AJAX request
    $categories = $request->get('categories', []);
    $collections = $request->get('collections', []);
    $clarity = $request->get('clarity', []);
    $shapes = $request->get('shapes', []);
    $qualities = $request->get('qualities', []);
    $searchTerm = $request->get('search');
    $sortOption = $request->get('sort', 'default');
    
    // Query Builder
    $query = Product::query();

    // Category filters (support both single and multiple)
    if ($req_category && is_numeric($req_category)) {
        $query->where('category_id', $req_category);
    } elseif (!empty($categories)) {
        $query->whereIn('category_id', $categories);
    }

    // Collection filters (support both single and multiple)
    if (!empty($req_collection)) {
        $collection = Cache::remember("collection_slug_{$req_collection}", $minutes, function () use ($req_collection) {
            return ProductCollection::where('slug', $req_collection)->first();
        });
        if ($collection) {
            $query->where('collection_id', $collection->id);
        }
    } elseif (!empty($collections)) {
        $query->whereIn('collection_id', $collections);
    }

    // Search filter
    if ($searchTerm) {
        $query->where(function ($q) use ($searchTerm) {
            $q->where('product_name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('product_description', 'LIKE', "%{$searchTerm}%")
              ->orWhere('product_short_description', 'LIKE', "%{$searchTerm}%");
        });
    }

    // Sorting options
    switch ($sortOption) {
        case 'name_asc':
            $query->orderBy('product_name', 'asc');
            break;
        case 'name_desc':
            $query->orderBy('product_name', 'desc');
            break;
        case 'price_asc':
            $query->orderBy('product_price', 'asc');
            break;
        case 'price_desc':
            $query->orderBy('product_price', 'desc');
            break;
        case 'newest':
            $query->orderBy('created_at', 'desc');
            break;
        case 'oldest':
            $query->orderBy('created_at', 'asc');
            break;
        case 'rating':
            $query->orderBy('product_avg_rating', 'desc');
            break;
        case 'popular':
            $query->orderBy('product_views', 'desc');
            break;
        default:
            if ($product_viewed !== '') {
                $query->orderBy('view_count', $product_viewed == 0 ? 'asc' : 'desc');
            } elseif ($gold_weight !== '') {
                $query->orderBy('product_gold_weight_14kt', $gold_weight == 0 ? 'asc' : 'desc');
            } elseif ($added_to_collection !== '') {
                $query->orderBy('created_at', $added_to_collection == 0 ? 'asc' : 'desc');
            } else {
                $query->orderBy('sequence', 'asc');
            }
            break;
    }

    // Store/Brand filter
    if ($store) {
        $storeId = match($store) {
            'singlestone' => 1,
            'crystalcut' => 2,
            'amantranjewels' => 3,
            default => null,
        };
        if ($storeId) {
            $query->where('website_name', $storeId);
        }
    }

    $query->where('status', 1);

    if (Auth::check() && Auth::user()->b2b == 0) {
        $query->where('user', '!=', 2);
    }

    // Paginate
    $paginated = $query->paginate(9)->withQueryString();

    // Price filtering after pagination
    if (($min_price || $max_price) && $paginated->isNotEmpty()) {
        $filtered = $paginated->getCollection()->filter(function ($product) use ($min_price, $max_price) {
            $price = Helper::calculatePriceInfo($product->id)['final_price'];
            return (!$min_price || $price >= $min_price) &&
                   (!$max_price || $price <= $max_price);
        });
        $paginated->setCollection($filtered->values());
    }

    // AJAX Response for filtering
    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'html' => view('frontend.partials.product_grid', ['all_products' => $paginated])->render(),
            'pagination' => $paginated->appends($request->all())->links()->render(),
            'next_page_url' => $paginated->nextPageUrl(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'total' => $paginated->total()
        ]);
    }

    // Cached data (static master tables)
    $categories = Cache::remember('shop_categories', $minutes, function () {
        return ProductCategory::all();
    });

    $diamondclaritycolorssolitaire = Cache::remember('diamond_clarity_solitaire', $minutes, fn() => DiamondClarityColorMaster::all());
    $diamondclaritycolors = Cache::remember('diamond_clarity', $minutes, fn() => DiamondClarityColorMaster::all());
    $diamondshapes = Cache::remember('diamond_shapes', $minutes, fn() => DiamondShapeMaster::all());
    $diamondwieghts = Cache::remember('diamond_weights', $minutes, fn() => DiamondWeightMaster::all());
    $gold_qualities = Cache::remember('gold_qualities', $minutes, fn() => GoldQualityMaster::all());
    $gold_colors = Cache::remember('gold_colors', $minutes, fn() => GoldColorMaster::all());
    $allCollections = Cache::remember('shop_collections', $minutes, fn() => ProductCollection::all());
    $trendingProducts = Cache::remember('trending_products', $minutes, fn() =>
        Product::where('product_tag', 1)->where('status', 1)->limit(3)->get()
    );

    return view('newshop', [
        'all_products' => $paginated,
        'categories' => $categories,
        'diamondclaritycolorssolitaire' => $diamondclaritycolorssolitaire,
        'diamondclaritycolors' => $diamondclaritycolors,
        'diamondshapes' => $diamondshapes,
        'diamondwieghts' => $diamondwieghts,
        'gold_qualities' => $gold_qualities,
        'gold_colors' => $gold_colors,
        'allCollections' => $allCollections,
        'trendingProducts' => $trendingProducts,
        'country' => $country,
    ]);
}
}
