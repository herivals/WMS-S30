<?php

namespace App\Tests\Controller;

/**
 * Tests fonctionnels du DashboardController.
 *
 * Scénarios couverts :
 * - Accès anonyme → redirection vers /login.
 * - Dashboard principal (/) s'affiche pour un utilisateur connecté.
 * - Dashboard stock (/stock/dashboard) s'affiche avec les KPIs.
 *
 * Note : les valeurs numériques des KPIs (total, disponibles, etc.) ne sont pas
 * assertées car elles dépendent de l'état de la base de test.
 * On vérifie que la page charge sans erreur et contient les blocs attendus.
 */
class DashboardControllerTest extends AbstractControllerTestCase
{
    // ── Accès anonyme ─────────────────────────────────────────────────────

    /** Le dashboard principal redirige un visiteur non connecté. */
    public function testMainDashboardRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/');
    }

    /** Le dashboard stock redirige un visiteur non connecté. */
    public function testStockDashboardRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/stock/dashboard');
    }

    // ── Dashboard principal ───────────────────────────────────────────────

    /** La page d'accueil se charge pour un utilisateur authentifié. */
    public function testMainDashboardLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    // ── Dashboard stock ───────────────────────────────────────────────────

    /** La page stock dashboard se charge sans erreur. */
    public function testStockDashboardLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/dashboard');
        $this->assertResponseIsSuccessful();
    }

    /**
     * Les compteurs KPIs sont présents dans la réponse HTML.
     * On cherche les clés sémantiques rendues par le template,
     * indépendamment des valeurs numériques.
     */
    public function testStockDashboardContainsKpiBlocks(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/stock/dashboard');
        $this->assertResponseIsSuccessful();
        // Ces textes sont rendus par le template stock.html.twig
        $body = $this->client->getResponse()->getContent();
        $this->assertStringContainsStringIgnoringCase('disponible', $body);
    }
}
