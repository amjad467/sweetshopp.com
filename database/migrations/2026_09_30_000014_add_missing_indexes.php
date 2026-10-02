<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * زیادکردنی index‌ەکانی نەبوو بۆ باشترکردنی performance
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $t) {
            $t->index('customer_id');
            $t->index('sale_id');
            $t->index('payment_date');
        });

        Schema::table('stock_movements', function (Blueprint $t) {
            $t->index('product_id');
        });

        Schema::table('sale_items', function (Blueprint $t) {
            $t->index('sale_id');
            $t->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $t) {
            $t->dropIndex(['customer_id']);
            $t->dropIndex(['sale_id']);
            $t->dropIndex(['payment_date']);
        });

        Schema::table('stock_movements', function (Blueprint $t) {
            $t->dropIndex(['product_id']);
        });

        Schema::table('sale_items', function (Blueprint $t) {
            $t->dropIndex(['sale_id']);
            $t->dropIndex(['product_id']);
        });
    }
};
