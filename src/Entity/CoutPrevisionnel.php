<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CoutPrevisionnelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoutPrevisionnelRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(normalizationContext: ['groups' => ['cout_previsionnel:read']], denormalizationContext: ['groups' => ['cout_previsionnel:write']])]
class CoutPrevisionnel
{
    public const CATEGORIE_ETUDES = 'ETUDES';
    public const CATEGORIE_INFRASTRUCTURES = 'INFRASTRUCTURES';
    public const CATEGORIE_EQUIPEMENTS = 'EQUIPEMENTS';
    public const CATEGORIE_RENFORCEMENT_CAPACITES = 'RENFORCEMENT_CAPACITES';
    public const CATEGORIE_SIG = 'SIG';
    public const CATEGORIE_SUIVI_EVALUATION = 'SUIVI_EVALUATION';
    public const CATEGORIE_FONCTIONNEMENT = 'FONCTIONNEMENT';
    public const CATEGORIE_AUTRE = 'AUTRE';
    public const CATEGORIES = [self::CATEGORIE_ETUDES, self::CATEGORIE_INFRASTRUCTURES, self::CATEGORIE_EQUIPEMENTS, self::CATEGORIE_RENFORCEMENT_CAPACITES, self::CATEGORIE_SIG, self::CATEGORIE_SUIVI_EVALUATION, self::CATEGORIE_FONCTIONNEMENT, self::CATEGORIE_AUTRE];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cout_previsionnel:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'coutsPrevisionnels')]
    #[Groups(['cout_previsionnel:read', 'cout_previsionnel:write'])]
    private ?ProgrammePner $programmePner = null;

    #[ORM\ManyToOne(inversedBy: 'coutsPrevisionnels')]
    #[Groups(['cout_previsionnel:read', 'cout_previsionnel:write'])]
    private ?ProjetElectrification $projetElectrification = null;

    #[ORM\ManyToOne(inversedBy: 'coutsPrevisionnels')]
    #[Groups(['cout_previsionnel:read', 'cout_previsionnel:write'])]
    private ?Zer $zer = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::CATEGORIES)]
    #[Groups(['cout_previsionnel:read', 'cout_previsionnel:write'])]
    private ?string $categorieCout = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(['cout_previsionnel:read', 'cout_previsionnel:write'])]
    private ?float $montantUsd = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Assert\Range(min: 2000, max: 2100)]
    #[Groups(['cout_previsionnel:read', 'cout_previsionnel:write'])]
    private ?int $annee = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['cout_previsionnel:read', 'cout_previsionnel:write'])]
    private ?string $commentaire = null;

    #[ORM\Column]
    #[Groups(['cout_previsionnel:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['cout_previsionnel:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getProgrammePner(): ?ProgrammePner { return $this->programmePner; }
    public function setProgrammePner(?ProgrammePner $programmePner): static { $this->programmePner = $programmePner; return $this; }
    public function getProjetElectrification(): ?ProjetElectrification { return $this->projetElectrification; }
    public function setProjetElectrification(?ProjetElectrification $projetElectrification): static { $this->projetElectrification = $projetElectrification; return $this; }
    public function getZer(): ?Zer { return $this->zer; }
    public function setZer(?Zer $zer): static { $this->zer = $zer; return $this; }
    public function getCategorieCout(): ?string { return $this->categorieCout; }
    public function setCategorieCout(string $categorieCout): static { $this->categorieCout = $categorieCout; return $this; }
    public function getMontantUsd(): ?float { return $this->montantUsd; }
    public function setMontantUsd(float $montantUsd): static { $this->montantUsd = $montantUsd; return $this; }
    public function getAnnee(): ?int { return $this->annee; }
    public function setAnnee(?int $annee): static { $this->annee = $annee; return $this; }
    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $commentaire): static { $this->commentaire = $commentaire; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
