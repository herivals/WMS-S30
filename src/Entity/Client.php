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

    #[ORM\Column(length: 150)]
    #[Groups(['stock_unit:read', 'stock_unit:write', 'stock_unit:list'])]
    private string $nom;

    #[ORM\Column(length: 50, unique: true, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write', 'stock_unit:list'])]
    private ?string $code = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private ?string $email = null;

    #[ORM\OneToMany(targetEntity: Charge::class, mappedBy: 'owner')]
    private Collection $charges;

    public function __construct()
    {
        $this->charges = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->code ? "[{$this->code}] {$this->nom}" : $this->nom;
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getCode(): ?string { return $this->code; }
    public function setCode(?string $code): static { $this->code = $code; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): static { $this->email = $email; return $this; }

    public function getCharges(): Collection { return $this->charges; }
}
