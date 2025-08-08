<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use App\Services\VisualSearchService;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    protected $searchService;
    protected $visualSearchService;

    public function __construct(SearchService $searchService, VisualSearchService $visualSearchService)
    {
        $this->searchService = $searchService;
        $this->visualSearchService = $visualSearchService;
    }

    /**
     * Main search page
     */
    public function index(Request $request)
    {
        // Handle visual search mode
        if ($request->has('visual') && $request->get('visual') == '1') {
            // The visual search results page - results should come from sessionStorage via JavaScript
            // This is the page you land on AFTER uploading an image through the visual search modal
            $results = [
                'products' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12),
                'facets' => [
                    'categories' => collect([]),
                    'brands' => collect([]),
                    'attributes' => collect([]),
                    'price_ranges' => [],
                ],
                'suggestions' => [],
                'query_info' => [
                    'query' => 'Visual Search Results',
                    'total_results' => $request->get('results', 0),
                    'has_results' => $request->get('results', 0) > 0,
                    'execution_time' => 0,
                    'is_visual_search' => true,
                    'waiting_for_js' => true, // Indicates results will be loaded by JavaScript
                ],
            ];
        }
        // Initialize empty results for first load
        elseif (!$request->filled('q') && !$request->has('filters')) {
            $results = [
                'products' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12),
                'facets' => [
                    'categories' => collect([]),
                    'brands' => collect([]),
                    'attributes' => collect([]),
                    'price_ranges' => [],
                ],
                'suggestions' => [],
                'query_info' => [
                    'query' => '',
                    'total_results' => 0,
                    'has_results' => false,
                    'execution_time' => 0,
                ],
            ];
        } else {
            $results = $this->searchService->search($request);
            
            // Log search for analytics
            if ($request->filled('q')) {
                $this->searchService->logSearch(
                    $request->get('q'),
                    $results['products']->total(),
                    auth()->id()
                );
            }
        }

        // For AJAX requests, return JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('search.partials.results', $results)->render(),
                'pagination' => $results['products']->appends($request->all())->links()->render(),
                'facets' => $results['facets'],
                'query_info' => $results['query_info'],
            ]);
        }

        // Get trending searches
        $trendingSearches = $this->searchService->getTrendingSearches();

        return view('search.index', compact('results', 'trendingSearches'));
    }

    /**
     * Auto-complete suggestions
     */
    public function suggestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'suggestions' => []
            ]);
        }

        $query = $request->get('q');
        
        // Cache suggestions for 5 minutes
        $suggestions = Cache::remember("search_suggestions_{$query}", 300, function() use ($query) {
            return $this->searchService->getSearchSuggestions($query);
        });

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
            'query' => $query,
        ]);
    }

    /**
     * Quick search for header search box
     */
    public function quickSearch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'results' => []
            ]);
        }

        $query = $request->get('q');
        
        // Get quick results (limited)
        $request->merge(['per_page' => 6]);
        $results = $this->searchService->search($request);

        return response()->json([
            'success' => true,
            'results' => $results['products']->items(),
            'suggestions' => $results['suggestions'],
            'total' => $results['products']->total(),
            'search_url' => route('search', ['q' => $query]),
        ]);
    }

    /**
     * Filter products (AJAX)
     */
    public function filter(Request $request)
    {
        $results = $this->searchService->search($request);

        return response()->json([
            'success' => true,
            'html' => view('search.partials.results', $results)->render(),
            'pagination' => $results['products']->appends($request->all())->links()->render(),
            'facets' => $results['facets'],
            'query_info' => $results['query_info'],
            'products_count' => $results['products']->total(),
        ]);
    }

    /**
     * Handle visual search by image
     */
    public function visualSearch(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5MB max
        ]);

        try {
            // Use the actual visual search service
            $results = $this->visualSearchService->findSimilarProducts($request->file('image'));

            return response()->json([
                'success' => true,
                'results' => $results,
                'message' => 'Visual search completed. Found ' . count($results) . ' similar products.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Visual search failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Handle visual search from main search page
     */
    protected function handleVisualSearch(Request $request)
    {
        if ($request->hasFile('image')) {
            try {
                // Use the actual visual search service
                $visualResults = $this->visualSearchService->findSimilarProducts($request->file('image'));
                
                // Convert visual search results to paginated format
                $products = collect($visualResults);
                $perPage = 12;
                $currentPage = $request->get('page', 1);
                $total = $products->count();
                
                $paginatedProducts = new \Illuminate\Pagination\LengthAwarePaginator(
                    $products->forPage($currentPage, $perPage),
                    $total,
                    $perPage,
                    $currentPage,
                    ['path' => $request->url(), 'pageName' => 'page']
                );

                $results = [
                    'products' => $paginatedProducts,
                    'facets' => [
                        'categories' => collect([]),
                        'brands' => collect([]),
                        'attributes' => collect([]),
                        'price_ranges' => [],
                    ],
                    'suggestions' => [],
                    'query_info' => [
                        'query' => 'Visual Search Results',
                        'total_results' => $total,
                        'has_results' => $total > 0,
                        'execution_time' => 0,
                        'is_visual_search' => true,
                    ],
                ];

                // For AJAX requests, return JSON
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'html' => view('search.partials.results', $results)->render(),
                        'pagination' => $results['products']->appends($request->all())->links()->render(),
                        'facets' => $results['facets'],
                        'query_info' => $results['query_info'],
                    ]);
                }

                return $results;
            } catch (\Exception $e) {
                $results = [
                    'products' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12),
                    'facets' => [
                        'categories' => collect([]),
                        'brands' => collect([]),
                        'attributes' => collect([]),
                        'price_ranges' => [],
                    ],
                    'suggestions' => [],
                    'query_info' => [
                        'query' => 'Visual Search Error',
                        'total_results' => 0,
                        'has_results' => false,
                        'execution_time' => 0,
                        'is_visual_search' => true,
                        'error' => $e->getMessage(),
                    ],
                ];
                
                return $results;
            }
        }

        // No image uploaded, return empty results
        return [
            'products' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12),
            'facets' => [
                'categories' => collect([]),
                'brands' => collect([]),
                'attributes' => collect([]),
                'price_ranges' => [],
            ],
            'suggestions' => [],
            'query_info' => [
                'query' => 'Visual Search Mode',
                'total_results' => 0,
                'has_results' => false,
                'execution_time' => 0,
                'is_visual_search' => true,
            ],
        ];
    }

    /**
     * Get trending searches
     */
    public function trending()
    {
        $trending = Cache::remember('trending_searches', 3600, function() {
            return $this->searchService->getTrendingSearches(15);
        });

        return response()->json([
            'success' => true,
            'trending' => $trending,
        ]);
    }

    /**
     * Search analytics (for admin)
     */
    public function analytics(Request $request)
    {
        // This would be in admin controller, but including here for completeness
        $analytics = \DB::table('search_logs')
            ->selectRaw('query, COUNT(*) as search_count, AVG(results_count) as avg_results')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('query')
            ->orderByDesc('search_count')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'analytics' => $analytics,
        ]);
    }
}
