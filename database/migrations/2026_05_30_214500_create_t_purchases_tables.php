<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained('m_merchants')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('m_suppliers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invoice_number')->nullable();
            $table->date('purchase_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['completed', 'cancelled'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['merchant_id', 'purchase_date']);
            $table->index(['supplier_id', 'purchase_date']);
        });

        Schema::create('t_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('t_purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('m_products')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_purchase_items');
        Schema::dropIfExists('t_purchases');
    }
};
