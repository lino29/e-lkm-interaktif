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
        Schema::create('module_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->string('section_type');
            $table->string('title');
            $table->string('slug');
            $table->longText('content')->nullable();
            $table->json('content_json')->nullable();
            $table->unsignedSmallInteger('order')->default(1);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->unique(['module_id', 'section_type', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_sections');
    }
};
