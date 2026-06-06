<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProjetElectrificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjetElectrificationRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Un projet d’électrification existe déjà avec ce code.')]
#[ApiResource(
    normalizationContext: ['groups' => ['projet_electrification:read']],
    denormalizationContext: ['groups' => ['projet_electrification:write']]
)]
class ProjetElectrification
{
    public const STATUT_PLANIFIE = 'PLANIFIE';
    public const STATUT_EN_PREPARATION = 'EN_PREPARATION';
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_SUSPENDU = 'SUSPENDU';
    public const STATUT_TERMINE = 'TERMINE';
    public const STATUT_ANNULE = 'ANNULE';

    public const STATUTS = [
        self::STATUT_PLANIFIE,
        self::STATUT_EN_PREPARATION,
        self::STATUT_EN_COURS,
        self::STATUT_SUSPENDU,
        self::STATUT_TERMINE,
        self::STATUT_ANNULE,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['projet_electrification:read', 'projet_localite:read', 'phase_projet:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['projet_electrification:read', 'projet_electrification:write', 'projet_localite:read', 'phase_projet:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[Groups(['projet_electrification:read', 'projet_electrification:write', 'projet_localite:read', 'phase_projet:read'])]
    private ?string $intitule = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['projet_electrification:read', 'projet_electrification:write'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'projetsElectrification')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['projet_electrification:read', 'projet_electrification:write'])]
    private ?ProgrammePner $programmePner = null;

    #[ORM\ManyToOne(inversedBy: 'projetsElectrification')]
    #[Groups(['projet_electrification:read', 'projet_electrification:write'])]
    private ?Zer $zer = null;

