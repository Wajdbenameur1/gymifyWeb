<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/orders')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'admin_orders_index')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findBy([], ['dateC' => 'DESC']);
        
        return $this->render('admin/order/index.html.twig', [
            'commandes' => $commandes
        ]);
    }

    #[Route('/{id}', name: 'admin_order_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('admin/order/show.html.twig', [
            'commande' => $commande
        ]);
    }

    #[Route('/{id}/update-status', name: 'admin_order_update_status', methods: ['POST'])]
    public function updateStatus(
        Request $request,
        Commande $commande, 
        EntityManagerInterface $entityManager
    ): Response
    {
        $newStatus = $request->request->get('status');
        if (!in_array($newStatus, ['En cours', 'Validée', 'Annulée'])) {
            $this->addFlash('error', 'Statut invalide.');
            return $this->redirectToRoute('admin_orders_index');
        }

        $commande->setStatutC($newStatus);
        $entityManager->flush();

        $this->addFlash('success', 'Le statut de la commande a été mis à jour.');
        return $this->redirectToRoute('admin_orders_index');
    }

    #[Route('/{id}/delete', name: 'admin_order_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Commande $commande,
        EntityManagerInterface $entityManager
    ): Response
    {
        $entityManager->remove($commande);
        $entityManager->flush();

        $this->addFlash('success', 'La commande a été supprimée.');
        return $this->redirectToRoute('admin_orders_index');
    }

    #[Route('/admin/orders/{id}/update', name: 'admin_order_update', methods: ['POST'])]
    public function update(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $status = $request->request->get('status');
        
        if ($status) {
            $commande->setStatus($status);
            $entityManager->flush();
            
            $this->addFlash('success', 'Statut de la commande mis à jour avec succès');
        }

        return $this->redirectToRoute('admin_order_show', ['id' => $commande->getId()]);
    }
} 