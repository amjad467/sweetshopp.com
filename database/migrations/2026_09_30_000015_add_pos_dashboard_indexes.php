<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->index(['is_active', 'name'], 'customers_active_name_index');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->index(['customer_id', 'debt_amount'], 'sales_customer_debt_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['customer_id', 'sale_id'], 'payments_customer_sale_index');
            $table->index(['payment_date', 'customer_id'], 'payments_date_customer_index');
        });

        Schema::table('debt_transactions', function (Blueprint $table) {
            $table->index(['customer_id', 'type', 'transaction_date'], 'debt_customer_type_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('customers', fn (Blueprint $table) => $table->dropIndex('customers_active_name_index'));
        Schema::table('sales', fn (Blueprint $table) => $table->dropIndex('sales_customer_debt_index'));
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_customer_sale_index');
            $table->dropIndex('payments_date_customer_index');
        });
        Schema::table('debt_transactions', fn (Blueprint $table) => $table->dropIndex('debt_customer_type_date_index'));
    }
};
