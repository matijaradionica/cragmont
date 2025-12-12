<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Ensure default roles exist even if seeders aren't run (e.g. after migrate:refresh).
        $now = now();
        DB::table('roles')->insert([
            ['name' => 'Admin', 'description' => 'Full system access with all permissions', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Moderator', 'description' => 'Can approve routes and manage locations', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Standard', 'description' => 'Can create routes (requires approval)', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Club/Equipper', 'description' => 'Can create routes (auto-approved)', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
