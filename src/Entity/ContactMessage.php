<?php

namespace App\Entity;

use App\Repository\ContactMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactMessageRepository::class)]
class ContactMessage
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank(message: 'Indiquez votre nom.')]
    #[Assert\Length(max: 120)]
    private string $name = '';

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Indiquez votre adresse e-mail.')]
    #[Assert\Email(message: 'Adresse e-mail invalide.')]
    #[Assert\Length(max: 180)]
    private string $email = '';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Écrivez votre message.')]
    #[Assert\Length(min: 10, max: 5000, minMessage: 'Votre message est trop court.', maxMessage: 'Votre message est trop long.')]
    private string $message = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'is_read', options: ['default' => false])]
    private bool $read = false;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(?string $v): static { $this->name = trim((string) $v); return $this; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(?string $v): static { $this->email = trim((string) $v); return $this; }
    public function getMessage(): string { return $this->message; }
    public function setMessage(?string $v): static { $this->message = trim((string) $v); return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function isRead(): bool { return $this->read; }
    public function setRead(bool $v): static { $this->read = $v; return $this; }
}
