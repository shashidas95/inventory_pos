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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2);
            $table->decimal('subtotal_amount', 10, 2);
            $table->decimal('vat_percentage', 5, 2);
            $table->decimal('vat_amount', 10, 2);
            $table->decimal('final_total', 10, 2);
            $table->text('notes')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
