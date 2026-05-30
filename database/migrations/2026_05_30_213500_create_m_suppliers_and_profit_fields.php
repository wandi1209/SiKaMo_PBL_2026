<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained('m_merchants')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['merchant_id', 'name']);
        });

        Schema::table('m_products', function (Blueprint $table) {
            if (! Schema::hasColumn('m_products', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('merchant_id')->constrained('m_suppliers')->nullOnDelete();
            }

            if (! Schema::hasColumn('m_products', 'cost_price')) {
                $table->unsignedInteger('cost_price')->default(0)->after('price');
            }
        });

        Schema::table('t_transaction_items', function (Blueprint $table) {
            if (! Schema::hasColumn('t_transaction_items', 'unit_cost')) {
                $table->decimal('unit_cost', 15, 2)->default(0)->after('unit_price');
            }

            if (! Schema::hasColumn('t_transaction_items', 'total_cost')) {
                $table->decimal('total_cost', 15, 2)->default(0)->after('subtotal');
            }

            if (! Schema::hasColumn('t_transaction_items', 'gross_profit')) {
                $table->decimal('gross_profit', 15, 2)->default(0)->after('total_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_transaction_items', function (Blueprint $table) {
            foreach (['gross_profit', 'total_cost', 'unit_cost'] as $column) {
                if (Schema::hasColumn('t_transaction_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('m_products', function (Blueprint $table) {
            if (Schema::hasColumn('m_products', 'supplier_id')) {
                $table->dropConstrainedForeignId('supplier_id');
            }

            if (Schema::hasColumn('m_products', 'cost_price')) {
                $table->dropColumn('cost_price');
            }
        });

        Schema::dropIfExists('m_suppliers');
    }
};
