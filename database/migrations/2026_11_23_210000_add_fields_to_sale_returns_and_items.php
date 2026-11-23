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
        Schema::table('sale_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_returns', 'refund_type')) {
                $table->string('refund_type', 50)->nullable()->default('ledger')->after('status');
            }
            if (!Schema::hasColumn('sale_returns', 'return_reason')) {
                $table->string('return_reason')->nullable()->after('remarks');
            }
        });

        Schema::table('sale_return_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_return_items', 'return_reason')) {
                $table->string('return_reason')->nullable()->after('unit');
            }
            if (!Schema::hasColumn('sale_return_items', 'stock_condition')) {
                $table->string('stock_condition', 20)->default('good')->after('line_total');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            if (Schema::hasColumn('sale_returns', 'refund_type')) {
                $table->dropColumn('refund_type');
            }
            if (Schema::hasColumn('sale_returns', 'return_reason')) {
                $table->dropColumn('return_reason');
            }
        });

        Schema::table('sale_return_items', function (Blueprint $table) {
            if (Schema::hasColumn('sale_return_items', 'return_reason')) {
                $table->dropColumn('return_reason');
            }
            if (Schema::hasColumn('sale_return_items', 'stock_condition')) {
                $table->dropColumn('stock_condition');
            }
        });
    }
};
