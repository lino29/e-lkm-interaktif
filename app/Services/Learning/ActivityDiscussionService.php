<?php

namespace App\Services\Learning;

use App\Models\ActivityAnswer;
use App\Models\Discussion;

class ActivityDiscussionService
{
    public function sync(ActivityAnswer $answer): void
    {
        if ($answer->activity->phase !== 'forum_diskusi' && $answer->activity->input_type !== 'discussion') {
            return;
        }

        // We use answer_text as the body of the discussion.
        // We sync by looking for a reflection discussion by this user for this learning unit.

        $discussion = Discussion::where('learning_unit_id', $answer->activity->learning_unit_id)
            ->where('user_id', $answer->user_id)
            ->where('type', 'reflection')
            ->first();

        if ($discussion) {
            $discussion->update([
                'title' => $answer->activity->title,
                'body' => $answer->answer_text ?: 'Mengirim refleksi diskusi.',
            ]);
        } else {
            Discussion::create([
                'learning_unit_id' => $answer->activity->learning_unit_id,
                'user_id' => $answer->user_id,
                'title' => $answer->activity->title,
                'body' => $answer->answer_text ?: 'Mengirim refleksi diskusi.',
                'type' => 'reflection',
            ]);
        }
    }
}
