<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{
    public const IMAGE = 'image';
    public const VIDEO = 'video';   // fichier vidéo envoyé
    public const EMBED = 'embed';   // YouTube / Vimeo

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'media')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Application $application = null;

    #[ORM\Column(length: 10)]
    private string $type = self::IMAGE;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $filename = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $embedProvider = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $embedId = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $caption = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getApplication(): ?Application { return $this->application; }
    public function setApplication(?Application $a): static { $this->application = $a; return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $t): static { $this->type = $t; return $this; }
    public function getFilename(): ?string { return $this->filename; }
    public function setFilename(?string $f): static { $this->filename = $f; return $this; }
    public function getEmbedProvider(): ?string { return $this->embedProvider; }
    public function setEmbedProvider(?string $p): static { $this->embedProvider = $p; return $this; }
    public function getEmbedId(): ?string { return $this->embedId; }
    public function setEmbedId(?string $i): static { $this->embedId = $i; return $this; }
    public function getCaption(): ?string { return $this->caption; }
    public function setCaption(?string $c): static { $this->caption = $c !== null && trim($c) !== '' ? trim($c) : null; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(int $p): static { $this->position = $p; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function isImage(): bool { return $this->type === self::IMAGE; }
    public function isVideoFile(): bool { return $this->type === self::VIDEO; }
    public function isEmbed(): bool { return $this->type === self::EMBED; }

    /** Adresse d'intégration « respectueuse de la vie privée » (pas de cookies tiers avant lecture). */
    public function getEmbedUrl(): ?string
    {
        return match ($this->embedProvider) {
            'youtube' => 'https://www.youtube-nocookie.com/embed/'.rawurlencode((string) $this->embedId),
            'vimeo' => 'https://player.vimeo.com/video/'.rawurlencode((string) $this->embedId).'?dnt=1',
            default => null,
        };
    }
}
