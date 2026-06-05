<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('r_whatsapp_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('r_whatsapp_logs', 'provider')) {
                $table->string('provider')->default('fonnte')->after('status');
            }

            if (! Schema::hasColumn('r_whatsapp_logs', 'provider_request_id')) {
                $table->string('provider_request_id')->nullable()->after('provider');
            }

            if (! Schema::hasColumn('r_whatsapp_logs', 'provider_message_ids')) {
                $table->json('provider_message_ids')->nullable()->after('provider_request_id');
            }

            if (! Schema::hasColumn('r_whatsapp_logs', 'response_body')) {
                $table->json('response_body')->nullable()->after('provider_message_ids');
            }

            if (! Schema::hasColumn('r_whatsapp_logs', 'error_message')) {
                $table->text('error_message')->nullable()->after('response_body');
            }
        });
    }

    public function down(): void
    {
        Schema::table('r_whatsapp_logs', function (Blueprint $table) {
            $columns = [
                'provider',
                'provider_request_id',
                'provider_message_ids',
                'response_body',
                'error_message',
            ];

            $existingColumns = array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn('r_whatsapp_logs', $column),
            );

            if ($existingColumns !== []) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
