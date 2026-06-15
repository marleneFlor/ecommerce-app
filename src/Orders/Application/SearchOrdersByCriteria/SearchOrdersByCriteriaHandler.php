<?php

declare(strict_types=1);

namespace App\Orders\Application\SearchOrdersByCriteria;

use App\Entity\Order;
use App\Orders\Domain\Model\OrderSearchCriteria;
use App\Orders\Domain\Model\Pagination;
use App\Orders\Domain\OrdersRepositoryInterface;
use App\Shared\Application\Query\QueryHandler;

final class SearchOrdersByCriteriaHandler implements QueryHandler
{
    public function __construct(private readonly OrdersRepositoryInterface $repository)
    {
    }

    /** @return Order[] */
    public function __invoke(SearchOrdersByCriteriaQuery $query): array
    {
        $criteria = OrderSearchCriteria::create(
            userId: $query->userId,
            productId: $query->productId,
            createdFrom: $query->createdFrom !== null ? new \DateTimeImmutable($query->createdFrom) : null,
            createdTo: $query->createdTo !== null ? new \DateTimeImmutable($query->createdTo) : null,
            pagination: Pagination::create($query->offset, $query->limit),
        );

        return $this->repository->searchByCriteria($criteria);
    }
}
