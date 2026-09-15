<?php

namespace Tests\Unit;

use App\Enums\QuestionType;
use App\Services\WordImport\WordCourseDocumentParser;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WordCourseDocumentParserTest extends TestCase
{
    #[Test]
    public function it_splits_a_word_document_into_course_chapters_and_quizzes(): void
    {
        $xml = $this->wordXml([
            ['Heading1', 'Algorithmique 1'],
            [null, 'Cours d’introduction à l’algorithmique.'],
            ['Heading2', 'Les algorithmes'],
            [null, 'Un algorithme est une suite d’instructions.'],
            ['Heading3', 'Interrogation du chapitre'],
            [null, '1. Un algorithme doit-il se terminer ?'],
            [null, 'a) Oui, sinon ce n’est pas un algorithme *'],
            [null, 'b) Non, une boucle infinie suffit'],
            [null, '2. Citez un langage vu en cours.'],
            [null, 'Réponse: Python'],
            ['Heading2', 'La complexité'],
            [null, 'On mesure le temps avec la notation grand O.'],
            [null, '1. Quelle notation désigne le pire des cas ?'],
            [null, 'a) Big O (juste)'],
            [null, 'b) Un simple tableau'],
        ]);

        $parsed = (new WordCourseDocumentParser)->parseXml($xml);

        $this->assertSame('Algorithmique 1', $parsed->title);
        $this->assertSame('Cours d’introduction à l’algorithmique.', $parsed->description);
        $this->assertCount(2, $parsed->chapters);

        $first = $parsed->chapters[0];
        $this->assertSame('Les algorithmes', $first->title);
        $this->assertStringContainsString('suite d’instructions', $first->content);
        $this->assertStringContainsString('<p style="text-align: justify">', $first->content);
        $this->assertCount(2, $first->questions);
        $this->assertSame(QuestionType::MultipleChoice, $first->questions[0]->type);
        $this->assertTrue($first->questions[0]->choices[0]['is_correct']);
        $this->assertFalse($first->questions[0]->choices[1]['is_correct']);
        $this->assertSame(QuestionType::Text, $first->questions[1]->type);
        $this->assertSame(['Python'], $first->questions[1]->accepted);

        $second = $parsed->chapters[1];
        $this->assertSame('La complexité', $second->title);
        $this->assertCount(1, $second->questions);
        $this->assertTrue($second->questions[0]->choices[0]['is_correct']);
    }

    #[Test]
    public function it_keeps_word_formatting_lists_and_tables_in_chapter_html(): void
    {
        $ns = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="'.$ns.'"><w:body>'
            .'<w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr><w:r><w:t>Cours formaté</w:t></w:r></w:p>'
            .'<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>Chapitre riche</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Un </w:t></w:r><w:r><w:rPr><w:b/></w:rPr><w:t>algorithme</w:t></w:r><w:r><w:t> est </w:t></w:r><w:r><w:rPr><w:i/></w:rPr><w:t>précis</w:t></w:r><w:r><w:rPr><w:u w:val="single"/></w:rPr><w:t>.</w:t></w:r></w:p>'
            .'<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:t>Centré</w:t></w:r></w:p>'
            .'<w:p><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr></w:pPr><w:r><w:t>Premier point</w:t></w:r></w:p>'
            .'<w:p><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr></w:pPr><w:r><w:t>Deuxième point</w:t></w:r></w:p>'
            .'<w:tbl><w:tr><w:tc><w:p><w:r><w:t>Cellule A</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Cellule B</w:t></w:r></w:p></w:tc></w:tr></w:tbl>'
            .'</w:body></w:document>';

        $parsed = (new WordCourseDocumentParser)->parseXml($xml);
        $html = $parsed->chapters[0]->content;

        $this->assertStringContainsString('<strong>algorithme</strong>', $html);
        $this->assertStringContainsString('<em>précis</em>', $html);
        $this->assertStringContainsString('<u>.</u>', $html);
        $this->assertStringContainsString('text-align: justify', $html);
        $this->assertStringContainsString('<ul>', $html);
        $this->assertStringContainsString('<li>Premier point</li>', $html);
        $this->assertStringContainsString('<table>', $html);
        $this->assertStringContainsString('Cellule A', $html);
    }

    /**
     * @param  list<array{0: ?string, 1: string}>  $paragraphs
     */
    private function wordXml(array $paragraphs): string
    {
        $body = '';

        foreach ($paragraphs as [$style, $text]) {
            $styleXml = $style
                ? '<w:pPr><w:pStyle w:val="'.$style.'"/></w:pPr>'
                : '';
            $body .= '<w:p>'.$styleXml.'<w:r><w:t>'.htmlspecialchars($text, ENT_XML1).'</w:t></w:r></w:p>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body>'.$body.'</w:body></w:document>';
    }
}