    #[ORM\ManyToOne(inversedBy: 'projetsElectrification')]
    #[Groups(['projet_electrification:read', 'projet_electrification:write'])]
    private ?SystemeElectrification $systemeElectrification = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::STATUTS)]
    #[Groups(['projet_electrification:read', 'projet_electrification:write'])]
    private ?string $statut = self::STATUT_PLANIFIE;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    #[Groups(['projet_electrification:read', 'projet_electrification:write'])]
    private ?\DateTimeImmutable $dateDebutPrevue = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    #[Groups(['projet_electrification:read', 'projet_electrification:write'])]
    private ?\DateTimeImmutable $dateFinPrevue = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['projet_electrification:read', 'projet_electrification:write'])]
    private ?\DateTimeImmutable $dateDebutEffective = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['projet_electrification:read', 'projet_electrification:write'])]
    private ?\DateTimeImmutable $dateFinEffective = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['projet_electrification:read', 'projet_electrification:write'])]
    private ?float $montantPrevisionnelUsd = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['projet_electrification:read', 'projet_electrification:write'])]
    private ?float $montantMobiliseUsd = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    #[Groups(['projet_electrification:read', 'projet_electrification:write'])]
    private ?string $sourceFinancement = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    #[Groups(['projet_electrification:read', 'projet_electrification:write'])]
    private ?string $maitreOuvrage = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    #[Groups(['projet_electrification:read', 'projet_electrification:write'])]
    private ?string $partenaireTechnique = null;

    #[ORM\Column]
    #[Groups(['projet_electrification:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['projet_electrification:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, ProjetLocalite> */
    #[ORM\OneToMany(mappedBy: 'projetElectrification', targetEntity: ProjetLocalite::class, orphanRemoval: true)]
    private Collection $projetLocalites;

    /** @var Collection<int, PhaseProjet> */
    #[ORM\OneToMany(mappedBy: 'projetElectrification', targetEntity: PhaseProjet::class, orphanRemoval: true)]
    private Collection $phasesProjet;

    public function __construct() { $this->projetLocalites = new ArrayCollection(); $this->phasesProjet = new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getIntitule(): ?string { return $this->intitule; }
    public function setIntitule(string $intitule): static { $this->intitule = trim($intitule); return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getProgrammePner(): ?ProgrammePner { return $this->programmePner; }
    public function setProgrammePner(?ProgrammePner $programmePner): static { $this->programmePner = $programmePner; return $this; }
    public function getZer(): ?Zer { return $this->zer; }
    public function setZer(?Zer $zer): static { $this->zer = $zer; return $this; }
    public function getSystemeElectrification(): ?SystemeElectrification { return $this->systemeElectrification; }
    public function setSystemeElectrification(?SystemeElectrification $systemeElectrification): static { $this->systemeElectrification = $systemeElectrification; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function getDateDebutPrevue(): ?\DateTimeImmutable { return $this->dateDebutPrevue; }
    public function setDateDebutPrevue(\DateTimeImmutable $dateDebutPrevue): static { $this->dateDebutPrevue = $dateDebutPrevue; return $this; }
    public function getDateFinPrevue(): ?\DateTimeImmutable { return $this->dateFinPrevue; }
    public function setDateFinPrevue(\DateTimeImmutable $dateFinPrevue): static { $this->dateFinPrevue = $dateFinPrevue; return $this; }
    public function getDateDebutEffective(): ?\DateTimeImmutable { return $this->dateDebutEffective; }
    public function setDateDebutEffective(?\DateTimeImmutable $dateDebutEffective): static { $this->dateDebutEffective = $dateDebutEffective; return $this; }
    public function getDateFinEffective(): ?\DateTimeImmutable { return $this->dateFinEffective; }
    public function setDateFinEffective(?\DateTimeImmutable $dateFinEffective): static { $this->dateFinEffective = $dateFinEffective; return $this; }
    public function getMontantPrevisionnelUsd(): ?float { return $this->montantPrevisionnelUsd; }
    public function setMontantPrevisionnelUsd(?float $montantPrevisionnelUsd): static { $this->montantPrevisionnelUsd = $montantPrevisionnelUsd; return $this; }
    public function getMontantMobiliseUsd(): ?float { return $this->montantMobiliseUsd; }
    public function setMontantMobiliseUsd(?float $montantMobiliseUsd): static { $this->montantMobiliseUsd = $montantMobiliseUsd; return $this; }
    public function getSourceFinancement(): ?string { return $this->sourceFinancement; }
    public function setSourceFinancement(?string $sourceFinancement): static { $this->sourceFinancement = $sourceFinancement; return $this; }
    public function getMaitreOuvrage(): ?string { return $this->maitreOuvrage; }
    public function setMaitreOuvrage(?string $maitreOuvrage): static { $this->maitreOuvrage = $maitreOuvrage; return $this; }
    public function getPartenaireTechnique(): ?string { return $this->partenaireTechnique; }
    public function setPartenaireTechnique(?string $partenaireTechnique): static { $this->partenaireTechnique = $partenaireTechnique; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    /** @return Collection<int, ProjetLocalite> */
    public function getProjetLocalites(): Collection { return $this->projetLocalites; }
    public function addProjetLocalite(ProjetLocalite $projetLocalite): static { if (!$this->projetLocalites->contains($projetLocalite)) { $this->projetLocalites->add($projetLocalite); $projetLocalite->setProjetElectrification($this); } return $this; }
    public function removeProjetLocalite(ProjetLocalite $projetLocalite): static { if ($this->projetLocalites->removeElement($projetLocalite) && $projetLocalite->getProjetElectrification() === $this) { $projetLocalite->setProjetElectrification(null); } return $this; }
    /** @return Collection<int, PhaseProjet> */
    public function getPhasesProjet(): Collection { return $this->phasesProjet; }
    public function addPhaseProjet(PhaseProjet $phaseProjet): static { if (!$this->phasesProjet->contains($phaseProjet)) { $this->phasesProjet->add($phaseProjet); $phaseProjet->setProjetElectrification($this); } return $this; }
    public function removePhaseProjet(PhaseProjet $phaseProjet): static { if ($this->phasesProjet->removeElement($phaseProjet) && $phaseProjet->getProjetElectrification() === $this) { $phaseProjet->setProjetElectrification(null); } return $this; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
