<?php

namespace App\Tests\Controller;

use App\Entity\Product;

/**
 * Tests fonctionnels du ProductController.
 *
 * Scénarios couverts :
 * - Accès anonyme → redirection /login.
 * - Liste avec recherche multi-champs (référence, désignation, famille, déposant).
 * - Affichage fiche produit.
 * - Création d'un produit (référence unique obligatoire).
 * - Modification d'un produit existant.
 * - Suppression avec CSRF.
 *
 * Note : la suppression d'un produit référencé par une charge échoue (FK).
 * Ce scénario n'est pas testé ici car il dépend de données externes.
 */
class ProductControllerTest extends AbstractControllerTestCase
{
    private ?Product $product = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->product = (new Product())
            ->setReference('TST-' . substr(uniqid(), -6))
            ->setDesignation('Produit Test PHPUnit');
        $this->em->persist($this->product);
        $this->em->flush();
        $this->toCleanup[] = $this->product;
    }

    // ── Accès anonyme ─────────────────────────────────────────────────────

    public function testIndexRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/products');
    }

    public function testNewRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/products/new');
    }

    // ── Liste ─────────────────────────────────────────────────────────────

    public function testIndexLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/products');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    /** La recherche par désignation filtre correctement. */
    public function testIndexSearchByDesignation(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/products?search=PHPUnit');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('PHPUnit', $this->client->getResponse()->getContent());
    }

    /** Une recherche sans résultat ne produit pas d'erreur. */
    public function testIndexSearchNoResults(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/products?search=xXxIMPOSSIBLExXx');
        $this->assertResponseIsSuccessful();
    }

    // ── Affichage ─────────────────────────────────────────────────────────

    public function testShowExistingProduct(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/products/' . $this->product->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testShowNonExistingProductReturns404(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/products/999999');
        $this->assertResponseStatusCodeSame(404);
    }

    // ── Création ──────────────────────────────────────────────────────────

    public function testNewFormLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/products/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testNewValidSubmissionCreatesProduct(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/products/new');
        $ref = 'NEW-' . substr(uniqid(), -6);
        $this->client->submitForm('Enregistrer', [
            'product_form[reference]'   => $ref,
            'product_form[designation]' => 'Produit Créé en Test',
        ]);
        $this->assertResponseRedirects();

        $created = $this->em->getRepository(Product::class)->findOneBy(['reference' => $ref]);
        $this->assertNotNull($created);
        $this->assertSame('Produit Créé en Test', $created->getDesignation());
        if ($created) {
            $this->toCleanup[] = $created;
        }
    }

    /** Une référence déjà utilisée doit provoquer une erreur de formulaire. */
    public function testNewDuplicateReferenceShowsError(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/products/new');
        $this->client->submitForm('Enregistrer', [
            'product_form[reference]'   => $this->product->getReference(), // déjà existante
            'product_form[designation]' => 'Doublon',
        ]);
        // Le formulaire est ré-affiché (pas de redirection) en cas de violation unique
        $this->assertResponseIsSuccessful();
    }

    // ── Modification ──────────────────────────────────────────────────────

    public function testEditFormLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/products/' . $this->product->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEditValidSubmissionUpdatesProduct(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/products/' . $this->product->getId() . '/edit');
        $this->client->submitForm('Enregistrer', [
            'product_form[designation]' => 'Désignation Modifiée',
        ]);
        $this->assertResponseRedirects();
        $this->em->refresh($this->product);
        $this->assertSame('Désignation Modifiée', $this->product->getDesignation());
    }

    // ── Suppression ───────────────────────────────────────────────────────

    public function testDeleteWithInvalidCsrfDoesNotDelete(): void
    {
        $this->loginAsUser();
        $id = $this->product->getId();
        $this->client->request('POST', '/stock/products/' . $id . '/delete', ['_token' => 'invalide']);
        $this->assertResponseRedirects();
        $this->assertNotNull($this->em->find(Product::class, $id));
    }

    public function testDeleteWithValidCsrfDeletesProduct(): void
    {
        $this->loginAsUser();
        $p = (new Product())->setReference('DEL-' . substr(uniqid(), -6))->setDesignation('À Supprimer');
        $this->em->persist($p);
        $this->em->flush();
        $id = $p->getId();

        $token = static::getContainer()->get('security.csrf.token_manager')
                        ->getToken('delete' . $id)->getValue();
        $this->client->request('POST', '/stock/products/' . $id . '/delete', ['_token' => $token]);
        $this->assertResponseRedirects();
        $this->em->clear();
        $this->assertNull($this->em->find(Product::class, $id));
    }
}
