<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\IndicateurPnerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: IndicateurPnerRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Un indicateur PNER existe déjà avec ce code.')]
#[ApiResource(
    normalizationContext: ['groups' => ['indicateur_pner:read']],
    denormalizationContext: ['groups' => ['indicateur_pner:write']]
)]
class IndicateurPner
{
    public const TYPE_ACCES_ELECTRICITE = 'ACCES_ELECTRICITE';
    public const TYPE_LOCALITES_ELECTRIFIEES = 'LOCALITES_ELECTRIFIEES';
    public const TYPE_MENAGES_RACCORDES = 'MENAGES_RACCORDES';
    public const TYPE_INFRASTRUCTURES_SOCIALES = 'INFRASTRUCTURES_SOCIALES';
    public const TYPE_GENRE = 'GENRE';
    public const TYPE_FINANCEMENT = 'FINANCEMENT';
    public const TYPE_EXECUTION_PROJET = 'EXECUTION_PROJET';
    public const TYPES = [self::TYPE_ACCES_ELECTRICITE, self::TYPE_LOCALITES_ELECTRIFIEES, self::TYPE_MENAGES_RACCORDES, self::TYPE_INFRASTRUCTURES_SOCIALES, self::TYPE_GENRE, self::TYPE_FINANCEMENT, self::TYPE_EXECUTION_PROJET];

    public const FREQUENCE_MENSUELLE = 'MENSUELLE';
    public const FREQUENCE_TRIMESTRIELLE = 'TRIMESTRIELLE';
    public const FREQUENCE_SEMESTRIELLE = 'SEMESTRIELLE';
    public const FREQUENCE_ANNUELLE = 'ANNUELLE';
    public const FREQUENCES = [self::FREQUENCE_MENSUELLE, self::FREQUENCE_TRIMESTRIELLE, self::FREQUENCE_SEMESTRIELLE, self::FREQUENCE_ANNUELLE];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['indicateur_pner:read', 'valeur_indicateur:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['indicateur_pner:read', 'indicateur_pner:write', 'valeur_indicateur:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[Groups(['indicateur_pner:read', 'indicateur_pner:write', 'valeur_indicateur:read'])]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['indicateur_pner:read', 'indicateur_pner:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::TYPES)]
    #[Groups(['indicateur_pner:read', 'indicateur_pner:write'])]
    private ?string $typeIndicateur = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['indicateur_pner:read', 'indicateur_pner:write'])]
    private ?string $unite = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['indicateur_pner:read', 'indicateur_pner:write'])]
    private ?float $valeurReference = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['indicateur_pner:read', 'indicateur_pner:write'])]
    private ?float $valeurCible = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::FREQUENCES)]
    #[Groups(['indicateur_pner:read', 'indicateur_pner:write'])]
    private ?string $frequenceSuivi = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    #[Groups(['indicateur_pner:read', 'indicateur_pner:write'])]
    private ?string $sourceDonnee = null;

    #[ORM\Column]
    #[Groups(['indicateur_pner:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['indicateur_pner:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, ValeurIndicateur> */
    #[ORM\OneToMany(mappedBy: 'indicateurPner', targetEntity: ValeurIndicateur::class, orphanRemoval: true)]
    private Collection $valeursIndicateur;

    public function __construct() { $this->valeursIndicateur = new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getLibelle(): ?string { return $this->libelle; }
    public function setLibelle(string $libelle): static { $this->libelle = trim($libelle); return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getTypeIndicateur(): ?string { return $this->typeIndicateur; }
    public function setTypeIndicateur(string $typeIndicateur): static { $this->typeIndicateur = $typeIndicateur; return $this; }
    public function getUnite(): ?string { return $this->unite; }
    public function setUnite(string $unite): static { $this->unite = trim($unite); return $this; }
    public function getValeurReference(): ?float { return $this->valeurReference; }
    public function setValeurReference(?float $valeurReference): static { $this->valeurReference = $valeurReference; return $this; }
    public function getValeurCible(): ?float { return $this->valeurCible; }
    public function setValeurCible(?float $valeurCible): static { $this->valeurCible = $valeurCible; return $this; }
    public function getFrequenceSuivi(): ?string { return $this->frequenceSuivi; }
    public function setFrequenceSuivi(string $frequenceSuivi): static { $this->frequenceSuivi = $frequenceSuivi; return $this; }
    public function getSourceDonnee(): ?string { return $this->sourceDonnee; }
    public function setSourceDonnee(?string $sourceDonnee): static { $this->sourceDonnee = $sourceDonnee !== null ? trim($sourceDonnee) : null; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    /** @return Collection<int, ValeurIndicateur> */
    public function getValeursIndicateur(): Collection { return $this->valeursIndicateur; }
    public function addValeurIndicateur(ValeurIndicateur $valeurIndicateur): static { if (!$this->valeursIndicateur->contains($valeurIndicateur)) { $this->valeursIndicateur->add($valeurIndicateur); $valeurIndicateur->setIndicateurPner($this); } return $this; }
    public function removeValeurIndicateur(ValeurIndicateur $valeurIndicateur): static { if ($this->valeursIndicateur->removeElement($valeurIndicateur) && $valeurIndicateur->getIndicateurPner() === $this) { $valeurIndicateur->setIndicateurPner(null); } return $this; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
