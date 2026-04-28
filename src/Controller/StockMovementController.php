<?php

namespace App\Controller;

use App\Entity\StockMovement;
use App\Repository\StockMovementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Consultation de l'historique des mouvements de stock (lecture seule).
 * Les mouvements sont créés automatiquement par StockUnitController
 * lors des modifications de lot ou de changement d'emplacement.
 * Ce contrôleur ne permet pas la création manuelle.
 */
#[Route('/stock/movements', name: 'stock_movement_')]
#[IsGranted('ROLE_USER')]
class StockMovementController extends AbstractController
{
    /**
     * Liste paginée de tous les mouvements, triée du plus récent au plus ancien.
     * Aucun filtre n'est appliqué ici ; la recherche par charge se fait depuis la fiche UL.
     */
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
            'page'      => $page,
            'total'     => $total,
            'limit'     => $limit,
            'pages'     => (int) ceil($total / max(1, $limit)),
        ]);
    }

    /** Détail d'un mouvement individuel. */
    #[Route('/{id}', name: 'show')]
    public function show(StockMovement $movement): Response
    {
        return $this->render('stock_movement/show.html.twig', ['movement' => $movement]);
    }
}
