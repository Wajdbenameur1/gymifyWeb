<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Entraineur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractController
{
    #[Route('/user/{id}', name: 'app_user_show')]
    public function show(User $user): Response
    {
        // Récupérer la spécialité seulement si l'utilisateur est un Entraineur
        $specialite = $user->getSpecialiteIfEntraineur();

        // Vous pouvez maintenant utiliser $specialite dans votre logique, par exemple pour l'afficher dans un template
        return $this->render('utilisateur/show.html.twig', [
            'user' => $user,
            'specialite' => $specialite,
        ]);
    }
}
