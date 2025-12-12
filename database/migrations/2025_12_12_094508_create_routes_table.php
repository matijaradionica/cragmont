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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('name');
            $table->foreignId('location_id')
                ->constrained('locations')
                ->onDelete('cascade');
            $table->foreignId('created_by_user_id')
                ->constrained('users')
                ->onDelete('restrict'); // Keep routes even if user deleted

            // Technical specs
            $table->unsignedInteger('length_m')->nullable(); // Length in meters
            $table->unsignedTinyInteger('pitch_count')->default(1);
            $table->enum('grade_type', ['UIAA', 'French'])->default('UIAA');
            $table->string('grade_value', 10); // e.g., "VII+", "7a"
            $table->enum('risk_rating', ['None', 'R', 'X'])->default('None');

            // Logistics
            $table->text('approach_description')->nullable();
            $table->text('descent_description')->nullable();
            $table->text('required_gear')->nullable();

            // Type
            $table->enum('route_type', ['Alpine', 'Sport', 'Traditional'])->default('Sport');

            // Visuals
            $table->string('topo_url')->nullable(); // Path to topo diagram

            // Status & Moderation
            $table->enum('status', ['New', 'Equipped', 'Needs Repair', 'Closed'])->default('New');
            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by_user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();

            // Indexes for performance
            $table->index(['location_id', 'name']);
            $table->index('is_approved');
            $table->index(['grade_type', 'grade_value']);
            $table->index('route_type');
            $table->index('created_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
