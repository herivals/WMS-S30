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
        $movements = $repo->createQueryBuilder('m')
            ->orderBy('m.date', 'DESC')
            ->setMaxResults(100)
            ->getQuery()->getResult();

        return $this->render('stock_movement/index.html.twig', ['movements' => $movements]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(StockMovement $movement): Response
    {
        return $this->render('stock_movement/show.html.twig', ['movement' => $movement]);
    }
}
