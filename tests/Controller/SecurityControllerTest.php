<?php

namespace App\Tests\Controller;

/**
 * Tests fonctionnels du SecurityController.
 *
 * Scénarios couverts :
 * - Affichage de la page de login (anonyme).
 * - Redirection vers le dashboard si déjà connecté.
 * - Soumission avec mauvaises credentials → message d'erreur.
 * - Soumission avec bons credentials → redirection vers le dashboard.
 * - La route /logout ne génère pas d'erreur 500 (gérée par le firewall).
 * - Accès aux routes 2FA (enable, qrcode, disable).
 *
 * Note : le vrai flux 2FA (scan QR + saisie TOTP) n'est pas testé ici
 * car il nécessiterait un token TOTP généré à l'instant (dépend du temps).
 */
class SecurityControllerTest extends AbstractControllerTestCase
{
    // ── /login ────────────────────────────────────────────────────────────

    /** La page de login s'affiche sans authentification préalable. */
    public function testLoginPageIsAccessibleAnonymously(): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /** Un utilisateur déjà connecté est redirigé vers le dashboard. */
    public function testAlreadyAuthenticatedRedirectsToDashboard(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/login');
        $this->assertResponseRedirects('/');
    }

    /** Des credentials invalides réaffichent le formulaire avec une erreur. */
    public function testInvalidCredentialsShowError(): void
    {
        $this->client->request('POST', '/login', [
            '_username' => 'inexistant@wms.test',
            '_password' => 'mauvais_mdp',
        ]);
        // Le firewall redirige vers /login après échec
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert, .error, [class*="error"], [class*="alert"]');
    }

    /** Des credentials valides redirigent vers le dashboard. */
    public function testValidCredentialsRedirectToDashboard(): void
    {
        $this->createTestUser('login-test@wms.test', ['ROLE_USER'], 'monMotDePasse');
        $this->client->request('POST', '/login', [
            '_username' => 'login-test@wms.test',
            '_password' => 'monMotDePasse',
        ]);
        $this->assertResponseRedirects();
    }

    // ── /logout ───────────────────────────────────────────────────────────

    /**
     * /logout est intercepté par le firewall Symfony avant d'atteindre la méthode du contrôleur.
     * On vérifie qu'il ne retourne pas 500 et redirige bien.
     */
    public function testLogoutRedirects(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/logout');
        $this->assertResponseRedirects();
    }

    // ── 2FA ───────────────────────────────────────────────────────────────

    /** /2fa/enable est accessible à un utilisateur authentifié. */
    public function test2faEnablePageLoads(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/2fa/enable');
        $this->assertResponseIsSuccessful();
    }

    /** /2fa/enable non authentifié → redirection login. */
    public function test2faEnableRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/2fa/enable');
    }

    /**
     * /2fa/qrcode sans 2FA activé retourne 404.
     * (Le secret n'est pas initialisé sur un nouvel utilisateur.)
     */
    public function testQrCodeWithout2faEnabledReturns404(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/2fa/qrcode');
        $this->assertResponseStatusCodeSame(404);
    }

    /** /2fa/disable non authentifié → redirection login. */
    public function testDisable2faRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/2fa/disable');
    }

    /** /2fa/disable sur un compte sans 2FA redirige quand même vers le dashboard. */
    public function testDisable2faWithout2faActiveStillRedirects(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/2fa/disable');
        $this->assertResponseRedirects('/');
    }
}
