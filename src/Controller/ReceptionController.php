<?php

namespace App\Controller;

use App\Entity\Reception;
use App\Form\ReceptionType;
use App\Repository\ReceptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/stock/receptions', name: 'reception_')]
#[IsGranted('ROLE_USER')]
class ReceptionController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, ReceptionRepository $repo): Response
    {
        $search = $request->query->get('search', '');
        $qb = $repo->createQueryBuilder('r');
        if ($search) {
            $qb->andWhere('r.reference LIKE :s')->setParameter('s', '%'.$search.'%');
        }
        $receptions = $qb->orderBy('r.date', 'DESC')->getQuery()->getResult();

        return $this->render('reception/index.html.twig', ['receptions' => $receptions, 'search' => $search]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $reception = new Reception();
        $form = $this->createForm(ReceptionType::class, $reception);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($reception);
            $em->flush();
            $this->addFlash('success', 'Réception créée.');
            return $this->redirectToRoute('reception_index');
        }
        return $this->render('reception/new.html.twig', ['form' => $form, 'reception' => $reception]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Reception $reception): Response
    {
        return $this->render('reception/show.html.twig', ['reception' => $reception]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Reception $reception, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ReceptionType::class, $reception);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Réception mise à jour.');
            return $this->redirectToRoute('reception_index');
        }
        return $this->render('reception/edit.html.twig', ['form' => $form, 'reception' => $reception]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Reception $reception, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reception->getId(), $request->request->get('_token'))) {
            $em->remove($reception);
            $em->flush();
            $this->addFlash('success', 'Réception supprimée.');
        }
        return $this->redirectToRoute('reception_index');
    }
}
