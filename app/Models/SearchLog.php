<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchLog extends Model
{
    protected $fillable = [
        'query',
        'results_count',
        'user_id',
        'ip_address',
        'user_agent',
        'filters',
        'sort_by',
        'execution_time',
    ];

    protected $casts = [
        'filters' => 'array',
        'execution_time' => 'decimal:4',
        'results_count' => 'integer',
    ];

    /**
     * Get the user that performed the search
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for popular searches
     */
    public function scopePopular($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days))
                    ->selectRaw('query, COUNT(*) as search_count')
                    ->groupBy('query')
                    ->orderByDesc('search_count');
    }

    /**
     * Scope for recent searches
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days))
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Scope for user searches
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get analytics data
     */
    public static function getAnalytics($days = 30)
    {
        $startDate = now()->subDays($days);

        return [
            'total_searches' => static::where('created_at', '>=', $startDate)->count(),
            'unique_queries' => static::where('created_at', '>=', $startDate)->distinct('query')->count(),
            'zero_results' => static::where('created_at', '>=', $startDate)->where('results_count', 0)->count(),
            'average_results' => static::where('created_at', '>=', $startDate)->avg('results_count'),
            'popular_queries' => static::popular($days)->limit(10)->get(),
            'daily_searches' => static::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                                     ->where('created_at', '>=', $startDate)
                                     ->groupBy('date')
                                     ->orderBy('date')
                                     ->get(),
        ];
    }
}
