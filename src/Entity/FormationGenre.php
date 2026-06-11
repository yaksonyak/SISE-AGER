<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\FormationGenreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FormationGenreRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Une formation genre existe déjà avec ce code.')]
#[ApiResource(normalizationContext: ['groups' => ['formation_genre:read']], denormalizationContext: ['groups' => ['formation_genre:write']])]
class FormationGenre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['formation_genre:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['formation_genre:read', 'formation_genre:write'])]
    private ?string $code = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[Groups(['formation_genre:read', 'formation_genre:write'])]
    private ?string $intitule = null;

    #[ORM\ManyToOne(inversedBy: 'formationsGenre')]
    #[Groups(['formation_genre:read', 'formation_genre:write'])]
    private ?ActionGenre $actionGenre = null;

    #[ORM\ManyToOne(inversedBy: 'formationsGenre')]
    #[Groups(['formation_genre:read', 'formation_genre:write'])]
    private ?Zer $zer = null;

    #[ORM\ManyToOne(inversedBy: 'formationsGenre')]
    #[Groups(['formation_genre:read', 'formation_genre:write'])]
    private ?Localite $localite = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['formation_genre:read', 'formation_genre:write'])]
    private ?\DateTimeImmutable $dateFormation = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(['formation_genre:read', 'formation_genre:write'])]
    private int $nombreParticipants = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(['formation_genre:read', 'formation_genre:write'])]
    private int $nombreFemmes = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(['formation_genre:read', 'formation_genre:write'])]
    private int $nombreHommes = 0;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[Groups(['formation_genre:read', 'formation_genre:write'])]
    private ?string $theme = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    #[Groups(['formation_genre:read', 'formation_genre:write'])]
    private ?string $formateur = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['formation_genre:read', 'formation_genre:write'])]
    private ?string $commentaire = null;

    #[ORM\Column]
    #[Groups(['formation_genre:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['formation_genre:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getIntitule(): ?string { return $this->intitule; }
    public function setIntitule(string $intitule): static { $this->intitule = trim($intitule); return $this; }
    public function getActionGenre(): ?ActionGenre { return $this->actionGenre; }
    public function setActionGenre(?ActionGenre $actionGenre): static { $this->actionGenre = $actionGenre; return $this; }
    public function getZer(): ?Zer { return $this->zer; }
    public function setZer(?Zer $zer): static { $this->zer = $zer; return $this; }
    public function getLocalite(): ?Localite { return $this->localite; }
    public function setLocalite(?Localite $localite): static { $this->localite = $localite; return $this; }
    public function getDateFormation(): ?\DateTimeImmutable { return $this->dateFormation; }
    public function setDateFormation(?\DateTimeImmutable $dateFormation): static { $this->dateFormation = $dateFormation; return $this; }
    public function getNombreParticipants(): int { return $this->nombreParticipants; }
    public function setNombreParticipants(int $nombreParticipants): static { $this->nombreParticipants = $nombreParticipants; return $this; }
    public function getNombreFemmes(): int { return $this->nombreFemmes; }
    public function setNombreFemmes(int $nombreFemmes): static { $this->nombreFemmes = $nombreFemmes; return $this; }
    public function getNombreHommes(): int { return $this->nombreHommes; }
    public function setNombreHommes(int $nombreHommes): static { $this->nombreHommes = $nombreHommes; return $this; }
    public function getTheme(): ?string { return $this->theme; }
    public function setTheme(string $theme): static { $this->theme = trim($theme); return $this; }
    public function getFormateur(): ?string { return $this->formateur; }
    public function setFormateur(?string $formateur): static { $this->formateur = $formateur; return $this; }
    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $commentaire): static { $this->commentaire = $commentaire; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    #[ORM\PrePersist] public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate] public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
