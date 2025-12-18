<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['approved_by_user_id']);

            // Drop index
            $table->dropIndex(['is_approved']);

            // Drop approval columns
            $table->dropColumn(['is_approved', 'approved_at', 'approved_by_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            // Re-add approval columns
            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by_user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // Re-add index
            $table->index('is_approved');
        });
    }
};
