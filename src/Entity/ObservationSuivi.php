<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ObservationSuiviRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ObservationSuiviRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['observation_suivi:read']],
    denormalizationContext: ['groups' => ['observation_suivi:write']]
)]
class ObservationSuivi
{
    public const NIVEAU_FAIBLE = 'FAIBLE';
    public const NIVEAU_MOYEN = 'MOYEN';
    public const NIVEAU_ELEVE = 'ELEVE';
    public const NIVEAU_CRITIQUE = 'CRITIQUE';
    public const NIVEAUX = [self::NIVEAU_FAIBLE, self::NIVEAU_MOYEN, self::NIVEAU_ELEVE, self::NIVEAU_CRITIQUE];

    public const STATUT_OUVERTE = 'OUVERTE';
    public const STATUT_EN_TRAITEMENT = 'EN_TRAITEMENT';
    public const STATUT_RESOLUE = 'RESOLUE';
    public const STATUT_CLASSEE = 'CLASSEE';
    public const STATUTS = [self::STATUT_OUVERTE, self::STATUT_EN_TRAITEMENT, self::STATUT_RESOLUE, self::STATUT_CLASSEE];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['observation_suivi:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'observationsSuivi')]
    #[Groups(['observation_suivi:read', 'observation_suivi:write'])]
    private ?RapportSuivi $rapportSuivi = null;

    #[ORM\ManyToOne(inversedBy: 'observationsSuivi')]
    #[Groups(['observation_suivi:read', 'observation_suivi:write'])]
    private ?ProjetElectrification $projetElectrification = null;

    #[ORM\ManyToOne(inversedBy: 'observationsSuivi')]
    #[Groups(['observation_suivi:read', 'observation_suivi:write'])]
    private ?Localite $localite = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[Groups(['observation_suivi:read', 'observation_suivi:write'])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['observation_suivi:read', 'observation_suivi:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::NIVEAUX)]
    #[Groups(['observation_suivi:read', 'observation_suivi:write'])]
    private ?string $niveauCriticite = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::STATUTS)]
    #[Groups(['observation_suivi:read', 'observation_suivi:write'])]
    private ?string $statut = self::STATUT_OUVERTE;

    #[ORM\Column]
    #[Groups(['observation_suivi:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['observation_suivi:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getRapportSuivi(): ?RapportSuivi { return $this->rapportSuivi; }
    public function setRapportSuivi(?RapportSuivi $rapportSuivi): static { $this->rapportSuivi = $rapportSuivi; return $this; }
    public function getProjetElectrification(): ?ProjetElectrification { return $this->projetElectrification; }
    public function setProjetElectrification(?ProjetElectrification $projetElectrification): static { $this->projetElectrification = $projetElectrification; return $this; }
    public function getLocalite(): ?Localite { return $this->localite; }
    public function setLocalite(?Localite $localite): static { $this->localite = $localite; return $this; }
    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = trim($titre); return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }
    public function getNiveauCriticite(): ?string { return $this->niveauCriticite; }
    public function setNiveauCriticite(string $niveauCriticite): static { $this->niveauCriticite = $niveauCriticite; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
