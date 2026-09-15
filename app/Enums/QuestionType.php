<?php

namespace App\Enums;

enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case Text = 'text';

    public function label(): string
    {
        return match ($this) {
            self::MultipleChoice => 'Choix multiples',
            self::Text => 'Réponse libre',
        };
    }
}
