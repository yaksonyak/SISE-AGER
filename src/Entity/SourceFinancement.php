<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SourceFinancementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SourceFinancementRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Une source de financement existe déjà avec ce code.')]
#[ApiResource(normalizationContext: ['groups' => ['source_financement:read']], denormalizationContext: ['groups' => ['source_financement:write']])]
class SourceFinancement
{
    public const TYPE_BUDGET_ETAT = 'BUDGET_ETAT';
    public const TYPE_DON = 'DON';
    public const TYPE_PRET = 'PRET';
    public const TYPE_PPP = 'PPP';
    public const TYPE_FONDS_VERT_CLIMAT = 'FONDS_VERT_CLIMAT';
    public const TYPE_FINANCEMENT_CARBONE = 'FINANCEMENT_CARBONE';
    public const TYPE_OFFRE_SPONTANEE = 'OFFRE_SPONTANEE';
    public const TYPE_SUBVENTION = 'SUBVENTION';
    public const TYPE_AUTRE = 'AUTRE';
    public const TYPES = [self::TYPE_BUDGET_ETAT, self::TYPE_DON, self::TYPE_PRET, self::TYPE_PPP, self::TYPE_FONDS_VERT_CLIMAT, self::TYPE_FINANCEMENT_CARBONE, self::TYPE_OFFRE_SPONTANEE, self::TYPE_SUBVENTION, self::TYPE_AUTRE];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['source_financement:read', 'convention_financement:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['source_financement:read', 'source_financement:write', 'convention_financement:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['source_financement:read', 'source_financement:write', 'convention_financement:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::TYPES)]
    #[Groups(['source_financement:read', 'source_financement:write'])]
    private ?string $typeSource = null;

    #[ORM\ManyToOne(inversedBy: 'sourcesFinancement')]
    #[Groups(['source_financement:read', 'source_financement:write'])]
    private ?BailleurFonds $bailleurFonds = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['source_financement:read', 'source_financement:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['source_financement:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['source_financement:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, ConventionFinancement> */
    #[ORM\OneToMany(mappedBy: 'sourceFinancement', targetEntity: ConventionFinancement::class)]
    private Collection $conventionsFinancement;

    public function __construct() { $this->conventionsFinancement = new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = trim($nom); return $this; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getTypeSource(): ?string { return $this->typeSource; }
    public function setTypeSource(string $typeSource): static { $this->typeSource = $typeSource; return $this; }
    public function getBailleurFonds(): ?BailleurFonds { return $this->bailleurFonds; }
    public function setBailleurFonds(?BailleurFonds $bailleurFonds): static { $this->bailleurFonds = $bailleurFonds; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    /** @return Collection<int, ConventionFinancement> */
    public function getConventionsFinancement(): Collection { return $this->conventionsFinancement; }
    public function addConventionFinancement(ConventionFinancement $conventionFinancement): static { if (!$this->conventionsFinancement->contains($conventionFinancement)) { $this->conventionsFinancement->add($conventionFinancement); $conventionFinancement->setSourceFinancement($this); } return $this; }
    public function removeConventionFinancement(ConventionFinancement $conventionFinancement): static { if ($this->conventionsFinancement->removeElement($conventionFinancement) && $conventionFinancement->getSourceFinancement() === $this) { $conventionFinancement->setSourceFinancement(null); } return $this; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
