<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PointGpsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PointGpsRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['point_gps:read']],
    denormalizationContext: ['groups' => ['point_gps:write']]
)]
class PointGps
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['point_gps:read', 'infrastructure_electrique:read', 'site_energetique:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Range(min: -90, max: 90)]
    #[Groups(['point_gps:read', 'point_gps:write', 'infrastructure_electrique:read', 'site_energetique:read'])]
    private ?float $latitude = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Range(min: -180, max: 180)]
    #[Groups(['point_gps:read', 'point_gps:write', 'infrastructure_electrique:read', 'site_energetique:read'])]
    private ?float $longitude = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['point_gps:read', 'point_gps:write'])]
    private ?float $altitude = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['point_gps:read', 'point_gps:write'])]
    private ?float $precisionGps = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Groups(['point_gps:read', 'point_gps:write'])]
    private ?string $source = null;

    #[ORM\Column]
    #[Groups(['point_gps:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['point_gps:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, InfrastructureElectrique> */
    #[ORM\OneToMany(mappedBy: 'pointGps', targetEntity: InfrastructureElectrique::class)]
    private Collection $infrastructuresElectriques;

    /** @var Collection<int, SiteEnergetique> */
    #[ORM\OneToMany(mappedBy: 'pointGps', targetEntity: SiteEnergetique::class)]
    private Collection $sitesEnergetiques;

    public function __construct() { $this->infrastructuresElectriques = new ArrayCollection(); $this->sitesEnergetiques = new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getLatitude(): ?float { return $this->latitude; }
    public function setLatitude(float $latitude): static { $this->latitude = $latitude; return $this; }
    public function getLongitude(): ?float { return $this->longitude; }
    public function setLongitude(float $longitude): static { $this->longitude = $longitude; return $this; }
    public function getAltitude(): ?float { return $this->altitude; }
    public function setAltitude(?float $altitude): static { $this->altitude = $altitude; return $this; }
    public function getPrecisionGps(): ?float { return $this->precisionGps; }
    public function setPrecisionGps(?float $precisionGps): static { $this->precisionGps = $precisionGps; return $this; }
    public function getSource(): ?string { return $this->source; }
    public function setSource(?string $source): static { $this->source = $source !== null ? trim($source) : null; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    /** @return Collection<int, InfrastructureElectrique> */
    public function getInfrastructuresElectriques(): Collection { return $this->infrastructuresElectriques; }
    public function addInfrastructureElectrique(InfrastructureElectrique $infrastructureElectrique): static { if (!$this->infrastructuresElectriques->contains($infrastructureElectrique)) { $this->infrastructuresElectriques->add($infrastructureElectrique); $infrastructureElectrique->setPointGps($this); } return $this; }
    public function removeInfrastructureElectrique(InfrastructureElectrique $infrastructureElectrique): static { if ($this->infrastructuresElectriques->removeElement($infrastructureElectrique) && $infrastructureElectrique->getPointGps() === $this) { $infrastructureElectrique->setPointGps(null); } return $this; }
    /** @return Collection<int, SiteEnergetique> */
    public function getSitesEnergetiques(): Collection { return $this->sitesEnergetiques; }
    public function addSiteEnergetique(SiteEnergetique $siteEnergetique): static { if (!$this->sitesEnergetiques->contains($siteEnergetique)) { $this->sitesEnergetiques->add($siteEnergetique); $siteEnergetique->setPointGps($this); } return $this; }
    public function removeSiteEnergetique(SiteEnergetique $siteEnergetique): static { if ($this->sitesEnergetiques->removeElement($siteEnergetique) && $siteEnergetique->getPointGps() === $this) { $siteEnergetique->setPointGps(null); } return $this; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
