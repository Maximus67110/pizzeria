<?php

namespace App\Controller;

use App\Repository\PizzaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PizzaRepository $pizzaRepository): Response
    {
        $pizzas = $pizzaRepository->findAll();
        return $this->render('home/index.html.twig', [
            'pizzas' => $pizzas,
        ]);
    }

    #[Route('/cart', name: 'app_add_cart', methods: ['POST'])]
    public function addCart(Request $request, SessionInterface $session, PizzaRepository $pizzaRepository): Response
    {
        $productId = (int) $request->get('productId');
        $quantity = (int) $request->get('quantity');

        if (!$quantity) {
            return $this->redirectToRoute('app_home');
        }

        $product = $pizzaRepository->findOneBy(['id' => $productId]);
        if (!$product) {
            return $this->redirectToRoute('app_home');
        }

        $cart = $session->get('cart', []);
        $cart[$productId] = $quantity;
        $session->set('cart', $cart);
        return $this->redirectToRoute('app_show_cart');
    }

    #[Route('/cart', name: 'app_show_cart', methods: ['GET'])]
    public function showCart(SessionInterface $session, PizzaRepository $pizzaRepository): Response
    {
        $cart = $session->get('cart', []);
        $populatedCart = [];
        foreach ($cart as $key => $value) {
            $product = $pizzaRepository->findOneBy(['id' => $key]);
            if (!$product) {
                continue;
            }
            $populatedCart[] = [
                'id' => $key,
                'name' => $product->getName(),
                'quantity' => $value
            ];
        }
        return $this->render('home/cart.html.twig', [
            'cart' => $populatedCart,
        ]);
    }

    #[Route('/cart/{id}', name: 'app_update_cart', methods: ['POST'])]
    public function updateCart(int $id, Request $request, SessionInterface $session): Response
    {
        $quantity = (int) $request->get('quantity');
        $cart = $session->get('cart', []);
        $cart[$id] = $quantity;
        $session->set('cart', $cart);
        return $this->redirectToRoute('app_show_cart');
    }

    #[Route('/cart/{id}', name: 'app_delete_cart', methods: ['GET'])]
    public function deleteCart(int $id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        unset($cart[$id]);
        $session->set('cart', $cart);
        return $this->redirectToRoute('app_show_cart');
    }
}
