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
            $cart = $this->cartService->list();
            $total = 0;
            foreach ($cart as $id => $quantity) {
                $pizza = $pizzaRepository->findOneBy(['id' => $id]);
                if (!$pizza) {
                    continue;
                }
                $orderLine = new OrderLine();
                $orderLine->setPizza($pizza);
                $orderLine->setQuantity($quantity);
                $total += $capturedPrice = $quantity * $pizza->getPrice();
                $orderLine->setCapturedPrice($capturedPrice);
                $order->addOrderLine($orderLine);
            }
            $order->setTotalPrice($total);
            $order->setState(OrderStatus::PENDING);
            $entityManager->persist($order);
            $entityManager->flush();
            $this->cartService->clearCart();
            $this->addFlash(
                'success',
                'Order successfully created'
            );
            return $this->redirectToRoute('app_home');
        }

        return $this->render('order/new.html.twig', [
            'form' => $form,
        ]);
    }
}
