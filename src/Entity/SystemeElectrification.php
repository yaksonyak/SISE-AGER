<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SystemeElectrificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SystemeElectrificationRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Un système d’électrification existe déjà avec ce code.')]
#[ApiResource(
    normalizationContext: ['groups' => ['systeme_electrification:read']],
    denormalizationContext: ['groups' => ['systeme_electrification:write']]
)]
class SystemeElectrification
{
    public const TYPE_EXTENSION_RESEAU_MT_30KV = 'EXTENSION_RESEAU_MT_30KV';
    public const TYPE_SOLAIRE_PV_CENTRALISE = 'SOLAIRE_PV_CENTRALISE';
    public const TYPE_SOLAIRE_PV_DECENTRALISE = 'SOLAIRE_PV_DECENTRALISE';
    public const TYPE_PETITE_HYDRO = 'PETITE_HYDRO';
    public const TYPE_HYBRIDE_PV_HYDRO = 'HYBRIDE_PV_HYDRO';

    public const TYPES = [
        self::TYPE_EXTENSION_RESEAU_MT_30KV,
        self::TYPE_SOLAIRE_PV_CENTRALISE,
        self::TYPE_SOLAIRE_PV_DECENTRALISE,
        self::TYPE_PETITE_HYDRO,
        self::TYPE_HYBRIDE_PV_HYDRO,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['systeme_electrification:read', 'localite:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['systeme_electrification:read', 'systeme_electrification:write', 'localite:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['systeme_electrification:read', 'systeme_electrification:write', 'localite:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::TYPES)]
    #[Groups(['systeme_electrification:read', 'systeme_electrification:write', 'localite:read'])]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['systeme_electrification:read', 'systeme_electrification:write'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive]
    #[Groups(['systeme_electrification:read', 'systeme_electrification:write'])]
    private ?float $tensionKv = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive]
    #[Groups(['systeme_electrification:read', 'systeme_electrification:write'])]
    private ?float $rayonKm = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(['systeme_electrification:read', 'systeme_electrification:write'])]
    private ?string $sourceEnergie = null;

    #[ORM\Column]
    #[Groups(['systeme_electrification:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['systeme_electrification:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, Localite> */
    #[ORM\OneToMany(mappedBy: 'systemeElectrification', targetEntity: Localite::class)]
    private Collection $localites;

    public function __construct() { $this->localites = new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = trim($nom); return $this; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getType(): ?string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getTensionKv(): ?float { return $this->tensionKv; }
    public function setTensionKv(?float $tensionKv): static { $this->tensionKv = $tensionKv; return $this; }
    public function getRayonKm(): ?float { return $this->rayonKm; }
    public function setRayonKm(?float $rayonKm): static { $this->rayonKm = $rayonKm; return $this; }
    public function getSourceEnergie(): ?string { return $this->sourceEnergie; }
    public function setSourceEnergie(string $sourceEnergie): static { $this->sourceEnergie = trim($sourceEnergie); return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    /** @return Collection<int, Localite> */
    public function getLocalites(): Collection { return $this->localites; }
    public function addLocalite(Localite $localite): static { if (!$this->localites->contains($localite)) { $this->localites->add($localite); $localite->setSystemeElectrification($this); } return $this; }
    public function removeLocalite(Localite $localite): static { if ($this->localites->removeElement($localite) && $localite->getSystemeElectrification() === $this) { $localite->setSystemeElectrification(null); } return $this; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
