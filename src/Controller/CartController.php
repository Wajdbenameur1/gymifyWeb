<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Produit;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    private $cartRepository;
    private $entityManager;

    public function __construct(CartRepository $cartRepository, EntityManagerInterface $entityManager)
    {
        $this->cartRepository = $cartRepository;
        $this->entityManager = $entityManager;
    }

    private function getCart(SessionInterface $session): Cart
    {
        $sessionId = $session->getId();
        $cart = $this->cartRepository->findOneBy(['sessionId' => $sessionId]);
        
        if (!$cart) {
            $cart = new Cart();
            $cart->setSessionId($sessionId);
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }
        
        return $cart;
    }

    #[Route('/', name: 'app_cart_index', methods: ['GET'])]
    public function index(SessionInterface $session): Response
    {
        $cart = $this->getCart($session);
        
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Request $request, Produit $produit, SessionInterface $session): Response
    {
        $cart = $this->getCart($session);
        $quantity = $request->request->getInt('quantity', 1);

        // Check if product is already in cart
        $cartItem = null;
        foreach ($cart->getItems() as $item) {
            if ($item->getProduit()->getId() === $produit->getId()) {
                $cartItem = $item;
                break;
            }
        }

        if ($cartItem) {
            // Update quantity if product exists
            $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
        } else {
            // Create new cart item if product doesn't exist
            $cartItem = new CartItem();
            $cartItem->setProduit($produit);
            $cartItem->setQuantity($quantity);
            $cart->addItem($cartItem);
        }

        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        $this->addFlash('success', 'Product added to cart successfully');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(Request $request, CartItem $cartItem): Response
    {
        $quantity = $request->request->getInt('quantity', 1);
        
        if ($quantity > 0) {
            $cartItem->setQuantity($quantity);
            $cartItem->getCart()->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
            $this->addFlash('success', 'Cart updated successfully');
        } else {
            $this->entityManager->remove($cartItem);
            $this->entityManager->flush();
            $this->addFlash('success', 'Item removed from cart');
        }

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(CartItem $cartItem): Response
    {
        $this->entityManager->remove($cartItem);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Item removed from cart');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(SessionInterface $session): Response
    {
        $cart = $this->getCart($session);
        foreach ($cart->getItems() as $item) {
            $this->entityManager->remove($item);
        }
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Cart cleared successfully');
        return $this->redirectToRoute('app_cart_index');
    }
} 