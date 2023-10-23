<?php

namespace App\Entity;

use App\Repository\OrderLineRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderLineRepository::class)]
class OrderLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?int $capturedPrice = null;

    #[ORM\ManyToOne(inversedBy: 'orderLines')]
    private ?Order $order_associative = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getCapturedPrice(): ?int
    {
        return $this->capturedPrice;
    }

    public function setCapturedPrice(int $capturedPrice): static
    {
        $this->capturedPrice = $capturedPrice;

        return $this;
    }

    public function getOrderAssociative(): ?Order
    {
        return $this->order_associative;
    }

    public function setOrderAssociative(?Order $order_associative): static
    {
        $this->order_associative = $order_associative;

        return $this;
    }
}
