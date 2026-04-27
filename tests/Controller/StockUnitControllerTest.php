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
 * Tests fonctionnels du StockUnitController.
 *
 * Scénarios couverts :
 * - Accès anonyme → redirection /login.
 * - Liste avec recherche et filtre par statut.
 * - Affichage fiche charge.
 * - Création d'une UL.
 * - Modification d'une UL.
 * - Génération automatique d'un mouvement lors du changement de lot.
 * - Génération automatique d'un mouvement lors du changement d'emplacement.
 * - Déplacement en masse (bulk_move) : mouvements TRANSFERT créés.
 * - Suppression avec CSRF.
 * - Endpoint AJAX /api/products filtré par déposant.
 */
class StockUnitControllerTest extends AbstractControllerTestCase
{
    private ?Charge $charge = null;
    private ?Product $product = null;
    private ?Client $owner = null;
    private ?Location $location = null;
    private ?Location $location2 = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = (new Product())
            ->setReference('SU-TST-' . substr(uniqid(), -5))
            ->setDesignation('Produit UL Test')
            ->setDeposant('DEP-TST');
        $this->em->persist($this->product);

        $this->owner = (new Client())
            ->setDeposant('DEP-TST-' . substr(uniqid(), -4))
            ->setNomDeposant('Client UL Test');
        $this->em->persist($this->owner);

        $this->location = (new Location())->setCode('SU-LOC1-' . substr(uniqid(), -4));
        $this->em->persist($this->location);

        $this->location2 = (new Location())->setCode('SU-LOC2-' . substr(uniqid(), -4));
        $this->em->persist($this->location2);

        $this->charge = (new Charge())
            ->setCodeCharge('SU-' . substr(uniqid(), -6))
            ->setProduct($this->product)
            ->setOwner($this->owner)
            ->setEmplacement($this->location)
            ->setTypeUnite(TypeUnite::COLIS)
            ->setStatut(StatutUL::DISPONIBLE)
            ->setQuantite(5.0)
            ->setQuantiteReservee(0.0)
            ->setEtat(EtatUL::GOOD)
            ->setLot('LOT-INITIAL')
            ->setCreatedBy('test');
        $this->em->persist($this->charge);
        $this->em->flush();

