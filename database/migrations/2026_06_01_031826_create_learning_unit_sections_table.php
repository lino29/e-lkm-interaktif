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
        Schema::create('learning_unit_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('learning_unit_sections')->cascadeOnDelete();
            $table->string('section_type');
            $table->string('title');
            $table->string('slug')->nullable();
            $table->longText('content')->nullable();
            $table->json('content_json')->nullable();
            $table->string('linked_model_type')->nullable();
            $table->unsignedBigInteger('linked_model_id')->nullable();
            $table->unsignedSmallInteger('order')->default(1);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->index(['learning_unit_id', 'section_type']);
            $table->index(['linked_model_type', 'linked_model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_unit_sections');
    }
};
