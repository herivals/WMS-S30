<?php

namespace App\Controller;

use App\Entity\StockMovement;
use App\Repository\StockMovementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/stock/movements', name: 'stock_movement_')]
#[IsGranted('ROLE_USER')]
class StockMovementController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, StockMovementRepository $repo): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $limit = $user->getItemsPerPage();
        $page = max(1, $request->query->getInt('page', 1));

        $qb = $repo->createQueryBuilder('m');

        $total = (clone $qb)->select('COUNT(m.id)')->getQuery()->getSingleScalarResult();

        $movements = $qb->orderBy('m.date', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $this->render('stock_movement/index.html.twig', [
            'movements' => $movements,
            'page' => $page,
            'total' => $total,
            'limit' => $limit,
            'pages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(StockMovement $movement): Response
    {
        return $this->render('stock_movement/show.html.twig', ['movement' => $movement]);
    }
}
