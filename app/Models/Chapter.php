<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'slug',
        'content',
        'position',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function renderedHtml(): string
    {
        return app(HtmlSanitizer::class)->sanitizeForDisplay((string) $this->content);
    }

    /**
     * Relation au pluriel requise par le scoped route binding Laravel ({quiz}).
     * La contrainte unique chapter_id garantit au plus un quiz par chapitre.
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(ChapterProgress::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function progressFor(User $user): ?ChapterProgress
    {
        if ($this->relationLoaded('progress')) {
            return $this->progress->firstWhere('user_id', $user->id)
                ?? $this->progress->first();
        }

        return $this->progress()->where('user_id', $user->id)->first();
    }

    public function hasSubmittedQuizFor(User $user): bool
    {
        $this->loadMissing('quiz');

        if ($this->quiz === null) {
            return false;
        }

        if ($this->quiz->relationLoaded('attempts')) {
            return $this->quiz->attempts->isNotEmpty();
        }

        return $this->quiz->attempts()->where('user_id', $user->id)->exists();
    }
}
