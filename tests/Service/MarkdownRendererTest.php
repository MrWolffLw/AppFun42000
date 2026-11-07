<?php

namespace App\Tests\Service;

use App\Service\MarkdownRenderer;
use PHPUnit\Framework\TestCase;

final class MarkdownRendererTest extends TestCase
{
    public function testRendersBasicMarkdown(): void
    {
        self::assertStringContainsString('<strong>gras</strong>', (new MarkdownRenderer())->render('**gras**'));
    }

    public function testStripsRawHtmlAndScripts(): void
    {
        $html = (new MarkdownRenderer())->render("<script>alert(1)</script>\n\n<img src=x onerror=alert(1)>");
        self::assertStringNotContainsString('<script', $html);
        self::assertStringNotContainsString('onerror', $html);
    }

    public function testRemovesUnsafeLinks(): void
    {
        self::assertStringNotContainsString('javascript:', (new MarkdownRenderer())->render('[clic](javascript:alert(1))'));
    }

    public function testEmptyInput(): void
    {
        self::assertSame('', (new MarkdownRenderer())->render(null));
        self::assertSame('', (new MarkdownRenderer())->render("  \n"));
    }
}
