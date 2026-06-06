<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\InfrastructureElectriqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InfrastructureElectriqueRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Une infrastructure électrique existe déjà avec ce code.')]
#[ApiResource(
    normalizationContext: ['groups' => ['infrastructure_electrique:read']],
    denormalizationContext: ['groups' => ['infrastructure_electrique:write']]
)]
class InfrastructureElectrique
{
    public const TYPE_POSTE_SOURCE = 'POSTE_SOURCE';
    public const TYPE_POSTE_TRANSFORMATION = 'POSTE_TRANSFORMATION';
    public const TYPE_LIGNE_MT = 'LIGNE_MT';
    public const TYPE_LIGNE_BT = 'LIGNE_BT';
    public const TYPE_MINI_RESEAU = 'MINI_RESEAU';
    public const TYPE_CENTRALE_SOLAIRE = 'CENTRALE_SOLAIRE';
    public const TYPE_CENTRALE_HYDRO = 'CENTRALE_HYDRO';
    public const TYPE_BATTERIE_STOCKAGE = 'BATTERIE_STOCKAGE';
    public const TYPES = [self::TYPE_POSTE_SOURCE, self::TYPE_POSTE_TRANSFORMATION, self::TYPE_LIGNE_MT, self::TYPE_LIGNE_BT, self::TYPE_MINI_RESEAU, self::TYPE_CENTRALE_SOLAIRE, self::TYPE_CENTRALE_HYDRO, self::TYPE_BATTERIE_STOCKAGE];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['infrastructure_electrique:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['infrastructure_electrique:read', 'infrastructure_electrique:write'])]
    private ?string $code = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['infrastructure_electrique:read', 'infrastructure_electrique:write'])]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::TYPES)]
    #[Groups(['infrastructure_electrique:read', 'infrastructure_electrique:write'])]
    private ?string $typeInfrastructure = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['infrastructure_electrique:read', 'infrastructure_electrique:write'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'infrastructuresElectriques')]
    #[Groups(['infrastructure_electrique:read', 'infrastructure_electrique:write'])]
    private ?PointGps $pointGps = null;

    #[ORM\ManyToOne(inversedBy: 'infrastructuresElectriques')]
    #[Groups(['infrastructure_electrique:read', 'infrastructure_electrique:write'])]
    private ?Localite $localite = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['infrastructure_electrique:read', 'infrastructure_electrique:write'])]
    private ?string $statut = null;

    #[ORM\Column]
    #[Groups(['infrastructure_electrique:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['infrastructure_electrique:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = trim($nom); return $this; }
    public function getTypeInfrastructure(): ?string { return $this->typeInfrastructure; }
    public function setTypeInfrastructure(string $typeInfrastructure): static { $this->typeInfrastructure = $typeInfrastructure; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getPointGps(): ?PointGps { return $this->pointGps; }
    public function setPointGps(?PointGps $pointGps): static { $this->pointGps = $pointGps; return $this; }
    public function getLocalite(): ?Localite { return $this->localite; }
    public function setLocalite(?Localite $localite): static { $this->localite = $localite; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = trim($statut); return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