        // Ordre de nettoyage : les mouvements créés pendant les tests seront gérés séparément
        $this->toCleanup = [$this->charge, $this->location2, $this->location, $this->owner, $this->product];
    }

    // ── Accès anonyme ─────────────────────────────────────────────────────

    public function testIndexRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/units');
    }

    public function testNewRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/units/new');
    }

    public function testShowRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/units/' . $this->charge->getId());
    }

    // ── Liste ─────────────────────────────────────────────────────────────

    public function testIndexLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/units');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    public function testIndexSearchByCodeCharge(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/units?search=' . $this->charge->getCodeCharge());
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            $this->charge->getCodeCharge(),
            $this->client->getResponse()->getContent()
        );
    }

    /** Le filtre par statut retourne uniquement les charges correspondantes. */
    public function testIndexFilterByStatut(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/units?statut=' . StatutUL::DISPONIBLE->value);
        $this->assertResponseIsSuccessful();
    }

    // ── Affichage ─────────────────────────────────────────────────────────

    public function testShowExistingCharge(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/units/' . $this->charge->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testShowNonExistingChargeReturns404(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/units/999999');
        $this->assertResponseStatusCodeSame(404);
    }

    // ── Création ──────────────────────────────────────────────────────────

    public function testNewFormLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/units/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    // ── Modification + mouvements automatiques ────────────────────────────

    public function testEditFormLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/units/' . $this->charge->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Changer le lot lors d'une modification crée un mouvement CHANGEMENT_LOT.
     * C'est la règle métier centrale : toute modification de lot est tracée.
     */
    public function testEditLotChangeCreatesChangementLotMovement(): void
    {
        $this->loginAsUser();
        $id = $this->charge->getId();

        $this->client->request('GET', '/stock/units/' . $id . '/edit');
        $this->client->submitForm('Enregistrer', [
            'stock_unit[lot]' => 'LOT-MODIFIE',
        ]);
        $this->assertResponseRedirects();

        // Vérifier qu'un mouvement CHANGEMENT_LOT a été créé
        $mvt = $this->em->getRepository(StockMovement::class)->findOneBy([
            'charge' => $this->charge,
            'type'   => TypeMouvement::CHANGEMENT_LOT,
        ]);
        $this->assertNotNull($mvt, 'Un mouvement CHANGEMENT_LOT doit être créé.');
        $this->assertStringContainsString('LOT-INITIAL', $mvt->getCommentaire());
        $this->assertStringContainsString('LOT-MODIFIE', $mvt->getCommentaire());

        // Nettoyage du mouvement créé
        if ($mvt) {
            $this->em->remove($mvt);
            $this->em->flush();
        }
    }

    /**
     * Changer l'emplacement lors d'une modification crée un mouvement TRANSFERT.
     */
    public function testEditEmplacementChangeCreatesTransfertMovement(): void
    {
        $this->loginAsUser();
        $id  = $this->charge->getId();
        $id2 = $this->location2->getId();

        $this->client->request('GET', '/stock/units/' . $id . '/edit');
        $this->client->submitForm('Enregistrer', [
            'stock_unit[emplacement]' => $id2,
        ]);
        $this->assertResponseRedirects();

        $mvt = $this->em->getRepository(StockMovement::class)->findOneBy([
            'charge' => $this->charge,
            'type'   => TypeMouvement::TRANSFERT,
        ]);
        $this->assertNotNull($mvt, 'Un mouvement TRANSFERT doit être créé lors du changement d\'emplacement.');
        $this->assertStringContainsString($this->location2->getCode(), $mvt->getCommentaire());

        if ($mvt) {
            $this->em->remove($mvt);
            $this->em->flush();
        }
    }

    /**
     * Modifier une charge sans changer le lot ni l'emplacement
     * ne doit PAS créer de mouvement supplémentaire.
     */
    public function testEditWithNoLotOrEmplacementChangeCreatesNoMovement(): void
    {
        $this->loginAsUser();
        $beforeCount = count(
            $this->em->getRepository(StockMovement::class)->findBy(['charge' => $this->charge])
        );

        $this->client->request('GET', '/stock/units/' . $this->charge->getId() . '/edit');
        $this->client->submitForm('Enregistrer', [
            'stock_unit[designation]' => 'Désignation modifiée',
        ]);
        $this->assertResponseRedirects();

        $afterCount = count(
            $this->em->getRepository(StockMovement::class)->findBy(['charge' => $this->charge])
        );
        $this->assertSame($beforeCount, $afterCount, 'Aucun mouvement ne doit être créé si lot et emplacement sont inchangés.');
    }

    // ── Bulk move ─────────────────────────────────────────────────────────

    /**
     * L'endpoint bulk_move déplace les charges et crée un mouvement TRANSFERT par charge.
     */
    public function testBulkMoveSendsChargesToNewLocation(): void
    {
        $this->loginAsUser();
        $this->client->request('POST', '/stock/units/bulk/move', [
            'chargeIds'    => json_encode([$this->charge->getId()]),
            'emplacementId' => $this->location2->getId(),
        ]);
        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertSame(1, $data['moved']);

        $this->em->refresh($this->charge);
        $this->assertSame($this->location2->getId(), $this->charge->getEmplacement()->getId());

        // Nettoyage du mouvement créé
        $mvt = $this->em->getRepository(StockMovement::class)->findOneBy([
            'charge' => $this->charge,
            'type'   => TypeMouvement::TRANSFERT,
        ]);
        if ($mvt) {
            $this->em->remove($mvt);
            $this->em->flush();
        }
    }

    /** bulk_move vers l'emplacement déjà actuel ne crée aucun mouvement. */
    public function testBulkMoveToSameLocationMovesZero(): void
    {
        $this->loginAsUser();
        $this->client->request('POST', '/stock/units/bulk/move', [
            'chargeIds'     => json_encode([$this->charge->getId()]),
            'emplacementId' => $this->location->getId(), // même emplacement
        ]);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(0, $data['moved']);
    }

    /** bulk_move sans emplacementId retourne une erreur 400. */
    public function testBulkMoveMissingEmplacementReturns400(): void
    {
        $this->loginAsUser();
        $this->client->request('POST', '/stock/units/bulk/move', [
            'chargeIds' => json_encode([$this->charge->getId()]),
        ]);
        $this->assertResponseStatusCodeSame(400);
    }

    /** bulk_move vers un emplacement inexistant retourne 404. */
    public function testBulkMoveInvalidEmplacementReturns404(): void
    {
        $this->loginAsUser();
        $this->client->request('POST', '/stock/units/bulk/move', [
            'chargeIds'     => json_encode([$this->charge->getId()]),
            'emplacementId' => 999999,
        ]);
        $this->assertResponseStatusCodeSame(404);
    }

    // ── API produits ──────────────────────────────────────────────────────

    /**
     * L'endpoint AJAX retourne du JSON valide pour une requête sans filtre.
     */
    public function testApiProductsReturnsJson(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/units/api/products');
        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());
    }

    /** Filtrer par clientId retourne uniquement les produits de ce déposant. */
    public function testApiProductsFilteredByClient(): void
    {
        $this->loginAsUser();

        // Produit avec le déposant du owner de test
        $p = (new Product())
            ->setReference('API-' . substr(uniqid(), -5))
            ->setDesignation('Produit API test')
            ->setDeposant($this->owner->getDeposant());
        $this->em->persist($p);
        $this->em->flush();
        $this->toCleanup[] = $p;

        $this->client->request('GET', '/stock/units/api/products?clientId=' . $this->owner->getId());
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $ids = array_column($data, 'id');
        $this->assertContains($p->getId(), $ids, 'Le produit du déposant doit être dans la réponse.');

        // Un produit d'un autre déposant ne doit pas apparaître
        $this->assertNotContains(
            $this->product->getId(),
            $ids,
            'Les produits d\'un autre déposant ne doivent pas apparaître.'
        );
    }

    // ── Suppression ───────────────────────────────────────────────────────

    public function testDeleteWithInvalidCsrfDoesNotDelete(): void
    {
        $this->loginAsUser();
        $id = $this->charge->getId();
        $this->client->request('POST', '/stock/units/' . $id . '/delete', ['_token' => 'invalide']);
        $this->assertResponseRedirects();
        $this->assertNotNull($this->em->find(Charge::class, $id));
    }

    public function testDeleteWithValidCsrfDeletesCharge(): void
    {
        $this->loginAsUser();

        $p = (new Product())->setReference('DEL-P-' . substr(uniqid(), -5))->setDesignation('Del');
        $this->em->persist($p);
        $c = (new Charge())
            ->setCodeCharge('DEL-C-' . substr(uniqid(), -5))
            ->setProduct($p)
            ->setTypeUnite(TypeUnite::COLIS)
            ->setStatut(StatutUL::DISPONIBLE)
            ->setQuantite(1.0)->setQuantiteReservee(0.0)
            ->setEtat(EtatUL::GOOD)->setCreatedBy('test');
        $this->em->persist($c);
        $this->em->flush();
        $id = $c->getId();

        $token = static::getContainer()->get('security.csrf.token_manager')
                        ->getToken('delete' . $id)->getValue();
        $this->client->request('POST', '/stock/units/' . $id . '/delete', ['_token' => $token]);
        $this->assertResponseRedirects();
        $this->em->clear();
        $this->assertNull($this->em->find(Charge::class, $id));

        // Nettoyage du produit orphelin
        $pm = $this->em->find(Product::class, $p->getId());
        if ($pm) { $this->em->remove($pm); $this->em->flush(); }
    }
}
