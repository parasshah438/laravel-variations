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
        Schema::create('product_recommendations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // null for global recommendations
            $table->string('session_id', 100)->nullable(); // reduced length for guest recommendations
            $table->unsignedBigInteger('product_id'); // the product being recommended
            $table->unsignedBigInteger('based_on_product_id')->nullable(); // product that triggered this recommendation
            $table->string('recommendation_type', 50); // collaborative, content_based, cross_sell, upsell, trending
            $table->decimal('confidence_score', 5, 4); // 0.0000 to 9.9999
            $table->json('reasoning')->nullable(); // explanation of why recommended
            $table->timestamp('expires_at')->nullable(); // when recommendation expires
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'recommendation_type', 'confidence_score'], 'pr_user_type_confidence_idx');
            $table->index(['session_id', 'recommendation_type'], 'pr_session_type_idx');
            $table->index(['product_id', 'confidence_score'], 'pr_product_confidence_idx');
            $table->index('expires_at', 'pr_expires_at_idx');
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('based_on_product_id')->references('id')->on('products')->onDelete('set null');
            
            // Unique constraint to prevent duplicate recommendations
            $table->unique(['user_id', 'product_id', 'recommendation_type'], 'pr_user_product_type_unique');
            $table->unique(['session_id', 'product_id', 'recommendation_type'], 'pr_session_product_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_recommendations');
    }
};
