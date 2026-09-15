<?php

namespace App\Services\WordImport;

use App\Enums\QuestionType;

class ParsedQuestion
{
    /**
     * @param  list<array{label: string, is_correct: bool}>  $choices
     * @param  list<string>  $accepted
     */
    public function __construct(
        public string $prompt,
        public QuestionType $type,
        public array $choices = [],
        public array $accepted = [],
    ) {}
}
