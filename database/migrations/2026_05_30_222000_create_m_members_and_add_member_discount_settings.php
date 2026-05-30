<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained('m_merchants')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('joined_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['merchant_id', 'phone']);
        });

        Schema::table('m_merchants', function (Blueprint $table) {
            if (! Schema::hasColumn('m_merchants', 'member_discount_is_active')) {
                $table->boolean('member_discount_is_active')->default(false)->after('qris_payload');
            }

            if (! Schema::hasColumn('m_merchants', 'member_discount_type')) {
                $table->enum('member_discount_type', ['percent', 'fixed'])->default('percent')->after('member_discount_is_active');
            }

            if (! Schema::hasColumn('m_merchants', 'member_discount_value')) {
                $table->decimal('member_discount_value', 15, 2)->default(0)->after('member_discount_type');
            }

            if (! Schema::hasColumn('m_merchants', 'member_discount_max_amount')) {
                $table->decimal('member_discount_max_amount', 15, 2)->nullable()->after('member_discount_value');
            }

            if (! Schema::hasColumn('m_merchants', 'member_discount_min_purchase')) {
                $table->decimal('member_discount_min_purchase', 15, 2)->default(0)->after('member_discount_max_amount');
            }
        });

        Schema::table('t_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('t_transactions', 'member_id')) {
                $table->foreignId('member_id')->nullable()->after('discount_code')->constrained('m_members')->nullOnDelete();
            }

            if (! Schema::hasColumn('t_transactions', 'member_name')) {
                $table->string('member_name')->nullable()->after('member_id');
            }

            if (! Schema::hasColumn('t_transactions', 'member_phone')) {
                $table->string('member_phone')->nullable()->after('member_name');
            }

            if (! Schema::hasColumn('t_transactions', 'member_discount_amount')) {
                $table->decimal('member_discount_amount', 15, 2)->default(0)->after('discount_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('t_transactions', 'member_id')) {
                $table->dropConstrainedForeignId('member_id');
            }

            foreach (['member_phone', 'member_name', 'member_discount_amount'] as $column) {
                if (Schema::hasColumn('t_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('m_merchants', function (Blueprint $table) {
            foreach ([
                'member_discount_min_purchase',
                'member_discount_max_amount',
                'member_discount_value',
                'member_discount_type',
                'member_discount_is_active',
            ] as $column) {
                if (Schema::hasColumn('m_merchants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('m_members');
    }
};
