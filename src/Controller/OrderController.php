<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Enum\OrderStatus;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Repository\PizzaRepository;
use App\Service\CartService;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/order')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly PaymentService $paymentService
    ) {}

    #[Route('/checkout', name: 'app_order_checkout')]
    public function checkout(Request $request, PizzaRepository $pizzaRepository, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $tokenProvider = $this->container->get('security.csrf.token_manager');
        $token = $tokenProvider->getToken('stripe_token')->getValue();
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
        $lineItems = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $cart = $this->cartService->getCart();
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
                $lineItem = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $pizza->getName(),
                        ],
                        'unit_amount' => $pizza->getPrice(),
                    ],
                    'quantity' => $quantity,
                ];
                $lineItems[] = $lineItem;
            }
            $order->setTotalPrice($total);
            $order->setState(OrderStatus::PENDING);
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($order);
            $entityManager->flush();
            $this->cartService->clearCart();
            $session->set('orderNumber', $order->getId());
            $StripeSession = $this->paymentService->pay($lineItems, $token);
            return $this->redirect($StripeSession->url, 303);
        }
        return $this->render('order/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/checkout/success', name: 'app_order_checkout_success', methods: ['GET'])]
    public function checkoutSuccess(Request $request, SessionInterface $session, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $token = $request->get('token');
        if (!$this->isCsrfTokenValid('stripe_token', $token)) {
            return new Response('I\'m a teapot', 418);
        }
        $orderId = $session->get('orderNumber');
        $order = $orderRepository->findOneBy(['id' => $orderId]);
        if (!$order) {
            $this->redirectToRoute('app_home');
        }
        $order->setState(OrderStatus::PAYED);
        $entityManager->flush();
        return $this->render('order/success.html.twig');
    }

    #[Route('/checkout/error', name: 'app_order_checkout_error', methods: ['GET'])]
    public function checkoutError(SessionInterface $session, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $orderId = $session->get('orderNumber');
        $order = $orderRepository->findOneBy(['id' => $orderId]);
        if (!$order) {
            $this->redirectToRoute('app_home');
        }
        $order->setState(OrderStatus::ERROR);
        $entityManager->flush();
        return $this->render('order/error.html.twig');
    }
}
