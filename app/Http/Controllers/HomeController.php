<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category.parent', 'brand', 'images', 'variations']);
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }
        
        // Category filter (including child categories)
        if ($request->has('category') && $request->category) {
            $category = Category::find($request->category);
            if ($category) {
                // If it's a parent category, include all its children
                if ($category->isParent()) {
                    $categoryIds = collect([$category->id])->merge($category->descendants()->pluck('id'));
                    $query->whereIn('category_id', $categoryIds);
                } else {
                    $query->where('category_id', $category->id);
                }
            }
        }
        
        // Brand filter
        if ($request->has('brand') && $request->brand) {
            $query->where('brand_id', $request->brand);
        }
        
        $products = $query->paginate(12);
        
        // Get categories for filter (only active ones)
        $categories = Category::active()
                             ->with('children')
                             ->orderBy('sort_order')
                             ->get();
        
        $brands = Brand::all();
        
        return view('home', compact('products', 'categories', 'brands'));
    }
    
    public function loadMore(Request $request)
    {
        $page = $request->get('page', 1);
        $query = Product::with(['category.parent', 'brand', 'images', 'variations']);
        
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        if ($request->has('category') && $request->category) {
            $category = Category::find($request->category);
            if ($category) {
                if ($category->isParent()) {
                    $categoryIds = collect([$category->id])->merge($category->descendants()->pluck('id'));
                    $query->whereIn('category_id', $categoryIds);
                } else {
                    $query->where('category_id', $category->id);
                }
            }
        }
        
        if ($request->has('brand') && $request->brand) {
            $query->where('brand_id', $request->brand);
        }
        
        $products = $query->paginate(12, ['*'], 'page', $page);
        
        return response()->json([
            'html' => view('partials.product-grid', compact('products'))->render(),
            'hasMore' => $products->hasMorePages()
        ]);
    }
    
    public function search(Request $request)
    {
        $term = $request->get('term');
        $products = Product::where('name', 'like', '%' . $term . '%')
                          ->limit(10)
                          ->get(['id', 'name']);
        
        return response()->json($products);
    }
    
    public function category($slug)
    {
        $category = Category::where('slug', $slug)->where('status', true)->firstOrFail();
        
        // Get products from this category and its children
        $products = $category->getAllProducts()
                           ->with(['category', 'brand', 'images', 'variations'])
                           ->paginate(12);
        
        $categories = Category::active()
                             ->with('children')
                             ->orderBy('sort_order')
                             ->get();
        
        $brands = Brand::all();
        
        return view('category', compact('category', 'products', 'categories', 'brands'));
    }
}
