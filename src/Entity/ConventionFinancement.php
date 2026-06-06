<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ConventionFinancementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConventionFinancementRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Une convention de financement existe déjà avec ce code.')]
#[ApiResource(normalizationContext: ['groups' => ['convention_financement:read']], denormalizationContext: ['groups' => ['convention_financement:write']])]
class ConventionFinancement
{
    public const STATUT_EN_PREPARATION = 'EN_PREPARATION';
    public const STATUT_SIGNEE = 'SIGNEE';
    public const STATUT_EN_EXECUTION = 'EN_EXECUTION';
    public const STATUT_CLOTUREE = 'CLOTUREE';
    public const STATUT_SUSPENDUE = 'SUSPENDUE';
    public const STATUT_ANNULEE = 'ANNULEE';
    public const STATUTS = [self::STATUT_EN_PREPARATION, self::STATUT_SIGNEE, self::STATUT_EN_EXECUTION, self::STATUT_CLOTUREE, self::STATUT_SUSPENDUE, self::STATUT_ANNULEE];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['convention_financement:read', 'decaissement:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['convention_financement:read', 'convention_financement:write', 'decaissement:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[Groups(['convention_financement:read', 'convention_financement:write', 'decaissement:read'])]
    private ?string $intitule = null;

    #[ORM\ManyToOne(inversedBy: 'conventionsFinancement')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['convention_financement:read', 'convention_financement:write'])]
    private ?BailleurFonds $bailleurFonds = null;

    #[ORM\ManyToOne(inversedBy: 'conventionsFinancement')]
    #[Groups(['convention_financement:read', 'convention_financement:write'])]
    private ?SourceFinancement $sourceFinancement = null;

    #[ORM\ManyToOne(inversedBy: 'conventionsFinancement')]
    #[Groups(['convention_financement:read', 'convention_financement:write'])]
    private ?ProgrammePner $programmePner = null;

    #[ORM\ManyToOne(inversedBy: 'conventionsFinancement')]
    #[Groups(['convention_financement:read', 'convention_financement:write'])]
    private ?ProjetElectrification $projetElectrification = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(['convention_financement:read', 'convention_financement:write'])]
    private ?float $montantUsd = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['convention_financement:read', 'convention_financement:write'])]
    private ?\DateTimeImmutable $dateSignature = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['convention_financement:read', 'convention_financement:write'])]
    private ?\DateTimeImmutable $dateDebut = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['convention_financement:read', 'convention_financement:write'])]
    private ?\DateTimeImmutable $dateFin = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::STATUTS)]
    #[Groups(['convention_financement:read', 'convention_financement:write'])]
    private ?string $statut = self::STATUT_EN_PREPARATION;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['convention_financement:read', 'convention_financement:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['convention_financement:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['convention_financement:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, Decaissement> */
    #[ORM\OneToMany(mappedBy: 'conventionFinancement', targetEntity: Decaissement::class, orphanRemoval: true)]
    private Collection $decaissements;

    public function __construct() { $this->decaissements = new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getIntitule(): ?string { return $this->intitule; }
    public function setIntitule(string $intitule): static { $this->intitule = trim($intitule); return $this; }
    public function getBailleurFonds(): ?BailleurFonds { return $this->bailleurFonds; }
    public function setBailleurFonds(?BailleurFonds $bailleurFonds): static { $this->bailleurFonds = $bailleurFonds; return $this; }
    public function getSourceFinancement(): ?SourceFinancement { return $this->sourceFinancement; }
    public function setSourceFinancement(?SourceFinancement $sourceFinancement): static { $this->sourceFinancement = $sourceFinancement; return $this; }
    public function getProgrammePner(): ?ProgrammePner { return $this->programmePner; }
    public function setProgrammePner(?ProgrammePner $programmePner): static { $this->programmePner = $programmePner; return $this; }
    public function getProjetElectrification(): ?ProjetElectrification { return $this->projetElectrification; }
    public function setProjetElectrification(?ProjetElectrification $projetElectrification): static { $this->projetElectrification = $projetElectrification; return $this; }
    public function getMontantUsd(): ?float { return $this->montantUsd; }
    public function setMontantUsd(float $montantUsd): static { $this->montantUsd = $montantUsd; return $this; }
    public function getDateSignature(): ?\DateTimeImmutable { return $this->dateSignature; }
    public function setDateSignature(?\DateTimeImmutable $dateSignature): static { $this->dateSignature = $dateSignature; return $this; }
    public function getDateDebut(): ?\DateTimeImmutable { return $this->dateDebut; }
    public function setDateDebut(?\DateTimeImmutable $dateDebut): static { $this->dateDebut = $dateDebut; return $this; }
    public function getDateFin(): ?\DateTimeImmutable { return $this->dateFin; }
    public function setDateFin(?\DateTimeImmutable $dateFin): static { $this->dateFin = $dateFin; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    /** @return Collection<int, Decaissement> */
    public function getDecaissements(): Collection { return $this->decaissements; }
    public function addDecaissement(Decaissement $decaissement): static { if (!$this->decaissements->contains($decaissement)) { $this->decaissements->add($decaissement); $decaissement->setConventionFinancement($this); } return $this; }
    public function removeDecaissement(Decaissement $decaissement): static { if ($this->decaissements->removeElement($decaissement) && $decaissement->getConventionFinancement() === $this) { $decaissement->setConventionFinancement(null); } return $this; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
