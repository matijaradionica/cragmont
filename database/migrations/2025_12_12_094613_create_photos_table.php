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
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')
                ->constrained('routes')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict');
            $table->string('path'); // storage/app/public/photos/...
            $table->boolean('is_topo')->default(false);
            $table->string('caption')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            // Index for ordering
            $table->index(['route_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
