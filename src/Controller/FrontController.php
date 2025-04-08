<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    #[Route('/', name: 'app_front_home')]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('front/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/product/{id}', name: 'app_front_product_show', methods: ['GET'])]
    public function show(int $id, ProduitRepository $produitRepository): Response
    {
        $produit = $produitRepository->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('The product does not exist');
        }

        return $this->render('front/show.html.twig', [
            'produit' => $produit,
        ]);
    }
} 