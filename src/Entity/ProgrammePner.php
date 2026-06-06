<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProgrammePnerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProgrammePnerRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Un programme PNER existe déjà avec ce code.')]
#[ApiResource(
    normalizationContext: ['groups' => ['programme_pner:read']],
    denormalizationContext: ['groups' => ['programme_pner:write']]
)]
class ProgrammePner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['programme_pner:read', 'localite:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['programme_pner:read', 'programme_pner:write', 'localite:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 30, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 30)]
    #[Groups(['programme_pner:read', 'programme_pner:write', 'localite:read'])]
    private ?string $code = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Range(min: 2000, max: 2100)]
    #[Groups(['programme_pner:read', 'programme_pner:write'])]
    private ?int $periodeDebut = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Range(min: 2000, max: 2100)]
    #[Groups(['programme_pner:read', 'programme_pner:write'])]
    private ?int $periodeFin = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['programme_pner:read', 'programme_pner:write'])]
    private ?int $nombreLocalitesPrevues = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0, max: 100)]
    #[Groups(['programme_pner:read', 'programme_pner:write'])]
    private ?float $tauxElectrificationCible = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['programme_pner:read', 'programme_pner:write'])]
    private ?float $montantPrevisionnelUsd = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['programme_pner:read', 'programme_pner:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['programme_pner:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['programme_pner:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Localite>
     */
    #[ORM\OneToMany(mappedBy: 'programmePner', targetEntity: Localite::class)]
    private Collection $localites;

    /** @var Collection<int, ProjetElectrification> */
    #[ORM\OneToMany(mappedBy: 'programmePner', targetEntity: ProjetElectrification::class)]
    private Collection $projetsElectrification;

    /** @var Collection<int, ValeurIndicateur> */
    #[ORM\OneToMany(mappedBy: 'programmePner', targetEntity: ValeurIndicateur::class)]
    private Collection $valeursIndicateur;

    /** @var Collection<int, RapportSuivi> */
    #[ORM\OneToMany(mappedBy: 'programmePner', targetEntity: RapportSuivi::class)]
    private Collection $rapportsSuivi;

    /** @var Collection<int, ConventionFinancement> */
    #[ORM\OneToMany(mappedBy: 'programmePner', targetEntity: ConventionFinancement::class)]
    private Collection $conventionsFinancement;

    /** @var Collection<int, CoutPrevisionnel> */
    #[ORM\OneToMany(mappedBy: 'programmePner', targetEntity: CoutPrevisionnel::class)]
    private Collection $coutsPrevisionnels;

    public function __construct()
    {
        $this->localites = new ArrayCollection();
        $this->projetsElectrification = new ArrayCollection();
        $this->valeursIndicateur = new ArrayCollection();
        $this->rapportsSuivi = new ArrayCollection();
        $this->conventionsFinancement = new ArrayCollection();
        $this->coutsPrevisionnels = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = trim($nom); return $this; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getPeriodeDebut(): ?int { return $this->periodeDebut; }
    public function setPeriodeDebut(int $periodeDebut): static { $this->periodeDebut = $periodeDebut; return $this; }
    public function getPeriodeFin(): ?int { return $this->periodeFin; }
    public function setPeriodeFin(int $periodeFin): static { $this->periodeFin = $periodeFin; return $this; }
    public function getNombreLocalitesPrevues(): ?int { return $this->nombreLocalitesPrevues; }
    public function setNombreLocalitesPrevues(?int $nombreLocalitesPrevues): static { $this->nombreLocalitesPrevues = $nombreLocalitesPrevues; return $this; }
    public function getTauxElectrificationCible(): ?float { return $this->tauxElectrificationCible; }
    public function setTauxElectrificationCible(?float $tauxElectrificationCible): static { $this->tauxElectrificationCible = $tauxElectrificationCible; return $this; }
    public function getMontantPrevisionnelUsd(): ?float { return $this->montantPrevisionnelUsd; }
    public function setMontantPrevisionnelUsd(?float $montantPrevisionnelUsd): static { $this->montantPrevisionnelUsd = $montantPrevisionnelUsd; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    /** @return Collection<int, Localite> */
    public function getLocalites(): Collection { return $this->localites; }
    public function addLocalite(Localite $localite): static { if (!$this->localites->contains($localite)) { $this->localites->add($localite); $localite->setProgrammePner($this); } return $this; }
    public function removeLocalite(Localite $localite): static { if ($this->localites->removeElement($localite) && $localite->getProgrammePner() === $this) { $localite->setProgrammePner(null); } return $this; }
    /** @return Collection<int, ProjetElectrification> */
    public function getProjetsElectrification(): Collection { return $this->projetsElectrification; }
    public function addProjetElectrification(ProjetElectrification $projetElectrification): static { if (!$this->projetsElectrification->contains($projetElectrification)) { $this->projetsElectrification->add($projetElectrification); $projetElectrification->setProgrammePner($this); } return $this; }
    public function removeProjetElectrification(ProjetElectrification $projetElectrification): static { if ($this->projetsElectrification->removeElement($projetElectrification) && $projetElectrification->getProgrammePner() === $this) { $projetElectrification->setProgrammePner(null); } return $this; }
    /** @return Collection<int, ValeurIndicateur> */
    public function getValeursIndicateur(): Collection { return $this->valeursIndicateur; }
    public function addValeurIndicateur(ValeurIndicateur $valeurIndicateur): static { if (!$this->valeursIndicateur->contains($valeurIndicateur)) { $this->valeursIndicateur->add($valeurIndicateur); $valeurIndicateur->setProgrammePner($this); } return $this; }
    public function removeValeurIndicateur(ValeurIndicateur $valeurIndicateur): static { if ($this->valeursIndicateur->removeElement($valeurIndicateur) && $valeurIndicateur->getProgrammePner() === $this) { $valeurIndicateur->setProgrammePner(null); } return $this; }
    /** @return Collection<int, RapportSuivi> */
    public function getRapportsSuivi(): Collection { return $this->rapportsSuivi; }
    public function addRapportSuivi(RapportSuivi $rapportSuivi): static { if (!$this->rapportsSuivi->contains($rapportSuivi)) { $this->rapportsSuivi->add($rapportSuivi); $rapportSuivi->setProgrammePner($this); } return $this; }
    public function removeRapportSuivi(RapportSuivi $rapportSuivi): static { if ($this->rapportsSuivi->removeElement($rapportSuivi) && $rapportSuivi->getProgrammePner() === $this) { $rapportSuivi->setProgrammePner(null); } return $this; }
    /** @return Collection<int, ConventionFinancement> */
    public function getConventionsFinancement(): Collection { return $this->conventionsFinancement; }
    public function addConventionFinancement(ConventionFinancement $conventionFinancement): static { if (!$this->conventionsFinancement->contains($conventionFinancement)) { $this->conventionsFinancement->add($conventionFinancement); $conventionFinancement->setProgrammePner($this); } return $this; }
    public function removeConventionFinancement(ConventionFinancement $conventionFinancement): static { if ($this->conventionsFinancement->removeElement($conventionFinancement) && $conventionFinancement->getProgrammePner() === $this) { $conventionFinancement->setProgrammePner(null); } return $this; }
    /** @return Collection<int, CoutPrevisionnel> */
    public function getCoutsPrevisionnels(): Collection { return $this->coutsPrevisionnels; }
    public function addCoutPrevisionnel(CoutPrevisionnel $coutPrevisionnel): static { if (!$this->coutsPrevisionnels->contains($coutPrevisionnel)) { $this->coutsPrevisionnels->add($coutPrevisionnel); $coutPrevisionnel->setProgrammePner($this); } return $this; }
    public function removeCoutPrevisionnel(CoutPrevisionnel $coutPrevisionnel): static { if ($this->coutsPrevisionnels->removeElement($coutPrevisionnel) && $coutPrevisionnel->getProgrammePner() === $this) { $coutPrevisionnel->setProgrammePner(null); } return $this; }

    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
