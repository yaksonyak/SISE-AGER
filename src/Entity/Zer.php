<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ZerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ZerRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Une ZER existe déjà avec ce code.')]
#[ApiResource(
    normalizationContext: ['groups' => ['zer:read']],
    denormalizationContext: ['groups' => ['zer:write']]
)]
class Zer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['zer:read', 'prefecture:read', 'localite:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['zer:read', 'zer:write', 'prefecture:read', 'localite:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 30, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 30)]
    #[Groups(['zer:read', 'zer:write', 'prefecture:read', 'localite:read'])]
    private ?string $code = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['zer:read', 'zer:write'])]
    private ?float $superficieKm2 = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['zer:read', 'zer:write'])]
    private ?int $population = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['zer:read', 'zer:write'])]
    private ?int $nombreMenages = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['zer:read', 'zer:write'])]
    private ?int $nombreLocalites = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0, max: 100)]
    #[Groups(['zer:read', 'zer:write'])]
    private ?float $tauxLocalitesMoins800Habitants = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Groups(['zer:read', 'zer:write'])]
    private ?string $potentielSolaire = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Groups(['zer:read', 'zer:write'])]
    private ?string $potentielHydro = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['zer:read', 'zer:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['zer:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['zer:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, Prefecture> */
    #[ORM\OneToMany(mappedBy: 'zer', targetEntity: Prefecture::class)]
    private Collection $prefectures;

    /** @var Collection<int, Localite> */
    #[ORM\OneToMany(mappedBy: 'zer', targetEntity: Localite::class)]
    private Collection $localites;

    /** @var Collection<int, ProjetElectrification> */
    #[ORM\OneToMany(mappedBy: 'zer', targetEntity: ProjetElectrification::class)]
    private Collection $projetsElectrification;

    public function __construct() { $this->prefectures = new ArrayCollection(); $this->localites = new ArrayCollection(); $this->projetsElectrification = new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = trim($nom); return $this; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getSuperficieKm2(): ?float { return $this->superficieKm2; }
    public function setSuperficieKm2(?float $superficieKm2): static { $this->superficieKm2 = $superficieKm2; return $this; }
    public function getPopulation(): ?int { return $this->population; }
    public function setPopulation(?int $population): static { $this->population = $population; return $this; }
    public function getNombreMenages(): ?int { return $this->nombreMenages; }
    public function setNombreMenages(?int $nombreMenages): static { $this->nombreMenages = $nombreMenages; return $this; }
    public function getNombreLocalites(): ?int { return $this->nombreLocalites; }
    public function setNombreLocalites(?int $nombreLocalites): static { $this->nombreLocalites = $nombreLocalites; return $this; }
    public function getTauxLocalitesMoins800Habitants(): ?float { return $this->tauxLocalitesMoins800Habitants; }
    public function setTauxLocalitesMoins800Habitants(?float $tauxLocalitesMoins800Habitants): static { $this->tauxLocalitesMoins800Habitants = $tauxLocalitesMoins800Habitants; return $this; }
    public function getPotentielSolaire(): ?string { return $this->potentielSolaire; }
    public function setPotentielSolaire(?string $potentielSolaire): static { $this->potentielSolaire = $potentielSolaire; return $this; }
    public function getPotentielHydro(): ?string { return $this->potentielHydro; }
    public function setPotentielHydro(?string $potentielHydro): static { $this->potentielHydro = $potentielHydro; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    /** @return Collection<int, Prefecture> */
    public function getPrefectures(): Collection { return $this->prefectures; }
    public function addPrefecture(Prefecture $prefecture): static { if (!$this->prefectures->contains($prefecture)) { $this->prefectures->add($prefecture); $prefecture->setZer($this); } return $this; }
    public function removePrefecture(Prefecture $prefecture): static { if ($this->prefectures->removeElement($prefecture) && $prefecture->getZer() === $this) { $prefecture->setZer(null); } return $this; }
    /** @return Collection<int, Localite> */
    public function getLocalites(): Collection { return $this->localites; }
    public function addLocalite(Localite $localite): static { if (!$this->localites->contains($localite)) { $this->localites->add($localite); $localite->setZer($this); } return $this; }
    public function removeLocalite(Localite $localite): static { if ($this->localites->removeElement($localite) && $localite->getZer() === $this) { $localite->setZer(null); } return $this; }
    /** @return Collection<int, ProjetElectrification> */
    public function getProjetsElectrification(): Collection { return $this->projetsElectrification; }
    public function addProjetElectrification(ProjetElectrification $projetElectrification): static { if (!$this->projetsElectrification->contains($projetElectrification)) { $this->projetsElectrification->add($projetElectrification); $projetElectrification->setZer($this); } return $this; }
    public function removeProjetElectrification(ProjetElectrification $projetElectrification): static { if ($this->projetsElectrification->removeElement($projetElectrification) && $projetElectrification->getZer() === $this) { $projetElectrification->setZer(null); } return $this; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
