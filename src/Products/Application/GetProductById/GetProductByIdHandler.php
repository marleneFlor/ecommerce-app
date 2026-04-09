<?php

declare(strict_types=1);

namespace App\Products\Application\GetProductById;

use App\Entity\Product;
use App\Products\Domain\ProductsRepositoryInterface;
use App\Shared\Application\Query\QueryHandler;

final class GetProductByIdHandler implements QueryHandler
{
    public function __construct(private readonly ProductsRepositoryInterface $repository)
    {
    }

    public function __invoke(GetProductByIdQuery $query): ?Product
    {
        return $this->repository->searchById($query->id);
    }
}
