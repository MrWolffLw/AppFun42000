<?php

namespace App\Service;

/** Reconnaît les liens YouTube et Vimeo et en extrait l'identifiant de la vidéo. */
class VideoUrlParser
{
    /** @return array{provider: string, id: string}|null */
    public function parse(string $url): ?array
    {
        $url = trim($url);
        $parts = parse_url($url);
        if ($parts === false || !isset($parts['host']) || !in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            return null;
        }
        $host = strtolower(preg_replace('/^(www|m)\./i', '', $parts['host']) ?? '');
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        if ($host === 'youtu.be') {
            return $this->youtube(ltrim($path, '/'));
        }
        if (in_array($host, ['youtube.com', 'youtube-nocookie.com'], true)) {
            if ($path === '/watch') {
                return $this->youtube((string) ($query['v'] ?? ''));
            }
            if (preg_match('#^/(?:embed|shorts|live)/([^/?]+)#', $path, $m)) {
                return $this->youtube($m[1]);
            }
        }
        if ($host === 'vimeo.com' && preg_match('#^/(\d{5,12})(?:/|$)#', $path, $m)) {
            return ['provider' => 'vimeo', 'id' => $m[1]];
        }
        if ($host === 'player.vimeo.com' && preg_match('#^/video/(\d{5,12})#', $path, $m)) {
            return ['provider' => 'vimeo', 'id' => $m[1]];
        }

        return null;
    }

    /** @return array{provider: string, id: string}|null */
    private function youtube(string $id): ?array
    {
        return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? ['provider' => 'youtube', 'id' => $id] : null;
    }
}
