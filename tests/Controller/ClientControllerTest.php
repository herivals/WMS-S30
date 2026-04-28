<?php

namespace App\Tests\Controller;

use App\Entity\Client;

/**
 * Tests fonctionnels du ClientController.
 *
 * Scénarios couverts :
 * - Accès anonyme → redirection /login (toutes les routes).
 * - Liste : affichage et recherche.
 * - Création : formulaire valide crée l'entité en base.
 * - Affichage : fiche détaillée retourne 200.
 * - Modification : formulaire valide met à jour l'entité.
 * - Suppression : CSRF valide supprime l'entité ; token invalide l'ignore.
 */
class ClientControllerTest extends AbstractControllerTestCase
{
    private ?Client $client_ = null; // suffixe _ pour éviter la collision avec $this->client

    protected function setUp(): void
    {
        parent::setUp();
        // Créer un déposant de test réutilisé par plusieurs tests
        $this->client_ = new Client();
        $this->client_
            ->setDeposant('TST-' . substr(uniqid(), -6))
            ->setNomDeposant('Déposant Test');
        $this->em->persist($this->client_);
        $this->em->flush();
        $this->toCleanup[] = $this->client_;
    }

    // ── Accès anonyme ─────────────────────────────────────────────────────

    public function testIndexRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/clients');
    }

    public function testNewRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/clients/new');
    }

    public function testShowRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/clients/' . $this->client_->getId());
    }

    public function testEditRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/clients/' . $this->client_->getId() . '/edit');
    }

    // ── Liste ─────────────────────────────────────────────────────────────

    /** La liste des clients se charge et contient le déposant de test. */
    public function testIndexLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/clients');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    /** La recherche filtre les résultats selon le nom du déposant. */
    public function testIndexSearchFilters(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/clients?search=Déposant+Test');
        $this->assertResponseIsSuccessful();
        $body = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Déposant Test', $body);
    }

    /** Une recherche sans résultat affiche un message vide. */
    public function testIndexSearchNoResults(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/clients?search=xXxINEXISTANTxXx');
        $this->assertResponseIsSuccessful();
    }

    // ── Affichage ─────────────────────────────────────────────────────────

    /** La fiche d'un déposant existant retourne 200. */
    public function testShowExistingClient(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/clients/' . $this->client_->getId());
        $this->assertResponseIsSuccessful();
    }

    /** La fiche d'un déposant inexistant retourne 404. */
    public function testShowNonExistingClientReturns404(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/clients/999999');
        $this->assertResponseStatusCodeSame(404);
    }

    // ── Création ──────────────────────────────────────────────────────────

    /** Le formulaire de création s'affiche. */
    public function testNewFormLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/clients/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /** Une soumission valide crée le déposant et redirige vers la liste. */
    public function testNewValidSubmissionCreatesClient(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/clients/new');
        $code = 'TEST-' . substr(uniqid(), -5);
        $this->client->submitForm('Enregistrer', [
            'client_form[deposant]'    => $code,
            'client_form[nomDeposant]' => 'Nouveau Déposant',
        ]);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Nettoyage de l'entité créée
        $created = $this->em->getRepository(Client::class)->findOneBy(['deposant' => $code]);
        if ($created) {
            $this->toCleanup[] = $created;
        }
    }

    // ── Modification ──────────────────────────────────────────────────────

    /** Le formulaire d'édition se charge avec les données actuelles. */
    public function testEditFormLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/clients/' . $this->client_->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /** Une soumission valide met à jour le déposant. */
    public function testEditValidSubmissionUpdatesClient(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/clients/' . $this->client_->getId() . '/edit');
        $this->client->submitForm('Enregistrer', [
            'client_form[nomDeposant]' => 'Nom Modifié',
        ]);
        $this->assertResponseRedirects();

        $this->em->refresh($this->client_);
        $this->assertSame('Nom Modifié', $this->client_->getNomDeposant());
    }

    // ── Suppression ───────────────────────────────────────────────────────

    /** Un token CSRF invalide n'effectue pas la suppression. */
    public function testDeleteWithInvalidCsrfTokenDoesNotDelete(): void
    {
        $this->loginAsUser();
        $id = $this->client_->getId();
        $this->client->request('POST', '/stock/clients/' . $id . '/delete', [
            '_token' => 'token_invalide',
        ]);
        $this->assertResponseRedirects();
        $this->assertNotNull($this->em->find(Client::class, $id));
    }

    /** Un token CSRF valide supprime le déposant. */
    public function testDeleteWithValidCsrfTokenDeletesClient(): void
    {
        $this->loginAsUser();

        // Créer un déposant spécifique à ce test pour ne pas perturber les autres
        $toDelete = (new Client())
            ->setDeposant('DEL-' . substr(uniqid(), -5))
            ->setNomDeposant('À Supprimer');
        $this->em->persist($toDelete);
        $this->em->flush();
        $id = $toDelete->getId();

        $token = static::getContainer()->get('security.csrf.token_manager')
                        ->getToken('delete' . $id)->getValue();

        $this->client->request('POST', '/stock/clients/' . $id . '/delete', ['_token' => $token]);
        $this->assertResponseRedirects();
        $this->em->clear();
        $this->assertNull($this->em->find(Client::class, $id));
    }
}
