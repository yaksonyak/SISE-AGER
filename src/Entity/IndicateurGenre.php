<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\IndicateurGenreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: IndicateurGenreRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Un indicateur genre existe déjà avec ce code.')]
#[ApiResource(normalizationContext: ['groups' => ['indicateur_genre:read']], denormalizationContext: ['groups' => ['indicateur_genre:write']])]
class IndicateurGenre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['indicateur_genre:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['indicateur_genre:read', 'indicateur_genre:write'])]
    private ?string $code = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[Groups(['indicateur_genre:read', 'indicateur_genre:write'])]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['indicateur_genre:read', 'indicateur_genre:write'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'indicateursGenre')]
    #[Groups(['indicateur_genre:read', 'indicateur_genre:write'])]
    private ?ActionGenre $actionGenre = null;

    #[ORM\ManyToOne(inversedBy: 'indicateursGenre')]
    #[Groups(['indicateur_genre:read', 'indicateur_genre:write'])]
    private ?ProjetElectrification $projetElectrification = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['indicateur_genre:read', 'indicateur_genre:write'])]
    private ?float $valeurReference = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['indicateur_genre:read', 'indicateur_genre:write'])]
    private ?float $valeurCible = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['indicateur_genre:read', 'indicateur_genre:write'])]
    private ?float $valeurActuelle = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    #[Groups(['indicateur_genre:read', 'indicateur_genre:write'])]
    private ?string $unite = null;

    #[ORM\Column]
    #[Groups(['indicateur_genre:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['indicateur_genre:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getLibelle(): ?string { return $this->libelle; }
    public function setLibelle(string $libelle): static { $this->libelle = trim($libelle); return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getActionGenre(): ?ActionGenre { return $this->actionGenre; }
    public function setActionGenre(?ActionGenre $actionGenre): static { $this->actionGenre = $actionGenre; return $this; }
    public function getProjetElectrification(): ?ProjetElectrification { return $this->projetElectrification; }
    public function setProjetElectrification(?ProjetElectrification $projetElectrification): static { $this->projetElectrification = $projetElectrification; return $this; }
    public function getValeurReference(): ?float { return $this->valeurReference; }
    public function setValeurReference(?float $valeurReference): static { $this->valeurReference = $valeurReference; return $this; }
    public function getValeurCible(): ?float { return $this->valeurCible; }
    public function setValeurCible(?float $valeurCible): static { $this->valeurCible = $valeurCible; return $this; }
    public function getValeurActuelle(): ?float { return $this->valeurActuelle; }
    public function setValeurActuelle(?float $valeurActuelle): static { $this->valeurActuelle = $valeurActuelle; return $this; }
    public function getUnite(): ?string { return $this->unite; }
    public function setUnite(?string $unite): static { $this->unite = $unite; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    #[ORM\PrePersist] public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate] public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
