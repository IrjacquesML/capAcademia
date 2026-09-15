<?php

namespace App\Models;

use App\Enums\AuditAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'actor_id',
        'action',
        'ip_address',
        'user_agent',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'action' => AuditAction::class,
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function description(): string
    {
        $meta = $this->meta ?? [];

        return match ($this->action) {
            AuditAction::Login => 'Connexion réussie à CapAcademia.',
            AuditAction::LoginFailed => 'Tentative de connexion refusée.',
            AuditAction::Logout => 'Déconnexion.',
            AuditAction::DashboardViewed => 'A ouvert l’accueil.',
            AuditAction::CourseListViewed => 'A consulté la liste des cours.',
            AuditAction::CourseViewed => sprintf(
                'A ouvert le cours « %s ».',
                $meta['course_title'] ?? 'cours',
            ),
            AuditAction::ChapterViewed => sprintf(
                'A ouvert le chapitre : %s — %s.',
                $meta['course_title'] ?? 'cours',
                $meta['chapter_title'] ?? 'chapitre',
            ),
            AuditAction::ChapterRead => sprintf(
                'Chapitre lu : %s — %s.',
                $meta['course_title'] ?? 'cours',
                $meta['chapter_title'] ?? 'chapitre',
            ),
            AuditAction::ProgressViewed => 'A consulté sa progression.',
            AuditAction::ProfileViewed => 'A consulté son profil.',
            AuditAction::QuizResultViewed => sprintf(
                'A consulté le corrigé « %s ».',
                $meta['quiz_title'] ?? 'interrogation',
            ),
            AuditAction::QuizSubmitted => sprintf(
                'Interrogation « %s » : %s %% (%s).',
                $meta['quiz_title'] ?? 'interrogation',
                $meta['percentage'] ?? '?',
                ! empty($meta['passed']) ? 'réussie' : 'échouée',
            ),
            AuditAction::UserCreated => sprintf(
                'Compte créé (%s).',
                $meta['role'] ?? 'rôle inconnu',
            ),
            AuditAction::UserUpdated => sprintf(
                'Compte modifié%s.',
                isset($meta['fields']) && is_array($meta['fields']) && $meta['fields'] !== []
                    ? ' : '.implode(', ', $meta['fields'])
                    : '',
            ),
            AuditAction::UserDeleted => sprintf(
                'Compte supprimé : %s (%s).',
                $meta['deleted_name'] ?? 'utilisateur',
                $meta['deleted_email'] ?? '—',
            ),
        };
    }
}
