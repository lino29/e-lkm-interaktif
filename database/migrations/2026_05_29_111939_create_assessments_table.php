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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_unit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->default('formative');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('kktp')->default(75);
            $table->unsignedTinyInteger('max_attempts')->default(2);
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('order')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
