<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DecaissementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DecaissementRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(normalizationContext: ['groups' => ['decaissement:read']], denormalizationContext: ['groups' => ['decaissement:write']])]
class Decaissement
{
    public const STATUT_PREVU = 'PREVU';
    public const STATUT_EFFECTUE = 'EFFECTUE';
    public const STATUT_REJETE = 'REJETE';
    public const STATUT_ANNULE = 'ANNULE';
    public const STATUTS = [self::STATUT_PREVU, self::STATUT_EFFECTUE, self::STATUT_REJETE, self::STATUT_ANNULE];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['decaissement:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'decaissements')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['decaissement:read', 'decaissement:write'])]
    private ?ConventionFinancement $conventionFinancement = null;

    #[ORM\ManyToOne(inversedBy: 'decaissements')]
    #[Groups(['decaissement:read', 'decaissement:write'])]
    private ?ProjetElectrification $projetElectrification = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(['decaissement:read', 'decaissement:write'])]
    private ?float $montantUsd = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    #[Groups(['decaissement:read', 'decaissement:write'])]
    private ?\DateTimeImmutable $dateDecaissement = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Groups(['decaissement:read', 'decaissement:write'])]
    private ?string $referencePaiement = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['decaissement:read', 'decaissement:write'])]
    private ?string $objet = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::STATUTS)]
    #[Groups(['decaissement:read', 'decaissement:write'])]
    private ?string $statut = self::STATUT_PREVU;

    #[ORM\Column]
    #[Groups(['decaissement:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['decaissement:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getConventionFinancement(): ?ConventionFinancement { return $this->conventionFinancement; }
    public function setConventionFinancement(?ConventionFinancement $conventionFinancement): static { $this->conventionFinancement = $conventionFinancement; return $this; }
    public function getProjetElectrification(): ?ProjetElectrification { return $this->projetElectrification; }
    public function setProjetElectrification(?ProjetElectrification $projetElectrification): static { $this->projetElectrification = $projetElectrification; return $this; }
    public function getMontantUsd(): ?float { return $this->montantUsd; }
    public function setMontantUsd(float $montantUsd): static { $this->montantUsd = $montantUsd; return $this; }
    public function getDateDecaissement(): ?\DateTimeImmutable { return $this->dateDecaissement; }
    public function setDateDecaissement(\DateTimeImmutable $dateDecaissement): static { $this->dateDecaissement = $dateDecaissement; return $this; }
    public function getReferencePaiement(): ?string { return $this->referencePaiement; }
    public function setReferencePaiement(?string $referencePaiement): static { $this->referencePaiement = $referencePaiement !== null ? trim($referencePaiement) : null; return $this; }
    public function getObjet(): ?string { return $this->objet; }
    public function setObjet(?string $objet): static { $this->objet = $objet; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
