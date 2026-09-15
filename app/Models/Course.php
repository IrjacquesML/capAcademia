<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'option_id',
        'promotion_id',
        'title',
        'slug',
        'description',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Course $course): void {
            $course->assertOptionBelongsToFaculty();
        });
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('position');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Isolation académique : un étudiant ne voit que les cours de SON triplet.
     * Ne jamais lister Course::all() dans un contrôleur étudiant.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdmin()) {
            return $user->faculty_id
                ? $query->where('faculty_id', $user->faculty_id)
                : $query;
        }

        if ($user->isTeacher()) {
            return $query->where('faculty_id', $user->faculty_id);
        }

        return $query
            ->published()
            ->forStudent($user);
    }

    /**
     * Fail-closed : sans faculté/option/promotion, le résultat est vide (jamais « tous les cours »).
     */
    public function scopeForStudent(Builder $query, User $user): Builder
    {
        if (! $user->hasCompleteAcademicContext()) {
            return $query->whereRaw('0 = 1');
        }

        return $query
            ->where('faculty_id', $user->faculty_id)
            ->where('option_id', $user->option_id)
            ->where('promotion_id', $user->promotion_id);
    }

    private function assertOptionBelongsToFaculty(): void
    {
        $optionFacultyId = Option::query()->whereKey($this->option_id)->value('faculty_id');

        if ((int) $optionFacultyId !== (int) $this->faculty_id) {
            throw new InvalidArgumentException(
                'Incohérence académique : l\'option du cours n\'appartient pas à la faculté indiquée.'
            );
        }
    }
}
