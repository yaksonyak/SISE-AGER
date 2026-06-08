<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PrefectureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PrefectureRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Une préfecture existe déjà avec ce code.')]
#[ApiResource(
    normalizationContext: ['groups' => ['prefecture:read']],
    denormalizationContext: ['groups' => ['prefecture:write']]
)]
class Prefecture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['prefecture:read', 'sous_prefecture:read', 'localite:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['prefecture:read', 'prefecture:write', 'sous_prefecture:read', 'localite:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 30, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 30)]
    #[Groups(['prefecture:read', 'prefecture:write', 'sous_prefecture:read', 'localite:read'])]
    private ?string $code = null;

    #[ORM\ManyToOne(inversedBy: 'prefectures')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['prefecture:read', 'prefecture:write'])]
    private ?Zer $zer = null;

    #[ORM\Column]
    #[Groups(['prefecture:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['prefecture:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, SousPrefecture> */
    #[ORM\OneToMany(mappedBy: 'prefecture', targetEntity: SousPrefecture::class)]
    private Collection $sousPrefectures;

    /** @var Collection<int, Localite> */
    #[ORM\OneToMany(mappedBy: 'prefecture', targetEntity: Localite::class)]
    private Collection $localites;

    public function __construct() { $this->sousPrefectures = new ArrayCollection(); $this->localites = new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = trim($nom); return $this; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getZer(): ?Zer { return $this->zer; }
    public function setZer(?Zer $zer): static { $this->zer = $zer; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    /** @return Collection<int, SousPrefecture> */
    public function getSousPrefectures(): Collection { return $this->sousPrefectures; }
    public function addSousPrefecture(SousPrefecture $sousPrefecture): static { if (!$this->sousPrefectures->contains($sousPrefecture)) { $this->sousPrefectures->add($sousPrefecture); $sousPrefecture->setPrefecture($this); } return $this; }
    public function removeSousPrefecture(SousPrefecture $sousPrefecture): static { if ($this->sousPrefectures->removeElement($sousPrefecture) && $sousPrefecture->getPrefecture() === $this) { $sousPrefecture->setPrefecture(null); } return $this; }
    /** @return Collection<int, Localite> */
    public function getLocalites(): Collection { return $this->localites; }
    public function addLocalite(Localite $localite): static { if (!$this->localites->contains($localite)) { $this->localites->add($localite); $localite->setPrefecture($this); } return $this; }
    public function removeLocalite(Localite $localite): static { if ($this->localites->removeElement($localite) && $localite->getPrefecture() === $this) { $localite->setPrefecture(null); } return $this; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
