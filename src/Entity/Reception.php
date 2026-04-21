<?php

namespace App\Entity;

use App\Enum\TypeReception;
use App\Repository\ReceptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReceptionRepository::class)]
#[ORM\Table(name: 'reception')]
class Reception
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['stock_unit:read', 'stock_unit:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Groups(['stock_unit:read', 'stock_unit:write', 'stock_unit:list'])]
    private string $reference;

    #[ORM\Column]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private \DateTimeImmutable $date;

    #[ORM\Column(length: 20, enumType: TypeReception::class)]
    #[Groups(['stock_unit:read', 'stock_unit:write'])]
    private TypeReception $typeReception = TypeReception::STANDARD;

    #[ORM\OneToMany(targetEntity: Charge::class, mappedBy: 'reception')]
    private Collection $charges;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
        $this->charges = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->reference;
    }

    public function getId(): ?int { return $this->id; }

    public function getReference(): string { return $this->reference; }
    public function setReference(string $reference): static { $this->reference = $reference; return $this; }

    public function getDate(): \DateTimeImmutable { return $this->date; }
    public function setDate(\DateTimeImmutable $date): static { $this->date = $date; return $this; }

    public function getTypeReception(): TypeReception { return $this->typeReception; }
    public function setTypeReception(TypeReception $typeReception): static { $this->typeReception = $typeReception; return $this; }

    public function getCharges(): Collection { return $this->charges; }
}
