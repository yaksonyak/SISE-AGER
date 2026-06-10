<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SiteEnergetiqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SiteEnergetiqueRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Un site énergétique existe déjà avec ce code.')]
#[ApiResource(
    normalizationContext: ['groups' => ['site_energetique:read']],
    denormalizationContext: ['groups' => ['site_energetique:write']]
)]
class SiteEnergetique
{
    public const TYPE_SOLAIRE = 'SOLAIRE';
    public const TYPE_HYDRO = 'HYDRO';
    public const TYPE_HYBRIDE = 'HYBRIDE';
    public const TYPE_DIESEL = 'DIESEL';
    public const TYPE_STOCKAGE = 'STOCKAGE';
    public const TYPES = [self::TYPE_SOLAIRE, self::TYPE_HYDRO, self::TYPE_HYBRIDE, self::TYPE_DIESEL, self::TYPE_STOCKAGE];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['site_energetique:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['site_energetique:read', 'site_energetique:write'])]
    private ?string $code = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['site_energetique:read', 'site_energetique:write'])]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::TYPES)]
    #[Groups(['site_energetique:read', 'site_energetique:write'])]
    private ?string $typeSite = null;

    #[ORM\ManyToOne(inversedBy: 'sitesEnergetiques')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['site_energetique:read', 'site_energetique:write'])]
    private ?Localite $localite = null;

    #[ORM\ManyToOne(inversedBy: 'sitesEnergetiques')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['site_energetique:read', 'site_energetique:write'])]
    private ?PointGps $pointGps = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['site_energetique:read', 'site_energetique:write'])]
    private ?float $puissanceInstalleeKw = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['site_energetique:read', 'site_energetique:write'])]
    private ?string $statut = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['site_energetique:read', 'site_energetique:write'])]
    private ?string $commentaire = null;

    #[ORM\Column]
    #[Groups(['site_energetique:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['site_energetique:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = trim($nom); return $this; }
    public function getTypeSite(): ?string { return $this->typeSite; }
    public function setTypeSite(string $typeSite): static { $this->typeSite = $typeSite; return $this; }
    public function getLocalite(): ?Localite { return $this->localite; }
    public function setLocalite(?Localite $localite): static { $this->localite = $localite; return $this; }
    public function getPointGps(): ?PointGps { return $this->pointGps; }
    public function setPointGps(?PointGps $pointGps): static { $this->pointGps = $pointGps; return $this; }
    public function getPuissanceInstalleeKw(): ?float { return $this->puissanceInstalleeKw; }
    public function setPuissanceInstalleeKw(?float $puissanceInstalleeKw): static { $this->puissanceInstalleeKw = $puissanceInstalleeKw; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = trim($statut); return $this; }
    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $commentaire): static { $this->commentaire = $commentaire; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
