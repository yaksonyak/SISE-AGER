<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\BeneficiaireGenreRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BeneficiaireGenreRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(normalizationContext: ['groups' => ['beneficiaire_genre:read']], denormalizationContext: ['groups' => ['beneficiaire_genre:write']])]
class BeneficiaireGenre
{
    public const CATEGORIE_MENAGES = 'MENAGES';
    public const CATEGORIE_ENTREPRENEURS = 'ENTREPRENEURS';
    public const CATEGORIE_FEMMES = 'FEMMES';
    public const CATEGORIE_JEUNES = 'JEUNES';
    public const CATEGORIE_INFRASTRUCTURES_COMMUNAUTAIRES = 'INFRASTRUCTURES_COMMUNAUTAIRES';
    public const CATEGORIE_GROUPE_VULNERABLE = 'GROUPE_VULNERABLE';
    public const CATEGORIE_AUTRE = 'AUTRE';
    public const CATEGORIES = [self::CATEGORIE_MENAGES, self::CATEGORIE_ENTREPRENEURS, self::CATEGORIE_FEMMES, self::CATEGORIE_JEUNES, self::CATEGORIE_INFRASTRUCTURES_COMMUNAUTAIRES, self::CATEGORIE_GROUPE_VULNERABLE, self::CATEGORIE_AUTRE];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['beneficiaire_genre:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'beneficiairesGenre')]
    #[Groups(['beneficiaire_genre:read', 'beneficiaire_genre:write'])]
    private ?ActionGenre $actionGenre = null;

    #[ORM\ManyToOne(inversedBy: 'beneficiairesGenre')]
    #[Groups(['beneficiaire_genre:read', 'beneficiaire_genre:write'])]
    private ?ProjetElectrification $projetElectrification = null;

    #[ORM\ManyToOne(inversedBy: 'beneficiairesGenre')]
    #[Groups(['beneficiaire_genre:read', 'beneficiaire_genre:write'])]
    private ?Localite $localite = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::CATEGORIES)]
    #[Groups(['beneficiaire_genre:read', 'beneficiaire_genre:write'])]
    private ?string $categorieBeneficiaire = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(['beneficiaire_genre:read', 'beneficiaire_genre:write'])]
    private int $nombreHommes = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(['beneficiaire_genre:read', 'beneficiaire_genre:write'])]
    private int $nombreFemmes = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(['beneficiaire_genre:read', 'beneficiaire_genre:write'])]
    private int $nombreJeunes = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(['beneficiaire_genre:read', 'beneficiaire_genre:write'])]
    private int $nombrePersonnesVulnerables = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['beneficiaire_genre:read', 'beneficiaire_genre:write'])]
    private ?string $commentaire = null;

    #[ORM\Column]
    #[Groups(['beneficiaire_genre:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['beneficiaire_genre:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getActionGenre(): ?ActionGenre { return $this->actionGenre; }
    public function setActionGenre(?ActionGenre $actionGenre): static { $this->actionGenre = $actionGenre; return $this; }
    public function getProjetElectrification(): ?ProjetElectrification { return $this->projetElectrification; }
    public function setProjetElectrification(?ProjetElectrification $projetElectrification): static { $this->projetElectrification = $projetElectrification; return $this; }
    public function getLocalite(): ?Localite { return $this->localite; }
    public function setLocalite(?Localite $localite): static { $this->localite = $localite; return $this; }
    public function getCategorieBeneficiaire(): ?string { return $this->categorieBeneficiaire; }
    public function setCategorieBeneficiaire(string $categorieBeneficiaire): static { $this->categorieBeneficiaire = $categorieBeneficiaire; return $this; }
    public function getNombreHommes(): int { return $this->nombreHommes; }
    public function setNombreHommes(int $nombreHommes): static { $this->nombreHommes = $nombreHommes; return $this; }
    public function getNombreFemmes(): int { return $this->nombreFemmes; }
    public function setNombreFemmes(int $nombreFemmes): static { $this->nombreFemmes = $nombreFemmes; return $this; }
    public function getNombreJeunes(): int { return $this->nombreJeunes; }
    public function setNombreJeunes(int $nombreJeunes): static { $this->nombreJeunes = $nombreJeunes; return $this; }
    public function getNombrePersonnesVulnerables(): int { return $this->nombrePersonnesVulnerables; }
    public function setNombrePersonnesVulnerables(int $nombrePersonnesVulnerables): static { $this->nombrePersonnesVulnerables = $nombrePersonnesVulnerables; return $this; }
    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $commentaire): static { $this->commentaire = $commentaire; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    #[ORM\PrePersist] public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate] public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
