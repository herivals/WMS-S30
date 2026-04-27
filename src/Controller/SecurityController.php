<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Authentification (login / logout) et gestion de la double authentification Google (2FA TOTP).
 */
class SecurityController extends AbstractController
{
    /**
     * Affiche le formulaire de connexion.
     * Redirige vers le dashboard si l'utilisateur est déjà authentifié
     * pour éviter d'afficher la page de login à tort après un retour arrière.
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * Le logout est entièrement géré par le firewall Symfony (security.yaml).
     * Cette méthode ne sera jamais exécutée ; l'exception signale une mauvaise configuration.
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method should never be called.');
    }

    /**
     * Active la 2FA Google pour l'utilisateur connecté.
     * Le secret TOTP n'est généré qu'une seule fois : si l'utilisateur revient sur cette page
     * après avoir déjà activé la 2FA, le secret existant est conservé.
     */
    #[Route('/2fa/enable', name: 'app_2fa_enable')]
    public function enable2fa(
        GoogleAuthenticatorInterface $googleAuthenticator,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        if (!$user->isGoogleAuthenticatorEnabled()) {
            $secret = $googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $em->flush();
        }

        return $this->render('security/enable_2fa.html.twig', [
            'secret' => $user->getGoogleAuthenticatorSecret(),
        ]);
    }

    /**
     * Génère et retourne le QR code PNG à scanner avec Google Authenticator.
     * Le contenu du QR code est fourni par le bundle scheb/2fa via `getQRContent()`.
     */
    #[Route('/2fa/qrcode', name: 'app_2fa_qrcode')]
    public function qrCode(GoogleAuthenticatorInterface $googleAuthenticator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        if (!$user->isGoogleAuthenticatorEnabled()) {
            throw $this->createNotFoundException();
        }

        $qrContent = $googleAuthenticator->getQRContent($user);

        $qrCode = new QrCode(
            data: $qrContent,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 250,
            margin: 10,
        );

        $result = (new PngWriter())->write($qrCode);

        return new Response($result->getString(), 200, ['Content-Type' => 'image/png']);
    }

    /**
     * Désactive la 2FA en supprimant le secret TOTP de l'utilisateur.
     * Le bundle scheb/2fa considère automatiquement la 2FA comme désactivée dès que le secret est null.
     */
    #[Route('/2fa/disable', name: 'app_2fa_disable')]
    public function disable2fa(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();
        $user->setGoogleAuthenticatorSecret(null);
        $em->flush();

        $this->addFlash('success', '2FA désactivé.');
        return $this->redirectToRoute('app_dashboard');
    }
}
