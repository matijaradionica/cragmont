<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condition_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('type'); // general | safety_concern
            $table->text('content');
            $table->boolean('is_approved')->default(false);
            $table->foreignId('moderator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['route_id', 'type', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condition_reports');
    }
};

