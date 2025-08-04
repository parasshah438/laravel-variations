<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recently_viewed_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('guest_token')->nullable();
            $table->foreignId('product_id')->constrained();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('recently_viewed_products');
    }
};
