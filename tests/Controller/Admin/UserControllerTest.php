<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use App\Tests\Controller\AbstractControllerTestCase;

/**
 * Tests fonctionnels du Admin\UserController.
 *
 * Scénarios couverts :
 * - Accès anonyme → redirection /login.
 * - Accès ROLE_USER → 403 (route ROLE_ADMIN uniquement).
 * - Liste avec filtre actif/inactif et recherche email/nom.
 * - Création d'un utilisateur avec mot de passe haché.
 * - Modification d'un utilisateur (sans changement de mot de passe).
 * - Activation/Désactivation (toggle) — interdit pour son propre compte.
 * - Suppression CSRF — interdite pour son propre compte.
 */
class UserControllerTest extends AbstractControllerTestCase
{
    private ?User $targetUser = null;

    protected function setUp(): void
    {
        parent::setUp();
        // Utilisateur cible (sur lequel les actions admin seront effectuées)
        $this->targetUser = $this->createTestUser(
            'target-' . uniqid() . '@wms.test',
            ['ROLE_USER'],
            'pass1234'
        );
    }

    // ── Accès anonyme ─────────────────────────────────────────────────────

    public function testIndexRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/admin/users');
    }

    public function testNewRequiresAuthentication(): void
    {
        $this->assertRedirectsToLogin('/admin/users/new');
    }

    // ── Accès ROLE_USER ───────────────────────────────────────────────────

    /** Un ROLE_USER ne peut pas accéder à la gestion des utilisateurs. */
    public function testIndexForbiddenForRoleUser(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/admin/users');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testNewForbiddenForRoleUser(): void
    {
        $this->loginAsUser();
        $this->client->request('GET', '/admin/users/new');
        $this->assertResponseStatusCodeSame(403);
    }

    // ── Liste ─────────────────────────────────────────────────────────────

    public function testIndexLoadsForAdmin(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/admin/users');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    /** Le filtre "active" ne retourne que les comptes actifs. */
    public function testIndexFilterActive(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/admin/users?filter=active');
        $this->assertResponseIsSuccessful();
    }

    /** Le filtre "inactive" ne retourne que les comptes inactifs. */
    public function testIndexFilterInactive(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/admin/users?filter=inactive');
        $this->assertResponseIsSuccessful();
    }

    /** La recherche par email trouve l'utilisateur cible. */
    public function testIndexSearchByEmail(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/admin/users?search=' . urlencode($this->targetUser->getEmail()));
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            $this->targetUser->getEmail(),
            $this->client->getResponse()->getContent()
        );
    }

    // ── Création ──────────────────────────────────────────────────────────

    public function testNewFormLoadsForAdmin(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/admin/users/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testNewValidSubmissionCreatesUser(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/admin/users/new');
        $email = 'created-' . uniqid() . '@wms.test';
        $this->client->submitForm('Enregistrer', [
            'user_form[email]'         => $email,
            'user_form[fullName]'      => 'Utilisateur Créé',
            'user_form[plainPassword]' => 'Secure1234!',
            'user_form[roles]'         => ['ROLE_USER'],
        ]);
        $this->assertResponseRedirects();

        $created = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        $this->assertNotNull($created);
        // Le mot de passe doit être haché, jamais stocké en clair
        $this->assertNotSame('Secure1234!', $created->getPassword());
        if ($created) {
            $this->toCleanup[] = $created;
        }
    }

    // ── Modification ──────────────────────────────────────────────────────

    public function testEditFormLoads(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/admin/users/' . $this->targetUser->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /** Modifier le nom sans changer le mot de passe conserve le hash existant. */
    public function testEditWithoutPasswordChangeKeepsHash(): void
    {
        $this->loginAsAdmin();
        $oldHash = $this->targetUser->getPassword();
        $this->client->request('GET', '/admin/users/' . $this->targetUser->getId() . '/edit');
        $this->client->submitForm('Enregistrer', [
            'user_form[fullName]'      => 'Nom Modifié',
            'user_form[plainPassword]' => '', // vide = pas de changement
        ]);
        $this->assertResponseRedirects();
        $this->em->refresh($this->targetUser);
        $this->assertSame($oldHash, $this->targetUser->getPassword(), 'Le hash ne doit pas changer si le champ est vide.');
        $this->assertSame('Nom Modifié', $this->targetUser->getFullName());
    }

    // ── Toggle actif/inactif ──────────────────────────────────────────────

    /** Un admin peut désactiver un autre utilisateur. */
    public function testToggleDeactivatesUser(): void
    {
        $this->loginAsAdmin();
        $this->client->request('POST', '/admin/users/' . $this->targetUser->getId() . '/toggle');
        $this->assertResponseRedirects();
        $this->em->refresh($this->targetUser);
        $this->assertFalse($this->targetUser->isActive());
    }

    /**
     * Un admin ne peut pas désactiver son propre compte.
     * Cela évite de se bloquer soi-même accidentellement.
     */
    public function testToggleCannotDeactivateOwnAccount(): void
    {
        $admin = $this->loginAsAdmin();
        $this->client->request('POST', '/admin/users/' . $admin->getId() . '/toggle');
        $this->assertResponseRedirects();
        $this->em->refresh($admin);
        // Le compte admin reste actif
        $this->assertTrue($admin->isActive());
    }

    // ── Suppression ───────────────────────────────────────────────────────

    /** Token CSRF invalide n'effectue pas la suppression. */
    public function testDeleteWithInvalidCsrfDoesNotDelete(): void
    {
        $this->loginAsAdmin();
        $id = $this->targetUser->getId();
        $this->client->request('POST', '/admin/users/' . $id . '/delete', ['_token' => 'invalide']);
        $this->assertResponseRedirects();
        $this->assertNotNull($this->em->find(User::class, $id));
    }

    /** Token CSRF valide supprime l'utilisateur cible. */
    public function testDeleteWithValidCsrfDeletesUser(): void
    {
        $this->loginAsAdmin();
        $toDelete = $this->createTestUser('del-' . uniqid() . '@wms.test');
        // On le retire de toCleanup car on va le supprimer manuellement
        $this->toCleanup = array_filter($this->toCleanup, fn($e) => $e !== $toDelete);
        $id = $toDelete->getId();

        $token = static::getContainer()->get('security.csrf.token_manager')
                        ->getToken('delete' . $id)->getValue();
        $this->client->request('POST', '/admin/users/' . $id . '/delete', ['_token' => $token]);
        $this->assertResponseRedirects();
        $this->em->clear();
        $this->assertNull($this->em->find(User::class, $id));
    }

    /**
     * Un admin ne peut pas supprimer son propre compte.
     * Double garde : CSRF + auto-suppression interdite.
     */
    public function testDeleteCannotDeleteOwnAccount(): void
    {
        $admin = $this->loginAsAdmin();
        $id    = $admin->getId();
        $token = static::getContainer()->get('security.csrf.token_manager')
                        ->getToken('delete' . $id)->getValue();

        $this->client->request('POST', '/admin/users/' . $id . '/delete', ['_token' => $token]);
        $this->assertResponseRedirects();
        $this->em->clear();
        // Le compte doit toujours exister
        $this->assertNotNull($this->em->find(User::class, $id));
    }
}
