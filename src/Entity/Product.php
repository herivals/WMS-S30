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

    public function getCharges(): Collection { return $this->charges; }
}
