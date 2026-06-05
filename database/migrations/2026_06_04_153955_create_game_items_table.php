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
        Schema::create('game_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educational_game_id')->constrained()->cascadeOnDelete();
            $table->string('item_type')->index();
            $table->text('prompt')->nullable();
            $table->text('question_text')->nullable();
            $table->string('media_path')->nullable();
            $table->string('media_url')->nullable();
            $table->json('options')->nullable();
            $table->json('correct_answer')->nullable();
            $table->text('explanation')->nullable();
            $table->decimal('score', 6, 2)->default(0);
            $table->unsignedSmallInteger('time_limit_seconds')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['educational_game_id', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_items');
    }
};
