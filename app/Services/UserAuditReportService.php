<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditEvent;
use App\Models\ChapterProgress;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserAuditReportService
{
    public function __construct(private readonly StudentCatalogService $catalog) {}

    /**
     * @return array{
     *     user: User,
     *     stats: array<string, mixed>,
     *     courses: Collection,
     *     attempts: Collection<int, QuizAttempt>,
     *     events: LengthAwarePaginator
     * }
     */
    public function build(User $user): array
    {
        $user->loadMissing(['faculty:id,name', 'option:id,name', 'promotion:id,name']);

        $attempts = QuizAttempt::query()
            ->where('user_id', $user->id)
            ->with(['quiz.chapter.course:id,title'])
            ->latest('submitted_at')
            ->get();

        $progress = ChapterProgress::query()
            ->where('user_id', $user->id)
            ->get();

        $lastLogin = AuditEvent::query()
            ->where('user_id', $user->id)
            ->where('action', AuditAction::Login)
            ->latest()
            ->first();

        $stats = [
            'logins' => AuditEvent::query()
                ->where('user_id', $user->id)
                ->where('action', AuditAction::Login)
                ->count(),
            'failed_logins' => AuditEvent::query()
                ->where('user_id', $user->id)
                ->where('action', AuditAction::LoginFailed)
                ->count(),
            'last_login_at' => $lastLogin?->created_at,
            'last_login_ip' => $lastLogin?->ip_address,
            'chapters_read' => $progress->whereNotNull('read_at')->count(),
            'chapters_completed' => $progress->whereNotNull('completed_at')->count(),
            'quiz_attempts' => $attempts->count(),
            'quiz_passed' => $attempts->where('passed', true)->count(),
            'average_score' => $attempts->isEmpty()
                ? null
                : round((float) $attempts->avg('percentage'), 1),
        ];

        $events = AuditEvent::query()
            ->where('user_id', $user->id)
            ->with('actor:id,name')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return [
            'user' => $user,
            'stats' => $stats,
            'courses' => $this->catalog->coursesWithProgress($user),
            'attempts' => $attempts,
            'events' => $events,
        ];
    }
}
