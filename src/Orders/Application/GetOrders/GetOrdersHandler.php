<?php

declare(strict_types=1);

namespace App\Orders\Application\GetOrders;

use App\Entity\Order;
use App\Orders\Domain\OrdersRepositoryInterface;
use App\Shared\Application\Query\QueryHandler;

final class GetOrdersHandler implements QueryHandler
{
    public function __construct(private readonly OrdersRepositoryInterface $repository)
    {
    }

    /** @return Order[] */
    public function __invoke(GetOrdersQuery $query): array
    {
        return $this->repository->findAll();
    }
}
