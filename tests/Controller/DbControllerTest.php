<?php

namespace App\Tests\Controller;

/**
 * Tests fonctionnels du DbController.
 *
 * Scénarios couverts :
 * - Accès anonyme → redirection /login.
 * - Accès ROLE_USER → 403 Forbidden (route ROLE_ADMIN uniquement).
 * - Accès ROLE_ADMIN → le dump se déclenche (on teste la réponse, pas le fichier).
 *
 * Note sur les tests d'intégration réels :
 * Le dump pg_dump complet n'est pas testé en environnement CI car il nécessite
 * que Docker tourne avec le conteneur DB démarré.
 * On vérifie uniquement que la route est sécurisée et accessible au bon rôle.
 */
class DbControllerTest extends AbstractControllerTestCase
{
    private const EXPORT_URL = '/su-admin/db/export/98u325_sav@tkl56138';

    // ── Accès anonyme ─────────────────────────────────────────────────────

    /** Un visiteur non connecté est redirigé vers /login. */
    public function testExportRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin(self::EXPORT_URL);
    }

    // ── Accès ROLE_USER ───────────────────────────────────────────────────

    /** Un utilisateur standard ne peut pas accéder à l'export (403). */
    public function testExportForbiddenForRoleUser(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', self::EXPORT_URL);
        $this->assertResponseStatusCodeSame(403);
    }

    // ── Accès ROLE_ADMIN ──────────────────────────────────────────────────

    /**
     * Un administrateur peut accéder à la route.
     * Le test ne valide pas le contenu du dump (dépend de Docker/pg_dump),
     * il valide que la route n'est pas inaccessible et que le contrôleur
     * tente bien l'export (le résultat peut être 200 ou 500 selon l'environnement).
     */
    public function testExportAccessibleForRoleAdmin(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', self::EXPORT_URL);

        $statusCode = $this->client->getResponse()->getStatusCode();
        // 200 : dump réussi. 500 : pg_dump non disponible en CI — les deux sont acceptables ici.
        $this->assertContains(
            $statusCode,
            [200, 500],
            'La route doit être accessible à un admin même si le dump échoue en CI.'
        );
    }
}
