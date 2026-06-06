<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\LocaliteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LocaliteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Une localité existe déjà avec ce code.')]
#[ApiResource(
    normalizationContext: ['groups' => ['localite:read']],
    denormalizationContext: ['groups' => ['localite:write']]
)]
class Localite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['localite:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['localite:read', 'localite:write'])]
    private ?string $nom = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['localite:read', 'localite:write'])]
    private ?string $code = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: -180, max: 180)]
    #[Groups(['localite:read', 'localite:write'])]
    private ?float $longitude = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: -90, max: 90)]
    #[Groups(['localite:read', 'localite:write'])]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['localite:read', 'localite:write'])]
    private ?int $nombreMenages = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['localite:read', 'localite:write'])]
    private ?int $populationTotale = null;

    #[ORM\Column(length: 80, nullable: true)]
    #[Assert\Length(max: 80)]
    #[Groups(['localite:read', 'localite:write'])]
    private ?string $categoriePopulation = null;

    #[ORM\Column(length: 80, nullable: true)]
    #[Assert\Length(max: 80)]
    #[Groups(['localite:read', 'localite:write'])]
    private ?string $statutElectrification = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['localite:read', 'localite:write'])]
    private ?float $distanceReseauKm = null;

    #[ORM\ManyToOne(inversedBy: 'localites')]
    #[Groups(['localite:read', 'localite:write'])]
    private ?Prefecture $prefecture = null;

    #[ORM\ManyToOne(inversedBy: 'localites')]
    #[Groups(['localite:read', 'localite:write'])]
    private ?SousPrefecture $sousPrefecture = null;

    #[ORM\ManyToOne(inversedBy: 'localites')]
    #[Groups(['localite:read', 'localite:write'])]
    private ?Zer $zer = null;

    #[ORM\ManyToOne(inversedBy: 'localites')]
    #[Groups(['localite:read', 'localite:write'])]
    private ?ProgrammePner $programmePner = null;

    #[ORM\ManyToOne(inversedBy: 'localites')]
    #[Groups(['localite:read', 'localite:write'])]
    private ?SystemeElectrification $systemeElectrification = null;

    #[ORM\Column]
    #[Groups(['localite:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['localite:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, ProjetLocalite> */
    #[ORM\OneToMany(mappedBy: 'localite', targetEntity: ProjetLocalite::class)]
    private Collection $projetLocalites;

    /** @var Collection<int, InfrastructureElectrique> */
    #[ORM\OneToMany(mappedBy: 'localite', targetEntity: InfrastructureElectrique::class)]
    private Collection $infrastructuresElectriques;

    /** @var Collection<int, SiteEnergetique> */
    #[ORM\OneToMany(mappedBy: 'localite', targetEntity: SiteEnergetique::class)]
    private Collection $sitesEnergetiques;

    /** @var Collection<int, DonneeGeospatialeLocalite> */
    #[ORM\OneToMany(mappedBy: 'localite', targetEntity: DonneeGeospatialeLocalite::class)]
    private Collection $donneesGeospatiales;

    public function __construct() { $this->projetLocalites = new ArrayCollection(); $this->infrastructuresElectriques = new ArrayCollection(); $this->sitesEnergetiques = new ArrayCollection(); $this->donneesGeospatiales = new ArrayCollection(); }

    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = trim($nom); return $this; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = mb_strtoupper(trim($code)); return $this; }
    public function getLongitude(): ?float { return $this->longitude; }
    public function setLongitude(?float $longitude): static { $this->longitude = $longitude; return $this; }
    public function getLatitude(): ?float { return $this->latitude; }
    public function setLatitude(?float $latitude): static { $this->latitude = $latitude; return $this; }
    public function getNombreMenages(): ?int { return $this->nombreMenages; }
    public function setNombreMenages(?int $nombreMenages): static { $this->nombreMenages = $nombreMenages; return $this; }
    public function getPopulationTotale(): ?int { return $this->populationTotale; }
    public function setPopulationTotale(?int $populationTotale): static { $this->populationTotale = $populationTotale; return $this; }
    public function getCategoriePopulation(): ?string { return $this->categoriePopulation; }
    public function setCategoriePopulation(?string $categoriePopulation): static { $this->categoriePopulation = $categoriePopulation; return $this; }
    public function getStatutElectrification(): ?string { return $this->statutElectrification; }
    public function setStatutElectrification(?string $statutElectrification): static { $this->statutElectrification = $statutElectrification; return $this; }
    public function getDistanceReseauKm(): ?float { return $this->distanceReseauKm; }
    public function setDistanceReseauKm(?float $distanceReseauKm): static { $this->distanceReseauKm = $distanceReseauKm; return $this; }
    public function getPrefecture(): ?Prefecture { return $this->prefecture; }
    public function setPrefecture(?Prefecture $prefecture): static { $this->prefecture = $prefecture; return $this; }
    public function getSousPrefecture(): ?SousPrefecture { return $this->sousPrefecture; }
    public function setSousPrefecture(?SousPrefecture $sousPrefecture): static { $this->sousPrefecture = $sousPrefecture; return $this; }
    public function getZer(): ?Zer { return $this->zer; }
    public function setZer(?Zer $zer): static { $this->zer = $zer; return $this; }
    public function getProgrammePner(): ?ProgrammePner { return $this->programmePner; }
    public function setProgrammePner(?ProgrammePner $programmePner): static { $this->programmePner = $programmePner; return $this; }
    public function getSystemeElectrification(): ?SystemeElectrification { return $this->systemeElectrification; }
    public function setSystemeElectrification(?SystemeElectrification $systemeElectrification): static { $this->systemeElectrification = $systemeElectrification; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    /** @return Collection<int, ProjetLocalite> */
    public function getProjetLocalites(): Collection { return $this->projetLocalites; }
    public function addProjetLocalite(ProjetLocalite $projetLocalite): static { if (!$this->projetLocalites->contains($projetLocalite)) { $this->projetLocalites->add($projetLocalite); $projetLocalite->setLocalite($this); } return $this; }
    public function removeProjetLocalite(ProjetLocalite $projetLocalite): static { if ($this->projetLocalites->removeElement($projetLocalite) && $projetLocalite->getLocalite() === $this) { $projetLocalite->setLocalite(null); } return $this; }
    /** @return Collection<int, InfrastructureElectrique> */
    public function getInfrastructuresElectriques(): Collection { return $this->infrastructuresElectriques; }
    public function addInfrastructureElectrique(InfrastructureElectrique $infrastructureElectrique): static { if (!$this->infrastructuresElectriques->contains($infrastructureElectrique)) { $this->infrastructuresElectriques->add($infrastructureElectrique); $infrastructureElectrique->setLocalite($this); } return $this; }
    public function removeInfrastructureElectrique(InfrastructureElectrique $infrastructureElectrique): static { if ($this->infrastructuresElectriques->removeElement($infrastructureElectrique) && $infrastructureElectrique->getLocalite() === $this) { $infrastructureElectrique->setLocalite(null); } return $this; }
    /** @return Collection<int, SiteEnergetique> */
    public function getSitesEnergetiques(): Collection { return $this->sitesEnergetiques; }
    public function addSiteEnergetique(SiteEnergetique $siteEnergetique): static { if (!$this->sitesEnergetiques->contains($siteEnergetique)) { $this->sitesEnergetiques->add($siteEnergetique); $siteEnergetique->setLocalite($this); } return $this; }
    public function removeSiteEnergetique(SiteEnergetique $siteEnergetique): static { if ($this->sitesEnergetiques->removeElement($siteEnergetique) && $siteEnergetique->getLocalite() === $this) { $siteEnergetique->setLocalite(null); } return $this; }
    /** @return Collection<int, DonneeGeospatialeLocalite> */
    public function getDonneesGeospatiales(): Collection { return $this->donneesGeospatiales; }
    public function addDonneeGeospatiale(DonneeGeospatialeLocalite $donneeGeospatiale): static { if (!$this->donneesGeospatiales->contains($donneeGeospatiale)) { $this->donneesGeospatiales->add($donneeGeospatiale); $donneeGeospatiale->setLocalite($this); } return $this; }
    public function removeDonneeGeospatiale(DonneeGeospatialeLocalite $donneeGeospatiale): static { if ($this->donneesGeospatiales->removeElement($donneeGeospatiale) && $donneeGeospatiale->getLocalite() === $this) { $donneeGeospatiale->setLocalite(null); } return $this; }
    #[ORM\PrePersist]
    public function initializeTimestamps(): void { $now = new \DateTimeImmutable(); $this->createdAt ??= $now; $this->updatedAt = $now; }
    #[ORM\PreUpdate]
    public function updateTimestamp(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
