<?php

declare(strict_types=1);

namespace App\Products\Domain;

use App\Entity\Product;

interface ProductsRepositoryInterface
{
    /** @return Product[] */
    public function findAll(): array;

    public function searchById(string $id): ?Product;
}
