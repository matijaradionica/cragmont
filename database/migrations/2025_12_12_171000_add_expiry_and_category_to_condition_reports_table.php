<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condition_reports', function (Blueprint $table) {
            $table->string('category')->nullable()->after('type');
            $table->timestamp('expires_at')->nullable()->after('content');
            $table->timestamp('archived_at')->nullable()->after('expires_at');

            $table->index(['type', 'expires_at', 'archived_at']);
        });
    }

    public function down(): void
    {
        Schema::table('condition_reports', function (Blueprint $table) {
            $table->dropIndex(['type', 'expires_at', 'archived_at']);
            $table->dropColumn(['category', 'expires_at', 'archived_at']);
        });
    }
};

