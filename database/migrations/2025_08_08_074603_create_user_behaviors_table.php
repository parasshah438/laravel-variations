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
        Schema::create('user_behaviors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // null for guest users
            $table->string('session_id', 100)->nullable(); // reduced length for guest tracking
            $table->unsignedBigInteger('product_id');
            $table->string('behavior_type', 50); // view, cart_add, cart_remove, wishlist_add, etc.
            $table->json('metadata')->nullable(); // additional context data
            $table->decimal('value', 10, 2)->nullable(); // for ratings, prices, quantities
            $table->timestamp('behavior_timestamp');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'behavior_type', 'behavior_timestamp'], 'ub_user_behavior_time_idx');
            $table->index(['session_id', 'behavior_type'], 'ub_session_behavior_idx');
            $table->index(['product_id', 'behavior_type'], 'ub_product_behavior_idx');
            $table->index('behavior_timestamp', 'ub_timestamp_idx');
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_behaviors');
    }
};
