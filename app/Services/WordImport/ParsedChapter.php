<?php

namespace App\Services\WordImport;

class ParsedChapter
{
    /**
     * @param  list<ParsedQuestion>  $questions
     */
    public function __construct(
        public string $title,
        public string $content,
        public array $questions = [],
    ) {}
}
