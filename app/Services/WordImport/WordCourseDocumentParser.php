<?php

namespace App\Services\WordImport;

use App\Enums\QuestionType;
use DOMDocument;
use DOMElement;
use DOMXPath;
use InvalidArgumentException;
use ZipArchive;

class WordCourseDocumentParser
{
    private const WORD_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    public function parseFile(string $path, ?string $fallbackTitle = null): ParsedCourseDocument
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new InvalidArgumentException('Impossible d’ouvrir le fichier Word. Utilisez un .docx.');
        }

        $documentXml = $zip->getFromName('word/document.xml');
        $stylesXml = $zip->getFromName('word/styles.xml') ?: null;
        $numberingXml = $zip->getFromName('word/numbering.xml') ?: null;
        $relsXml = $zip->getFromName('word/_rels/document.xml.rels') ?: null;
        $rels = $this->relationshipMap(is_string($relsXml) ? $relsXml : null);
        $media = $this->extractMedia($zip, $rels);
        $zip->close();

        if (! is_string($documentXml) || $documentXml === '') {
            throw new InvalidArgumentException('Le document Word est vide ou illisible.');
        }

        $parsed = $this->parseXml(
            $documentXml,
            is_string($stylesXml) ? $stylesXml : null,
            $fallbackTitle,
            is_string($numberingXml) ? $numberingXml : null,
            $rels,
        );
        $parsed->media = $media;

        return $parsed;
    }

    /**
     * @param  array<string, array{type: string, target: string}>  $rels
     */
    public function parseXml(
        string $documentXml,
        ?string $stylesXml = null,
        ?string $fallbackTitle = null,
        ?string $numberingXml = null,
        array $rels = [],
    ): ParsedCourseDocument {
        $headingMap = $this->headingMapFromStyles($stylesXml);
        $converter = new WordHtmlConverter(
            $headingMap,
            $this->numberingMap($numberingXml),
            $rels,
        );
        $blocks = $this->extractBlocks($documentXml, $converter);

        if ($blocks === []) {
            throw new InvalidArgumentException('Aucun texte n’a été trouvé dans le document.');
        }

        return $this->buildCourse($blocks, $fallbackTitle);
    }

    /**
     * @return array<string, int>
     */
    private function headingMapFromStyles(?string $stylesXml): array
    {
        $map = [];

        if ($stylesXml === null || $stylesXml === '') {
            return $map;
        }

        $dom = $this->loadDom($stylesXml);
        $xpath = $this->xpath($dom);

        foreach ($xpath->query('//w:style') as $style) {
            if (! $style instanceof DOMElement) {
                continue;
            }

            $styleId = $style->getAttribute('w:styleId');
            if ($styleId === '') {
                continue;
            }

            $level = null;
            $outline = $xpath->query('.//w:outlineLvl', $style)->item(0);
            if ($outline instanceof DOMElement && $outline->hasAttribute('w:val')) {
                $level = ((int) $outline->getAttribute('w:val')) + 1;
            } elseif (preg_match('/(?:heading|titre)\s*([1-6])/i', $styleId, $match)) {
                $level = (int) $match[1];
            }

            if ($level !== null && $level >= 1 && $level <= 6) {
                $map[$styleId] = $level;
            }
        }

        return $map;
    }

    /**
     * @return list<array{
     *     kind: string,
     *     level: int,
     *     text: string,
     *     html: string,
     *     innerHtml: string,
     *     bold: bool,
     *     list: ?array{numId: int, ilvl: int, ordered: bool}
     * }>
     */
    private function extractBlocks(string $documentXml, WordHtmlConverter $converter): array
    {
        $dom = $this->loadDom($documentXml);
        $xpath = $this->xpath($dom);
        $blocks = [];

        foreach ($this->bodyNodes($xpath) as $node) {
            $block = $converter->convertBlock($xpath, $node);
            if ($block['text'] === '' && $block['html'] === '' && $block['innerHtml'] === '') {
                continue;
            }
            $blocks[] = $block;
        }

        return $blocks;
    }

    /**
     * @return list<DOMElement>
     */
    private function bodyNodes(DOMXPath $xpath): array
    {
        $nodes = [];
        foreach ($xpath->query('//w:body/*') as $child) {
            if ($child instanceof DOMElement) {
                $this->collectBodyNodes($child, $nodes);
            }
        }

        return $nodes;
    }

    /**
     * @param  list<DOMElement>  $nodes
     */
    private function collectBodyNodes(DOMElement $node, array &$nodes): void
    {
        $name = $node->localName;
        if ($name === 'p' || $name === 'tbl') {
            $nodes[] = $node;

            return;
        }

        if ($name !== 'sdt') {
            return;
        }

        foreach ($node->getElementsByTagNameNS(self::WORD_NS, 'sdtContent') as $content) {
            foreach ($content->childNodes as $child) {
                if ($child instanceof DOMElement) {
                    $this->collectBodyNodes($child, $nodes);
                }
            }
        }
    }

    /**
     * @param  list<array{
     *     kind: string,
     *     level: int,
     *     text: string,
     *     html: string,
     *     innerHtml: string,
     *     bold: bool,
     *     list: ?array{numId: int, ilvl: int, ordered: bool}
     * }>  $blocks
     */
    private function buildCourse(array $blocks, ?string $fallbackTitle): ParsedCourseDocument
    {
        $title = $fallbackTitle ?: 'Cours importé';
        $descriptionParts = [];
        $chapters = [];
        $current = null;
        $inQuiz = false;
        $preface = true;
        $titleTaken = false;
        $openLists = [];

        $closeListsUntil = function (?int $untilIlvl) use (&$openLists, &$current): void {
            if ($current === null) {
                $openLists = [];

                return;
            }

            while ($openLists !== []) {
                $last = $openLists[array_key_last($openLists)];
                if ($untilIlvl !== null && $last['ilvl'] < $untilIlvl) {
                    break;
                }
                $current['content'] .= '</li></'.($last['ordered'] ? 'ol' : 'ul').'>';
                array_pop($openLists);
            }
        };

        $flushList = function () use ($closeListsUntil): void {
            $closeListsUntil(null);
        };

        $flush = function () use (&$chapters, &$current, $flushList): void {
            $flushList();
            if ($current === null) {
                return;
            }

            $current['content'] = trim($current['content']);
            $current['questions'] = $this->parseQuestions($current['quizLines']);
            unset($current['quizLines']);
            $chapters[] = new ParsedChapter($current['title'], $current['content'], $current['questions']);
            $current = null;
        };

        foreach ($blocks as $block) {
            $text = $block['text'];
            $level = $block['level'];

            if ($level === 1) {
                if (! $titleTaken && $current === null && $chapters === []) {
                    $title = $text;
                    $titleTaken = true;

                    continue;
                }
                $level = 2;
            }

            if ($level === 2) {
                $flush();
                $preface = false;
                $current = [
                    'title' => $text,
                    'content' => '',
                    'quizLines' => [],
                ];
                $inQuiz = false;

                continue;
            }

            if ($level === 3 && preg_match('/interro|quiz|qcm|questionnaire|évaluation|evaluation/i', $text)) {
                if ($current === null) {
                    $current = ['title' => 'Chapitre 1', 'content' => '', 'quizLines' => []];
                }
                $flushList();
                $inQuiz = true;

                continue;
            }

            if ($preface && $current === null) {
                $descriptionParts[] = $text;

                continue;
            }

            if ($current === null) {
                $current = ['title' => 'Chapitre 1', 'content' => '', 'quizLines' => []];
                $preface = false;
            }

            if ($inQuiz || $this->looksLikeQuestionStart($text)) {
                $flushList();
                $inQuiz = true;
                $line = $block['bold'] && ! str_contains($text, '*') ? $text.' *' : $text;
                $current['quizLines'][] = $line;

                continue;
            }

            if ($block['kind'] === 'table') {
                $flushList();
                $current['content'] .= $block['html'];

                continue;
            }

            if ($block['list'] !== null) {
                $ilvl = $block['list']['ilvl'];
                $ordered = $block['list']['ordered'];
                $numId = $block['list']['numId'];
                $closeListsUntil($ilvl + 1);

                $top = $openLists === [] ? null : $openLists[array_key_last($openLists)];
                if ($top !== null && $top['ilvl'] === $ilvl && ($top['numId'] !== $numId || $top['ordered'] !== $ordered)) {
                    $closeListsUntil($ilvl);
                    $top = $openLists === [] ? null : $openLists[array_key_last($openLists)];
                }

                if ($top === null || $top['ilvl'] < $ilvl) {
                    $tag = $ordered ? 'ol' : 'ul';
                    $current['content'] .= '<'.$tag.'><li>'.$block['innerHtml'];
                    $openLists[] = ['ilvl' => $ilvl, 'ordered' => $ordered, 'numId' => $numId];
                } else {
                    $current['content'] .= '</li><li>'.$block['innerHtml'];
                }

                continue;
            }

            $flushList();
            $current['content'] .= $block['html'];
        }

        $flush();

        if ($chapters === []) {
            throw new InvalidArgumentException('Aucun chapitre (Titre 2) n’a été détecté dans le document.');
        }

        $description = trim(implode("\n\n", $descriptionParts));

        return new ParsedCourseDocument(
            title: $title,
            description: $description !== '' ? $description : null,
            chapters: $chapters,
        );
    }

    /**
     * @param  list<string>  $lines
     * @return list<ParsedQuestion>
     */
    private function parseQuestions(array $lines): array
    {
        $questions = [];
        $current = null;

        $push = function () use (&$questions, &$current): void {
            if ($current === null || trim($current['prompt']) === '') {
                $current = null;

                return;
            }

            $isText = $current['accepted'] !== [];
            $questions[] = new ParsedQuestion(
                prompt: trim($current['prompt']),
                type: $isText ? QuestionType::Text : QuestionType::MultipleChoice,
                choices: $isText ? [] : $current['choices'],
                accepted: $current['accepted'],
            );
            $current = null;
        };

        foreach ($lines as $line) {
            if (preg_match('/^(?:Q(?:uestion)?\s*)?(?:\d+[\.\)]\s+|Q[\.:]\s+)(.+)$/iu', $line, $match)) {
                $push();
                $current = ['prompt' => trim($match[1]), 'choices' => [], 'accepted' => []];

                continue;
            }

            if ($current === null) {
                continue;
            }

            if (preg_match('/^R(?:éponse|eponse)?\s*[:\-]\s*(.+)$/iu', $line, $match)) {
                $current['accepted'][] = trim($match[1]);
                $current['type'] = 'text';

                continue;
            }

            if (preg_match('/^[\(\[]?([a-dA-D]|[a-d])[\)\.\]]\s*(.+)$/u', $line, $match)
                || preg_match('/^[-•]\s+(.+)$/u', $line, $match)
                || preg_match('/^A[\.:]\s*(.+)$/iu', $line, $match)) {
                $raw = $match[2] ?? $match[1];
                [$label, $correct] = $this->normalizeChoice((string) $raw);
                $current['choices'][] = ['label' => $label, 'is_correct' => $correct];
            }
        }

        $push();

        return array_values(array_filter(
            $questions,
            function (ParsedQuestion $question): bool {
                if ($question->type === QuestionType::Text) {
                    return $question->accepted !== [];
                }

                $hasCorrect = collect($question->choices)->contains(fn (array $choice): bool => $choice['is_correct']);

                return count($question->choices) >= 2 && $hasCorrect;
            },
        ));
    }

    /**
     * @return array{0: string, 1: bool}
     */
    private function normalizeChoice(string $raw): array
    {
        $correct = false;
        $label = trim($raw);

        if (preg_match('/\((?:juste|correcte?|vrai)\)\s*$/iu', $label)) {
            $correct = true;
            $label = trim(preg_replace('/\((?:juste|correcte?|vrai)\)\s*$/iu', '', $label) ?? $label);
        }

        if (str_starts_with($label, '*')) {
            $correct = true;
            $label = ltrim($label, "* \t");
        }

        if (str_ends_with($label, '*')) {
            $correct = true;
            $label = rtrim($label, "* \t");
        }

        if (preg_match('/^\[x\]\s*/i', $label)) {
            $correct = true;
            $label = trim(preg_replace('/^\[x\]\s*/i', '', $label) ?? $label);
        }

        return [$label, $correct];
    }

    private function looksLikeQuestionStart(string $text): bool
    {
        return (bool) preg_match('/^(?:Q(?:uestion)?\s*)?(?:\d+[\.\)]\s+|Q[\.:]\s+)/iu', $text);
    }

    private function looksLikeAnswer(string $text): bool
    {
        return (bool) preg_match('/^(?:[\(\[]?[a-dA-D][\)\.\]]\s+|[-•]\s+|A[\.:]\s+|R(?:éponse|eponse)?\s*[:\-])/u', $text);
    }

    private function headingLevelFromStyleId(string $styleId): int
    {
        if (preg_match('/(?:heading|titre)\s*([1-6])/i', $styleId, $match)) {
            return (int) $match[1];
        }

        return 0;
    }

    /**
     * @return array<int, array<int, bool>>
     */
    private function numberingMap(?string $xml): array
    {
        $map = [];
        if ($xml === null || $xml === '') {
            return $map;
        }

        $dom = $this->loadDom($xml);
        $xpath = $this->xpath($dom);
        $abstract = [];

        foreach ($xpath->query('//w:abstractNum') as $abs) {
            if (! $abs instanceof DOMElement) {
                continue;
            }
            $id = (int) $abs->getAttribute('w:abstractNumId');
            foreach ($xpath->query('./w:lvl', $abs) as $lvl) {
                if (! $lvl instanceof DOMElement) {
                    continue;
                }
                $ilvl = (int) $lvl->getAttribute('w:ilvl');
                $fmt = $xpath->query('./w:numFmt', $lvl)->item(0);
                $val = $fmt instanceof DOMElement ? strtolower($fmt->getAttribute('w:val')) : 'bullet';
                $abstract[$id][$ilvl] = ! in_array($val, ['bullet', 'none'], true);
            }
        }

        foreach ($xpath->query('//w:num') as $num) {
            if (! $num instanceof DOMElement) {
                continue;
            }
            $numId = (int) $num->getAttribute('w:numId');
            $absNode = $xpath->query('./w:abstractNumId', $num)->item(0);
            $absId = $absNode instanceof DOMElement ? (int) $absNode->getAttribute('w:val') : null;
            if ($absId !== null && isset($abstract[$absId])) {
                $map[$numId] = $abstract[$absId];
            }
        }

        return $map;
    }

    /**
     * @return array<string, array{type: string, target: string}>
     */
    private function relationshipMap(?string $xml): array
    {
        $map = [];
        if ($xml === null || $xml === '') {
            return $map;
        }

        $dom = $this->loadDom($xml);
        foreach ($dom->getElementsByTagName('Relationship') as $rel) {
            if (! $rel instanceof DOMElement) {
                continue;
            }
            $id = $rel->getAttribute('Id');
            $type = $rel->getAttribute('Type');
            $kind = str_contains($type, '/image') ? 'image' : (str_contains($type, '/hyperlink') ? 'hyperlink' : 'other');
            $map[$id] = [
                'type' => $kind,
                'target' => $rel->getAttribute('Target'),
            ];
        }

        return $map;
    }

    /**
     * @param  array<string, array{type: string, target: string}>  $rels
     * @return array<string, array{name: string, bytes: string, mime: string}>
     */
    private function extractMedia(ZipArchive $zip, array $rels): array
    {
        $media = [];

        foreach ($rels as $id => $rel) {
            if ($rel['type'] !== 'image') {
                continue;
            }

            $target = str_replace('\\', '/', $rel['target']);
            $bytes = false;
            foreach (['word/'.ltrim($target, '/'), ltrim($target, '/'), 'word/media/'.basename($target)] as $path) {
                $found = $zip->getFromName($path);
                if (is_string($found) && $found !== '') {
                    $bytes = $found;
                    break;
                }
            }

            if (! is_string($bytes) || $bytes === '') {
                continue;
            }

            $name = basename($target);
            $media[$id] = [
                'name' => $name,
                'bytes' => $bytes,
                'mime' => $this->mimeFromName($name),
            ];
        }

        return $media;
    }

    private function mimeFromName(string $name): string
    {
        return match (strtolower(pathinfo($name, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'bmp' => 'image/bmp',
            default => 'application/octet-stream',
        };
    }

    private function loadDom(string $xml): DOMDocument
    {
        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = false;
        @$dom->loadXML($xml);

        return $dom;
    }

    private function xpath(DOMDocument $dom): DOMXPath
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', self::WORD_NS);

        return $xpath;
    }
}
