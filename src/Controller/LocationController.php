<?php

namespace App\Controller;

use App\Entity\Location;
use App\Form\LocationType;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/stock/locations', name: 'location_')]
#[IsGranted('ROLE_USER')]
class LocationController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, LocationRepository $repo): Response
    {
        $search = $request->query->get('search', '');
        $allee  = $request->query->get('allee', '');
        $qb = $repo->createQueryBuilder('l');
        if ($search) {
            $qb->andWhere('l.code LIKE :s')->setParameter('s', '%'.$search.'%');
        }
        if ($allee) {
            $qb->andWhere('l.allee = :a')->setParameter('a', $allee);
        }
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $limit = $user->getItemsPerPage();
        $page = max(1, $request->query->getInt('page', 1));

        $total = (clone $qb)->select('COUNT(l.id)')->getQuery()->getSingleScalarResult();

        $locations = $qb->orderBy('l.code', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $this->render('location/index.html.twig', [
            'locations' => $locations,
            'search' => $search,
            'allee' => $allee,
            'page' => $page,
            'total' => $total,
            'limit' => $limit,
            'pages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $location = new Location();
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($location);
            $em->flush();
            $this->addFlash('success', 'Emplacement créé.');
            return $this->redirectToRoute('location_index');
        }
        return $this->render('location/new.html.twig', ['form' => $form, 'location' => $location]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Location $location): Response
    {
        return $this->render('location/show.html.twig', ['location' => $location]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Location $location, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Emplacement mis à jour.');
            return $this->redirectToRoute('location_index');
        }
        return $this->render('location/edit.html.twig', ['form' => $form, 'location' => $location]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Location $location, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$location->getId(), $request->request->get('_token'))) {
            $em->remove($location);
            $em->flush();
            $this->addFlash('success', 'Emplacement supprimé.');
        }
        return $this->redirectToRoute('location_index');
    }
}
