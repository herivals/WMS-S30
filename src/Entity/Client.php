<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\Table(name: 'client')]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['stock_unit:read', 'stock_unit:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write', 'stock_unit:list'])]
    private ?string $email = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write', 'stock_unit:list'])]
    private string $deposant;

    #[ORM\Column(length: 150)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private string $nomDeposant;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $batRetour = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $villeSoc = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $telSoc = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse2Soc = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $paysSoc = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaireSoc = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $releveNumeroParc = false;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $contactSoc = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $releveNumeroSerie = false;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $codePaysSoc = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse1Soc = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $cpSoc = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $faxSoc = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $quartierRetour = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $responsableCompte = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbJoursTravaillesSur3Mois = null;

    #[ORM\OneToMany(targetEntity: Charge::class, mappedBy: 'owner')]
    private Collection $charges;

    public function __construct()
    {
        $this->charges = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "[{$this->deposant}] {$this->nomDeposant}";
    }

    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): static { $this->email = $email; return $this; }

    public function getDeposant(): string { return $this->deposant; }
    public function setDeposant(string $deposant): static { $this->deposant = $deposant; return $this; }

    public function getNomDeposant(): string { return $this->nomDeposant; }
    public function setNomDeposant(string $nomDeposant): static { $this->nomDeposant = $nomDeposant; return $this; }

    public function getBatRetour(): ?string { return $this->batRetour; }
    public function setBatRetour(?string $batRetour): static { $this->batRetour = $batRetour; return $this; }

    public function getVilleSoc(): ?string { return $this->villeSoc; }
    public function setVilleSoc(?string $villeSoc): static { $this->villeSoc = $villeSoc; return $this; }

    public function getTelSoc(): ?string { return $this->telSoc; }
    public function setTelSoc(?string $telSoc): static { $this->telSoc = $telSoc; return $this; }

    public function getAdresse2Soc(): ?string { return $this->adresse2Soc; }
    public function setAdresse2Soc(?string $adresse2Soc): static { $this->adresse2Soc = $adresse2Soc; return $this; }

    public function getPaysSoc(): ?string { return $this->paysSoc; }
    public function setPaysSoc(?string $paysSoc): static { $this->paysSoc = $paysSoc; return $this; }

    public function getCommentaireSoc(): ?string { return $this->commentaireSoc; }
    public function setCommentaireSoc(?string $commentaireSoc): static { $this->commentaireSoc = $commentaireSoc; return $this; }

    public function isReleveNumeroParc(): bool { return $this->releveNumeroParc; }
    public function setReleveNumeroParc(bool $releveNumeroParc): static { $this->releveNumeroParc = $releveNumeroParc; return $this; }

    public function getContactSoc(): ?string { return $this->contactSoc; }
    public function setContactSoc(?string $contactSoc): static { $this->contactSoc = $contactSoc; return $this; }

    public function isReleveNumeroSerie(): bool { return $this->releveNumeroSerie; }
    public function setReleveNumeroSerie(bool $releveNumeroSerie): static { $this->releveNumeroSerie = $releveNumeroSerie; return $this; }

    public function getCodePaysSoc(): ?string { return $this->codePaysSoc; }
    public function setCodePaysSoc(?string $codePaysSoc): static { $this->codePaysSoc = $codePaysSoc; return $this; }

    public function getAdresse1Soc(): ?string { return $this->adresse1Soc; }
    public function setAdresse1Soc(?string $adresse1Soc): static { $this->adresse1Soc = $adresse1Soc; return $this; }

    public function getCpSoc(): ?string { return $this->cpSoc; }
    public function setCpSoc(?string $cpSoc): static { $this->cpSoc = $cpSoc; return $this; }

    public function getFaxSoc(): ?string { return $this->faxSoc; }
    public function setFaxSoc(?string $faxSoc): static { $this->faxSoc = $faxSoc; return $this; }

    public function getQuartierRetour(): ?string { return $this->quartierRetour; }
    public function setQuartierRetour(?string $quartierRetour): static { $this->quartierRetour = $quartierRetour; return $this; }

    public function getResponsableCompte(): ?string { return $this->responsableCompte; }
    public function setResponsableCompte(?string $responsableCompte): static { $this->responsableCompte = $responsableCompte; return $this; }

    public function getNbJoursTravaillesSur3Mois(): ?int { return $this->nbJoursTravaillesSur3Mois; }
    public function setNbJoursTravaillesSur3Mois(?int $nbJoursTravaillesSur3Mois): static { $this->nbJoursTravaillesSur3Mois = $nbJoursTravaillesSur3Mois; return $this; }

    // Backward-compatible aliases with previous naming.
    public function getNom(): string { return $this->nomDeposant; }
    public function setNom(string $nom): static { $this->nomDeposant = $nom; return $this; }
    public function getCode(): string { return $this->deposant; }
    public function setCode(string $code): static { $this->deposant = $code; return $this; }

    public function getCharges(): Collection { return $this->charges; }
}
