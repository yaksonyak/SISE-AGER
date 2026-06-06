<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProjetLocaliteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjetLocaliteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['projet_localite:read']],
    denormalizationContext: ['groups' => ['projet_localite:write']]
)]
class ProjetLocalite
{
    public const STATUT_A_ELECTRIFIER = 'A_ELECTRIFIER';
    public const STATUT_EN_TRAVAUX = 'EN_TRAVAUX';
    public const STATUT_ELECTRIFIEE = 'ELECTRIFIEE';
    public const STATUT_REPORTEE = 'REPORTEE';
    public const STATUT_ABANDONNEE = 'ABANDONNEE';
    public const STATUTS = [self::STATUT_A_ELECTRIFIER, self::STATUT_EN_TRAVAUX, self::STATUT_ELECTRIFIEE, self::STATUT_REPORTEE, self::STATUT_ABANDONNEE];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['projet_localite:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'projetLocalites')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['projet_localite:read', 'projet_localite:write'])]
    private ?ProjetElectrification $projetElectrification = null;

    #[ORM\ManyToOne(inversedBy: 'projetLocalites')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['projet_localite:read', 'projet_localite:write'])]
    private ?Localite $localite = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::STATUTS)]
    #[Groups(['projet_localite:read', 'projet_localite:write'])]
    private ?string $statutLocalite = self::STATUT_A_ELECTRIFIER;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['projet_localite:read', 'projet_localite:write'])]
    private ?\DateTimeImmutable $dateRaccordementPrevue = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['projet_localite:read', 'projet_localite:write'])]
    private ?\DateTimeImmutable $dateRaccordementEffective = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['projet_localite:read', 'projet_localite:write'])]
    private ?string $commentaire = null;

    #[ORM\Column]
    #[Groups(['projet_localite:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['projet_localite:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getProjetElectrification(): ?ProjetElectrification { return $this->projetElectrification; }
    public function setProjetElectrification(?ProjetElectrification $projetElectrification): static { $this->projetElectrification = $projetElectrification; return $this; }
    public function getLocalite(): ?Localite { return $this->localite; }
    public function setLocalite(?Localite $localite): static { $this->localite = $localite; return $this; }
    public function getStatutLocalite(): ?string { return $this->statutLocalite; }
    public function setStatutLocalite(string $statutLocalite): static { $this->statutLocalite = $statutLocalite; return $this; }
    public function getDateRaccordementPrevue(): ?\DateTimeImmutable { return $this->dateRaccordementPrevue; }
    public function setDateRaccordementPrevue(?\DateTimeImmutable $dateRaccordementPrevue): static { $this->dateRaccordementPrevue = $dateRaccordementPrevue; return $this; }
    public function getDateRaccordementEffective(): ?\DateTimeImmutable { return $this->dateRaccordementEffective; }
    public function setDateRaccordementEffective(?\DateTimeImmutable $dateRaccordementEffective): static { $this->dateRaccordementEffective = $dateRaccordementEffective; return $this; }
    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $commentaire): static { $this->commentaire = $commentaire; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
