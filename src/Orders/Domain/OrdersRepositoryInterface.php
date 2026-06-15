<?php

declare(strict_types=1);

namespace App\Orders\Domain;

use App\Entity\Order;
use App\Orders\Domain\Model\OrderSearchCriteria;

interface OrdersRepositoryInterface
{
    /** @return Order[] */
    public function findAll(): array;

    public function searchById(string $id): ?Order;

    /** @return Order[] */
    public function searchByCriteria(OrderSearchCriteria $criteria): array;
}
