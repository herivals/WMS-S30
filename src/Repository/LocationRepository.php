<?php

namespace App\Repository;

use App\Entity\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    public function findByAllee(string $allee): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.allee = :allee')
            ->setParameter('allee', $allee)
            ->orderBy('l.code', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findVides(): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.charges', 'c')
            ->andWhere('c.id IS NULL')
            ->orderBy('l.code', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
