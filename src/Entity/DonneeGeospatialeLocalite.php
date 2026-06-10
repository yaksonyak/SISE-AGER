<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DonneeGeospatialeLocaliteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DonneeGeospatialeLocaliteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['donnee_geospatiale_localite:read']],
    denormalizationContext: ['groups' => ['donnee_geospatiale_localite:write']]
)]
class DonneeGeospatialeLocalite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['donnee_geospatiale_localite:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'donneesGeospatiales')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['donnee_geospatiale_localite:read', 'donnee_geospatiale_localite:write'])]
    private ?Localite $localite = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['donnee_geospatiale_localite:read', 'donnee_geospatiale_localite:write'])]
    private ?float $superficieKm2 = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['donnee_geospatiale_localite:read', 'donnee_geospatiale_localite:write'])]
    private ?int $populationReference = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['donnee_geospatiale_localite:read', 'donnee_geospatiale_localite:write'])]
    private ?int $menagesReference = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['donnee_geospatiale_localite:read', 'donnee_geospatiale_localite:write'])]
    private ?float $densitePopulation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['donnee_geospatiale_localite:read', 'donnee_geospatiale_localite:write'])]
    private ?string $observations = null;

    #[ORM\Column]
    #[Groups(['donnee_geospatiale_localite:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['donnee_geospatiale_localite:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getLocalite(): ?Localite { return $this->localite; }
    public function setLocalite(?Localite $localite): static { $this->localite = $localite; return $this; }
    public function getSuperficieKm2(): ?float { return $this->superficieKm2; }
    public function setSuperficieKm2(?float $superficieKm2): static { $this->superficieKm2 = $superficieKm2; return $this; }
    public function getPopulationReference(): ?int { return $this->populationReference; }
    public function setPopulationReference(?int $populationReference): static { $this->populationReference = $populationReference; return $this; }
    public function getMenagesReference(): ?int { return $this->menagesReference; }
    public function setMenagesReference(?int $menagesReference): static { $this->menagesReference = $menagesReference; return $this; }
    public function getDensitePopulation(): ?float { return $this->densitePopulation; }
    public function setDensitePopulation(?float $densitePopulation): static { $this->densitePopulation = $densitePopulation; return $this; }
    public function getObservations(): ?string { return $this->observations; }
    public function setObservations(?string $observations): static { $this->observations = $observations; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
