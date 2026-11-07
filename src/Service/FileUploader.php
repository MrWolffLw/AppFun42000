<?php

namespace App\Service;

use App\Entity\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Enregistre les fichiers envoyés sous un nom aléatoire, avec une extension déduite du CONTENU réel
 * (jamais du nom fourni par le navigateur).
 */
class FileUploader
{
    private const DIRS = ['icons', 'covers', 'images', 'videos'];

    public function __construct(private readonly string $uploadDir) {}

    public function upload(UploadedFile $file, string $subdir): string
    {
        $this->assertDir($subdir);
        $extension = $file->guessExtension();
        if (!$extension || !preg_match('/^[a-z0-9]{2,5}$/', $extension)) {
            throw new \RuntimeException('Type de fichier non reconnu.');
        }
        $name = bin2hex(random_bytes(12)).'.'.$extension;
        $dir = $this->uploadDir.'/'.$subdir;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException('Impossible de créer le dossier d’envoi.');
        }
        $file->move($dir, $name);

        return $name;
    }

    public function remove(string $subdir, ?string $filename): void
    {
        if ($filename === null || $filename === '') {
            return;
        }
        $this->assertDir($subdir);
        $path = $this->uploadDir.'/'.$subdir.'/'.basename($filename);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /** Supprime le fichier (image ou vidéo) rattaché à un média ; sans effet pour les vidéos YouTube/Vimeo. */
    public function removeMediaFile(Media $media): void
    {
        $this->remove($media->isImage() ? 'images' : 'videos', $media->getFilename());
    }

    private function assertDir(string $subdir): void
    {
        if (!in_array($subdir, self::DIRS, true)) {
            throw new \InvalidArgumentException('Dossier inconnu.');
        }
    }
}
