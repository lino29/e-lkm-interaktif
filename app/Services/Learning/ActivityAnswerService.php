<?php

namespace App\Services\Learning;

use App\Models\Activity;
use App\Models\ActivityAnswer;
use Illuminate\Contracts\Auth\Authenticatable;

class ActivityAnswerService
{
    public function save(
        Activity $activity,
        Authenticatable $user,
        ?string $answerText,
        ?array $answerJson,
        $file = null,
        string $status = 'submitted'
    ): ActivityAnswer {
        // Calculate computed fields for table inputs
        if ($activity->input_type === 'table' && is_array($answerJson)) {
            $schema = $activity->answer_schema ?? [];
            $columns = $schema['columns'] ?? [];

            foreach ($answerJson as $index => &$row) {
                foreach ($columns as $column) {
                    if (($column['type'] ?? '') === 'computed') {
                        $formula = $column['formula'] ?? '';
                        $row[$column['name']] = $this->calculateFormula($formula, $row);
                    }
                }
            }
            unset($row);
        }

        $filePath = null;
        if ($file) {
            $filePath = $file->store('activities/'.$activity->id, 'public');
        }

        $existingAnswer = ActivityAnswer::where('activity_id', $activity->id)
            ->where('user_id', $user->id)
            ->first();

        // Prevent updating if already reviewed
        if ($existingAnswer?->status === 'reviewed') {
            return $existingAnswer;
        }

        $data = [
            'activity_id' => $activity->id,
            'user_id' => $user->id,
            'answer_text' => $answerText,
            'answer_json' => $answerJson,
            'status' => $status,
        ];

        if ($filePath) {
            $data['file_path'] = $filePath;
        }

        if ($status === 'submitted' || $status === 'reviewed') {
            $data['submitted_at'] = now();
        } else {
            $data['submitted_at'] = null; // draft
        }

        return ActivityAnswer::updateOrCreate(
            [
                'activity_id' => $activity->id,
                'user_id' => $user->id,
            ],
            $data
        );
    }

    private function calculateFormula(string $formula, array $row): float|int|null
    {
        // For now, support simple formula like: suhu_akhir - suhu_awal
        // Very basic parsing for MVP
        if ($formula === 'suhu_akhir - suhu_awal') {
            $akhir = (float) ($row['suhu_akhir'] ?? 0);
            $awal = (float) ($row['suhu_awal'] ?? 0);

            return $akhir - $awal;
        }

        return null;
    }
}
