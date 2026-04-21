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

#[Route('/stock/clients', name: 'client_')]
#[IsGranted('ROLE_USER')]
class ClientController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, ClientRepository $repo): Response
    {
        $search = $request->query->get('search', '');
        $qb = $repo->createQueryBuilder('c');
        if ($search) {
            $qb->andWhere('c.nom LIKE :s OR c.code LIKE :s')->setParameter('s', '%'.$search.'%');
        }
        $clients = $qb->orderBy('c.nom', 'ASC')->getQuery()->getResult();

        return $this->render('client/index.html.twig', ['clients' => $clients, 'search' => $search]);
    }

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

    #[Route('/{id}', name: 'show')]
    public function show(Client $client): Response
    {
        return $this->render('client/show.html.twig', ['client' => $client]);
    }

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
