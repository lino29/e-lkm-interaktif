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
        Schema::create('game_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educational_game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('started')->index();
            $table->unsignedSmallInteger('attempt_number')->default(1);
            $table->decimal('score', 6, 2)->default(0);
            $table->decimal('max_score', 6, 2)->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('finished_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['educational_game_id', 'user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_attempts');
    }
};
