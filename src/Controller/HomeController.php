<?php

namespace App\Controller;

use App\Repository\PizzaRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

    public function header(): Response
    {
        $count = $this->cartService->count();
        return $this->render('_header.html.twig', [
            'count' => $count
        ]);
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request, PizzaRepository $pizzaRepository): Response
    {
        $minPrice = $request->get('minPrice');
        $maxPrice = $request->get('maxPrice');
        $order = explode('_', $request->get('order'));
        if (isset($order[0], $order[1]) && (!in_array($order[0], ['price', 'name']) || !in_array($order[1], ['asc', 'desc']))) {
            return new Response('I\'m a teapot', 418);
        }
        $pizzas = $pizzaRepository->search($minPrice, $maxPrice, $order[0] ?? null, $order[1] ?? null);
        return $this->render('home/index.html.twig', [
            'pizzas' => $pizzas,
        ]);
    }

    #[Route('/cart', name: 'app_add_cart', methods: ['POST'])]
    public function addCart(Request $request, PizzaRepository $pizzaRepository): Response
    {
        $productId = (int) $request->get('productId');
        $quantity = (int) $request->get('quantity');

        if (!$quantity) {
            return new Response('Product can\'t be empty', 400);
        }

        $product = $pizzaRepository->findOneBy(['id' => $productId]);
        if (!$product) {
            return new Response('product doesn\'t exist', 400);
        }

        $cart = $this->cartService->update($productId, $quantity);
        return new Response(array_sum($cart));
    }

    #[Route('/cart', name: 'app_show_cart', methods: ['GET'])]
    public function showCart(): Response
    {
        $cart = $this->cartService->list();
        return $this->render('home/cart.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/cart/{id}', name: 'app_delete_cart', methods: ['GET'])]
    public function deleteCart(int $id): Response
    {
        $this->cartService->delete($id);
        return $this->redirectToRoute('app_show_cart');
    }
}
