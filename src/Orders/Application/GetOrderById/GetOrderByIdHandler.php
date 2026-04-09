<?php

declare(strict_types=1);

namespace App\Orders\Application\GetOrderById;

use App\Entity\Order;
use App\Orders\Domain\OrdersRepositoryInterface;
use App\Shared\Application\Query\QueryHandler;

final class GetOrderByIdHandler implements QueryHandler
{
    public function __construct(private readonly OrdersRepositoryInterface $repository)
    {
    }

    public function __invoke(GetOrderByIdQuery $query): ?Order
    {
        return $this->repository->searchById($query->id);
    }
}
