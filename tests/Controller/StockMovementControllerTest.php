<?php

namespace App\Tests\Controller;

use App\Entity\Charge;
use App\Entity\Client;
use App\Entity\Location;
use App\Entity\Product;
use App\Entity\StockMovement;
use App\Enum\EtatUL;
use App\Enum\StatutUL;
use App\Enum\TypeMouvement;
use App\Enum\TypeUnite;

/**
 * Tests fonctionnels du StockMovementController.
 *
 * Scénarios couverts :
 * - Accès anonyme → redirection /login.
 * - Liste paginée des mouvements.
 * - Affichage du détail d'un mouvement.
 * - Vérification que la création manuelle n'est pas exposée (lecture seule).
 *
 * Les mouvements sont créés manuellement en base pour ces tests
 * car ils sont normalement générés automatiquement par StockUnitController.
 */
class StockMovementControllerTest extends AbstractControllerTestCase
{
    private ?StockMovement $movement = null;
    private ?Charge $charge = null;
    private ?Product $product = null;
    private ?Client $owner = null;
    private ?Location $location = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer le minimum d'entités pour avoir un mouvement valide
        $this->product = (new Product())
            ->setReference('MVT-TST-' . substr(uniqid(), -5))
            ->setDesignation('Produit pour test mouvement');
        $this->em->persist($this->product);

        $this->owner = (new Client())
            ->setDeposant('MVT-' . substr(uniqid(), -5))
            ->setNomDeposant('Client Mouvement');
        $this->em->persist($this->owner);

        $this->location = (new Location())->setCode('MVT-' . substr(uniqid(), -5));
        $this->em->persist($this->location);

        $this->charge = (new Charge())
            ->setCodeCharge('CH-MVT-' . substr(uniqid(), -5))
            ->setProduct($this->product)
            ->setOwner($this->owner)
            ->setEmplacement($this->location)
            ->setTypeUnite(TypeUnite::COLIS)
            ->setStatut(StatutUL::DISPONIBLE)
            ->setQuantite(10.0)
            ->setQuantiteReservee(0.0)
            ->setEtat(EtatUL::GOOD)
            ->setCreatedBy('test');
        $this->em->persist($this->charge);

        $this->movement = (new StockMovement())
            ->setCharge($this->charge)
            ->setType(TypeMouvement::ENTREE)
            ->setQuantite(10.0)
            ->setUserId(1)
            ->setUserName('test@wms.test')
            ->setCommentaire('Mouvement de test PHPUnit');
        $this->em->persist($this->movement);
        $this->em->flush();

        // Ordre inverse pour respecter les FK lors du tearDown
        $this->toCleanup = [$this->movement, $this->charge, $this->location, $this->owner, $this->product];
    }

    // ── Accès anonyme ─────────────────────────────────────────────────────

    public function testIndexRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/movements');
    }

    public function testShowRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/movements/' . $this->movement->getId());
    }

    // ── Liste ─────────────────────────────────────────────────────────────

    public function testIndexLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/movements');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    /** Le mouvement de test est présent dans la liste. */
    public function testIndexContainsTestMovement(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/movements');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            TypeMouvement::ENTREE->label(),
            $this->client->getResponse()->getContent()
        );
    }

    // ── Affichage détail ──────────────────────────────────────────────────

    public function testShowExistingMovement(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/movements/' . $this->movement->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testShowNonExistingMovementReturns404(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/movements/999999');
        $this->assertResponseStatusCodeSame(404);
    }

    // ── Lecture seule ─────────────────────────────────────────────────────

    /** Il n'existe pas de route POST /stock/movements (création manuelle non exposée). */
    public function testNoCreationEndpointExists(): void
    {
        $this->loginAsUser();
        $this->client->request('POST', '/stock/movements');
        // Soit 404 soit 405 Method Not Allowed — dans tous les cas pas 200
        $this->assertNotSame(200, $this->client->getResponse()->getStatusCode());
    }
}
