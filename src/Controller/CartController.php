<?php

namespace App\Controller;

use App\Repository\PizzaRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

    #[Route('/', name: 'app_cart_add', methods: ['POST'])]
    public function add(Request $request, PizzaRepository $pizzaRepository): Response
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

    #[Route('/', name: 'app_cart_show', methods: ['GET'])]
    public function show(): Response
    {
        $cart = $this->cartService->list();
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/{id}', name: 'app_cart_delete', methods: ['GET'])]
    public function delete(int $id): Response
    {
        $this->cartService->delete($id);
        return $this->redirectToRoute('app_cart_show');
    }
}
