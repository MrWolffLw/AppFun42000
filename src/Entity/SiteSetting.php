<?php

namespace App\Entity;

use App\Repository\SiteSettingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** Réglages du site : une seule ligne, modifiable depuis l'administration. */
#[ORM\Entity(repositoryClass: SiteSettingRepository::class)]
class SiteSetting
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    private string $siteName = 'Mes applications';

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    private string $heroTitle = 'Des applications utiles, pensées avec soin.';

    #[ORM\Column(length: 400)]
    #[Assert\Length(max: 400)]
    private string $heroSubtitle = 'Découvrez mes applications, leurs fonctionnalités, des captures d’écran et des vidéos.';

    #[ORM\Column(length: 300)]
    #[Assert\Length(max: 300)]
    private string $metaDescription = 'Présentation de mes applications mobiles : fonctionnalités, captures d’écran, vidéos et liens de téléchargement.';

    #[ORM\Column(type: Types::TEXT)]
    private string $aboutText = "## À propos\n\nPrésentez ici qui vous êtes et ce que vous construisez.";

    #[ORM\Column(type: Types::TEXT)]
    private string $legalText = "## Mentions légales\n\n**Éditeur :** [nom ou raison sociale], [adresse]\n\n**Contact :** [adresse e-mail]\n\n**Hébergeur :** [nom et adresse de l’hébergeur]";

    #[ORM\Column(type: Types::TEXT)]
    private string $privacyText = "## Politique de confidentialité\n\nDécrivez ici les données collectées par ce site (formulaire de contact, journaux du serveur) et vos coordonnées pour exercer les droits.";

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    private ?string $contactEmail = null;

    public function getId(): ?int { return $this->id; }
    public function getSiteName(): string { return $this->siteName; }
    public function setSiteName(?string $v): static { $this->siteName = trim((string) $v); return $this; }
    public function getHeroTitle(): string { return $this->heroTitle; }
    public function setHeroTitle(?string $v): static { $this->heroTitle = trim((string) $v); return $this; }
    public function getHeroSubtitle(): string { return $this->heroSubtitle; }
    public function setHeroSubtitle(?string $v): static { $this->heroSubtitle = trim((string) $v); return $this; }
    public function getMetaDescription(): string { return $this->metaDescription; }
    public function setMetaDescription(?string $v): static { $this->metaDescription = trim((string) $v); return $this; }
    public function getAboutText(): string { return $this->aboutText; }
    public function setAboutText(?string $v): static { $this->aboutText = (string) $v; return $this; }
    public function getLegalText(): string { return $this->legalText; }
    public function setLegalText(?string $v): static { $this->legalText = (string) $v; return $this; }
    public function getPrivacyText(): string { return $this->privacyText; }
    public function setPrivacyText(?string $v): static { $this->privacyText = (string) $v; return $this; }
    public function getContactEmail(): ?string { return $this->contactEmail; }
    public function setContactEmail(?string $v): static { $this->contactEmail = $v; return $this; }
}
