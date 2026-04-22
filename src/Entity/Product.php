<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'product')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['stock_unit:read', 'stock_unit:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write', 'stock_unit:list'])]
    private string $reference;

    #[ORM\Column(length: 255)]
    #[Groups(['stock_unit:read', 'stock_unit:write', 'stock_unit:list'])]
    private string $designation;

    #[ORM\Column(length: 13, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?string $ean13 = null;

    #[ORM\Column(length: 14, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?string $gtin = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $refClient = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $famille = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $deposant = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $choixLotEnPrep = false;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $condit = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $consommable = false;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $controleUnicite = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $datePrevueInvent = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $delaiDluo = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $delaiFournisseur = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $delaiReappro = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dernierInvent = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dernierePrep = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $derniereRecep = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $desigLongue = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $dotation = false;

    #[ORM\Column(nullable: true)]
    private ?int $encoursExpedition001 = null;
    #[ORM\Column(nullable: true)]
    private ?int $encoursExpedition002 = null;
    #[ORM\Column(nullable: true)]
    private ?int $encoursExpedition003 = null;
    #[ORM\Column(nullable: true)]
    private ?int $encoursExpedition004 = null;
    #[ORM\Column(nullable: true)]
    private ?int $encoursReception001 = null;
    #[ORM\Column(nullable: true)]
    private ?int $encoursReception002 = null;
    #[ORM\Column(nullable: true)]
    private ?int $encoursReception003 = null;
    #[ORM\Column(nullable: true)]
    private ?int $encoursReception004 = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $estUnKit = false;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $etat = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $etiqArticle = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $fournisseur = null;

    #[ORM\Column(nullable: true)]
    private ?int $frequenceInvent = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $gestionDluo = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $gestionLot = false;

    #[ORM\Column(length: 255, nullable: true)] private ?string $infoArticle1 = null;
    #[ORM\Column(length: 255, nullable: true)] private ?string $infoArticle2 = null;
    #[ORM\Column(length: 255, nullable: true)] private ?string $infoArticle3 = null;
    #[ORM\Column(length: 255, nullable: true)] private ?string $infoArticle4 = null;
    #[ORM\Column(length: 255, nullable: true)] private ?string $infoArticle5 = null;
    #[ORM\Column(length: 255, nullable: true)] private ?string $infoArticle6 = null;
    #[ORM\Column(length: 255, nullable: true)] private ?string $infoArticle7 = null;
    #[ORM\Column(length: 255, nullable: true)] private ?string $infoArticle8 = null;
    #[ORM\Column(length: 255, nullable: true)] private ?string $infoArticle9 = null;
    #[ORM\Column(length: 255, nullable: true)] private ?string $infoArticle10 = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $instructionPersonnalisation = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $inventEnCours = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $kitALaVolee = false;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 3, nullable: true)]
    private ?string $nbComposants = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbInvent = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $obsolete = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $parametre = false;

    #[ORM\Column(nullable: true)]
    private ?int $pcb = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $personnalisation = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $plusDe30Kg = false;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $prixUnitHt = null;

    #[ORM\Column(nullable: true)] private ?int $qteDispoBad = null;
    #[ORM\Column(nullable: true)] private ?int $qteDispoGood = null;
    #[ORM\Column(nullable: true)] private ?int $qteEnQuarantaine001 = null;
    #[ORM\Column(nullable: true)] private ?int $qteEnQuarantaine002 = null;
    #[ORM\Column(nullable: true)] private ?int $qteEnQuarantaine003 = null;
    #[ORM\Column(nullable: true)] private ?int $qteEnQuarantaine004 = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $qtePreconiseeCommande = null;

    #[ORM\Column(nullable: true)] private ?int $qteReservee001 = null;
    #[ORM\Column(nullable: true)] private ?int $qteReservee002 = null;
    #[ORM\Column(nullable: true)] private ?int $qteReservee003 = null;
    #[ORM\Column(nullable: true)] private ?int $qteReservee004 = null;

    #[ORM\Column(nullable: true)] private ?int $qteStockee001 = null;
    #[ORM\Column(nullable: true)] private ?int $qteStockee002 = null;
    #[ORM\Column(nullable: true)] private ?int $qteStockee003 = null;
    #[ORM\Column(nullable: true)] private ?int $qteStockee004 = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $reassortAtelier = false;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $refFourn = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $refModele = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $releveNumeroParc = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $releveNumeroSerie = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $relveNpEnPrepa = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $relveNsEnPrepa = false;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $reparateur = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $reparateurS30 = false;

    #[ORM\Column(nullable: true)]
    private ?int $rot = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $screenable = false;

    #[ORM\Column(nullable: true)]
    private ?int $seuilAlerte = null;

    #[ORM\Column(nullable: true)]
    private ?int $spcb = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $tendance = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $tendanceCoefficient = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $tendanceMax = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $tendanceMin = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $typeEdition = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $ulReception001 = null;
    #[ORM\Column(length: 80, nullable: true)]
    private ?string $ulReception002 = null;
    #[ORM\Column(length: 80, nullable: true)]
    private ?string $ulReception003 = null;
    #[ORM\Column(length: 80, nullable: true)]
    private ?string $ulReception004 = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $uniteDeMesure = null;

    #[ORM\OneToMany(targetEntity: Charge::class, mappedBy: 'product')]
    private Collection $charges;

    public function __construct()
    {
        $this->charges = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "[{$this->reference}] {$this->designation}";
    }

    public function getId(): ?int { return $this->id; }

    public function getReference(): string { return $this->reference; }
    public function setReference(string $reference): static { $this->reference = $reference; return $this; }

    public function getDesignation(): string { return $this->designation; }
    public function setDesignation(string $designation): static { $this->designation = $designation; return $this; }

    public function getEan13(): ?string { return $this->ean13; }
    public function setEan13(?string $ean13): static { $this->ean13 = $ean13; return $this; }

    public function getGtin(): ?string { return $this->gtin; }
    public function setGtin(?string $gtin): static { $this->gtin = $gtin; return $this; }

    public function getRefClient(): ?string { return $this->refClient; }
    public function setRefClient(?string $refClient): static { $this->refClient = $refClient; return $this; }
    public function getFamille(): ?string { return $this->famille; }
    public function setFamille(?string $famille): static { $this->famille = $famille; return $this; }
    public function getDeposant(): ?string { return $this->deposant; }
    public function setDeposant(?string $deposant): static { $this->deposant = $deposant; return $this; }
    public function isChoixLotEnPrep(): bool { return $this->choixLotEnPrep; }
    public function setChoixLotEnPrep(bool $choixLotEnPrep): static { $this->choixLotEnPrep = $choixLotEnPrep; return $this; }
    public function getCondit(): ?string { return $this->condit; }
    public function setCondit(?string $condit): static { $this->condit = $condit; return $this; }
    public function isConsommable(): bool { return $this->consommable; }
    public function setConsommable(bool $consommable): static { $this->consommable = $consommable; return $this; }
    public function getControleUnicite(): ?string { return $this->controleUnicite; }
    public function setControleUnicite(?string $controleUnicite): static { $this->controleUnicite = $controleUnicite; return $this; }
    public function getDateCreation(): ?\DateTimeImmutable { return $this->dateCreation; }
    public function setDateCreation(?\DateTimeImmutable $dateCreation): static { $this->dateCreation = $dateCreation; return $this; }
    public function getDatePrevueInvent(): ?\DateTimeImmutable { return $this->datePrevueInvent; }
    public function setDatePrevueInvent(?\DateTimeImmutable $datePrevueInvent): static { $this->datePrevueInvent = $datePrevueInvent; return $this; }
    public function getDelaiDluo(): ?string { return $this->delaiDluo; }
    public function setDelaiDluo(?string $delaiDluo): static { $this->delaiDluo = $delaiDluo; return $this; }
    public function getDelaiFournisseur(): ?string { return $this->delaiFournisseur; }
    public function setDelaiFournisseur(?string $delaiFournisseur): static { $this->delaiFournisseur = $delaiFournisseur; return $this; }
    public function getDelaiReappro(): ?string { return $this->delaiReappro; }
    public function setDelaiReappro(?string $delaiReappro): static { $this->delaiReappro = $delaiReappro; return $this; }
    public function getDernierInvent(): ?\DateTimeImmutable { return $this->dernierInvent; }
    public function setDernierInvent(?\DateTimeImmutable $dernierInvent): static { $this->dernierInvent = $dernierInvent; return $this; }
    public function getDernierePrep(): ?\DateTimeImmutable { return $this->dernierePrep; }
    public function setDernierePrep(?\DateTimeImmutable $dernierePrep): static { $this->dernierePrep = $dernierePrep; return $this; }
    public function getDerniereRecep(): ?\DateTimeImmutable { return $this->derniereRecep; }
    public function setDerniereRecep(?\DateTimeImmutable $derniereRecep): static { $this->derniereRecep = $derniereRecep; return $this; }
    public function getDesigLongue(): ?string { return $this->desigLongue; }
    public function setDesigLongue(?string $desigLongue): static { $this->desigLongue = $desigLongue; return $this; }
    public function isDotation(): bool { return $this->dotation; }
    public function setDotation(bool $dotation): static { $this->dotation = $dotation; return $this; }
    public function getEncoursExpedition001(): ?int { return $this->encoursExpedition001; }
    public function setEncoursExpedition001(?int $encoursExpedition001): static { $this->encoursExpedition001 = $encoursExpedition001; return $this; }
    public function getEncoursExpedition002(): ?int { return $this->encoursExpedition002; }
    public function setEncoursExpedition002(?int $encoursExpedition002): static { $this->encoursExpedition002 = $encoursExpedition002; return $this; }
    public function getEncoursExpedition003(): ?int { return $this->encoursExpedition003; }
    public function setEncoursExpedition003(?int $encoursExpedition003): static { $this->encoursExpedition003 = $encoursExpedition003; return $this; }
    public function getEncoursExpedition004(): ?int { return $this->encoursExpedition004; }
    public function setEncoursExpedition004(?int $encoursExpedition004): static { $this->encoursExpedition004 = $encoursExpedition004; return $this; }
    public function getEncoursReception001(): ?int { return $this->encoursReception001; }
    public function setEncoursReception001(?int $encoursReception001): static { $this->encoursReception001 = $encoursReception001; return $this; }
    public function getEncoursReception002(): ?int { return $this->encoursReception002; }
    public function setEncoursReception002(?int $encoursReception002): static { $this->encoursReception002 = $encoursReception002; return $this; }
    public function getEncoursReception003(): ?int { return $this->encoursReception003; }
    public function setEncoursReception003(?int $encoursReception003): static { $this->encoursReception003 = $encoursReception003; return $this; }
    public function getEncoursReception004(): ?int { return $this->encoursReception004; }
    public function setEncoursReception004(?int $encoursReception004): static { $this->encoursReception004 = $encoursReception004; return $this; }
    public function isEstUnKit(): bool { return $this->estUnKit; }
    public function setEstUnKit(bool $estUnKit): static { $this->estUnKit = $estUnKit; return $this; }
    public function getEtat(): ?string { return $this->etat; }
    public function setEtat(?string $etat): static { $this->etat = $etat; return $this; }
    public function getEtiqArticle(): ?string { return $this->etiqArticle; }
    public function setEtiqArticle(?string $etiqArticle): static { $this->etiqArticle = $etiqArticle; return $this; }
    public function getFournisseur(): ?string { return $this->fournisseur; }
    public function setFournisseur(?string $fournisseur): static { $this->fournisseur = $fournisseur; return $this; }
    public function getFrequenceInvent(): ?int { return $this->frequenceInvent; }
    public function setFrequenceInvent(?int $frequenceInvent): static { $this->frequenceInvent = $frequenceInvent; return $this; }
    public function isGestionDluo(): bool { return $this->gestionDluo; }
    public function setGestionDluo(bool $gestionDluo): static { $this->gestionDluo = $gestionDluo; return $this; }
    public function isGestionLot(): bool { return $this->gestionLot; }
    public function setGestionLot(bool $gestionLot): static { $this->gestionLot = $gestionLot; return $this; }
    public function getInfoArticle1(): ?string { return $this->infoArticle1; }
    public function setInfoArticle1(?string $infoArticle1): static { $this->infoArticle1 = $infoArticle1; return $this; }
    public function getInfoArticle2(): ?string { return $this->infoArticle2; }
    public function setInfoArticle2(?string $infoArticle2): static { $this->infoArticle2 = $infoArticle2; return $this; }
    public function getInfoArticle3(): ?string { return $this->infoArticle3; }
    public function setInfoArticle3(?string $infoArticle3): static { $this->infoArticle3 = $infoArticle3; return $this; }
    public function getInfoArticle4(): ?string { return $this->infoArticle4; }
    public function setInfoArticle4(?string $infoArticle4): static { $this->infoArticle4 = $infoArticle4; return $this; }
    public function getInfoArticle5(): ?string { return $this->infoArticle5; }
    public function setInfoArticle5(?string $infoArticle5): static { $this->infoArticle5 = $infoArticle5; return $this; }
    public function getInfoArticle6(): ?string { return $this->infoArticle6; }
    public function setInfoArticle6(?string $infoArticle6): static { $this->infoArticle6 = $infoArticle6; return $this; }
    public function getInfoArticle7(): ?string { return $this->infoArticle7; }
    public function setInfoArticle7(?string $infoArticle7): static { $this->infoArticle7 = $infoArticle7; return $this; }
    public function getInfoArticle8(): ?string { return $this->infoArticle8; }
    public function setInfoArticle8(?string $infoArticle8): static { $this->infoArticle8 = $infoArticle8; return $this; }
    public function getInfoArticle9(): ?string { return $this->infoArticle9; }
    public function setInfoArticle9(?string $infoArticle9): static { $this->infoArticle9 = $infoArticle9; return $this; }
    public function getInfoArticle10(): ?string { return $this->infoArticle10; }
    public function setInfoArticle10(?string $infoArticle10): static { $this->infoArticle10 = $infoArticle10; return $this; }
    public function getInstructionPersonnalisation(): ?string { return $this->instructionPersonnalisation; }
    public function setInstructionPersonnalisation(?string $instructionPersonnalisation): static { $this->instructionPersonnalisation = $instructionPersonnalisation; return $this; }
    public function isInventEnCours(): bool { return $this->inventEnCours; }
    public function setInventEnCours(bool $inventEnCours): static { $this->inventEnCours = $inventEnCours; return $this; }
    public function isKitALaVolee(): bool { return $this->kitALaVolee; }
    public function setKitALaVolee(bool $kitALaVolee): static { $this->kitALaVolee = $kitALaVolee; return $this; }
    public function getNbComposants(): ?string { return $this->nbComposants; }
    public function setNbComposants(?string $nbComposants): static { $this->nbComposants = $nbComposants; return $this; }
    public function getNbInvent(): ?int { return $this->nbInvent; }
    public function setNbInvent(?int $nbInvent): static { $this->nbInvent = $nbInvent; return $this; }
    public function isObsolete(): bool { return $this->obsolete; }
    public function setObsolete(bool $obsolete): static { $this->obsolete = $obsolete; return $this; }
    public function isParametre(): bool { return $this->parametre; }
    public function setParametre(bool $parametre): static { $this->parametre = $parametre; return $this; }
    public function getPcb(): ?int { return $this->pcb; }
    public function setPcb(?int $pcb): static { $this->pcb = $pcb; return $this; }
    public function isPersonnalisation(): bool { return $this->personnalisation; }
    public function setPersonnalisation(bool $personnalisation): static { $this->personnalisation = $personnalisation; return $this; }
    public function isPlusDe30Kg(): bool { return $this->plusDe30Kg; }
    public function setPlusDe30Kg(bool $plusDe30Kg): static { $this->plusDe30Kg = $plusDe30Kg; return $this; }
    public function getPrixUnitHt(): ?string { return $this->prixUnitHt; }
    public function setPrixUnitHt(?string $prixUnitHt): static { $this->prixUnitHt = $prixUnitHt; return $this; }
    public function getQteDispoBad(): ?int { return $this->qteDispoBad; }
    public function setQteDispoBad(?int $qteDispoBad): static { $this->qteDispoBad = $qteDispoBad; return $this; }
    public function getQteDispoGood(): ?int { return $this->qteDispoGood; }
    public function setQteDispoGood(?int $qteDispoGood): static { $this->qteDispoGood = $qteDispoGood; return $this; }
    public function getQteEnQuarantaine001(): ?int { return $this->qteEnQuarantaine001; }
    public function setQteEnQuarantaine001(?int $qteEnQuarantaine001): static { $this->qteEnQuarantaine001 = $qteEnQuarantaine001; return $this; }
    public function getQteEnQuarantaine002(): ?int { return $this->qteEnQuarantaine002; }
    public function setQteEnQuarantaine002(?int $qteEnQuarantaine002): static { $this->qteEnQuarantaine002 = $qteEnQuarantaine002; return $this; }
    public function getQteEnQuarantaine003(): ?int { return $this->qteEnQuarantaine003; }
    public function setQteEnQuarantaine003(?int $qteEnQuarantaine003): static { $this->qteEnQuarantaine003 = $qteEnQuarantaine003; return $this; }
    public function getQteEnQuarantaine004(): ?int { return $this->qteEnQuarantaine004; }
    public function setQteEnQuarantaine004(?int $qteEnQuarantaine004): static { $this->qteEnQuarantaine004 = $qteEnQuarantaine004; return $this; }
    public function getQtePreconiseeCommande(): ?string { return $this->qtePreconiseeCommande; }
    public function setQtePreconiseeCommande(?string $qtePreconiseeCommande): static { $this->qtePreconiseeCommande = $qtePreconiseeCommande; return $this; }
    public function getQteReservee001(): ?int { return $this->qteReservee001; }
    public function setQteReservee001(?int $qteReservee001): static { $this->qteReservee001 = $qteReservee001; return $this; }
    public function getQteReservee002(): ?int { return $this->qteReservee002; }
    public function setQteReservee002(?int $qteReservee002): static { $this->qteReservee002 = $qteReservee002; return $this; }
    public function getQteReservee003(): ?int { return $this->qteReservee003; }
    public function setQteReservee003(?int $qteReservee003): static { $this->qteReservee003 = $qteReservee003; return $this; }
    public function getQteReservee004(): ?int { return $this->qteReservee004; }
    public function setQteReservee004(?int $qteReservee004): static { $this->qteReservee004 = $qteReservee004; return $this; }
    public function getQteStockee001(): ?int { return $this->qteStockee001; }
    public function setQteStockee001(?int $qteStockee001): static { $this->qteStockee001 = $qteStockee001; return $this; }
    public function getQteStockee002(): ?int { return $this->qteStockee002; }
    public function setQteStockee002(?int $qteStockee002): static { $this->qteStockee002 = $qteStockee002; return $this; }
    public function getQteStockee003(): ?int { return $this->qteStockee003; }
    public function setQteStockee003(?int $qteStockee003): static { $this->qteStockee003 = $qteStockee003; return $this; }
    public function getQteStockee004(): ?int { return $this->qteStockee004; }
    public function setQteStockee004(?int $qteStockee004): static { $this->qteStockee004 = $qteStockee004; return $this; }
    public function isReassortAtelier(): bool { return $this->reassortAtelier; }
    public function setReassortAtelier(bool $reassortAtelier): static { $this->reassortAtelier = $reassortAtelier; return $this; }
    public function getRefFourn(): ?string { return $this->refFourn; }
    public function setRefFourn(?string $refFourn): static { $this->refFourn = $refFourn; return $this; }
    public function getRefModele(): ?string { return $this->refModele; }
    public function setRefModele(?string $refModele): static { $this->refModele = $refModele; return $this; }
    public function isReleveNumeroParc(): bool { return $this->releveNumeroParc; }
    public function setReleveNumeroParc(bool $releveNumeroParc): static { $this->releveNumeroParc = $releveNumeroParc; return $this; }
    public function isReleveNumeroSerie(): bool { return $this->releveNumeroSerie; }
    public function setReleveNumeroSerie(bool $releveNumeroSerie): static { $this->releveNumeroSerie = $releveNumeroSerie; return $this; }
    public function isRelveNpEnPrepa(): bool { return $this->relveNpEnPrepa; }
    public function setRelveNpEnPrepa(bool $relveNpEnPrepa): static { $this->relveNpEnPrepa = $relveNpEnPrepa; return $this; }
    public function isRelveNsEnPrepa(): bool { return $this->relveNsEnPrepa; }
    public function setRelveNsEnPrepa(bool $relveNsEnPrepa): static { $this->relveNsEnPrepa = $relveNsEnPrepa; return $this; }
    public function getReparateur(): ?string { return $this->reparateur; }
    public function setReparateur(?string $reparateur): static { $this->reparateur = $reparateur; return $this; }
    public function isReparateurS30(): bool { return $this->reparateurS30; }
    public function setReparateurS30(bool $reparateurS30): static { $this->reparateurS30 = $reparateurS30; return $this; }
    public function getRot(): ?int { return $this->rot; }
    public function setRot(?int $rot): static { $this->rot = $rot; return $this; }
    public function isScreenable(): bool { return $this->screenable; }
    public function setScreenable(bool $screenable): static { $this->screenable = $screenable; return $this; }
    public function getSeuilAlerte(): ?int { return $this->seuilAlerte; }
    public function setSeuilAlerte(?int $seuilAlerte): static { $this->seuilAlerte = $seuilAlerte; return $this; }
    public function getSpcb(): ?int { return $this->spcb; }
    public function setSpcb(?int $spcb): static { $this->spcb = $spcb; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(?string $statut): static { $this->statut = $statut; return $this; }
    public function getTendance(): ?string { return $this->tendance; }
    public function setTendance(?string $tendance): static { $this->tendance = $tendance; return $this; }
    public function getTendanceCoefficient(): ?string { return $this->tendanceCoefficient; }
    public function setTendanceCoefficient(?string $tendanceCoefficient): static { $this->tendanceCoefficient = $tendanceCoefficient; return $this; }
    public function getTendanceMax(): ?string { return $this->tendanceMax; }
    public function setTendanceMax(?string $tendanceMax): static { $this->tendanceMax = $tendanceMax; return $this; }
    public function getTendanceMin(): ?string { return $this->tendanceMin; }
    public function setTendanceMin(?string $tendanceMin): static { $this->tendanceMin = $tendanceMin; return $this; }
    public function getTypeEdition(): ?string { return $this->typeEdition; }
    public function setTypeEdition(?string $typeEdition): static { $this->typeEdition = $typeEdition; return $this; }
    public function getUlReception001(): ?string { return $this->ulReception001; }
    public function setUlReception001(?string $ulReception001): static { $this->ulReception001 = $ulReception001; return $this; }
    public function getUlReception002(): ?string { return $this->ulReception002; }
    public function setUlReception002(?string $ulReception002): static { $this->ulReception002 = $ulReception002; return $this; }
    public function getUlReception003(): ?string { return $this->ulReception003; }
    public function setUlReception003(?string $ulReception003): static { $this->ulReception003 = $ulReception003; return $this; }
    public function getUlReception004(): ?string { return $this->ulReception004; }
    public function setUlReception004(?string $ulReception004): static { $this->ulReception004 = $ulReception004; return $this; }
    public function getUniteDeMesure(): ?string { return $this->uniteDeMesure; }
    public function setUniteDeMesure(?string $uniteDeMesure): static { $this->uniteDeMesure = $uniteDeMesure; return $this; }

    public function getCharges(): Collection { return $this->charges; }
}
