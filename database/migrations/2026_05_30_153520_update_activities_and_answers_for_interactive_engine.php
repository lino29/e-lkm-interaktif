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
        Schema::table('activities', function (Blueprint $table) {
            $table->json('display_config')->nullable()->after('answer_schema');
            $table->json('validation_rules')->nullable()->after('display_config');
            $table->json('sample_answer')->nullable()->after('validation_rules');
            $table->string('media_path')->nullable()->after('sample_answer');
            $table->boolean('requires_teacher_review')->default(false)->after('media_path');
        });

        Schema::table('activity_answers', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('score');
            $table->foreignId('reviewed_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('teacher_feedback')->nullable()->after('reviewed_at');
            $table->json('metadata')->nullable()->after('teacher_feedback');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_answers', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['status', 'reviewed_by', 'reviewed_at', 'teacher_feedback', 'metadata']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['display_config', 'validation_rules', 'sample_answer', 'media_path', 'requires_teacher_review']);
        });
    }
};
