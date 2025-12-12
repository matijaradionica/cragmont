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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('locations')
                ->onDelete('cascade'); // Delete children when parent deleted
            $table->decimal('gps_lat', 10, 8)->nullable(); // e.g., 47.12345678
            $table->decimal('gps_lng', 11, 8)->nullable(); // e.g., -122.12345678
            $table->text('description')->nullable();
            $table->integer('level')->default(0); // 0=Mountain, 1=Cliff, 2=Sector
            $table->timestamps();

            // Indexes for performance
            $table->index(['parent_id', 'name']);
            $table->index(['gps_lat', 'gps_lng']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
