<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use InvalidArgumentException;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'faculty_id',
        'option_id',
        'promotion_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            $user->assertOptionBelongsToFaculty();
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

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function chapterProgress(): HasMany
    {
        return $this->hasMany(ChapterProgress::class);
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(AuditEvent::class);
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function isTeacher(): bool
    {
        return $this->role === UserRole::Teacher;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isPrivileged(): bool
    {
        return $this->isAdmin() || $this->isSuperAdmin();
    }

    public function homeRoute(): string
    {
        return $this->isPrivileged() ? 'admin.dashboard' : 'dashboard';
    }

    public function roleLabel(): string
    {
        return $this->role->label();
    }

    /**
     * @return list<UserRole>
     */
    public function assignableRoles(): array
    {
        if ($this->isSuperAdmin()) {
            return UserRole::cases();
        }

        if ($this->isAdmin()) {
            return [UserRole::Student, UserRole::Teacher];
        }

        return [];
    }

    public function canManageUser(User $other): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $this->isAdmin() || $other->isSuperAdmin() || $other->isAdmin()) {
            return false;
        }

        if ($this->faculty_id === null) {
            return true;
        }

        return (int) $this->faculty_id === (int) $other->faculty_id;
    }

    public function scopeManageableBy(Builder $query, User $actor): Builder
    {
        if ($actor->isSuperAdmin()) {
            return $query;
        }

        if (! $actor->isAdmin()) {
            return $query->whereRaw('0 = 1');
        }

        $query->whereNotIn('role', [UserRole::SuperAdmin->value, UserRole::Admin->value]);

        if ($actor->faculty_id) {
            $query->where('faculty_id', $actor->faculty_id);
        }

        return $query;
    }

    /**
     * Un étudiant sans triplet académique complet ne doit jamais voir de cours.
     */
    public function hasCompleteAcademicContext(): bool
    {
        return $this->faculty_id !== null
            && $this->option_id !== null
            && $this->promotion_id !== null;
    }

    /**
     * Règle d'or : accès uniquement si Faculté + Option + Promotion correspondent exactement.
     * Les identifiants sont comparés en entier pour éviter les faux positifs de typage SQL.
     */
    public function canAccessCourse(Course $course): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isAdmin()) {
            return $this->faculty_id === null
                || (int) $this->faculty_id === (int) $course->faculty_id;
        }

        if ($this->isTeacher()) {
            return (int) $this->faculty_id === (int) $course->faculty_id;
        }

        if (! $this->isStudent() || ! $this->hasCompleteAcademicContext()) {
            return false;
        }

        return (int) $this->faculty_id === (int) $course->faculty_id
            && (int) $this->option_id === (int) $course->option_id
            && (int) $this->promotion_id === (int) $course->promotion_id;
    }

    private function assertOptionBelongsToFaculty(): void
    {
        if ($this->option_id === null || $this->faculty_id === null) {
            return;
        }

        $optionFacultyId = Option::query()->whereKey($this->option_id)->value('faculty_id');

        if ((int) $optionFacultyId !== (int) $this->faculty_id) {
            throw new InvalidArgumentException(
                'Incohérence académique : l\'option n\'appartient pas à la faculté de l\'utilisateur.'
            );
        }
    }
}
