<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Exception\TableNotFoundException;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProduitRepository $produitRepository, CommandeRepository $commandeRepository): Response
    {
        try {
            // Get some statistics for the dashboard
            $totalProducts = $produitRepository->count([]);
            $lowStockProducts = $produitRepository->findLowStock(5);
            $recentOrders = $commandeRepository->findRecentOrders(5);
        } catch (TableNotFoundException $e) {
            // If tables don't exist yet, show empty data
            $totalProducts = 0;
            $lowStockProducts = [];
            $recentOrders = [];
            $this->addFlash('warning', 'Database tables are not properly set up yet.');
        }

        return $this->render('home/index.html.twig', [
            'totalProducts' => $totalProducts,
            'lowStockProducts' => $lowStockProducts,
            'recentOrders' => $recentOrders,
        ]);
    }
} 