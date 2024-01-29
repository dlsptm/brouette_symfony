<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profil', name: 'app_profil')]
class ProfilController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'Profil de l\'utilisateur',
        ]);
    }

    #[Route('/orders', name: 'app_orders')]
    public function orders(): Response
    {
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'Commandes de l\'utilisateur',
        ]);
    }
}
