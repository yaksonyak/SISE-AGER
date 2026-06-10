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

    /** @var Collection<int, ValeurIndicateur> */
    #[ORM\OneToMany(mappedBy: 'projetElectrification', targetEntity: ValeurIndicateur::class)]
    private Collection $valeursIndicateur;

    /** @var Collection<int, RapportSuivi> */
    #[ORM\OneToMany(mappedBy: 'projetElectrification', targetEntity: RapportSuivi::class)]
    private Collection $rapportsSuivi;

    /** @var Collection<int, ObservationSuivi> */
    #[ORM\OneToMany(mappedBy: 'projetElectrification', targetEntity: ObservationSuivi::class)]
    private Collection $observationsSuivi;

    /** @var Collection<int, ConventionFinancement> */
    #[ORM\OneToMany(mappedBy: 'projetElectrification', targetEntity: ConventionFinancement::class)]
    private Collection $conventionsFinancement;

    /** @var Collection<int, Decaissement> */
    #[ORM\OneToMany(mappedBy: 'projetElectrification', targetEntity: Decaissement::class)]
    private Collection $decaissements;

    /** @var Collection<int, CoutPrevisionnel> */
    #[ORM\OneToMany(mappedBy: 'projetElectrification', targetEntity: CoutPrevisionnel::class)]
    private Collection $coutsPrevisionnels;

    /** @var Collection<int, ActionGenre> */
    #[ORM\OneToMany(mappedBy: 'projetElectrification', targetEntity: ActionGenre::class)]
    private Collection $actionsGenre;

    /** @var Collection<int, BeneficiaireGenre> */
    #[ORM\OneToMany(mappedBy: 'projetElectrification', targetEntity: BeneficiaireGenre::class)]
    private Collection $beneficiairesGenre;

    /** @var Collection<int, IndicateurGenre> */
    #[ORM\OneToMany(mappedBy: 'projetElectrification', targetEntity: IndicateurGenre::class)]
    private Collection $indicateursGenre;

    public function __construct() { $this->projetLocalites = new ArrayCollection(); $this->phasesProjet = new ArrayCollection(); $this->valeursIndicateur = new ArrayCollection(); $this->rapportsSuivi = new ArrayCollection(); $this->observationsSuivi = new ArrayCollection(); $this->conventionsFinancement = new ArrayCollection(); $this->decaissements = new ArrayCollection(); $this->coutsPrevisionnels = new ArrayCollection(); $this->actionsGenre = new ArrayCollection(); $this->beneficiairesGenre = new ArrayCollection(); $this->indicateursGenre = new ArrayCollection(); }
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
    /** @return Collection<int, ValeurIndicateur> */
    public function getValeursIndicateur(): Collection { return $this->valeursIndicateur; }
    public function addValeurIndicateur(ValeurIndicateur $valeurIndicateur): static { if (!$this->valeursIndicateur->contains($valeurIndicateur)) { $this->valeursIndicateur->add($valeurIndicateur); $valeurIndicateur->setProjetElectrification($this); } return $this; }
    public function removeValeurIndicateur(ValeurIndicateur $valeurIndicateur): static { if ($this->valeursIndicateur->removeElement($valeurIndicateur) && $valeurIndicateur->getProjetElectrification() === $this) { $valeurIndicateur->setProjetElectrification(null); } return $this; }
    /** @return Collection<int, RapportSuivi> */
    public function getRapportsSuivi(): Collection { return $this->rapportsSuivi; }
    public function addRapportSuivi(RapportSuivi $rapportSuivi): static { if (!$this->rapportsSuivi->contains($rapportSuivi)) { $this->rapportsSuivi->add($rapportSuivi); $rapportSuivi->setProjetElectrification($this); } return $this; }
    public function removeRapportSuivi(RapportSuivi $rapportSuivi): static { if ($this->rapportsSuivi->removeElement($rapportSuivi) && $rapportSuivi->getProjetElectrification() === $this) { $rapportSuivi->setProjetElectrification(null); } return $this; }
    /** @return Collection<int, ObservationSuivi> */
    public function getObservationsSuivi(): Collection { return $this->observationsSuivi; }
    public function addObservationSuivi(ObservationSuivi $observationSuivi): static { if (!$this->observationsSuivi->contains($observationSuivi)) { $this->observationsSuivi->add($observationSuivi); $observationSuivi->setProjetElectrification($this); } return $this; }
    public function removeObservationSuivi(ObservationSuivi $observationSuivi): static { if ($this->observationsSuivi->removeElement($observationSuivi) && $observationSuivi->getProjetElectrification() === $this) { $observationSuivi->setProjetElectrification(null); } return $this; }
    /** @return Collection<int, ConventionFinancement> */
    public function getConventionsFinancement(): Collection { return $this->conventionsFinancement; }
    public function addConventionFinancement(ConventionFinancement $conventionFinancement): static { if (!$this->conventionsFinancement->contains($conventionFinancement)) { $this->conventionsFinancement->add($conventionFinancement); $conventionFinancement->setProjetElectrification($this); } return $this; }
    public function removeConventionFinancement(ConventionFinancement $conventionFinancement): static { if ($this->conventionsFinancement->removeElement($conventionFinancement) && $conventionFinancement->getProjetElectrification() === $this) { $conventionFinancement->setProjetElectrification(null); } return $this; }
    /** @return Collection<int, Decaissement> */
    public function getDecaissements(): Collection { return $this->decaissements; }
    public function addDecaissement(Decaissement $decaissement): static { if (!$this->decaissements->contains($decaissement)) { $this->decaissements->add($decaissement); $decaissement->setProjetElectrification($this); } return $this; }
    public function removeDecaissement(Decaissement $decaissement): static { if ($this->decaissements->removeElement($decaissement) && $decaissement->getProjetElectrification() === $this) { $decaissement->setProjetElectrification(null); } return $this; }
    /** @return Collection<int, CoutPrevisionnel> */
    public function getCoutsPrevisionnels(): Collection { return $this->coutsPrevisionnels; }
    public function addCoutPrevisionnel(CoutPrevisionnel $coutPrevisionnel): static { if (!$this->coutsPrevisionnels->contains($coutPrevisionnel)) { $this->coutsPrevisionnels->add($coutPrevisionnel); $coutPrevisionnel->setProjetElectrification($this); } return $this; }
    public function removeCoutPrevisionnel(CoutPrevisionnel $coutPrevisionnel): static { if ($this->coutsPrevisionnels->removeElement($coutPrevisionnel) && $coutPrevisionnel->getProjetElectrification() === $this) { $coutPrevisionnel->setProjetElectrification(null); } return $this; }
    /** @return Collection<int, ActionGenre> */
    public function getActionsGenre(): Collection { return $this->actionsGenre; }
    public function addActionGenre(ActionGenre $actionGenre): static { if (!$this->actionsGenre->contains($actionGenre)) { $this->actionsGenre->add($actionGenre); $actionGenre->setProjetElectrification($this); } return $this; }
    public function removeActionGenre(ActionGenre $actionGenre): static { if ($this->actionsGenre->removeElement($actionGenre) && $actionGenre->getProjetElectrification() === $this) { $actionGenre->setProjetElectrification(null); } return $this; }
    /** @return Collection<int, BeneficiaireGenre> */
    public function getBeneficiairesGenre(): Collection { return $this->beneficiairesGenre; }
    /** @return Collection<int, IndicateurGenre> */
    public function getIndicateursGenre(): Collection { return $this->indicateursGenre; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
