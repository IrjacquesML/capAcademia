<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;

class CreateSuperAdminCommand extends Command
{
    protected $signature = 'app:create-super-admin {--email=} {--name=}';

    protected $description = 'Créer ou promouvoir un compte super administrateur';

    public function handle(): int
    {
        $email = trim((string) ($this->option('email') ?: $this->ask('E-mail du compte')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Adresse e-mail invalide.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user && ! $this->confirm("Le compte {$email} existe. Le promouvoir comme super administrateur et remplacer son mot de passe ?", false)) {
            $this->warn('Aucune modification effectuée.');

            return self::FAILURE;
        }

        $name = trim((string) ($this->option('name') ?: $this->ask('Nom du compte', $user?->name ?? 'Super administrateur')));
        $password = $this->secret('Mot de passe du compte (8 caractères minimum)');
        $confirmation = $this->secret('Confirmez le mot de passe');

        if (strlen((string) $password) < 8 || $password !== $confirmation) {
            $this->error('Le mot de passe doit contenir au moins 8 caractères et les deux saisies doivent correspondre.');

            return self::FAILURE;
        }

        $user ??= new User;
        $user->fill([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => UserRole::SuperAdmin,
            'faculty_id' => null,
            'option_id' => null,
            'promotion_id' => null,
        ])->save();

        $this->info("Compte super administrateur prêt : {$email}");

        return self::SUCCESS;
    }
}
