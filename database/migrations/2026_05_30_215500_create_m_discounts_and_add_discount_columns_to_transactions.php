<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained('m_merchants')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->enum('type', ['percent', 'fixed']);
            $table->decimal('value', 15, 2);
            $table->decimal('max_discount_amount', 15, 2)->nullable();
            $table->decimal('min_purchase_amount', 15, 2)->default(0);
            $table->unsignedInteger('quota')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['merchant_id', 'code']);
        });

        Schema::table('t_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('t_transactions', 'discount_id')) {
                $table->foreignId('discount_id')->nullable()->after('user_id')->constrained('m_discounts')->nullOnDelete();
            }

            if (! Schema::hasColumn('t_transactions', 'discount_code')) {
                $table->string('discount_code')->nullable()->after('discount_id');
            }

            if (! Schema::hasColumn('t_transactions', 'subtotal_amount')) {
                $table->decimal('subtotal_amount', 15, 2)->default(0)->after('invoice_number');
            }

            if (! Schema::hasColumn('t_transactions', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('subtotal_amount');
            }
        });

        DB::table('t_transactions')
            ->where('subtotal_amount', 0)
            ->update(['subtotal_amount' => DB::raw('total_amount')]);
    }

    public function down(): void
    {
        Schema::table('t_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('t_transactions', 'discount_id')) {
                $table->dropConstrainedForeignId('discount_id');
            }

            foreach (['discount_code', 'discount_amount', 'subtotal_amount'] as $column) {
                if (Schema::hasColumn('t_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('m_discounts');
    }
};
