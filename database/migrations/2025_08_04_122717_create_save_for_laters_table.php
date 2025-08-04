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
        Schema::create('save_for_laters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('guest_token')->nullable();
            $table->foreignId('product_variation_id')->constrained()->onDelete('cascade');
            $table->integer('qty')->default(1);
            $table->timestamps();
            
            $table->index(['user_id', 'product_variation_id']);
            $table->index(['guest_token', 'product_variation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('save_for_laters');
    }
};
