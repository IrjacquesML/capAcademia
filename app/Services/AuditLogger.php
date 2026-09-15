<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Support\Str;
use Throwable;

class AuditLogger
{
    public function __construct(private readonly AdminNotifier $admins) {}

    /**
     * @param  array<string, mixed>  $meta
     */
    public function record(?User $subject, AuditAction $action, array $meta = [], ?User $actor = null): void
    {
        if ($subject === null) {
            return;
        }

        try {
            $event = AuditEvent::query()->create([
                'user_id' => $subject->id,
                'actor_id' => $actor?->id ?? auth()->id(),
                'action' => $action,
                'ip_address' => request()->ip(),
                'user_agent' => Str::limit((string) request()->userAgent(), 512, ''),
                'meta' => $meta === [] ? null : $meta,
            ]);

            $this->admins->notify($event);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
