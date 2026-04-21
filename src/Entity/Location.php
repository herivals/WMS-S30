<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
#[ORM\Table(name: 'location')]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['stock_unit:read', 'stock_unit:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write', 'stock_unit:list'])]
    private string $code;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?string $allee = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?string $rack = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?string $niveau = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?string $position = null;

    #[ORM\OneToMany(targetEntity: Charge::class, mappedBy: 'emplacement')]
    private Collection $charges;

    public function __construct()
    {
        $this->charges = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->code;
    }

    public function getFullAddress(): string
    {
        return implode('-', array_filter([$this->allee, $this->rack, $this->niveau, $this->position]));
    }

    public function getId(): ?int { return $this->id; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): static { $this->code = $code; return $this; }

    public function getAllee(): ?string { return $this->allee; }
    public function setAllee(?string $allee): static { $this->allee = $allee; return $this; }

    public function getRack(): ?string { return $this->rack; }
    public function setRack(?string $rack): static { $this->rack = $rack; return $this; }

    public function getNiveau(): ?string { return $this->niveau; }
    public function setNiveau(?string $niveau): static { $this->niveau = $niveau; return $this; }

    public function getPosition(): ?string { return $this->position; }
    public function setPosition(?string $position): static { $this->position = $position; return $this; }

    public function getCharges(): Collection { return $this->charges; }
}
