<?php

namespace App\Services\WordImport;

use DOMElement;
use DOMXPath;

class WordHtmlConverter
{
    private const WORD_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    private const RELS_NS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    /**
     * @param  array<string, int>  $headingMap
     * @param  array<int, array<int, bool>>  $numbering  numId => [ilvl => isOrdered]
     * @param  array<string, array{type: string, target: string}>  $rels
     */
    public function __construct(
        private readonly array $headingMap,
        private readonly array $numbering,
        private readonly array $rels,
    ) {}

    /**
     * @return array{
     *     kind: 'paragraph'|'table',
     *     level: int,
     *     text: string,
     *     html: string,
     *     innerHtml: string,
     *     bold: bool,
     *     list: ?array{numId: int, ilvl: int, ordered: bool}
     * }
     */
    public function convertBlock(DOMXPath $xpath, DOMElement $node): array
    {
        if ($node->localName === 'tbl') {
            $html = $this->convertTable($xpath, $node);

            return [
                'kind' => 'table',
                'level' => 0,
                'text' => trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
                'html' => $html,
                'innerHtml' => $html,
                'bold' => false,
                'list' => null,
            ];
        }

        return $this->convertParagraph($xpath, $node);
    }

    /**
     * @return array{
     *     kind: 'paragraph',
     *     level: int,
     *     text: string,
     *     html: string,
     *     innerHtml: string,
     *     bold: bool,
     *     list: ?array{numId: int, ilvl: int, ordered: bool}
     * }
     */
    private function convertParagraph(DOMXPath $xpath, DOMElement $paragraph): array
    {
        $styleId = '';
        $styleNode = $xpath->query('./w:pPr/w:pStyle', $paragraph)->item(0);
        if ($styleNode instanceof DOMElement) {
            $styleId = $styleNode->getAttribute('w:val');
        }

        $level = $this->headingMap[$styleId] ?? $this->headingLevelFromStyleId($styleId);
        $inner = $this->convertInline($xpath, $paragraph);
        $text = trim(html_entity_decode(strip_tags(str_replace('<br>', ' ', $inner)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);

        $styles = ['text-align: justify'];

        $indent = $xpath->query('./w:pPr/w:ind', $paragraph)->item(0);
        if ($indent instanceof DOMElement) {
            $left = $indent->getAttribute('w:left');
            if ($left !== '' && is_numeric($left) && (int) $left > 0) {
                $styles[] = 'margin-left: '.round(((int) $left) / 20).'pt';
            }
        }

        $shd = $xpath->query('./w:pPr/w:shd', $paragraph)->item(0);
        if ($shd instanceof DOMElement) {
            $fill = $this->hexColor($shd->getAttribute('w:fill'));
            if ($fill !== null) {
                $styles[] = 'background-color: '.$fill;
            }
        }

        $list = $this->listMeta($xpath, $paragraph);
        $bold = $xpath->query('.//w:b[not(@w:val="0") and not(@w:val="false")]', $paragraph)->length > 0;

        $tag = match (true) {
            $level >= 3 && $level <= 6 => 'h'.$level,
            default => 'p',
        };

        $attr = $styles === [] ? '' : ' style="'.e(implode('; ', $styles)).'"';
        $html = $inner === '' && $tag === 'p'
            ? ''
            : '<'.$tag.$attr.'>'.($inner !== '' ? $inner : '&nbsp;').'</'.$tag.'>';

        return [
            'kind' => 'paragraph',
            'level' => $level,
            'text' => $text,
            'html' => $html,
            'innerHtml' => $inner,
            'bold' => $bold,
            'list' => $list,
        ];
    }

    /**
     * @return ?array{numId: int, ilvl: int, ordered: bool}
     */
    private function listMeta(DOMXPath $xpath, DOMElement $paragraph): ?array
    {
        $numIdNode = $xpath->query('./w:pPr/w:numPr/w:numId', $paragraph)->item(0);
        if (! $numIdNode instanceof DOMElement) {
            return null;
        }

        $numId = (int) $numIdNode->getAttribute('w:val');
        $ilvlNode = $xpath->query('./w:pPr/w:numPr/w:ilvl', $paragraph)->item(0);
        $ilvl = $ilvlNode instanceof DOMElement ? (int) $ilvlNode->getAttribute('w:val') : 0;
        $ordered = $this->numbering[$numId][$ilvl] ?? false;

        return ['numId' => $numId, 'ilvl' => $ilvl, 'ordered' => $ordered];
    }

    private function convertTable(DOMXPath $xpath, DOMElement $table): string
    {
        $rows = '';
        $rowIndex = 0;
        foreach ($xpath->query('./w:tr', $table) as $row) {
            if (! $row instanceof DOMElement) {
                continue;
            }
            $cells = '';
            $cellTag = $rowIndex === 0 ? 'th' : 'td';
            foreach ($xpath->query('./w:tc', $row) as $cell) {
                if (! $cell instanceof DOMElement) {
                    continue;
                }
                $cells .= $this->convertTableCell($xpath, $cell, $cellTag);
            }
            $rows .= '<tr>'.$cells.'</tr>';
            $rowIndex++;
        }

        return $rows === '' ? '' : '<table>'.$rows.'</table>';
    }

    private function convertTableCell(DOMXPath $xpath, DOMElement $cell, string $tag): string
    {
        $colspan = 1;
        $span = $xpath->query('./w:tcPr/w:gridSpan', $cell)->item(0);
        if ($span instanceof DOMElement && is_numeric($span->getAttribute('w:val'))) {
            $colspan = max(1, (int) $span->getAttribute('w:val'));
        }

        $styles = [];
        $shd = $xpath->query('./w:tcPr/w:shd', $cell)->item(0);
        if ($shd instanceof DOMElement) {
            $fill = $this->hexColor($shd->getAttribute('w:fill'));
            if ($fill !== null) {
                $styles[] = 'background-color: '.$fill;
            }
        }
        $vAlign = $xpath->query('./w:tcPr/w:vAlign', $cell)->item(0);
        if ($vAlign instanceof DOMElement) {
            $val = $vAlign->getAttribute('w:val');
            $map = ['center' => 'middle', 'bottom' => 'bottom', 'top' => 'top'];
            if (isset($map[$val])) {
                $styles[] = 'vertical-align: '.$map[$val];
            }
        }

        $inner = '';
        foreach ($xpath->query('./w:p|./w:tbl', $cell) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }
            if ($child->localName === 'tbl') {
                $inner .= $this->convertTable($xpath, $child);

                continue;
            }
            $html = $this->convertInline($xpath, $child);
            $inner .= $html === '' ? '' : '<p>'.$html.'</p>';
        }

        $attrs = $colspan > 1 ? ' colspan="'.$colspan.'"' : '';
        if ($styles !== []) {
            $attrs .= ' style="'.e(implode('; ', $styles)).'"';
        }

        return '<'.$tag.$attrs.'>'.($inner !== '' ? $inner : '&nbsp;').'</'.$tag.'>';
    }

    private function convertInline(DOMXPath $xpath, DOMElement $container): string
    {
        $html = '';

        foreach ($container->childNodes as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $name = $child->localName;
            if (in_array($name, ['pPr', 'rPr', 'del', 'bookmarkStart', 'bookmarkEnd', 'commentRangeStart', 'commentRangeEnd', 'proofErr', 'lastRenderedPageBreak', 'instrText', 'fldChar'], true)) {
                continue;
            }

            if ($name === 'r') {
                $html .= $this->convertRun($xpath, $child);

                continue;
            }

            if ($name === 'hyperlink') {
                $href = $this->hyperlinkHref($child);
                $inner = $this->convertInline($xpath, $child);
                if ($inner === '') {
                    continue;
                }
                $html .= $href !== null
                    ? '<a href="'.e($href).'">'.$inner.'</a>'
                    : $inner;

                continue;
            }

            if (in_array($name, ['drawing', 'pict', 'object'], true)) {
                $html .= $this->convertImage($xpath, $child);

                continue;
            }

            $html .= $this->convertInline($xpath, $child);
        }

        return $html;
    }

    private function convertRun(DOMXPath $xpath, DOMElement $run): string
    {
        $inner = '';

        foreach ($run->childNodes as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $name = $child->localName;
            if ($name === 't') {
                $inner .= e($child->textContent);

                continue;
            }
            if ($name === 'tab') {
                $inner .= '&emsp;';

                continue;
            }
            if ($name === 'br' || $name === 'cr') {
                $inner .= '<br>';

                continue;
            }
            if (in_array($name, ['drawing', 'pict', 'object'], true)) {
                $inner .= $this->convertImage($xpath, $child);
            }
        }

        if ($inner === '') {
            return '';
        }

        $styles = [];
        $wrap = [];

        if ($this->flagOn($xpath, $run, 'b')) {
            $wrap[] = 'strong';
        }
        if ($this->flagOn($xpath, $run, 'i')) {
            $wrap[] = 'em';
        }
        if ($this->flagOn($xpath, $run, 'u')) {
            $wrap[] = 'u';
        }
        if ($this->flagOn($xpath, $run, 'strike') || $this->flagOn($xpath, $run, 'dstrike')) {
            $wrap[] = 's';
        }

        $vert = $xpath->query('./w:rPr/w:vertAlign', $run)->item(0);
        if ($vert instanceof DOMElement) {
            $val = $vert->getAttribute('w:val');
            if ($val === 'superscript') {
                $wrap[] = 'sup';
            }
            if ($val === 'subscript') {
                $wrap[] = 'sub';
            }
        }

        $color = $xpath->query('./w:rPr/w:color', $run)->item(0);
        if ($color instanceof DOMElement) {
            $hex = $this->hexColor($color->getAttribute('w:val'));
            if ($hex !== null) {
                $styles[] = 'color: '.$hex;
            }
        }

        $fonts = $xpath->query('./w:rPr/w:rFonts', $run)->item(0);
        if ($fonts instanceof DOMElement) {
            $family = $fonts->getAttribute('w:ascii') ?: $fonts->getAttribute('w:hAnsi');
            if ($family !== '' && ! preg_match('/[<>"\';]/', $family)) {
                $styles[] = 'font-family: '.$family;
            }
        }

        $highlight = $xpath->query('./w:rPr/w:highlight', $run)->item(0);
        if ($highlight instanceof DOMElement) {
            $styles[] = 'background-color: '.$this->highlightColor($highlight->getAttribute('w:val'));
        }

        $shd = $xpath->query('./w:rPr/w:shd', $run)->item(0);
        if ($shd instanceof DOMElement) {
            $fill = $this->hexColor($shd->getAttribute('w:fill'));
            if ($fill !== null) {
                $styles[] = 'background-color: '.$fill;
            }
        }

        $sz = $xpath->query('./w:rPr/w:sz', $run)->item(0);
        if ($sz instanceof DOMElement && is_numeric($sz->getAttribute('w:val'))) {
            $pt = ((int) $sz->getAttribute('w:val')) / 2;
            if ($pt > 0 && $pt !== 11.0) {
                $styles[] = 'font-size: '.$pt.'pt';
            }
        }

        $html = $inner;
        foreach ($wrap as $tag) {
            $html = '<'.$tag.'>'.$html.'</'.$tag.'>';
        }

        if ($styles !== []) {
            $html = '<span style="'.e(implode('; ', $styles)).'">'.$html.'</span>';
        }

        return $html;
    }

    private function convertImage(DOMXPath $xpath, DOMElement $node): string
    {
        $id = '';
        foreach ($xpath->query('.//*[local-name()="blip"]', $node) as $blip) {
            if ($blip instanceof DOMElement) {
                $id = $blip->getAttributeNS(self::RELS_NS, 'embed') ?: $blip->getAttribute('r:embed');
                break;
            }
        }

        if ($id === '') {
            foreach ($xpath->query('.//*[local-name()="imagedata"]', $node) as $image) {
                if ($image instanceof DOMElement) {
                    $id = $image->getAttributeNS(self::RELS_NS, 'id') ?: $image->getAttribute('r:id');
                    break;
                }
            }
        }

        if ($id === '' || ! isset($this->rels[$id]) || $this->rels[$id]['type'] !== 'image') {
            return '';
        }

        return '<img src="media://'.e($id).'" alt="">';
    }

    private function hyperlinkHref(DOMElement $link): ?string
    {
        $id = $link->getAttributeNS(self::RELS_NS, 'id') ?: $link->getAttribute('r:id');
        if ($id !== '' && isset($this->rels[$id])) {
            return $this->rels[$id]['target'];
        }

        $anchor = $link->getAttribute('w:anchor');

        return $anchor !== '' ? '#'.$anchor : null;
    }

    private function flagOn(DOMXPath $xpath, DOMElement $run, string $tag): bool
    {
        $node = $xpath->query('./w:rPr/w:'.$tag, $run)->item(0);
        if (! $node instanceof DOMElement) {
            return false;
        }

        $val = strtolower($node->getAttribute('w:val'));

        return $val === '' || ! in_array($val, ['0', 'false', 'off'], true);
    }

    private function hexColor(string $val): ?string
    {
        $val = strtoupper(trim($val));
        if ($val === '' || $val === 'AUTO') {
            return null;
        }

        return preg_match('/^[0-9A-F]{6}$/', $val) ? '#'.$val : null;
    }

    private function highlightColor(string $name): string
    {
        return match (strtolower($name)) {
            'yellow' => '#ffff00',
            'green' => '#00ff00',
            'cyan' => '#00ffff',
            'magenta' => '#ff00ff',
            'blue' => '#0000ff',
            'red' => '#ff0000',
            'darkblue' => '#00008b',
            'darkcyan' => '#008b8b',
            'darkgreen' => '#006400',
            'darkmagenta' => '#8b008b',
            'darkred' => '#8b0000',
            'darkyellow' => '#cccc00',
            'darkgray', 'darkgrey' => '#a9a9a9',
            'lightgray', 'lightgrey' => '#d3d3d3',
            default => '#ffff99',
        };
    }

    private function headingLevelFromStyleId(string $styleId): int
    {
        if (preg_match('/(?:heading|titre)\s*([1-6])/i', $styleId, $match)) {
            return (int) $match[1];
        }

        return 0;
    }
}
