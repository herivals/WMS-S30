<?php

namespace App\Entity;

use App\Enum\EtatUL;
use App\Enum\StatutUL;
use App\Enum\TypeFlux;
use App\Enum\TypeReception;
use App\Enum\TypeUnite;
use App\Repository\ChargeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ChargeRepository::class)]
#[ORM\Table(name: 'stock_unit')]
#[ORM\HasLifecycleCallbacks]
class Charge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['stock_unit:read', 'stock_unit:list'])]
    private ?int $id = null;

    // ── Identité ──
    #[ORM\Column(length: 50, unique: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write', 'stock_unit:list'])]
    private string $codeCharge;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?string $serialNumber = null;

    // ── Relations ──
    #[ORM\ManyToOne(targetEntity: Reception::class, inversedBy: 'charges')]
    #[Groups(['stock_unit:read'])]
    private ?Reception $reception = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'charges')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['stock_unit:read', 'stock_unit:write', 'stock_unit:list'])]
    private Product $product;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'charges')]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?Client $owner = null;

    #[ORM\ManyToOne(targetEntity: Location::class, inversedBy: 'charges')]
    #[Groups(['stock_unit:read', 'stock_unit:write', 'stock_unit:list'])]
    private ?Location $emplacement = null;

    // ── Désignation ──
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?string $designation = null;

    // ── Statut / Type ──
    #[ORM\Column(length: 20, enumType: StatutUL::class)]
    #[Groups(['stock_unit:read', 'stock_unit:write', 'stock_unit:list'])]
    private StatutUL $statut = StatutUL::DISPONIBLE;

    #[ORM\Column(length: 10, enumType: TypeUnite::class)]
    #[Groups(['stock_unit:read', 'stock_unit:write', 'stock_unit:list'])]
    private TypeUnite $typeUnite;

    // ── Lot / Traçabilité ──
    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?string $lot = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?string $lotFabrication = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?\DateTimeImmutable $dateFabrication = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?\DateTimeImmutable $dluo = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['stock_unit:read'])]
    private ?\DateTimeImmutable $dateDernierMouvement = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['stock_unit:read'])]
    private ?\DateTimeImmutable $dateDernierPrelevement = null;

    // ── Localisation dénormalisée ──
    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:list'])]
    private ?string $allee = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:list'])]
    private ?string $rack = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:list'])]
    private ?string $niveau = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:list'])]
    private ?string $position = null;

    // ── Dimensions ──
    #[ORM\Column(nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?float $poids = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?float $largeur = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?float $hauteur = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?float $profondeur = null;

    // ── Quantités ──
    #[ORM\Column]
    #[Groups(['stock_unit:read', 'stock_unit:write', 'stock_unit:list'])]
    private float $quantite = 0;

    #[ORM\Column]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private float $quantiteReservee = 0;

    #[ORM\Column]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private float $quantiteARegrouper = 0;

    // ── Métier ──
    #[ORM\Column(length: 20, enumType: TypeReception::class, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?TypeReception $typeReception = null;

    #[ORM\Column(length: 5, enumType: TypeFlux::class, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?TypeFlux $typeFlux = null;

    #[ORM\Column(length: 10, enumType: EtatUL::class)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private EtatUL $etat = EtatUL::GOOD;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?string $familleLogistique = null;

    // ── Atelier ──
    #[ORM\Column(nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?float $tempsAtelier = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?string $technicien = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?string $etatFinal = null;

    // ── Facturation ──
    #[ORM\Column(nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?float $prixAchat = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?string $uniteFacturation = null;

    // ── Flags ──
    #[ORM\Column]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private bool $multiReference = false;

    #[ORM\Column]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private bool $aInventorier = false;

    #[ORM\Column]
    #[Groups(['stock_unit:read', 'stock_unit:list'])]
    private bool $estRebut = false;

    #[ORM\Column]
    #[Groups(['stock_unit:read', 'stock_unit:list'])]
    private bool $estBloque = false;

    // ── Audit ──
    #[ORM\Column]
    #[Groups(['stock_unit:read'])]
    private \DateTimeImmutable $dateCreation;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['stock_unit:read'])]
    private ?string $createdBy = null;

    public function __toString(): string
    {
        return $this->codeCharge;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if (!isset($this->dateCreation)) {
            $this->dateCreation = new \DateTimeImmutable();
        }
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    // ── Quantité disponible (virtuelle) ──
    #[Groups(['stock_unit:read', 'stock_unit:list'])]
    public function getQuantiteDisponible(): float
    {
        return $this->quantite - $this->quantiteReservee;
    }

    // ── Méthodes métier ──
    public function marquerRebut(): static
    {
        $this->estRebut = true;
        $this->statut = StatutUL::REBUT;
        return $this;
    }

    public function bloquer(): static
    {
        $this->estBloque = true;
        $this->statut = StatutUL::BLOQUE;
        return $this;
    }

    public function debloquer(): static
    {
        $this->estBloque = false;
        $this->statut = StatutUL::DISPONIBLE;
        return $this;
    }

    public function synchroniserLocalisation(): static
    {
        if ($this->emplacement !== null) {
            $this->allee    = $this->emplacement->getAllee();
            $this->rack     = $this->emplacement->getRack();
            $this->niveau   = $this->emplacement->getNiveau();
            $this->position = $this->emplacement->getPosition();
        }
        return $this;
    }

    // ── Getters / Setters ──
    public function getId(): ?int { return $this->id; }

    public function getCodeCharge(): string { return $this->codeCharge; }
    public function setCodeCharge(string $codeCharge): static { $this->codeCharge = $codeCharge; return $this; }

    public function getSerialNumber(): ?string { return $this->serialNumber; }
    public function setSerialNumber(?string $sn): static { $this->serialNumber = $sn; return $this; }

    public function getReception(): ?Reception { return $this->reception; }
    public function setReception(?Reception $r): static { $this->reception = $r; return $this; }

    public function getProduct(): Product { return $this->product; }
    public function setProduct(Product $p): static { $this->product = $p; return $this; }

    public function getOwner(): ?Client { return $this->owner; }
    public function setOwner(?Client $o): static { $this->owner = $o; return $this; }

    public function getEmplacement(): ?Location { return $this->emplacement; }
    public function setEmplacement(?Location $l): static { $this->emplacement = $l; $this->synchroniserLocalisation(); return $this; }

    public function getDesignation(): ?string { return $this->designation; }
    public function setDesignation(?string $d): static { $this->designation = $d; return $this; }

    public function getStatut(): StatutUL { return $this->statut; }
    public function setStatut(StatutUL $s): static { $this->statut = $s; return $this; }

    public function getTypeUnite(): TypeUnite { return $this->typeUnite; }
    public function setTypeUnite(TypeUnite $t): static { $this->typeUnite = $t; return $this; }

    public function getLot(): ?string { return $this->lot; }
    public function setLot(?string $l): static { $this->lot = $l; return $this; }

    public function getLotFabrication(): ?string { return $this->lotFabrication; }
    public function setLotFabrication(?string $l): static { $this->lotFabrication = $l; return $this; }

    public function getDateFabrication(): ?\DateTimeImmutable { return $this->dateFabrication; }
    public function setDateFabrication(?\DateTimeImmutable $d): static { $this->dateFabrication = $d; return $this; }

    public function getDluo(): ?\DateTimeImmutable { return $this->dluo; }
    public function setDluo(?\DateTimeImmutable $d): static { $this->dluo = $d; return $this; }

    public function getDateDernierMouvement(): ?\DateTimeImmutable { return $this->dateDernierMouvement; }
    public function setDateDernierMouvement(?\DateTimeImmutable $d): static { $this->dateDernierMouvement = $d; return $this; }

    public function getDateDernierPrelevement(): ?\DateTimeImmutable { return $this->dateDernierPrelevement; }
    public function setDateDernierPrelevement(?\DateTimeImmutable $d): static { $this->dateDernierPrelevement = $d; return $this; }

    public function getAllee(): ?string { return $this->allee; }
    public function setAllee(?string $a): static { $this->allee = $a; return $this; }

    public function getRack(): ?string { return $this->rack; }
    public function setRack(?string $r): static { $this->rack = $r; return $this; }

    public function getNiveau(): ?string { return $this->niveau; }
    public function setNiveau(?string $n): static { $this->niveau = $n; return $this; }

    public function getPosition(): ?string { return $this->position; }
    public function setPosition(?string $p): static { $this->position = $p; return $this; }

    public function getPoids(): ?float { return $this->poids; }
    public function setPoids(?float $p): static { $this->poids = $p; return $this; }

    public function getLargeur(): ?float { return $this->largeur; }
    public function setLargeur(?float $l): static { $this->largeur = $l; return $this; }

    public function getHauteur(): ?float { return $this->hauteur; }
    public function setHauteur(?float $h): static { $this->hauteur = $h; return $this; }

    public function getProfondeur(): ?float { return $this->profondeur; }
    public function setProfondeur(?float $p): static { $this->profondeur = $p; return $this; }

    public function getQuantite(): float { return $this->quantite; }
    public function setQuantite(float $q): static { $this->quantite = $q; return $this; }

    public function getQuantiteReservee(): float { return $this->quantiteReservee; }
    public function setQuantiteReservee(float $q): static { $this->quantiteReservee = $q; return $this; }

    public function getQuantiteARegrouper(): float { return $this->quantiteARegrouper; }
    public function setQuantiteARegrouper(float $q): static { $this->quantiteARegrouper = $q; return $this; }

    public function getTypeReception(): ?TypeReception { return $this->typeReception; }
    public function setTypeReception(?TypeReception $t): static { $this->typeReception = $t; return $this; }

    public function getTypeFlux(): ?TypeFlux { return $this->typeFlux; }
    public function setTypeFlux(?TypeFlux $t): static { $this->typeFlux = $t; return $this; }

    public function getEtat(): EtatUL { return $this->etat; }
    public function setEtat(EtatUL $e): static { $this->etat = $e; return $this; }

    public function getFamilleLogistique(): ?string { return $this->familleLogistique; }
    public function setFamilleLogistique(?string $f): static { $this->familleLogistique = $f; return $this; }

    public function getTempsAtelier(): ?float { return $this->tempsAtelier; }
    public function setTempsAtelier(?float $t): static { $this->tempsAtelier = $t; return $this; }

    public function getTechnicien(): ?string { return $this->technicien; }
    public function setTechnicien(?string $t): static { $this->technicien = $t; return $this; }

    public function getEtatFinal(): ?string { return $this->etatFinal; }
    public function setEtatFinal(?string $e): static { $this->etatFinal = $e; return $this; }

    public function getPrixAchat(): ?float { return $this->prixAchat; }
    public function setPrixAchat(?float $p): static { $this->prixAchat = $p; return $this; }

    public function getUniteFacturation(): ?string { return $this->uniteFacturation; }
    public function setUniteFacturation(?string $u): static { $this->uniteFacturation = $u; return $this; }

    public function isMultiReference(): bool { return $this->multiReference; }
    public function setMultiReference(bool $m): static { $this->multiReference = $m; return $this; }

    public function isAInventorier(): bool { return $this->aInventorier; }
    public function setAInventorier(bool $a): static { $this->aInventorier = $a; return $this; }

    public function isEstRebut(): bool { return $this->estRebut; }
    public function isEstBloque(): bool { return $this->estBloque; }

    public function getDateCreation(): \DateTimeImmutable { return $this->dateCreation; }

    public function getCreatedBy(): ?string { return $this->createdBy; }
    public function setCreatedBy(?string $c): static { $this->createdBy = $c; return $this; }
}
