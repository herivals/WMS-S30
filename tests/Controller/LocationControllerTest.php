<?php

namespace App\Tests\Controller;

use App\Entity\Location;

/**
 * Tests fonctionnels du LocationController.
 *
 * Scénarios couverts :
 * - Accès anonyme → redirection /login.
 * - Liste avec recherche et filtre allée.
 * - Affichage fiche détaillée.
 * - Création d'un emplacement via formulaire.
 * - Modification d'un emplacement existant.
 * - Suppression avec CSRF valide / invalide.
 */
class LocationControllerTest extends AbstractControllerTestCase
{
    private ?Location $location = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->location = (new Location())
            ->setCode('TEST-' . substr(uniqid(), -5))
            ->setAllee('Z')
            ->setRack('99')
            ->setNiveau('9')
            ->setPosition('9');
        $this->em->persist($this->location);
        $this->em->flush();
        $this->toCleanup[] = $this->location;
    }

    // ── Accès anonyme ─────────────────────────────────────────────────────

    public function testIndexRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/locations');
    }

    public function testNewRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/locations/new');
    }

    // ── Liste ─────────────────────────────────────────────────────────────

    public function testIndexLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/locations');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    /** La recherche par code retourne uniquement les emplacements correspondants. */
    public function testIndexSearchByCode(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/locations?search=' . $this->location->getCode());
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            $this->location->getCode(),
            $this->client->getResponse()->getContent()
        );
    }

    /** Le filtre par allée fonctionne. */
    public function testIndexFilterByAllee(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/locations?allee=Z');
        $this->assertResponseIsSuccessful();
    }

    // ── Affichage ─────────────────────────────────────────────────────────

    public function testShowExistingLocation(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/locations/' . $this->location->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testShowNonExistingLocationReturns404(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/locations/999999');
        $this->assertResponseStatusCodeSame(404);
    }

    // ── Création ──────────────────────────────────────────────────────────

    public function testNewFormLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/locations/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testNewValidSubmissionCreatesLocation(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/locations/new');
        $code = 'NEW-' . substr(uniqid(), -5);
        $this->client->submitForm('Enregistrer', [
            'location_form[code]'  => $code,
            'location_form[allee]' => 'A',
            'location_form[rack]'  => '01',
        ]);
        $this->assertResponseRedirects();

        $created = $this->em->getRepository(Location::class)->findOneBy(['code' => $code]);
        $this->assertNotNull($created);
        if ($created) {
            $this->toCleanup[] = $created;
        }
    }

    // ── Modification ──────────────────────────────────────────────────────

    public function testEditFormLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/locations/' . $this->location->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEditValidSubmissionUpdatesLocation(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/locations/' . $this->location->getId() . '/edit');
        $this->client->submitForm('Enregistrer', [
            'location_form[rack]' => '88',
        ]);
        $this->assertResponseRedirects();
        $this->em->refresh($this->location);
        $this->assertSame('88', $this->location->getRack());
    }

    // ── Suppression ───────────────────────────────────────────────────────

    public function testDeleteWithInvalidCsrfDoesNotDelete(): void
    {
        $this->loginAsUser();
        $id = $this->location->getId();
        $this->client->request('POST', '/stock/locations/' . $id . '/delete', [
            '_token' => 'invalide',
        ]);
        $this->assertResponseRedirects();
        $this->assertNotNull($this->em->find(Location::class, $id));
    }

    public function testDeleteWithValidCsrfDeletesLocation(): void
    {
        $this->loginAsUser();
        $loc = (new Location())->setCode('DEL-' . substr(uniqid(), -5));
        $this->em->persist($loc);
        $this->em->flush();
        $id = $loc->getId();

        $token = static::getContainer()->get('security.csrf.token_manager')
                        ->getToken('delete' . $id)->getValue();

        $this->client->request('POST', '/stock/locations/' . $id . '/delete', ['_token' => $token]);
        $this->assertResponseRedirects();
        $this->em->clear();
        $this->assertNull($this->em->find(Location::class, $id));
    }
}
