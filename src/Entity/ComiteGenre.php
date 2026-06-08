<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ComiteGenreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ComiteGenreRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Un comité genre existe déjà avec ce code.')]
#[ApiResource(normalizationContext: ['groups' => ['comite_genre:read']], denormalizationContext: ['groups' => ['comite_genre:write']])]
class ComiteGenre
{
    public const TYPE_COMITE_SECTORIEL = 'COMITE_SECTORIEL';
    public const TYPE_COMITE_INTERSECTORIEL = 'COMITE_INTERSECTORIEL';
    public const TYPE_CIMESA_REGIONAL = 'CIMESA_REGIONAL';
    public const TYPE_COMITE_LOCAL = 'COMITE_LOCAL';
    public const TYPES = [self::TYPE_COMITE_SECTORIEL, self::TYPE_COMITE_INTERSECTORIEL, self::TYPE_CIMESA_REGIONAL, self::TYPE_COMITE_LOCAL];
    public const STATUT_EN_CREATION = 'EN_CREATION';
    public const STATUT_FONCTIONNEL = 'FONCTIONNEL';
    public const STATUT_INACTIF = 'INACTIF';
    public const STATUT_DISSOUS = 'DISSOUS';
    public const STATUTS = [self::STATUT_EN_CREATION, self::STATUT_FONCTIONNEL, self::STATUT_INACTIF, self::STATUT_DISSOUS];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['comite_genre:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['comite_genre:read', 'comite_genre:write'])]
    private ?string $code = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[Groups(['comite_genre:read', 'comite_genre:write'])]
    private ?string $nom = null;

    #[ORM\Column(length: 40)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::TYPES)]
    #[Groups(['comite_genre:read', 'comite_genre:write'])]
    private ?string $typeComite = null;

    #[ORM\ManyToOne(inversedBy: 'comitesGenre')]
    #[Groups(['comite_genre:read', 'comite_genre:write'])]
    private ?Zer $zer = null;

    #[ORM\ManyToOne(inversedBy: 'comitesGenre')]
    #[Groups(['comite_genre:read', 'comite_genre:write'])]
    private ?Localite $localite = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['comite_genre:read', 'comite_genre:write'])]
    private ?\DateTimeImmutable $dateMiseEnPlace = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(['comite_genre:read', 'comite_genre:write'])]
    private int $nombreMembres = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(['comite_genre:read', 'comite_genre:write'])]
    private int $nombreFemmes = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(['comite_genre:read', 'comite_genre:write'])]
    private int $nombreHommes = 0;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::STATUTS)]
    #[Groups(['comite_genre:read', 'comite_genre:write'])]
    private ?string $statut = self::STATUT_EN_CREATION;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['comite_genre:read', 'comite_genre:write'])]
    private ?string $commentaire = null;

    #[ORM\Column]
    #[Groups(['comite_genre:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['comite_genre:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = trim($nom); return $this; }
    public function getTypeComite(): ?string { return $this->typeComite; }
    public function setTypeComite(string $typeComite): static { $this->typeComite = $typeComite; return $this; }
    public function getZer(): ?Zer { return $this->zer; }
    public function setZer(?Zer $zer): static { $this->zer = $zer; return $this; }
    public function getLocalite(): ?Localite { return $this->localite; }
    public function setLocalite(?Localite $localite): static { $this->localite = $localite; return $this; }
    public function getDateMiseEnPlace(): ?\DateTimeImmutable { return $this->dateMiseEnPlace; }
    public function setDateMiseEnPlace(?\DateTimeImmutable $dateMiseEnPlace): static { $this->dateMiseEnPlace = $dateMiseEnPlace; return $this; }
    public function getNombreMembres(): int { return $this->nombreMembres; }
    public function setNombreMembres(int $nombreMembres): static { $this->nombreMembres = $nombreMembres; return $this; }
    public function getNombreFemmes(): int { return $this->nombreFemmes; }
    public function setNombreFemmes(int $nombreFemmes): static { $this->nombreFemmes = $nombreFemmes; return $this; }
    public function getNombreHommes(): int { return $this->nombreHommes; }
    public function setNombreHommes(int $nombreHommes): static { $this->nombreHommes = $nombreHommes; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $commentaire): static { $this->commentaire = $commentaire; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    #[ORM\PrePersist] public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate] public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
