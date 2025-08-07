<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('search_logs', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('search_logs', 'query')) {
                $table->string('query')->after('id');
            }
            if (!Schema::hasColumn('search_logs', 'results_count')) {
                $table->integer('results_count')->after('query');
            }
            if (!Schema::hasColumn('search_logs', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('results_count');
            }
            if (!Schema::hasColumn('search_logs', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('search_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('search_logs', 'filters')) {
                $table->json('filters')->nullable()->after('user_agent');
            }
            if (!Schema::hasColumn('search_logs', 'sort_by')) {
                $table->string('sort_by')->nullable()->after('filters');
            }
            if (!Schema::hasColumn('search_logs', 'execution_time')) {
                $table->decimal('execution_time', 8, 4)->nullable()->after('sort_by');
            }
        });
        
        // Add indexes if they don't exist
        try {
            Schema::table('search_logs', function (Blueprint $table) {
                $table->index(['query', 'created_at']);
                $table->index('user_id');
                $table->index('created_at');
            });
        } catch (\Exception $e) {
            // Indexes might already exist, ignore the error
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('search_logs', function (Blueprint $table) {
            $table->dropColumn([
                'query', 'results_count', 'user_id', 'ip_address', 
                'user_agent', 'filters', 'sort_by', 'execution_time'
            ]);
        });
    }
};
