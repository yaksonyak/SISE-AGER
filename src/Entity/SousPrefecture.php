<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SousPrefectureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SousPrefectureRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Une sous-préfecture existe déjà avec ce code.')]
#[ApiResource(
    normalizationContext: ['groups' => ['sous_prefecture:read']],
    denormalizationContext: ['groups' => ['sous_prefecture:write']]
)]
class SousPrefecture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['sous_prefecture:read', 'localite:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['sous_prefecture:read', 'sous_prefecture:write', 'localite:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 30, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 30)]
    #[Groups(['sous_prefecture:read', 'sous_prefecture:write', 'localite:read'])]
    private ?string $code = null;

    #[ORM\ManyToOne(inversedBy: 'sousPrefectures')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['sous_prefecture:read', 'sous_prefecture:write'])]
    private ?Prefecture $prefecture = null;

    #[ORM\Column]
    #[Groups(['sous_prefecture:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['sous_prefecture:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, Localite> */
    #[ORM\OneToMany(mappedBy: 'sousPrefecture', targetEntity: Localite::class)]
    private Collection $localites;

    public function __construct() { $this->localites = new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = trim($nom); return $this; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getPrefecture(): ?Prefecture { return $this->prefecture; }
    public function setPrefecture(?Prefecture $prefecture): static { $this->prefecture = $prefecture; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    /** @return Collection<int, Localite> */
    public function getLocalites(): Collection { return $this->localites; }
    public function addLocalite(Localite $localite): static { if (!$this->localites->contains($localite)) { $this->localites->add($localite); $localite->setSousPrefecture($this); } return $this; }
    public function removeLocalite(Localite $localite): static { if ($this->localites->removeElement($localite) && $localite->getSousPrefecture() === $this) { $localite->setSousPrefecture(null); } return $this; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
