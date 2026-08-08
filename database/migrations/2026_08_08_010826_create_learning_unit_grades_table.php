<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('learning_unit_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('score', 4, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['learning_unit_id', 'student_id']);
            $table->index(['reviewed_by', 'reviewed_at']);
        });

        $completedSubmissions = DB::table('activity_answers')
            ->join('activities', 'activities.id', '=', 'activity_answers.activity_id')
            ->join('learning_units', 'learning_units.id', '=', 'activities.learning_unit_id')
            ->whereIn('activity_answers.status', ['submitted', 'reviewed'])
            ->whereBetween('learning_units.order', [1, 5])
            ->select([
                'activity_answers.user_id',
                'learning_units.id as learning_unit_id',
                DB::raw('MAX(activity_answers.submitted_at) as ready_at'),
            ])
            ->groupBy('activity_answers.user_id', 'learning_units.id')
            ->get();

        $gradeRows = $completedSubmissions
            ->filter(function (object $submission): bool {
                $requiredActivityIds = DB::table('activities')
                    ->where('learning_unit_id', $submission->learning_unit_id)
                    ->where('is_required', true)
                    ->pluck('id');

                if ($requiredActivityIds->isEmpty()) {
                    return false;
                }

                $submittedCount = DB::table('activity_answers')
                    ->where('user_id', $submission->user_id)
                    ->whereIn('activity_id', $requiredActivityIds)
                    ->whereIn('status', ['submitted', 'reviewed'])
                    ->distinct()
                    ->count('activity_id');

                return $submittedCount === $requiredActivityIds->count();
            })
            ->map(fn (object $submission): array => [
                'learning_unit_id' => $submission->learning_unit_id,
                'student_id' => $submission->user_id,
                'created_at' => $submission->ready_at ?? now(),
                'updated_at' => $submission->ready_at ?? now(),
            ])
            ->all();

        if ($gradeRows !== []) {
            DB::table('learning_unit_grades')->insertOrIgnore($gradeRows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_unit_grades');
    }
};
