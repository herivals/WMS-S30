<?php

namespace App\Controller;

use App\Entity\UserPreference;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profil', name: 'profile_')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/parametres', name: 'settings')]
    public function settings(Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $pref = $user->getPreference();

        if (!$pref) {
            $pref = new UserPreference();
            $user->setPreference($pref);
        }

        if ($request->isMethod('POST')) {
            $itemsPerPage = (int) $request->request->get('items_per_page', 25);
            if (!in_array($itemsPerPage, [10, 25, 50, 100], true)) {
                $itemsPerPage = 25;
            }
            $pref->setItemsPerPage($itemsPerPage);
            $em->persist($pref);
            $em->flush();
            $this->addFlash('success', 'Préférences enregistrées.');
            return $this->redirectToRoute('profile_settings');
        }

        return $this->render('profile/settings.html.twig', [
            'pref' => $pref,
            'itemsOptions' => [10, 25, 50, 100],
        ]);
    }
}
