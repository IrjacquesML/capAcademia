<?php

namespace App\Services\WordImport;

use ZipArchive;

class WordCourseTemplateGenerator
{
    public const FILENAME = 'modele-cours-CapAcademia.docx';

    public function bytes(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'ca-modele-');
        if ($path === false) {
            throw new \RuntimeException('Impossible de générer le modèle Word.');
        }

        @unlink($path);
        $path .= '.docx';

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Impossible de générer le modèle Word.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->packageRels());
        $zip->addFromString('word/_rels/document.xml.rels', $this->documentRels());
        $zip->addFromString('word/styles.xml', $this->styles());
        $zip->addFromString('word/document.xml', $this->document());
        $zip->close();

        $bytes = file_get_contents($path);
        @unlink($path);

        if ($bytes === false || $bytes === '') {
            throw new \RuntimeException('Le modèle Word généré est vide.');
        }

        return $bytes;
    }

    private function contentTypes(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
</Types>
XML;
    }

    private function packageRels(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>
XML;
    }

    private function documentRels(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;
    }

    private function styles(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:style w:type="paragraph" w:default="1" w:styleId="Normal">
    <w:name w:val="Normal"/>
    <w:qFormat/>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading1">
    <w:name w:val="heading 1"/>
    <w:basedOn w:val="Normal"/>
    <w:qFormat/>
    <w:pPr><w:outlineLvl w:val="0"/></w:pPr>
    <w:rPr><w:b/><w:sz w:val="36"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading2">
    <w:name w:val="heading 2"/>
    <w:basedOn w:val="Normal"/>
    <w:qFormat/>
    <w:pPr><w:outlineLvl w:val="1"/></w:pPr>
    <w:rPr><w:b/><w:sz w:val="28"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading3">
    <w:name w:val="heading 3"/>
    <w:basedOn w:val="Normal"/>
    <w:qFormat/>
    <w:pPr><w:outlineLvl w:val="2"/></w:pPr>
    <w:rPr><w:b/><w:sz w:val="24"/></w:rPr>
  </w:style>
</w:styles>
XML;
    }

    private function document(): string
    {
        $body = $this->p('Heading1', 'Titre du cours (à modifier)')
            .$this->p(null, 'Remplacez ce paragraphe par la description du cours. Il se trouve sous le Titre 1, avant le premier Titre 2.')
            .$this->p('Heading2', 'Chapitre 1 — titre du chapitre')
            .$this->pRich(null, [
                ['plain', 'Le mot '],
                ['b', 'algorithme'],
                ['plain', ' désigne une suite '],
                ['i', 'finie'],
                ['plain', ' d’instructions '],
                ['u', 'non ambiguës'],
                ['plain', '.'],
            ])
            .$this->p(null, 'Vous pouvez coller tableaux, listes à puces, couleurs et images : ils seront conservés.')
            .$this->listItem('Premier point de la liste')
            .$this->listItem('Deuxième point de la liste')
            .$this->table([
                ['Notion', 'Définition'],
                ['Algorithme', 'Suite d’instructions'],
                ['Programme', 'Traduction dans un langage'],
            ])
            .$this->p('Heading3', 'Interrogation')
            .$this->p(null, '1. Exemple de question à choix multiples ?')
            .$this->p(null, 'a) Mauvaise réponse')
            .$this->p(null, 'b) Bonne réponse *')
            .$this->p(null, 'c) Autre mauvaise réponse')
            .$this->p(null, '2. Exemple de question ouverte : citez un mot-clé du chapitre.')
            .$this->p(null, 'Réponse: exemple')
            .$this->p('Heading2', 'Chapitre 2 — titre du chapitre')
            .$this->p(null, 'Deuxième chapitre : copiez ce bloc autant de fois que nécessaire.')
            .$this->p('Heading3', 'Interrogation')
            .$this->p(null, '1. Quelle est la bonne réponse ?')
            .$this->p(null, 'a) Réponse correcte (juste)')
            .$this->p(null, 'b) Réponse incorrecte');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body>'.$body.'</w:body></w:document>';
    }

    private function p(?string $style, string $text): string
    {
        $styleXml = $style !== null
            ? '<w:pPr><w:pStyle w:val="'.$style.'"/></w:pPr>'
            : '';

        return '<w:p>'.$styleXml.'<w:r><w:t>'.htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</w:t></w:r></w:p>';
    }

    /**
     * @param  list<array{0: string, 1: string}>  $runs
     */
    private function pRich(?string $style, array $runs): string
    {
        $styleXml = $style !== null
            ? '<w:pPr><w:pStyle w:val="'.$style.'"/></w:pPr>'
            : '';
        $inner = '';

        foreach ($runs as [$kind, $text]) {
            $props = match ($kind) {
                'b' => '<w:rPr><w:b/></w:rPr>',
                'i' => '<w:rPr><w:i/></w:rPr>',
                'u' => '<w:rPr><w:u w:val="single"/></w:rPr>',
                default => '',
            };
            $inner .= '<w:r>'.$props.'<w:t>'.htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</w:t></w:r>';
        }

        return '<w:p>'.$styleXml.$inner.'</w:p>';
    }

    private function listItem(string $text): string
    {
        return '<w:p><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr></w:pPr>'
            .'<w:r><w:t>'.htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</w:t></w:r></w:p>';
    }

    /**
     * @param  list<list<string>>  $rows
     */
    private function table(array $rows): string
    {
        $xml = '<w:tbl>';
        foreach ($rows as $row) {
            $xml .= '<w:tr>';
            foreach ($row as $cell) {
                $xml .= '<w:tc><w:p><w:r><w:t>'.htmlspecialchars($cell, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</w:t></w:r></w:p></w:tc>';
            }
            $xml .= '</w:tr>';
        }

        return $xml.'</w:tbl>';
    }
}
