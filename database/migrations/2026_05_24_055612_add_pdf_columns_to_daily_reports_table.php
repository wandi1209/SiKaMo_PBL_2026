<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('r_daily_reports', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('total_items_sold');
            $table->timestamp('pdf_generated_at')->nullable()->after('pdf_path');

            $table->index(['merchant_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::table('r_daily_reports', function (Blueprint $table) {
            $table->dropIndex(['merchant_id', 'report_date']);
            $table->dropColumn(['pdf_path', 'pdf_generated_at']);
        });
    }
};
