<?php

namespace App\Repository;

use App\Entity\Charge;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Retourne les produits filtrés par déposant (client) et/ou emplacement.
     * Si $deposant est null, aucun filtre client n'est appliqué.
     * Si $emplacementId est null, aucun filtre emplacement n'est appliqué.
     */
    public function findByDeposantAndEmplacement(?string $deposant, ?int $emplacementId): array
    {
        $qb = $this->createQueryBuilder('p');

        if ($deposant !== null) {
            $qb->andWhere('p.deposant = :dep')->setParameter('dep', $deposant);
        }

        if ($emplacementId !== null) {
            $sub = $this->_em->createQueryBuilder()
                ->select('IDENTITY(c2.product)')
                ->from(Charge::class, 'c2')
                ->where('IDENTITY(c2.emplacement) = :empId')
                ->getDQL();

            $qb->andWhere($qb->expr()->in('p.id', $sub))
               ->setParameter('empId', $emplacementId);
        }

        return $qb->orderBy('p.reference', 'ASC')->getQuery()->getResult();
    }
}
