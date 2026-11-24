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
            if (!Schema::hasColumn('sale_returns', 'return_type')) {
                $table->string('return_type', 50)->default('invoice')->after('sale_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            if (Schema::hasColumn('sale_returns', 'return_type')) {
                $table->dropColumn('return_type');
            }
        });
    }
};
