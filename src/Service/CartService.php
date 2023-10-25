<?php

namespace App\Service;

use App\Repository\PizzaRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private SessionInterface $session;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly PizzaRepository $pizzaRepository
    ) {
        $this->session = $this->requestStack->getSession();
    }

    public function update(int $pizzaId, int $quantity): mixed
    {
        $cart = $this->getCart();
        $cart[$pizzaId] = $quantity;
        $this->setCart($cart);
        return $cart;
    }

    public function delete(int $pizzaId): mixed
    {
        $cart = $this->getCart();
        unset($cart[$pizzaId]);
        $this->setCart($cart);
        return $cart;
    }

    public function count(): int
    {
        $cart = $this->getCart();
        return array_sum($cart);
    }

    public function list(): array
    {
        $cart = $this->getCart();
        $populatedCart = [];
        foreach ($cart as $key => $value) {
            $product = $this->pizzaRepository->findOneBy(['id' => $key]);
            if (!$product) {
                continue;
            }
            $populatedCart[] = [
                'id' => $key,
                'name' => $product->getName(),
                'quantity' => $value
            ];
        }
        return $populatedCart;
    }

    private function setCart(mixed $cart): void
    {
        $this->session->set('cart', $cart);
    }

    private function getCart()
    {
        return $this->session->get('cart', []);
    }
}