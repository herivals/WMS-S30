<?php

namespace App\Repository;

use App\Entity\Charge;
use App\Enum\StatutUL;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChargeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Charge::class);
    }

    public function findByStatut(StatutUL $statut): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('c.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByEmplacement(int $locationId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.emplacement = :id')
            ->setParameter('id', $locationId)
            ->getQuery()
            ->getResult();
    }

    public function findDisponibles(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', StatutUL::DISPONIBLE)
            ->andWhere('c.quantite > 0')
            ->orderBy('c.codeCharge', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByProduct(int $productId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.product = :id')
            ->setParameter('id', $productId)
            ->orderBy('c.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function searchByCode(string $code): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.codeCharge LIKE :code OR c.serialNumber LIKE :code')
            ->setParameter('code', '%' . $code . '%')
            ->orderBy('c.codeCharge', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
