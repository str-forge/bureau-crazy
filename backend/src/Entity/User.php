<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $totpSecret = null;

    // Neue Felder für die TDD-Anforderung der Kontosperrung
    #[ORM\Column(type: 'integer')]
    private int $failed2faAttempts = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $isLocked = false;

    public function __construct(string $email)
    {
        $this->id = Uuid::v7();
        $this->email = $email;
        $this->roles = ['ROLE_USER'];
        $this->failed2faAttempts = 0;
        $this->isLocked = false;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(?string $totpSecret): self
    {
        $this->totpSecret = $totpSecret;
        return $this;
    }

    public function is2faEnabled(): bool
    {
        return !empty($this->totpSecret);
    }

    // Neue TDD-Business-Logik-Methoden
    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    public function incrementFailed2faAttempts(): self
    {
        $this->failed2faAttempts++;
        if ($this->failed2faAttempts >= 3) {
            $this->isLocked = true;
        }
        return $this;
    }

    public function resetFailed2faAttempts(): self
    {
        $this->failed2faAttempts = 0;
        $this->isLocked = false;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }
}
