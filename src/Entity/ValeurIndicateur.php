<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ValeurIndicateurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ValeurIndicateurRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['valeur_indicateur:read']],
    denormalizationContext: ['groups' => ['valeur_indicateur:write']]
)]
class ValeurIndicateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['valeur_indicateur:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'valeursIndicateur')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['valeur_indicateur:read', 'valeur_indicateur:write'])]
    private ?IndicateurPner $indicateurPner = null;

    #[ORM\ManyToOne(inversedBy: 'valeursIndicateur')]
    #[Groups(['valeur_indicateur:read', 'valeur_indicateur:write'])]
    private ?ProgrammePner $programmePner = null;

    #[ORM\ManyToOne(inversedBy: 'valeursIndicateur')]
    #[Groups(['valeur_indicateur:read', 'valeur_indicateur:write'])]
    private ?Zer $zer = null;

    #[ORM\ManyToOne(inversedBy: 'valeursIndicateur')]
    #[Groups(['valeur_indicateur:read', 'valeur_indicateur:write'])]
    private ?ProjetElectrification $projetElectrification = null;

    #[ORM\ManyToOne(inversedBy: 'valeursIndicateur')]
    #[Groups(['valeur_indicateur:read', 'valeur_indicateur:write'])]
    private ?Localite $localite = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 30)]
    #[Groups(['valeur_indicateur:read', 'valeur_indicateur:write'])]
    private ?string $periode = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Range(min: 2000, max: 2100)]
    #[Groups(['valeur_indicateur:read', 'valeur_indicateur:write'])]
    private ?int $annee = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Groups(['valeur_indicateur:read', 'valeur_indicateur:write'])]
    private ?float $valeur = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['valeur_indicateur:read', 'valeur_indicateur:write'])]
    private ?string $commentaire = null;

    #[ORM\Column]
    #[Groups(['valeur_indicateur:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['valeur_indicateur:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getIndicateurPner(): ?IndicateurPner { return $this->indicateurPner; }
    public function setIndicateurPner(?IndicateurPner $indicateurPner): static { $this->indicateurPner = $indicateurPner; return $this; }
    public function getProgrammePner(): ?ProgrammePner { return $this->programmePner; }
    public function setProgrammePner(?ProgrammePner $programmePner): static { $this->programmePner = $programmePner; return $this; }
    public function getZer(): ?Zer { return $this->zer; }
    public function setZer(?Zer $zer): static { $this->zer = $zer; return $this; }
    public function getProjetElectrification(): ?ProjetElectrification { return $this->projetElectrification; }
    public function setProjetElectrification(?ProjetElectrification $projetElectrification): static { $this->projetElectrification = $projetElectrification; return $this; }
    public function getLocalite(): ?Localite { return $this->localite; }
    public function setLocalite(?Localite $localite): static { $this->localite = $localite; return $this; }
    public function getPeriode(): ?string { return $this->periode; }
    public function setPeriode(string $periode): static { $this->periode = trim($periode); return $this; }
    public function getAnnee(): ?int { return $this->annee; }
    public function setAnnee(int $annee): static { $this->annee = $annee; return $this; }
    public function getValeur(): ?float { return $this->valeur; }
    public function setValeur(float $valeur): static { $this->valeur = $valeur; return $this; }
    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $commentaire): static { $this->commentaire = $commentaire; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
