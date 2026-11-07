<?php

namespace App\Entity;

use App\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['slug'], message: 'Ce lien (slug) est déjà utilisé par une autre application.')]
class Application
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 120)]
    private string $name = '';

    #[ORM\Column(length: 140, unique: true)]
    #[Assert\Length(max: 140)]
    #[Assert\Regex(pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', message: 'Utilisez uniquement des minuscules, des chiffres et des tirets.')]
    private string $slug = '';

    #[ORM\Column(length: 160, nullable: true)]
    #[Assert\Length(max: 160)]
    private ?string $tagline = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 400)]
    private ?string $shortDescription = null;

    /** Description complète, en Markdown. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 20000)]
    private ?string $description = null;

    /** Points forts, un par ligne. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 4000)]
    private ?string $featuresText = null;

    #[ORM\Column(length: 60, nullable: true)]
    #[Assert\Length(max: 60)]
    private ?string $category = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    private ?string $version = null;

    #[ORM\Column(length: 7)]
    #[Assert\Regex(pattern: '/^#[0-9a-fA-F]{6}$/', message: 'Couleur invalide.')]
    private string $accentColor = '#4338ca';

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $iconFilename = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $coverFilename = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(protocols: ['http', 'https'], message: 'Lien invalide (http ou https).')]
    private ?string $googlePlayUrl = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(protocols: ['http', 'https'], message: 'Lien invalide (http ou https).')]
    private ?string $appStoreUrl = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(protocols: ['http', 'https'], message: 'Lien invalide (http ou https).')]
    private ?string $websiteUrl = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(protocols: ['http', 'https'], message: 'Lien invalide (http ou https).')]
    private ?string $privacyUrl = null;

    #[ORM\Column(name: 'is_published', options: ['default' => false])]
    private bool $published = false;

    #[ORM\Column(options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, Media> */
    #[ORM\OneToMany(targetEntity: Media::class, mappedBy: 'application', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC', 'id' => 'ASC'])]
    private Collection $media;

    public function __construct()
    {
        $this->media = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function touch(): void { $this->updatedAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(?string $v): static { $this->name = trim((string) $v); return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(?string $v): static { $this->slug = trim((string) $v); return $this; }
    public function getTagline(): ?string { return $this->tagline; }
    public function setTagline(?string $v): static { $this->tagline = $v; return $this; }
    public function getShortDescription(): ?string { return $this->shortDescription; }
    public function setShortDescription(?string $v): static { $this->shortDescription = $v; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $v): static { $this->description = $v; return $this; }
    public function getFeaturesText(): ?string { return $this->featuresText; }
    public function setFeaturesText(?string $v): static { $this->featuresText = $v; return $this; }
    public function getCategory(): ?string { return $this->category; }
    public function setCategory(?string $v): static { $this->category = $v; return $this; }
    public function getVersion(): ?string { return $this->version; }
    public function setVersion(?string $v): static { $this->version = $v; return $this; }
    public function getAccentColor(): string { return $this->accentColor; }
    public function setAccentColor(?string $v): static { $this->accentColor = $v ?: '#4338ca'; return $this; }
    public function getIconFilename(): ?string { return $this->iconFilename; }
    public function setIconFilename(?string $v): static { $this->iconFilename = $v; return $this; }
    public function getCoverFilename(): ?string { return $this->coverFilename; }
    public function setCoverFilename(?string $v): static { $this->coverFilename = $v; return $this; }
    public function getGooglePlayUrl(): ?string { return $this->googlePlayUrl; }
    public function setGooglePlayUrl(?string $v): static { $this->googlePlayUrl = $v; return $this; }
    public function getAppStoreUrl(): ?string { return $this->appStoreUrl; }
    public function setAppStoreUrl(?string $v): static { $this->appStoreUrl = $v; return $this; }
    public function getWebsiteUrl(): ?string { return $this->websiteUrl; }
    public function setWebsiteUrl(?string $v): static { $this->websiteUrl = $v; return $this; }
    public function getPrivacyUrl(): ?string { return $this->privacyUrl; }
    public function setPrivacyUrl(?string $v): static { $this->privacyUrl = $v; return $this; }
    public function isPublished(): bool { return $this->published; }
    public function setPublished(bool $v): static { $this->published = $v; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(?int $v): static { $this->position = $v ?? 0; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    /** @return Collection<int, Media> */
    public function getMedia(): Collection { return $this->media; }
    public function addMedia(Media $m): static { if (!$this->media->contains($m)) { $this->media->add($m); $m->setApplication($this); } return $this; }
    public function removeMedia(Media $m): static { $this->media->removeElement($m); return $this; }

    /** @return list<string> */
    public function getFeatures(): array
    {
        $lines = preg_split('/\R/', (string) $this->featuresText) ?: [];

        return array_values(array_filter(array_map('trim', $lines), static fn (string $l): bool => $l !== ''));
    }
}
