<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->foreignId('product_variation_id')->nullable()->constrained()->after('product_id');
            $table->string('variation_attribute_value_id')->nullable()->after('product_variation_id');
            $table->integer('sort_order')->default(0)->after('is_main');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropForeign(['product_variation_id']);
            $table->dropColumn(['product_variation_id', 'variation_attribute_value_id', 'sort_order']);
        });
    }
};
