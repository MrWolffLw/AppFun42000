<?php

namespace App\Tests\Service;

use App\Service\VideoUrlParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VideoUrlParserTest extends TestCase
{
    #[DataProvider('validUrls')]
    public function testValidUrls(string $url, string $provider, string $id): void
    {
        self::assertSame(['provider' => $provider, 'id' => $id], (new VideoUrlParser())->parse($url));
    }

    public static function validUrls(): iterable
    {
        yield 'youtube watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=5s', 'youtube', 'dQw4w9WgXcQ'];
        yield 'youtube court' => ['https://youtu.be/dQw4w9WgXcQ?si=abc', 'youtube', 'dQw4w9WgXcQ'];
        yield 'youtube shorts mobile' => ['https://m.youtube.com/shorts/ABCDEFGHIJK', 'youtube', 'ABCDEFGHIJK'];
        yield 'youtube embed' => ['https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', 'youtube', 'dQw4w9WgXcQ'];
        yield 'vimeo' => ['https://vimeo.com/76979871', 'vimeo', '76979871'];
        yield 'vimeo player' => ['https://player.vimeo.com/video/76979871?h=abc', 'vimeo', '76979871'];
    }

    #[DataProvider('invalidUrls')]
    public function testInvalidUrls(string $url): void
    {
        self::assertNull((new VideoUrlParser())->parse($url));
    }

    public static function invalidUrls(): iterable
    {
        yield 'script' => ['javascript:alert(1)'];
        yield 'autre site' => ['https://example.com/watch?v=dQw4w9WgXcQ'];
        yield 'faux sous-domaine' => ['https://youtube.com.evil.example/watch?v=dQw4w9WgXcQ'];
        yield 'identifiant trop court' => ['https://www.youtube.com/watch?v=abc'];
        yield 'vide' => [''];
    }
}
