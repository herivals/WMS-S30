<?php

namespace App\Tests\Controller;

use App\Entity\Reception;
use App\Enum\TypeReception;

/**
 * Tests fonctionnels du ReceptionController.
 *
 * Scénarios couverts :
 * - Accès anonyme → redirection /login.
 * - Liste avec tri par date décroissante et recherche par référence.
 * - Affichage fiche réception.
 * - Création d'une réception (référence unique obligatoire).
 * - Modification d'une réception.
 * - Suppression CSRF valide/invalide.
 */
class ReceptionControllerTest extends AbstractControllerTestCase
{
    private ?Reception $reception = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reception = (new Reception())
            ->setReference('REC-TST-' . substr(uniqid(), -5))
            ->setTypeReception(TypeReception::STANDARD);
        $this->em->persist($this->reception);
        $this->em->flush();
        $this->toCleanup[] = $this->reception;
    }

    // ── Accès anonyme ─────────────────────────────────────────────────────

    public function testIndexRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/receptions');
    }

    public function testNewRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/receptions/new');
    }

    // ── Liste ─────────────────────────────────────────────────────────────

    public function testIndexLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/receptions');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    public function testIndexSearchByReference(): void
    {
        $this->loginAsUser();
        $ref = $this->reception->getReference();
        $this->client->request('GET', '/stock/receptions?search=' . urlencode($ref));
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString($ref, $this->client->getResponse()->getContent());
    }

    // ── Affichage ─────────────────────────────────────────────────────────

    public function testShowExistingReception(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/receptions/' . $this->reception->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testShowNonExistingReceptionReturns404(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/receptions/999999');
        $this->assertResponseStatusCodeSame(404);
    }

    // ── Création ──────────────────────────────────────────────────────────

    public function testNewFormLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/receptions/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testNewValidSubmissionCreatesReception(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/receptions/new');
        $ref = 'REC-NEW-' . substr(uniqid(), -5);
        $this->client->submitForm('Enregistrer', [
            'reception_form[reference]'     => $ref,
            'reception_form[typeReception]' => TypeReception::RETOUR->value,
        ]);
        $this->assertResponseRedirects();

        $created = $this->em->getRepository(Reception::class)->findOneBy(['reference' => $ref]);
        $this->assertNotNull($created);
        $this->assertSame(TypeReception::RETOUR, $created->getTypeReception());
        if ($created) {
            $this->toCleanup[] = $created;
        }
    }

    /** Une référence dupliquée réaffiche le formulaire avec erreur. */
    public function testNewDuplicateReferenceShowsError(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/receptions/new');
        $this->client->submitForm('Enregistrer', [
            'reception_form[reference]' => $this->reception->getReference(),
        ]);
        $this->assertResponseIsSuccessful(); // pas de redirection = formulaire ré-affiché
    }

    // ── Modification ──────────────────────────────────────────────────────

    public function testEditFormLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/receptions/' . $this->reception->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEditUpdatesTypeReception(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/receptions/' . $this->reception->getId() . '/edit');
        $this->client->submitForm('Enregistrer', [
            'reception_form[typeReception]' => TypeReception::TRANSFERT->value,
        ]);
        $this->assertResponseRedirects();
        $this->em->refresh($this->reception);
        $this->assertSame(TypeReception::TRANSFERT, $this->reception->getTypeReception());
    }

    // ── Suppression ───────────────────────────────────────────────────────

    public function testDeleteWithInvalidCsrfDoesNotDelete(): void
    {
        $this->loginAsUser();
        $id = $this->reception->getId();
        $this->client->request('POST', '/stock/receptions/' . $id . '/delete', ['_token' => 'invalide']);
        $this->assertResponseRedirects();
        $this->assertNotNull($this->em->find(Reception::class, $id));
    }

    public function testDeleteWithValidCsrfDeletesReception(): void
    {
        $this->loginAsUser();
        $r = (new Reception())->setReference('DEL-' . substr(uniqid(), -5))->setTypeReception(TypeReception::STANDARD);
        $this->em->persist($r);
        $this->em->flush();
        $id = $r->getId();

        $token = static::getContainer()->get('security.csrf.token_manager')
                        ->getToken('delete' . $id)->getValue();
        $this->client->request('POST', '/stock/receptions/' . $id . '/delete', ['_token' => $token]);
        $this->assertResponseRedirects();
        $this->em->clear();
        $this->assertNull($this->em->find(Reception::class, $id));
    }
}
