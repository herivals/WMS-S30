<?php

namespace App\Tests\Controller;

/**
 * Tests fonctionnels du ProfileController.
 *
 * Scénarios couverts :
 * - Accès anonyme → redirection /login.
 * - Affichage de la page de paramètres.
 * - Soumission d'une valeur autorisée → enregistrée en base.
 * - Soumission d'une valeur non autorisée → ramenée à 25 (valeur par défaut).
 */
class ProfileControllerTest extends AbstractControllerTestCase
{
    // ── Accès anonyme ─────────────────────────────────────────────────────

    public function testSettingsRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/profil/parametres');
    }

    // ── Affichage ─────────────────────────────────────────────────────────

    public function testSettingsPageLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/profil/parametres');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    // ── Soumission ────────────────────────────────────────────────────────

    /** Une valeur autorisée (50) est correctement enregistrée. */
    public function testValidItemsPerPageIsSaved(): void
    {
        $user = $this->loginAsUser();
        $this->client->request('POST', '/profil/parametres', [
            'items_per_page' => 50,
        ]);
        $this->assertResponseRedirects('/profil/parametres');

        $this->em->refresh($user);
        $this->assertSame(50, $user->getItemsPerPage());
    }

    /** Une valeur arbitraire (777) non listée dans la whitelist est ramenée à 25. */
    public function testInvalidItemsPerPageFallsBackTo25(): void
    {
        $user = $this->loginAsUser();
        $this->client->request('POST', '/profil/parametres', [
            'items_per_page' => 777,
        ]);
        $this->assertResponseRedirects('/profil/parametres');

        $this->em->refresh($user);
        $this->assertSame(25, $user->getItemsPerPage());
    }

    /** La valeur 0 (cas limite) est également rejetée et ramenée à 25. */
    public function testZeroItemsPerPageFallsBackTo25(): void
    {
        $user = $this->loginAsUser();
        $this->client->request('POST', '/profil/parametres', [
            'items_per_page' => 0,
        ]);
        $this->assertResponseRedirects('/profil/parametres');
        $this->em->refresh($user);
        $this->assertSame(25, $user->getItemsPerPage());
    }

    /** Toutes les valeurs autorisées (10, 25, 50, 100) sont acceptées. */
    public function testAllAllowedValuesAreAccepted(): void
    {
        foreach ([10, 25, 50, 100] as $value) {
            $user = $this->loginAsUser();
            $this->client->request('POST', '/profil/parametres', ['items_per_page' => $value]);
            $this->assertResponseRedirects();
            $this->em->refresh($user);
            $this->assertSame($value, $user->getItemsPerPage(), "La valeur $value devrait être acceptée.");
        }
    }
}
