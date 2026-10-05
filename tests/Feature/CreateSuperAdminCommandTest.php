<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateSuperAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_command_creates_a_super_admin_with_a_hashed_password(): void
    {
        $this->artisan('app:create-super-admin', [
            '--email' => 'admin@capacademia.net',
            '--name' => 'Super administrateur',
        ])
            ->expectsQuestion('Mot de passe du compte (8 caractères minimum)', 'test-password-123')
            ->expectsQuestion('Confirmez le mot de passe', 'test-password-123')
            ->assertSuccessful();

        $user = User::query()->where('email', 'admin@capacademia.net')->firstOrFail();

        $this->assertSame(UserRole::SuperAdmin, $user->role);
        $this->assertTrue(Hash::check('test-password-123', $user->password));
    }
}
