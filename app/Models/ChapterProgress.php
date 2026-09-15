<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterProgress extends Model
{
    use HasFactory;

    protected $table = 'chapter_progress';

    protected $fillable = [
        'user_id',
        'chapter_id',
        'read_at',
        'quiz_passed_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'quiz_passed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * Un chapitre n'est validé qu'après lecture ET interrogation réussie (si un quiz existe).
     */
    public function syncCompletion(): void
    {
        $this->loadMissing('chapter.quiz');

        $quizRequired = $this->chapter?->quiz !== null;
        $quizOk = ! $quizRequired || $this->quiz_passed_at !== null;
        $readOk = $this->read_at !== null;

        if ($readOk && $quizOk) {
            $this->completed_at ??= now();
        }

        $this->save();
    }
}
