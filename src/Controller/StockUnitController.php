<?php

namespace App\Controller;

use App\Entity\Charge;
use App\Entity\Client;
use App\Entity\Location;
use App\Entity\Product;
use App\Entity\StockMovement;
use App\Enum\StatutUL;
use App\Enum\TypeMouvement;
use App\Form\StockUnitType;
use App\Repository\ChargeRepository;
use App\Repository\LocationRepository;
use App\Repository\ProductRepository;
use App\Repository\StockMovementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion des unités logistiques (charges / UL) : création, consultation, modification,
 * suppression et déplacement en masse.
 *
 * Points notables :
 * - La modification d'un lot ou d'un emplacement génère automatiquement un StockMovement traçant l'opération.
 * - L'endpoint `api_products` alimente le dropdown produit en AJAX, filtré par déposant et emplacement.
 * - L'endpoint `bulk_move` permet de déplacer plusieurs charges en une seule requête POST.
 */
#[Route('/stock/units', name: 'stock_unit_')]
#[IsGranted('ROLE_USER')]
class StockUnitController extends AbstractController
{
    /**
     * Liste paginée des charges avec recherche sur le code, la référence et la désignation produit.
     * Les emplacements sont passés au template pour alimenter le modal de déplacement en masse.
     */
    #[Route('', name: 'index')]
    public function index(Request $request, ChargeRepository $repo, LocationRepository $locationRepo): Response
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
            'locations'  => $locationRepo->findBy([], ['code' => 'ASC']),
            'search'     => $search,
            'statut'     => $statut,
            'statuts'    => StatutUL::cases(),
            'page'       => $page,
            'total'      => $total,
            'limit'      => $limit,
            'pages'      => (int) ceil($total / max(1, $limit)),
        ]);
    }

    /**
     * Endpoint AJAX : retourne les produits filtrés par déposant (clientId) et/ou emplacement (emplacementId).
     * Utilisé par le formulaire charge pour recharger dynamiquement le dropdown produit
     * sans recharger la page entière.
     */
    #[Route('/api/products', name: 'api_products', methods: ['GET'])]
    public function apiProducts(Request $request, ProductRepository $productRepo, EntityManagerInterface $em): JsonResponse
    {
        $clientId = $request->query->get('clientId');
        $emplacementId = $request->query->get('emplacementId');

        $deposant = null;
        if ($clientId) {
            $client = $em->find(Client::class, (int) $clientId);
            if ($client) {
                $deposant = $client->getDeposant();
            }
        }

        $products = $productRepo->findByDeposantAndEmplacement(
            $deposant,
            $emplacementId ? (int) $emplacementId : null
        );

        return $this->json(array_map(
            fn(Product $p) => ['id' => $p->getId(), 'text' => (string) $p],
            $products
        ));
    }

    /**
     * Déplace plusieurs charges vers un même emplacement en une seule opération.
     * Un mouvement TRANSFERT est créé pour chaque charge effectivement déplacée.
     * Les charges déjà à l'emplacement cible sont silencieusement ignorées.
     */
    #[Route('/bulk/move', name: 'bulk_move', methods: ['POST'])]
    public function bulkMove(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $chargeIds    = json_decode($request->request->get('chargeIds', '[]'), true);
        $emplacementId = (int) $request->request->get('emplacementId');

        if (empty($chargeIds) || !$emplacementId) {
            return $this->json(['error' => 'Données manquantes'], 400);
        }

        $newEmplacement = $em->find(Location::class, $emplacementId);
        if (!$newEmplacement) {
            return $this->json(['error' => 'Emplacement introuvable'], 404);
        }

        /** @var \App\Entity\User $user */
        $user     = $this->getUser();
        $userId   = $user?->getId();
        $userName = $user?->getUserIdentifier();
        $moved    = 0;

        foreach ($chargeIds as $id) {
            $charge = $em->find(Charge::class, (int) $id);
            if (!$charge) continue;

            $oldEmplacement = $charge->getEmplacement();
            if ($oldEmplacement?->getId() === $newEmplacement->getId()) continue;

            $charge->setEmplacement($newEmplacement);

            $mvt = (new StockMovement())
                ->setCharge($charge)
                ->setType(TypeMouvement::TRANSFERT)
                ->setQuantite($charge->getQuantite())
                ->setUserId($userId)
                ->setUserName($userName)
                ->setCommentaire(sprintf('%s → %s',
                    $oldEmplacement?->getCode() ?? '—',
                    $newEmplacement->getCode()
                ));
            $em->persist($mvt);
            $moved++;
        }

        $em->flush();

        return $this->json(['success' => true, 'moved' => $moved]);
    }

    /** Création d'une nouvelle unité logistique. */
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

    /** Fiche détaillée d'une charge avec les 10 derniers mouvements associés. */
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

    /**
     * Modification d'une charge.
     * Les valeurs de lot et d'emplacement sont capturées avant que le formulaire
     * ne modifie l'objet, afin de comparer avec l'état soumis et tracer les changements.
     */
    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Charge $charge, Request $request, EntityManagerInterface $em): Response
    {
        // Capturer les valeurs avant modification du formulaire
        $oldLot        = $charge->getLot();
        $oldEmplacement = $charge->getEmplacement();

        $form = $this->createForm(StockUnitType::class, $charge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User $user */
            $user     = $this->getUser();
            $userId   = $user?->getId();
            $userName = $user?->getUserIdentifier();

            if ($charge->getLot() !== $oldLot) {
                $mvt = (new StockMovement())
                    ->setCharge($charge)
                    ->setType(TypeMouvement::CHANGEMENT_LOT)
                    ->setQuantite($charge->getQuantite())
                    ->setUserId($userId)
                    ->setUserName($userName)
                    ->setCommentaire(sprintf('Lot : %s → %s', $oldLot ?? '—', $charge->getLot() ?? '—'));
                $em->persist($mvt);
            }

            if ($charge->getEmplacement() !== $oldEmplacement) {
                $mvt = (new StockMovement())
                    ->setCharge($charge)
                    ->setType(TypeMouvement::TRANSFERT)
                    ->setQuantite($charge->getQuantite())
                    ->setUserId($userId)
                    ->setUserName($userName)
                    ->setCommentaire(sprintf('%s → %s',
                        $oldEmplacement?->getCode() ?? '—',
                        $charge->getEmplacement()?->getCode() ?? '—'
                    ));
                $em->persist($mvt);
            }

            $em->flush();
            $this->addFlash('success', 'Unité logistique mise à jour.');
            return $this->redirectToRoute('stock_unit_show', ['id' => $charge->getId()]);
        }

        return $this->render('stock_unit/edit.html.twig', ['form' => $form, 'charge' => $charge]);
    }

    /** Suppression d'une charge après vérification du token CSRF. */
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
