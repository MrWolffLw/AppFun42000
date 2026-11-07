<?php

namespace App\Service;

use League\CommonMark\CommonMarkConverter;

/** Convertit le Markdown saisi en administration en HTML sûr (le HTML brut est supprimé). */
class MarkdownRenderer
{
    private CommonMarkConverter $converter;

    public function __construct()
    {
        $this->converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]);
    }

    public function render(?string $markdown): string
    {
        return $markdown === null || trim($markdown) === '' ? '' : (string) $this->converter->convert($markdown);
    }
}
