<?php

namespace App\Controller\Shop;

use App\Entity\Produit;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

#[Route('/shop')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'shop_product_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('shop/product/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/cart', name: 'shop_cart_index', methods: ['GET'])]
    public function cart(SessionInterface $session, ProduitRepository $produitRepository): Response
    {
        $cart = $session->get('cart', []);
        $cartItems = [];
        $total = 0;

        if (!empty($cart)) {
            foreach ($cart as $id => $quantity) {
                $product = $produitRepository->find($id);
                if ($product) {
                    $cartItems[] = [
                        'product' => $product,
                        'quantity' => $quantity
                    ];
                    $total += $product->getPrixP() * $quantity;
                }
            }
        }

        return $this->render('shop/cart/index.html.twig', [
            'items' => $cartItems,
            'total' => $total
        ]);
    }

    #[Route('/cart/add/{id}', name: 'shop_cart_add', methods: ['POST'])]
    public function addToCart(Request $request, Produit $product, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $id = $product->getIdP();

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $session->set('cart', $cart);
        $this->addFlash('success', 'Le produit a été ajouté au panier.');
        
        return $this->redirectToRoute('shop_cart_index');
    }

    #[Route('/cart/remove/{id}', name: 'shop_cart_remove', methods: ['POST'])]
    public function removeFromCart(Produit $product, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $id = $product->getIdP();

        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);
        $this->addFlash('success', 'Le produit a été retiré du panier.');
        
        return $this->redirectToRoute('shop_cart_index');
    }

    #[Route('/checkout', name: 'shop_checkout', methods: ['POST'])]
    public function checkout(
        SessionInterface $session, 
        ProduitRepository $produitRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $cart = $session->get('cart', []);
        
        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('shop_cart_index');
        }

        // Create new order
        $commande = new Commande();
        $commande->setDateC(new DateTime());
        $commande->setStatutC('En cours');
        
        $total = 0;
        
        // Add order items
        foreach ($cart as $id => $quantity) {
            $product = $produitRepository->find($id);
            if ($product) {
                $ligneCommande = new LigneCommande();
                $ligneCommande->setCommande($commande);
                $ligneCommande->setProduit($product);
                $ligneCommande->setQuantite($quantity);
                $ligneCommande->setPrix($product->getPrixP());
                
                $total += $product->getPrixP() * $quantity;
                
                $entityManager->persist($ligneCommande);
                
                // Update product stock
                $newStock = $product->getStockP() - $quantity;
                $product->setStockP($newStock);
            }
        }
        
        $commande->setTotalC($total);
        
        // Save everything to database
        $entityManager->persist($commande);
        $entityManager->flush();
        
        // Clear the cart
        $session->remove('cart');
        
        $this->addFlash('success', 'Votre commande a été validée avec succès!');
        return $this->redirectToRoute('shop_cart_index');
    }

    #[Route('/mes-commandes', name: 'shop_orders_index', methods: ['GET'])]
    public function orders(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findBy([], ['dateC' => 'DESC']);
        
        return $this->render('shop/order/index.html.twig', [
            'commandes' => $commandes
        ]);
    }

    #[Route('/commande/{id}', name: 'shop_order_show', methods: ['GET'])]
    public function showOrder(Commande $commande): Response
    {
        return $this->render('shop/order/show.html.twig', [
            'commande' => $commande
        ]);
    }

    #[Route('/commande/{id}/annuler', name: 'shop_order_cancel', methods: ['POST'])]
    public function cancelOrder(
        Commande $commande, 
        EntityManagerInterface $entityManager
    ): Response
    {
        // Only allow cancellation of orders that are "En cours"
        if ($commande->getStatutC() !== 'En cours') {
            $this->addFlash('error', 'Cette commande ne peut plus être annulée.');
            return $this->redirectToRoute('shop_order_show', ['id' => $commande->getIdC()]);
        }

        // Update order status
        $commande->setStatutC('Annulée');

        // Restore product quantities
        foreach ($commande->getProduits() as $produit) {
            // Find the ligne_commande to get the quantity
            foreach ($entityManager->getRepository(LigneCommande::class)->findBy(['commande' => $commande, 'produit' => $produit]) as $ligneCommande) {
                $newStock = $produit->getStockP() + $ligneCommande->getQuantite();
                $produit->setStockP($newStock);
            }
        }

        $entityManager->flush();

        $this->addFlash('success', 'La commande a été annulée avec succès.');
        return $this->redirectToRoute('shop_orders_index');
    }
} 