<?php

namespace App\Service;

use App\Repository\PizzaRepository;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentService
{
    public function __construct(
        private ContainerBagInterface $containerBag,
        private UrlGeneratorInterface $router
    ) {}

    public function pay(array $lineItems): Session
    {
        $apiKey = $this->containerBag->get('stripe_secret_key');
        Stripe::setApiKey($apiKey);
        return Session::create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->router->generate('app_order_checkout_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->router->generate('app_order_checkout_error', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ]);
    }
}