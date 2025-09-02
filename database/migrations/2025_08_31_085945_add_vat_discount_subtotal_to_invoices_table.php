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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('vat_percentage', 5, 2)->default(0)->after('total_amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('vat_percentage');
            $table->decimal('subtotal_amount', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('vat_amount', 10, 2)->default(0)->after('subtotal_amount');
            $table->decimal('final_total', 10, 2)->default(0)->after('discount_amount');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['vat_percentage', 'discount_amount', 'subtotal_amount', 'vat_amount', 'final_total']);
        });
    }
};
