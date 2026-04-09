<?php

declare(strict_types=1);

namespace App\Orders\Domain;

use App\Entity\Order;

interface OrdersRepositoryInterface
{
    /** @return Order[] */
    public function findAll(): array;

    public function searchById(string $id): ?Order;
}
