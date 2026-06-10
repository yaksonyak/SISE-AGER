<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ActionGenreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActionGenreRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Une action genre existe déjà avec ce code.')]
#[ApiResource(normalizationContext: ['groups' => ['action_genre:read']], denormalizationContext: ['groups' => ['action_genre:write']])]
class ActionGenre
{
    public const AXE_INSTITUTIONNALISATION_GENRE = 'INSTITUTIONNALISATION_GENRE';
    public const AXE_INTEGRATION_PROJETS = 'INTEGRATION_PROJETS';
    public const AXE_SENSIBILISATION_COMMUNICATION = 'SENSIBILISATION_COMMUNICATION';
    public const AXE_RENFORCEMENT_CAPACITES = 'RENFORCEMENT_CAPACITES';
    public const AXE_ENTREPRENARIAT_FEMININ = 'ENTREPRENARIAT_FEMININ';
    public const AXE_SUIVI_EVALUATION_GENRE = 'SUIVI_EVALUATION_GENRE';
    public const AXES = [self::AXE_INSTITUTIONNALISATION_GENRE, self::AXE_INTEGRATION_PROJETS, self::AXE_SENSIBILISATION_COMMUNICATION, self::AXE_RENFORCEMENT_CAPACITES, self::AXE_ENTREPRENARIAT_FEMININ, self::AXE_SUIVI_EVALUATION_GENRE];
    public const STATUT_PLANIFIEE = 'PLANIFIEE';
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_REALISEE = 'REALISEE';
    public const STATUT_SUSPENDUE = 'SUSPENDUE';
    public const STATUT_ANNULEE = 'ANNULEE';
    public const STATUTS = [self::STATUT_PLANIFIEE, self::STATUT_EN_COURS, self::STATUT_REALISEE, self::STATUT_SUSPENDUE, self::STATUT_ANNULEE];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['action_genre:read', 'beneficiaire_genre:read', 'formation_genre:read', 'indicateur_genre:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['action_genre:read', 'action_genre:write', 'beneficiaire_genre:read', 'formation_genre:read', 'indicateur_genre:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[Groups(['action_genre:read', 'action_genre:write', 'beneficiaire_genre:read', 'formation_genre:read', 'indicateur_genre:read'])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['action_genre:read', 'action_genre:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::AXES)]
    #[Groups(['action_genre:read', 'action_genre:write'])]
    private ?string $axeStrategique = null;

    #[ORM\ManyToOne(inversedBy: 'actionsGenre')]
    #[Groups(['action_genre:read', 'action_genre:write'])]
    private ?ProgrammePner $programmePner = null;

    #[ORM\ManyToOne(inversedBy: 'actionsGenre')]
    #[Groups(['action_genre:read', 'action_genre:write'])]
    private ?ProjetElectrification $projetElectrification = null;

    #[ORM\ManyToOne(inversedBy: 'actionsGenre')]
    #[Groups(['action_genre:read', 'action_genre:write'])]
    private ?Zer $zer = null;

    #[ORM\ManyToOne(inversedBy: 'actionsGenre')]
    #[Groups(['action_genre:read', 'action_genre:write'])]
    private ?Localite $localite = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::STATUTS)]
    #[Groups(['action_genre:read', 'action_genre:write'])]
    private ?string $statut = self::STATUT_PLANIFIEE;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['action_genre:read', 'action_genre:write'])]
    private ?\DateTimeImmutable $dateDebutPrevue = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['action_genre:read', 'action_genre:write'])]
    private ?\DateTimeImmutable $dateFinPrevue = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['action_genre:read', 'action_genre:write'])]
    private ?\DateTimeImmutable $dateDebutEffective = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['action_genre:read', 'action_genre:write'])]
    private ?\DateTimeImmutable $dateFinEffective = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    #[Groups(['action_genre:read', 'action_genre:write'])]
    private ?string $responsable = null;

    #[ORM\Column]
    #[Groups(['action_genre:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['action_genre:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, BeneficiaireGenre> */
    #[ORM\OneToMany(mappedBy: 'actionGenre', targetEntity: BeneficiaireGenre::class)]
    private Collection $beneficiairesGenre;

    /** @var Collection<int, FormationGenre> */
    #[ORM\OneToMany(mappedBy: 'actionGenre', targetEntity: FormationGenre::class)]
    private Collection $formationsGenre;

    /** @var Collection<int, IndicateurGenre> */
    #[ORM\OneToMany(mappedBy: 'actionGenre', targetEntity: IndicateurGenre::class)]
    private Collection $indicateursGenre;

    public function __construct() { $this->beneficiairesGenre = new ArrayCollection(); $this->formationsGenre = new ArrayCollection(); $this->indicateursGenre = new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = trim($titre); return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getAxeStrategique(): ?string { return $this->axeStrategique; }
    public function setAxeStrategique(string $axeStrategique): static { $this->axeStrategique = $axeStrategique; return $this; }
    public function getProgrammePner(): ?ProgrammePner { return $this->programmePner; }
    public function setProgrammePner(?ProgrammePner $programmePner): static { $this->programmePner = $programmePner; return $this; }
    public function getProjetElectrification(): ?ProjetElectrification { return $this->projetElectrification; }
    public function setProjetElectrification(?ProjetElectrification $projetElectrification): static { $this->projetElectrification = $projetElectrification; return $this; }
    public function getZer(): ?Zer { return $this->zer; }
    public function setZer(?Zer $zer): static { $this->zer = $zer; return $this; }
    public function getLocalite(): ?Localite { return $this->localite; }
    public function setLocalite(?Localite $localite): static { $this->localite = $localite; return $this; }
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
    public function getResponsable(): ?string { return $this->responsable; }
    public function setResponsable(?string $responsable): static { $this->responsable = $responsable; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    /** @return Collection<int, BeneficiaireGenre> */ public function getBeneficiairesGenre(): Collection { return $this->beneficiairesGenre; }
    /** @return Collection<int, FormationGenre> */ public function getFormationsGenre(): Collection { return $this->formationsGenre; }
    /** @return Collection<int, IndicateurGenre> */ public function getIndicateursGenre(): Collection { return $this->indicateursGenre; }
    #[ORM\PrePersist] public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate] public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
