<?php

namespace App\Services\WordImport;

class ParsedCourseDocument
{
    /**
     * @param  list<ParsedChapter>  $chapters
     * @param  array<string, array{name: string, bytes: string, mime: string}>  $media
     */
    public function __construct(
        public string $title,
        public ?string $description,
        public array $chapters,
        public array $media = [],
    ) {}
}
