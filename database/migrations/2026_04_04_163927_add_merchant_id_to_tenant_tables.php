<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add merchant_id to m_categories (nullable karena ada data lama)
        Schema::table('m_categories', function (Blueprint $table) {
            $table->foreignId('merchant_id')
                ->nullable()
                ->after('id')
                ->constrained('m_merchants')
                ->onDelete('cascade');
        });

        // Add merchant_id to m_products (nullable karena ada data lama)
        Schema::table('m_products', function (Blueprint $table) {
            $table->foreignId('merchant_id')
                ->nullable()
                ->after('id')
                ->constrained('m_merchants')
                ->onDelete('cascade');
        });

        // Add merchant_id to r_daily_reports (nullable karena ada data lama)
        Schema::table('r_daily_reports', function (Blueprint $table) {
            $table->foreignId('merchant_id')
                ->nullable()
                ->after('id')
                ->constrained('m_merchants')
                ->onDelete('cascade');
        });

        // Add merchant_id to r_whatsapp_logs (nullable karena ada data lama)
        Schema::table('r_whatsapp_logs', function (Blueprint $table) {
            $table->foreignId('merchant_id')
                ->nullable()
                ->after('id')
                ->constrained('m_merchants')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('m_categories', function (Blueprint $table) {
            $table->dropForeign(['merchant_id']);
            $table->dropColumn('merchant_id');
        });

        Schema::table('m_products', function (Blueprint $table) {
            $table->dropForeign(['merchant_id']);
            $table->dropColumn('merchant_id');
        });

        Schema::table('r_daily_reports', function (Blueprint $table) {
            $table->dropForeign(['merchant_id']);
            $table->dropColumn('merchant_id');
        });

        Schema::table('r_whatsapp_logs', function (Blueprint $table) {
            $table->dropForeign(['merchant_id']);
            $table->dropColumn('merchant_id');
        });
    }
};

