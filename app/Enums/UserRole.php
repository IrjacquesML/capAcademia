<?php

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Teacher = 'teacher';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Étudiant',
            self::Teacher => 'Enseignant',
            self::Admin => 'Admin',
            self::SuperAdmin => 'Super administrateur',
        };
    }
}
