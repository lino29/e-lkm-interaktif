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
        Schema::create('game_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_item_id')->constrained()->cascadeOnDelete();
            $table->json('answer')->nullable();
            $table->boolean('is_correct')->default(false)->index();
            $table->decimal('score', 6, 2)->default(0);
            $table->unsignedSmallInteger('time_spent_seconds')->nullable();
            $table->boolean('hint_used')->default(false);
            $table->text('feedback')->nullable();
            $table->timestamp('answered_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['game_attempt_id', 'game_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_attempt_answers');
    }
};
