<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreignId('merchant_id')->nullable()->after('user_id')->constrained('m_merchants')->nullOnDelete();
            $table->string('user_name')->nullable()->after('merchant_id');
            $table->string('user_email')->nullable()->after('user_name');
            $table->string('user_role')->nullable()->after('user_email');
            $table->string('auditable_table')->nullable()->after('auditable_type');
            $table->json('changed_fields')->nullable()->after('new_values');
            $table->string('summary')->nullable()->after('changed_fields');
            $table->string('request_method')->nullable()->after('summary');
            $table->text('request_url')->nullable()->after('request_method');
            $table->string('route_name')->nullable()->after('request_url');
            $table->string('panel')->nullable()->after('route_name');
            $table->string('request_id')->nullable()->after('panel');
            $table->json('metadata')->nullable()->after('request_id');

            $table->index(['merchant_id', 'created_at']);
            $table->index(['user_role', 'created_at']);
            $table->index('request_id');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['merchant_id', 'created_at']);
            $table->dropIndex(['user_role', 'created_at']);
            $table->dropIndex(['request_id']);

            $table->dropConstrainedForeignId('merchant_id');
            $table->dropColumn([
                'user_name',
                'user_email',
                'user_role',
                'auditable_table',
                'changed_fields',
                'summary',
                'request_method',
                'request_url',
                'route_name',
                'panel',
                'request_id',
                'metadata',
            ]);
        });
    }
};
