<?php

namespace Database\Factories;

use App\Enums\AuditAction;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditEvent>
 */
class AuditEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'actor_id' => null,
            'action' => AuditAction::Login,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'meta' => [],
        ];
    }
}
