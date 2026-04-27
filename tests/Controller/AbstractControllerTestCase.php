<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Classe de base pour tous les tests fonctionnels de contrôleurs.
 *
 * Fournit :
 * - Un client HTTP (KernelBrowser) réinitialisé avant chaque test.
 * - Des helpers pour créer et connecter un utilisateur ROLE_USER ou ROLE_ADMIN.
 * - Un nettoyage automatique des entités créées pendant les tests (tearDown).
 *
 * Convention : les entités créées dans un test doivent être ajoutées à
 * $this->toCleanup pour être supprimées dans tearDown().
 */
abstract class AbstractControllerTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    /** Entités à supprimer dans tearDown pour isoler chaque test. */
    protected array $toCleanup = [];

    protected function setUp(): void
    {
        // Garantit qu'aucun kernel résiduel d'un test précédent ne bloque createClient().
        // WebTestCase interdit d'appeler createClient() si le kernel est déjà bootlé.
        static::ensureKernelShutdown();

        $this->client = static::createClient();
        // getContainer() est disponible après createClient()
        $this->em = static::getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        // Suppression en ordre inverse pour respecter les FK
        foreach (array_reverse($this->toCleanup) as $entity) {
            try {
                $managed = $this->em->find($entity::class, $entity->getId());
                if ($managed) {
                    $this->em->remove($managed);
                }
            } catch (\Throwable) {
                // Entité déjà supprimée par le test lui-même — on ignore
            }
        }
        $this->em->flush();
        $this->toCleanup = [];

        // KernelTestCase::tearDown() appelle ensureKernelShutdown() — obligatoire
        parent::tearDown();
    }

    // ── Helpers utilisateurs ──────────────────────────────────────────────

    /**
     * Crée un utilisateur en base avec le rôle donné et le planifie pour nettoyage.
     */
    protected function createTestUser(
        string $email,
        array $roles = ['ROLE_USER'],
        string $password = 'test1234'
    ): User {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user   = new User();
        $user->setEmail($email)
             ->setFullName('Test ' . implode(',', $roles))
             ->setRoles($roles)
             ->setPassword($hasher->hashPassword($user, $password));
        $this->em->persist($user);
        $this->em->flush();
        $this->toCleanup[] = $user;
        return $user;
    }

    /**
     * Connecte un utilisateur ROLE_USER et le retourne.
     * Utilise loginUser() qui bypass le formulaire de login,
     * ce qui isole les tests de contrôleurs des tests d'authentification.
     */
    protected function loginAsUser(): User
    {
        $user = $this->createTestUser('user-' . uniqid() . '@wms.test', ['ROLE_USER']);
        $this->client->loginUser($user);
        return $user;
    }

    /**
     * Connecte un utilisateur ROLE_ADMIN et le retourne.
     */
    protected function loginAsAdmin(): User
    {
        $admin = $this->createTestUser('admin-' . uniqid() . '@wms.test', ['ROLE_ADMIN', 'ROLE_USER']);
        $this->client->loginUser($admin);
        return $admin;
    }

    // ── Helpers assertions ─────────────────────────────────────────────────

    /**
     * Vérifie qu'un accès non authentifié redirige vers /login.
     */
    protected function assertRedirectsToLogin(string $url, string $method = 'GET'): void
    {
        $this->client->request($method, $url);
        $this->assertResponseRedirects();
        $this->assertStringContainsString('/login', $this->client->getResponse()->headers->get('Location') ?? '');
    }
}
