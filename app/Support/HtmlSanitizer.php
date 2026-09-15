<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'sub', 'sup',
        'span', 'div', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li',
        'table', 'thead', 'tbody', 'tr', 'td', 'th', 'img', 'a',
        'blockquote', 'hr', 'pre', 'code',
    ];

    private const ALLOWED_STYLES = [
        'color', 'background', 'background-color', 'font-size', 'font-weight',
        'font-family', 'font-style', 'text-decoration', 'text-align', 'text-indent',
        'margin', 'margin-left', 'margin-right', 'padding', 'width', 'height',
        'max-width', 'vertical-align', 'border', 'border-collapse', 'border-color',
        'border-width', 'border-style',
    ];

    public function looksLikeHtml(string $content): bool
    {
        return (bool) preg_match('/<\/?[a-z][\s\S]*>/i', $content);
    }

    public function sanitizeForDisplay(string $content): string
    {
        $content = trim($content);
        if ($content === '') {
            return '';
        }

        if (! $this->looksLikeHtml($content)) {
            $parts = preg_split("/\n\s*\n/", $content) ?: [$content];
            $html = '';
            foreach ($parts as $part) {
                $html .= '<p>'.nl2br(e(trim($part)), false).'</p>';
            }

            return $html;
        }

        return $this->sanitize($content);
    }

    public function sanitize(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument;
        $dom->encoding = 'UTF-8';
        @$dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="ca-root">'.$html.'</div>',
            LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING,
        );

        $root = $dom->getElementById('ca-root');
        if (! $root instanceof DOMElement) {
            return '';
        }

        $this->scrub($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out;
    }

    private function scrub(DOMNode $node): void
    {
        $toRemove = [];
        $toUnwrap = [];

        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'link', 'meta'], true)) {
                    $toRemove[] = $child;

                    continue;
                }
                if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                    $this->scrub($child);
                    $toUnwrap[] = $child;

                    continue;
                }

                $this->scrubAttributes($child);
                $this->scrub($child);
            }
        }

        foreach ($toRemove as $child) {
            $child->parentNode?->removeChild($child);
        }

        foreach ($toUnwrap as $child) {
            while ($child->firstChild) {
                $child->parentNode?->insertBefore($child->firstChild, $child);
            }
            $child->parentNode?->removeChild($child);
        }
    }

    private function scrubAttributes(DOMElement $element): void
    {
        $allowed = ['style', 'href', 'src', 'alt', 'colspan', 'rowspan', 'class'];
        $remove = [];

        foreach ($element->attributes ?? [] as $attribute) {
            $name = strtolower($attribute->name);
            if (str_starts_with($name, 'on') || ! in_array($name, $allowed, true)) {
                $remove[] = $attribute->name;
            }
        }

        foreach ($remove as $name) {
            $element->removeAttribute($name);
        }

        if ($element->hasAttribute('style')) {
            $style = $this->cleanStyle($element->getAttribute('style'));
            if ($style === null) {
                $element->removeAttribute('style');
            } else {
                $element->setAttribute('style', $style);
            }
        }

        if ($element->tagName === 'a' && $element->hasAttribute('href')) {
            $href = trim($element->getAttribute('href'));
            if (! preg_match('/^(https?:|mailto:|#|\/)/i', $href)) {
                $element->removeAttribute('href');
            }
            $element->setAttribute('rel', 'noopener noreferrer');
            $element->setAttribute('target', '_blank');
        }

        if ($element->tagName === 'img') {
            $src = trim($element->getAttribute('src'));
            if (! $this->isSafeImageSrc($src)) {
                $element->parentNode?->removeChild($element);

                return;
            }
            $element->setAttribute('src', $src);
        }
    }

    private function cleanStyle(string $style): ?string
    {
        $kept = [];

        foreach (explode(';', $style) as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }
            [$property, $value] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);
            if (! in_array($property, self::ALLOWED_STYLES, true)) {
                continue;
            }
            if (preg_match('/expression|javascript|url\s*\(\s*["\']?\s*javascript/i', $value)) {
                continue;
            }
            $kept[] = $property.': '.$value;
        }

        return $kept === [] ? null : implode('; ', $kept);
    }

    private function isSafeImageSrc(string $src): bool
    {
        if ($src === '' || preg_match('/javascript:|data:/i', $src)) {
            return false;
        }

        return (bool) preg_match('#^(/storage/|https?://|media://)#i', $src)
            || (str_starts_with($src, '/') && ! str_starts_with($src, '//'));
    }
}
