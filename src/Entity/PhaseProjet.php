<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PhaseProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PhaseProjetRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['phase_projet:read']],
    denormalizationContext: ['groups' => ['phase_projet:write']]
)]
class PhaseProjet
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
    #[Groups(['phase_projet:read', 'activite_projet:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'phasesProjet')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['phase_projet:read', 'phase_projet:write'])]
    private ?ProjetElectrification $projetElectrification = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['phase_projet:read', 'phase_projet:write', 'activite_projet:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['phase_projet:read', 'phase_projet:write', 'activite_projet:read'])]
    private ?string $code = null;

    #[ORM\Column]
    #[Assert\Positive]
    #[Groups(['phase_projet:read', 'phase_projet:write'])]
    private ?int $ordre = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::STATUTS)]
    #[Groups(['phase_projet:read', 'phase_projet:write'])]
    private ?string $statut = self::STATUT_PLANIFIE;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['phase_projet:read', 'phase_projet:write'])]
    private ?\DateTimeImmutable $dateDebutPrevue = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['phase_projet:read', 'phase_projet:write'])]
    private ?\DateTimeImmutable $dateFinPrevue = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['phase_projet:read', 'phase_projet:write'])]
    private ?\DateTimeImmutable $dateDebutEffective = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['phase_projet:read', 'phase_projet:write'])]
    private ?\DateTimeImmutable $dateFinEffective = null;

    #[ORM\Column]
    #[Groups(['phase_projet:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['phase_projet:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, ActiviteProjet> */
    #[ORM\OneToMany(mappedBy: 'phaseProjet', targetEntity: ActiviteProjet::class, orphanRemoval: true)]
    private Collection $activitesProjet;

    public function __construct() { $this->activitesProjet = new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getProjetElectrification(): ?ProjetElectrification { return $this->projetElectrification; }
    public function setProjetElectrification(?ProjetElectrification $projetElectrification): static { $this->projetElectrification = $projetElectrification; return $this; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = trim($nom); return $this; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getOrdre(): ?int { return $this->ordre; }
    public function setOrdre(int $ordre): static { $this->ordre = $ordre; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function getDateDebutPrevue(): ?\DateTimeImmutable { return $this->dateDebutPrevue; }
    public function setDateDebutPrevue(?\DateTimeImmutable $dateDebutPrevue): static { $this->dateDebutPrevue = $dateDebutPrevue; return $this; }
    public function getDateFinPrevue(): ?\DateTimeImmutable { return $this->dateFinPrevue; }
    public function setDateFinPrevue(?\DateTimeImmutable $dateFinPrevue): static { $this->dateFinPrevue = $dateFinPrevue; return $this; }
    public function getDateDebutEffective(): ?\DateTimeImmutable { return $this->dateDebutEffective; }
    public function setDateDebutEffective(?\DateTimeImmutable $dateDebutEffective): static { $this->dateDebutEffective = $dateDebutEffective; return $this; }
    public function getDateFinEffective(): ?\DateTimeImmutable { return $this->dateFinEffective; }
    public function setDateFinEffective(?\DateTimeImmutable $dateFinEffective): static { $this->dateFinEffective = $dateFinEffective; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    /** @return Collection<int, ActiviteProjet> */
    public function getActivitesProjet(): Collection { return $this->activitesProjet; }
    public function addActiviteProjet(ActiviteProjet $activiteProjet): static { if (!$this->activitesProjet->contains($activiteProjet)) { $this->activitesProjet->add($activiteProjet); $activiteProjet->setPhaseProjet($this); } return $this; }
    public function removeActiviteProjet(ActiviteProjet $activiteProjet): static { if ($this->activitesProjet->removeElement($activiteProjet) && $activiteProjet->getPhaseProjet() === $this) { $activiteProjet->setPhaseProjet(null); } return $this; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
