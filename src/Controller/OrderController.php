<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Enum\OrderStatus;
use App\Form\OrderType;
use App\Repository\PizzaRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/order')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

    #[Route('/checkout', name: 'app_order_checkout')]
    public function checkout(Request $request, PizzaRepository $pizzaRepository, EntityManagerInterface $entityManager): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $total = $this->cartService->total();
            $order->setTotalPrice($total);
            $order->setState(OrderStatus::PENDING);
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setUpdatedAt(new \DateTimeImmutable());
            $cart = $this->cartService->list();
            foreach ($cart as $product) {
                $pizza = $pizzaRepository->findOneBy(['id' => $product['id']]);
                if (!$pizza) {
                    continue;
                }
                $orderLine = new OrderLine();
                $orderLine->setPizza($pizza);
                $orderLine->setQuantity($product['quantity']);
                $orderLine->setCapturedPrice($product['quantity'] * $pizza->getPrice());
                $order->addOrderLine($orderLine);
            }
            $entityManager->persist($order);
            $entityManager->flush();
            $this->cartService->clearCart();
            return $this->redirectToRoute('app_home');
        }

        return $this->render('order/new.html.twig', [
            'form' => $form,
        ]);
    }
}
