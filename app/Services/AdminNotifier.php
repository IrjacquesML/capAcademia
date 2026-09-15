<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\AuditEvent;
use App\Models\User;
use App\Notifications\UserActivityNotification;
use Throwable;

class AdminNotifier
{
    public function notify(AuditEvent $event): void
    {
        $event->loadMissing('user');

        if ($event->user === null || ! $event->user->isStudent() || ! $event->action->notifiesAdmins()) {
            return;
        }

        $exclude = array_values(array_unique(array_filter([
            (int) $event->user_id,
            $event->actor_id !== null ? (int) $event->actor_id : null,
        ])));

        try {
            User::query()
                ->whereIn('role', [UserRole::Admin->value, UserRole::SuperAdmin->value])
                ->when($exclude !== [], fn ($query) => $query->whereNotIn('id', $exclude))
                ->get()
                ->filter(fn (User $admin) => $admin->canManageUser($event->user))
                ->each(fn (User $admin) => $admin->notify(new UserActivityNotification($event)));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
