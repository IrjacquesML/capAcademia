<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    #[Test]
    public function it_keeps_safe_markup_and_drops_scripts(): void
    {
        $sanitizer = new HtmlSanitizer;
        $html = $sanitizer->sanitize('<p>Ok</p><script>alert(1)</script><p onclick="x()">Suite</p>');

        $this->assertStringContainsString('<p>Ok</p>', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('alert(1)', $html);
        $this->assertStringNotContainsString('onclick', $html);
    }

    #[Test]
    public function it_wraps_plain_text_for_display(): void
    {
        $html = (new HtmlSanitizer)->sanitizeForDisplay("Ligne 1\n\nLigne 2");

        $this->assertStringContainsString('<p>Ligne 1</p>', $html);
        $this->assertStringContainsString('<p>Ligne 2</p>', $html);
    }
}
