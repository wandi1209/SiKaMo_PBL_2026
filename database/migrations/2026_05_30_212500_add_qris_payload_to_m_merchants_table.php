<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_merchants', function (Blueprint $table) {
            if (! Schema::hasColumn('m_merchants', 'qris_payload')) {
                $table->text('qris_payload')->nullable()->after('qr_code_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('m_merchants', function (Blueprint $table) {
            if (Schema::hasColumn('m_merchants', 'qris_payload')) {
                $table->dropColumn('qris_payload');
            }
        });
    }
};
