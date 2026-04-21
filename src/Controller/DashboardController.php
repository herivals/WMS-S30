<?php

namespace App\Controller;

use App\Enum\StatutUL;
use App\Enum\TypeUnite;
use App\Repository\ChargeRepository;
use App\Repository\StockMovementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig');
    }

    #[Route('/stock/dashboard', name: 'stock_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function stock(ChargeRepository $chargeRepo, StockMovementRepository $mvtRepo): Response
    {
        $totalUL        = $chargeRepo->count([]);
        $disponibles    = $chargeRepo->count(['statut' => StatutUL::DISPONIBLE]);
        $reservees      = $chargeRepo->count(['statut' => StatutUL::RESERVE]);
        $bloqueesRebuts = $chargeRepo->count(['statut' => StatutUL::BLOQUE])
                        + $chargeRepo->count(['statut' => StatutUL::REBUT]);

        $derniersMvts = $mvtRepo->createQueryBuilder('m')
            ->orderBy('m.date', 'DESC')
            ->setMaxResults(10)
            ->getQuery()->getResult();

        $repartitionType = [];
        foreach (TypeUnite::cases() as $type) {
            $repartitionType[$type->value] = $chargeRepo->count(['typeUnite' => $type]);
        }

        $now = new \DateTimeImmutable();
        $dluoDepassees = $chargeRepo->createQueryBuilder('c')
            ->andWhere('c.dluo IS NOT NULL AND c.dluo < :now')
            ->setParameter('now', $now)
            ->setMaxResults(10)->getQuery()->getResult();

        $aInventorier = $chargeRepo->createQueryBuilder('c')
            ->andWhere('c.aInventorier = true')
            ->setMaxResults(10)->getQuery()->getResult();

        $bloquees = $chargeRepo->findByStatut(StatutUL::BLOQUE);

        return $this->render('dashboard/stock.html.twig', [
            'totalUL'          => $totalUL,
            'disponibles'      => $disponibles,
            'reservees'        => $reservees,
            'bloqueesRebuts'   => $bloqueesRebuts,
            'derniersMvts'     => $derniersMvts,
            'repartitionType'  => $repartitionType,
            'dluoDepassees'    => $dluoDepassees,
            'aInventorier'     => $aInventorier,
            'bloquees'         => $bloquees,
        ]);
    }
}
