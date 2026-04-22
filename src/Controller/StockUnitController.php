<?php

namespace App\Controller;

use App\Entity\Charge;
use App\Enum\StatutUL;
use App\Form\StockUnitType;
use App\Repository\ChargeRepository;
use App\Repository\StockMovementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/stock/units', name: 'stock_unit_')]
#[IsGranted('ROLE_USER')]
class StockUnitController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, ChargeRepository $repo): Response
    {
        $search = $request->query->get('search', '');
        $statut = $request->query->get('statut', '');

        $qb = $repo->createQueryBuilder('c')
            ->leftJoin('c.product', 'p')
            ->leftJoin('c.emplacement', 'e')
            ->addSelect('p', 'e');

        if ($search) {
            $qb->andWhere('c.codeCharge LIKE :s OR p.reference LIKE :s OR p.designation LIKE :s')
               ->setParameter('s', '%'.$search.'%');
        }
        if ($statut && StatutUL::tryFrom($statut)) {
            $qb->andWhere('c.statut = :statut')->setParameter('statut', StatutUL::from($statut));
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $limit = $user->getItemsPerPage();
        $page = max(1, $request->query->getInt('page', 1));

        $total = (clone $qb)->select('COUNT(c.id)')->getQuery()->getSingleScalarResult();

        $stockUnits = $qb->orderBy('c.dateCreation', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $this->render('stock_unit/index.html.twig', [
            'stockUnits' => $stockUnits,
            'search' => $search,
            'statut' => $statut,
            'statuts' => StatutUL::cases(),
            'page' => $page,
            'total' => $total,
            'limit' => $limit,
            'pages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $charge = new Charge();
        $form = $this->createForm(StockUnitType::class, $charge);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($charge);
            $em->flush();
            $this->addFlash('success', 'Unité logistique créée.');
            return $this->redirectToRoute('stock_unit_show', ['id' => $charge->getId()]);
        }
        return $this->render('stock_unit/new.html.twig', ['form' => $form, 'charge' => $charge]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Charge $charge, StockMovementRepository $mvtRepo): Response
    {
        $mouvements = $mvtRepo->createQueryBuilder('m')
            ->andWhere('m.charge = :charge')->setParameter('charge', $charge)
            ->orderBy('m.date', 'DESC')
            ->setMaxResults(10)
            ->getQuery()->getResult();

        return $this->render('stock_unit/show.html.twig', [
            'charge'     => $charge,
            'mouvements' => $mouvements,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Charge $charge, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(StockUnitType::class, $charge);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Unité logistique mise à jour.');
            return $this->redirectToRoute('stock_unit_show', ['id' => $charge->getId()]);
        }
        return $this->render('stock_unit/edit.html.twig', ['form' => $form, 'charge' => $charge]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Charge $charge, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$charge->getId(), $request->request->get('_token'))) {
            $em->remove($charge);
            $em->flush();
            $this->addFlash('success', 'Unité logistique supprimée.');
        }
        return $this->redirectToRoute('stock_unit_index');
    }
}
