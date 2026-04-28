<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion des déposants (clients) : création, consultation, modification et suppression.
 * Un déposant est identifié par son code unique `deposant` qui sert de clé de rapprochement
 * avec les produits (Product.deposant) et les charges (Charge.owner).
 */
#[Route('/stock/clients', name: 'client_')]
#[IsGranted('ROLE_USER')]
class ClientController extends AbstractController
{
    /**
     * Liste paginée des déposants avec recherche sur nom, code et e-mail.
     */
    #[Route('', name: 'index')]
    public function index(Request $request, ClientRepository $repo): Response
    {
        $search = $request->query->get('search', '');
        $qb = $repo->createQueryBuilder('c');
        if ($search) {
            $qb
                ->andWhere('c.nomDeposant LIKE :s OR c.deposant LIKE :s OR c.email LIKE :s')
                ->setParameter('s', '%'.$search.'%');
        }
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $limit = $user->getItemsPerPage();
        $page = max(1, $request->query->getInt('page', 1));

        $total = (clone $qb)->select('COUNT(c.id)')->getQuery()->getSingleScalarResult();

        $clients = $qb->orderBy('c.nomDeposant', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $this->render('client/index.html.twig', [
            'clients' => $clients,
            'search'  => $search,
            'page'    => $page,
            'total'   => $total,
            'limit'   => $limit,
            'pages'   => (int) ceil($total / max(1, $limit)),
        ]);
    }

    /** Création d'un nouveau déposant. */
    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($client);
            $em->flush();
            $this->addFlash('success', 'Client créé.');
            return $this->redirectToRoute('client_index');
        }
        return $this->render('client/new.html.twig', ['form' => $form, 'client' => $client]);
    }

    /** Fiche détaillée d'un déposant. */
    #[Route('/{id}', name: 'show')]
    public function show(Client $client): Response
    {
        return $this->render('client/show.html.twig', ['client' => $client]);
    }

    /** Modification d'un déposant existant. */
    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Client $client, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Client mis à jour.');
            return $this->redirectToRoute('client_index');
        }
        return $this->render('client/edit.html.twig', ['form' => $form, 'client' => $client]);
    }

    /**
     * Suppression d'un déposant après vérification du token CSRF.
     * La suppression peut échouer si des charges ou produits y sont encore rattachés (contrainte FK).
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Client $client, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            $em->remove($client);
            $em->flush();
            $this->addFlash('success', 'Client supprimé.');
        }
        return $this->redirectToRoute('client_index');
    }
}
