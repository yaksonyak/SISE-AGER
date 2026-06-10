<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ActiviteProjetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActiviteProjetRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['activite_projet:read']],
    denormalizationContext: ['groups' => ['activite_projet:write']]
)]
class ActiviteProjet
{
    public const STATUT_PLANIFIE = 'PLANIFIE';
    public const STATUT_EN_PREPARATION = 'EN_PREPARATION';
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_SUSPENDU = 'SUSPENDU';
    public const STATUT_TERMINE = 'TERMINE';
    public const STATUT_ANNULE = 'ANNULE';
    public const STATUTS = [self::STATUT_PLANIFIE, self::STATUT_EN_PREPARATION, self::STATUT_EN_COURS, self::STATUT_SUSPENDU, self::STATUT_TERMINE, self::STATUT_ANNULE];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['activite_projet:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'activitesProjet')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['activite_projet:read', 'activite_projet:write'])]
    private ?PhaseProjet $phaseProjet = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[Groups(['activite_projet:read', 'activite_projet:write'])]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['activite_projet:read', 'activite_projet:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::STATUTS)]
    #[Groups(['activite_projet:read', 'activite_projet:write'])]
    private ?string $statut = self::STATUT_PLANIFIE;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 100)]
    #[Groups(['activite_projet:read', 'activite_projet:write'])]
    private ?float $tauxExecution = 0.0;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['activite_projet:read', 'activite_projet:write'])]
    private ?\DateTimeImmutable $dateDebutPrevue = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['activite_projet:read', 'activite_projet:write'])]
    private ?\DateTimeImmutable $dateFinPrevue = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['activite_projet:read', 'activite_projet:write'])]
    private ?\DateTimeImmutable $dateDebutEffective = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['activite_projet:read', 'activite_projet:write'])]
    private ?\DateTimeImmutable $dateFinEffective = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    #[Groups(['activite_projet:read', 'activite_projet:write'])]
    private ?string $responsable = null;

    #[ORM\Column]
    #[Groups(['activite_projet:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['activite_projet:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getPhaseProjet(): ?PhaseProjet { return $this->phaseProjet; }
    public function setPhaseProjet(?PhaseProjet $phaseProjet): static { $this->phaseProjet = $phaseProjet; return $this; }
    public function getLibelle(): ?string { return $this->libelle; }
    public function setLibelle(string $libelle): static { $this->libelle = trim($libelle); return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function getTauxExecution(): ?float { return $this->tauxExecution; }
    public function setTauxExecution(float $tauxExecution): static { $this->tauxExecution = $tauxExecution; return $this; }
    public function getDateDebutPrevue(): ?\DateTimeImmutable { return $this->dateDebutPrevue; }
    public function setDateDebutPrevue(?\DateTimeImmutable $dateDebutPrevue): static { $this->dateDebutPrevue = $dateDebutPrevue; return $this; }
    public function getDateFinPrevue(): ?\DateTimeImmutable { return $this->dateFinPrevue; }
    public function setDateFinPrevue(?\DateTimeImmutable $dateFinPrevue): static { $this->dateFinPrevue = $dateFinPrevue; return $this; }
    public function getDateDebutEffective(): ?\DateTimeImmutable { return $this->dateDebutEffective; }
    public function setDateDebutEffective(?\DateTimeImmutable $dateDebutEffective): static { $this->dateDebutEffective = $dateDebutEffective; return $this; }
    public function getDateFinEffective(): ?\DateTimeImmutable { return $this->dateFinEffective; }
    public function setDateFinEffective(?\DateTimeImmutable $dateFinEffective): static { $this->dateFinEffective = $dateFinEffective; return $this; }
    public function getResponsable(): ?string { return $this->responsable; }
    public function setResponsable(?string $responsable): static { $this->responsable = $responsable; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
