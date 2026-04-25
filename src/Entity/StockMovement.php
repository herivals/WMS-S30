<?php

namespace App\Entity;

use App\Enum\TypeMouvement;
use App\Repository\StockMovementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StockMovementRepository::class)]
#[ORM\Table(name: 'stock_movement')]
class StockMovement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['stock_unit:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Charge::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['stock_unit:read'])]
    private Charge $charge;

    #[ORM\Column(length: 20, enumType: TypeMouvement::class)]
    #[Groups(['stock_unit:read'])]
    private TypeMouvement $type;

    #[ORM\Column]
    #[Groups(['stock_unit:read'])]
    private float $quantite;

    #[ORM\Column]
    #[Groups(['stock_unit:read'])]
    private \DateTimeImmutable $date;

    #[ORM\Column(nullable: true)]
    #[Groups(['stock_unit:read'])]
    private ?int $userId = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['stock_unit:read'])]
    private ?string $userName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['stock_unit:read'])]
    private ?string $commentaire = null;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return sprintf('%s — %s × %.2f', $this->charge, $this->type->label(), $this->quantite);
    }

    public function getId(): ?int { return $this->id; }

    public function getCharge(): Charge { return $this->charge; }
    public function setCharge(Charge $charge): static { $this->charge = $charge; return $this; }

    public function getType(): TypeMouvement { return $this->type; }
    public function setType(TypeMouvement $type): static { $this->type = $type; return $this; }

    public function getQuantite(): float { return $this->quantite; }
    public function setQuantite(float $quantite): static { $this->quantite = $quantite; return $this; }

    public function getDate(): \DateTimeImmutable { return $this->date; }
    public function setDate(\DateTimeImmutable $date): static { $this->date = $date; return $this; }

    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(?int $userId): static { $this->userId = $userId; return $this; }

    public function getUserName(): ?string { return $this->userName; }
    public function setUserName(?string $userName): static { $this->userName = $userName; return $this; }

    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $commentaire): static { $this->commentaire = $commentaire; return $this; }
}
