<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'active')->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_users' => User::count(),
            'total_categories' => Category::count(),
            'total_revenue' => Order::where('status', '!=', 'cancelled')->sum('total'),
            'monthly_revenue' => Order::where('status', '!=', 'cancelled')
                                    ->whereMonth('created_at', Carbon::now()->month)
                                    ->sum('total')
        ];

        // Recent orders
        $recentOrders = Order::with(['user', 'items.productVariation.product'])
                            ->latest()
                            ->take(5)
                            ->get();

        // Low stock products
        $lowStockProducts = Product::with(['variations' => function($query) {
                                        $query->where('stock', '<=', 5)->where('stock', '>', 0);
                                    }])
                                  ->whereHas('variations', function($query) {
                                      $query->where('stock', '<=', 5)->where('stock', '>', 0);
                                  })
                                  ->take(5)
                                  ->get();

        // Monthly sales chart data
        $monthlySales = Order::selectRaw('MONTH(created_at) as month, COUNT(*) as count, SUM(total) as revenue')
                           ->where('status', '!=', 'cancelled')
                           ->whereYear('created_at', Carbon::now()->year)
                           ->groupBy('month')
                           ->orderBy('month')
                           ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'lowStockProducts', 'monthlySales'));
    }
}
