<?php

namespace App\Entity;

use App\Enum\DeadlineType;
use App\Repository\DeadlineRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\UuidV7;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DeadlineRepository::class)]
class Deadline
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private ?UuidV7 $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

   #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotBlank(message: 'Ein Frist-Datum ist zwingend erforderlich.')]
    #[Assert\GreaterThanOrEqual(
        value: 'today',
        message: 'Das Frist-Datum darf nicht in der Vergangenheit liegen.'
    )]
    private ?\DateTimeImmutable $dueDate = null;

    // Das native PHP-Enum gemappt für Doctrine
    #[ORM\Column(length: 50, enumType: DeadlineType::class)]
    private ?DeadlineType $type = null;

    // Die ManyToOne-Beziehung: Eine Frist gehört immer zu genau einem Projekt
    #[ORM\ManyToOne(inversedBy: 'deadlines')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?FundingProject $fundingProject = null;

    public function __construct()
    {
        $this->id = new UuidV7(); // UUIDv7 wird im Konstruktor generiert
    }

    public function getId(): ?UuidV7
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getType(): ?DeadlineType
    {
        return $this->type;
    }

    public function setType(DeadlineType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getFundingProject(): ?FundingProject
    {
        return $this->fundingProject;
    }

    public function setFundingProject(?FundingProject $fundingProject): static
    {
        $this->fundingProject = $fundingProject;

        return $this;
    }
}
