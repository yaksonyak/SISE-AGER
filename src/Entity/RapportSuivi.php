<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\RapportSuiviRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RapportSuiviRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Un rapport de suivi existe déjà avec ce code.')]
#[ApiResource(
    normalizationContext: ['groups' => ['rapport_suivi:read']],
    denormalizationContext: ['groups' => ['rapport_suivi:write']]
)]
class RapportSuivi
{
    public const TYPE_RAPPORT_MENSUEL = 'RAPPORT_MENSUEL';
    public const TYPE_RAPPORT_TRIMESTRIEL = 'RAPPORT_TRIMESTRIEL';
    public const TYPE_RAPPORT_SEMESTRIEL = 'RAPPORT_SEMESTRIEL';
    public const TYPE_RAPPORT_ANNUEL = 'RAPPORT_ANNUEL';
    public const TYPE_RAPPORT_PROJET = 'RAPPORT_PROJET';
    public const TYPE_RAPPORT_ZER = 'RAPPORT_ZER';
    public const TYPES = [self::TYPE_RAPPORT_MENSUEL, self::TYPE_RAPPORT_TRIMESTRIEL, self::TYPE_RAPPORT_SEMESTRIEL, self::TYPE_RAPPORT_ANNUEL, self::TYPE_RAPPORT_PROJET, self::TYPE_RAPPORT_ZER];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['rapport_suivi:read', 'observation_suivi:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['rapport_suivi:read', 'rapport_suivi:write', 'observation_suivi:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[Groups(['rapport_suivi:read', 'rapport_suivi:write', 'observation_suivi:read'])]
    private ?string $titre = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::TYPES)]
    #[Groups(['rapport_suivi:read', 'rapport_suivi:write'])]
    private ?string $typeRapport = null;

    #[ORM\ManyToOne(inversedBy: 'rapportsSuivi')]
    #[Groups(['rapport_suivi:read', 'rapport_suivi:write'])]
    private ?ProgrammePner $programmePner = null;

    #[ORM\ManyToOne(inversedBy: 'rapportsSuivi')]
    #[Groups(['rapport_suivi:read', 'rapport_suivi:write'])]
    private ?Zer $zer = null;

    #[ORM\ManyToOne(inversedBy: 'rapportsSuivi')]
    #[Groups(['rapport_suivi:read', 'rapport_suivi:write'])]
    private ?ProjetElectrification $projetElectrification = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    #[Groups(['rapport_suivi:read', 'rapport_suivi:write'])]
    private ?\DateTimeImmutable $periodeDebut = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    #[Groups(['rapport_suivi:read', 'rapport_suivi:write'])]
    private ?\DateTimeImmutable $periodeFin = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['rapport_suivi:read', 'rapport_suivi:write'])]
    private ?string $resume = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['rapport_suivi:read', 'rapport_suivi:write'])]
    private ?string $recommandations = null;

    #[ORM\Column]
    #[Groups(['rapport_suivi:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['rapport_suivi:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, ObservationSuivi> */
    #[ORM\OneToMany(mappedBy: 'rapportSuivi', targetEntity: ObservationSuivi::class)]
    private Collection $observationsSuivi;

    public function __construct() { $this->observationsSuivi = new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = trim($titre); return $this; }
    public function getTypeRapport(): ?string { return $this->typeRapport; }
    public function setTypeRapport(string $typeRapport): static { $this->typeRapport = $typeRapport; return $this; }
    public function getProgrammePner(): ?ProgrammePner { return $this->programmePner; }
    public function setProgrammePner(?ProgrammePner $programmePner): static { $this->programmePner = $programmePner; return $this; }
    public function getZer(): ?Zer { return $this->zer; }
    public function setZer(?Zer $zer): static { $this->zer = $zer; return $this; }
    public function getProjetElectrification(): ?ProjetElectrification { return $this->projetElectrification; }
    public function setProjetElectrification(?ProjetElectrification $projetElectrification): static { $this->projetElectrification = $projetElectrification; return $this; }
    public function getPeriodeDebut(): ?\DateTimeImmutable { return $this->periodeDebut; }
    public function setPeriodeDebut(\DateTimeImmutable $periodeDebut): static { $this->periodeDebut = $periodeDebut; return $this; }
    public function getPeriodeFin(): ?\DateTimeImmutable { return $this->periodeFin; }
    public function setPeriodeFin(\DateTimeImmutable $periodeFin): static { $this->periodeFin = $periodeFin; return $this; }
    public function getResume(): ?string { return $this->resume; }
    public function setResume(?string $resume): static { $this->resume = $resume; return $this; }
    public function getRecommandations(): ?string { return $this->recommandations; }
    public function setRecommandations(?string $recommandations): static { $this->recommandations = $recommandations; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    /** @return Collection<int, ObservationSuivi> */
    public function getObservationsSuivi(): Collection { return $this->observationsSuivi; }
    public function addObservationSuivi(ObservationSuivi $observationSuivi): static { if (!$this->observationsSuivi->contains($observationSuivi)) { $this->observationsSuivi->add($observationSuivi); $observationSuivi->setRapportSuivi($this); } return $this; }
    public function removeObservationSuivi(ObservationSuivi $observationSuivi): static { if ($this->observationsSuivi->removeElement($observationSuivi) && $observationSuivi->getRapportSuivi() === $this) { $observationSuivi->setRapportSuivi(null); } return $this; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
