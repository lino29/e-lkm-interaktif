<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LearningUnitReadyForReview extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $learningUnitGradeId,
        public int $studentId,
        public string $studentName,
        public int $learningUnitId,
        public string $learningUnitTitle,
        public string $moduleTitle,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'learning_unit_grade_id' => $this->learningUnitGradeId,
            'student_id' => $this->studentId,
            'student_name' => $this->studentName,
            'learning_unit_id' => $this->learningUnitId,
            'learning_unit_title' => $this->learningUnitTitle,
            'module_title' => $this->moduleTitle,
            'message' => $this->studentName.' telah menyelesaikan '.$this->learningUnitTitle.' dan menunggu penilaian.',
            'action_url' => route('guru.activity-reviews', ['grade' => $this->learningUnitGradeId]),
        ];
    }
}
