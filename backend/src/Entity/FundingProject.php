<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FundingProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV7;

#[ORM\Entity(repositoryClass: FundingProjectRepository::class)]
#[ORM\Table(name: 'funding_projects')]
class FundingProject
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private string $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $title = '';

    #[ORM\Column(type: Types::INTEGER)]
    private int $budget = 0; // Speicherung in Cent (z.B. 500000 = 5000,00 €)

    #[ORM\OneToMany(
        mappedBy: 'fundingProject',
        targetEntity: Deadline::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $deadlines;

    public function __construct()
    {
        // Wir generieren die zeitlich sortierbare UUIDv7 direkt im Konstruktor
        $this->id = (string) new UuidV7();
        $this->deadlines = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getBudget(): int
    {
        return $this->budget;
    }

    public function setBudget(int $budget): self
    {
        $this->budget = $budget;
        return $this;
    }

    /**
     * @return Collection<int, Deadline>
     */
    public function getDeadlines(): Collection
    {
        return $this->deadlines;
    }

    public function addDeadline(Deadline $deadline): self
    {
        if (!$this->deadlines->contains($deadline)) {
            $this->deadlines[] = $deadline;
            $deadline->setFundingProject($this);
        }

        return $this;
    }
}
