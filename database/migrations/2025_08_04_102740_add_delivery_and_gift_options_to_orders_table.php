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
        Schema::table('orders', function (Blueprint $table) {
            // Delivery options
            $table->string('delivery_speed', 20)->default('standard')->after('status');
            $table->date('delivery_date')->nullable()->after('delivery_speed');
            $table->string('delivery_time_slot', 20)->nullable()->after('delivery_date');
            $table->text('delivery_instructions')->nullable()->after('delivery_time_slot');
            
            // Gift options
            $table->boolean('is_gift')->default(false)->after('delivery_instructions');
            $table->boolean('gift_wrap')->default(false)->after('is_gift');
            $table->text('gift_message')->nullable()->after('gift_wrap');
            $table->string('gift_recipient_name')->nullable()->after('gift_message');
            
            // Communication preferences
            $table->boolean('sms_updates')->default(true)->after('gift_recipient_name');
            $table->boolean('email_updates')->default(true)->after('sms_updates');
            
            // Additional charges
            $table->decimal('delivery_charge', 8, 2)->default(0)->after('email_updates');
            $table->decimal('gift_wrap_charge', 8, 2)->default(0)->after('delivery_charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_speed',
                'delivery_date',
                'delivery_time_slot',
                'delivery_instructions',
                'is_gift',
                'gift_wrap',
                'gift_message',
                'gift_recipient_name',
                'sms_updates',
                'email_updates',
                'delivery_charge',
                'gift_wrap_charge'
            ]);
        });
    }
};
