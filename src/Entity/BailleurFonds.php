<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\BailleurFondsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BailleurFondsRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Un bailleur de fonds existe déjà avec ce code.')]
#[ApiResource(normalizationContext: ['groups' => ['bailleur_fonds:read']], denormalizationContext: ['groups' => ['bailleur_fonds:write']])]
class BailleurFonds
{
    public const TYPE_ETAT = 'ETAT';
    public const TYPE_PTF = 'PTF';
    public const TYPE_SECTEUR_PRIVE = 'SECTEUR_PRIVE';
    public const TYPE_ONG = 'ONG';
    public const TYPE_FONDS_CLIMAT = 'FONDS_CLIMAT';
    public const TYPE_BANQUE = 'BANQUE';
    public const TYPE_AUTRE = 'AUTRE';
    public const TYPES = [self::TYPE_ETAT, self::TYPE_PTF, self::TYPE_SECTEUR_PRIVE, self::TYPE_ONG, self::TYPE_FONDS_CLIMAT, self::TYPE_BANQUE, self::TYPE_AUTRE];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['bailleur_fonds:read', 'source_financement:read', 'convention_financement:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['bailleur_fonds:read', 'bailleur_fonds:write', 'source_financement:read', 'convention_financement:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['bailleur_fonds:read', 'bailleur_fonds:write', 'source_financement:read', 'convention_financement:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::TYPES)]
    #[Groups(['bailleur_fonds:read', 'bailleur_fonds:write'])]
    private ?string $typeBailleur = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Groups(['bailleur_fonds:read', 'bailleur_fonds:write'])]
    private ?string $pays = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    #[Groups(['bailleur_fonds:read', 'bailleur_fonds:write'])]
    private ?string $contactPrincipal = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    #[Groups(['bailleur_fonds:read', 'bailleur_fonds:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    #[Groups(['bailleur_fonds:read', 'bailleur_fonds:write'])]
    private ?string $telephone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['bailleur_fonds:read', 'bailleur_fonds:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['bailleur_fonds:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['bailleur_fonds:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, SourceFinancement> */
    #[ORM\OneToMany(mappedBy: 'bailleurFonds', targetEntity: SourceFinancement::class)]
    private Collection $sourcesFinancement;

    /** @var Collection<int, ConventionFinancement> */
    #[ORM\OneToMany(mappedBy: 'bailleurFonds', targetEntity: ConventionFinancement::class)]
    private Collection $conventionsFinancement;

    public function __construct() { $this->sourcesFinancement = new ArrayCollection(); $this->conventionsFinancement = new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = trim($nom); return $this; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getTypeBailleur(): ?string { return $this->typeBailleur; }
    public function setTypeBailleur(string $typeBailleur): static { $this->typeBailleur = $typeBailleur; return $this; }
    public function getPays(): ?string { return $this->pays; }
    public function setPays(?string $pays): static { $this->pays = $pays !== null ? trim($pays) : null; return $this; }
    public function getContactPrincipal(): ?string { return $this->contactPrincipal; }
    public function setContactPrincipal(?string $contactPrincipal): static { $this->contactPrincipal = $contactPrincipal !== null ? trim($contactPrincipal) : null; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): static { $this->email = $email !== null ? mb_strtolower(trim($email)) : null; return $this; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): static { $this->telephone = $telephone !== null ? trim($telephone) : null; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    /** @return Collection<int, SourceFinancement> */
    public function getSourcesFinancement(): Collection { return $this->sourcesFinancement; }
    public function addSourceFinancement(SourceFinancement $sourceFinancement): static { if (!$this->sourcesFinancement->contains($sourceFinancement)) { $this->sourcesFinancement->add($sourceFinancement); $sourceFinancement->setBailleurFonds($this); } return $this; }
    public function removeSourceFinancement(SourceFinancement $sourceFinancement): static { if ($this->sourcesFinancement->removeElement($sourceFinancement) && $sourceFinancement->getBailleurFonds() === $this) { $sourceFinancement->setBailleurFonds(null); } return $this; }
    /** @return Collection<int, ConventionFinancement> */
    public function getConventionsFinancement(): Collection { return $this->conventionsFinancement; }
    public function addConventionFinancement(ConventionFinancement $conventionFinancement): static { if (!$this->conventionsFinancement->contains($conventionFinancement)) { $this->conventionsFinancement->add($conventionFinancement); $conventionFinancement->setBailleurFonds($this); } return $this; }
    public function removeConventionFinancement(ConventionFinancement $conventionFinancement): static { if ($this->conventionsFinancement->removeElement($conventionFinancement) && $conventionFinancement->getBailleurFonds() === $this) { $conventionFinancement->setBailleurFonds(null); } return $this; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
