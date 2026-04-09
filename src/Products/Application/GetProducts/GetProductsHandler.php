<?php

declare(strict_types=1);

namespace App\Products\Application\GetProducts;

use App\Entity\Product;
use App\Products\Domain\ProductsRepositoryInterface;
use App\Shared\Application\Query\QueryHandler;

final class GetProductsHandler implements QueryHandler
{
    public function __construct(private readonly ProductsRepositoryInterface $repository)
    {
    }

    /** @return Product[] */
    public function __invoke(GetProductsQuery $query): array
    {
        return $this->repository->findAll();
    }
}
