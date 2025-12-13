<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE condition_reports MODIFY content TEXT NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("UPDATE condition_reports SET content = '' WHERE content IS NULL");
        DB::statement('ALTER TABLE condition_reports MODIFY content TEXT NOT NULL');
    }
};
