<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_merchants', function (Blueprint $table) {
            $table->time('operational_start_time')->nullable()->after('logo');
            $table->time('operational_end_time')->nullable()->after('operational_start_time');
            $table->string('qr_code_image')->nullable()->after('operational_end_time');
        });
    }

    public function down(): void
    {
        Schema::table('m_merchants', function (Blueprint $table) {
            $table->dropColumn([
                'operational_start_time',
                'operational_end_time',
                'qr_code_image',
            ]);
        });
    }
};

