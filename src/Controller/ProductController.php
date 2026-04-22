<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/stock/products', name: 'product_')]
#[IsGranted('ROLE_USER')]
class ProductController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, ProductRepository $repo): Response
    {
        $search = $request->query->get('search', '');
        $qb = $repo->createQueryBuilder('p');
        if ($search) {
            $qb
                ->andWhere('p.reference LIKE :s OR p.designation LIKE :s OR p.famille LIKE :s OR p.deposant LIKE :s')
                ->setParameter('s', '%'.$search.'%');
        }
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $limit = $user->getItemsPerPage();
        $page = max(1, $request->query->getInt('page', 1));

        $total = (clone $qb)->select('COUNT(p.id)')->getQuery()->getSingleScalarResult();

        $products = $qb->orderBy('p.reference', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'search' => $search,
            'page' => $page,
            'total' => $total,
            'limit' => $limit,
            'pages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();
            $this->addFlash('success', 'Produit créé.');
            return $this->redirectToRoute('product_index');
        }
        return $this->render('product/new.html.twig', ['form' => $form, 'product' => $product]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', ['product' => $product]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Produit mis à jour.');
            return $this->redirectToRoute('product_index');
        }
        return $this->render('product/edit.html.twig', ['form' => $form, 'product' => $product]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $em->remove($product);
            $em->flush();
            $this->addFlash('success', 'Produit supprimé.');
        }
        return $this->redirectToRoute('product_index');
    }
}
