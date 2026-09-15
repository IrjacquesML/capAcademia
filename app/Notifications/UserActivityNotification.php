<?php

namespace App\Notifications;

use App\Models\AuditEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserActivityNotification extends Notification
{
    use Queueable;

    public function __construct(public AuditEvent $event) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $event = $this->event->loadMissing('user:id,name,role');
        $user = $event->user;

        return [
            'audit_event_id' => $event->id,
            'user_id' => $event->user_id,
            'user_name' => $user?->name,
            'user_role' => $user?->roleLabel(),
            'action' => $event->action->value,
            'action_label' => $event->action->label(),
            'message' => trim(($user?->name ?? 'Utilisateur').' — '.$event->description()),
            'url' => $user ? route('admin.users.audit', $user) : route('admin.notifications.index'),
        ];
    }
}
