<?php

declare(strict_types=1);

namespace App\Products\Application\GetProducts;

use App\Shared\Application\Query\Query;

final class GetProductsQuery implements Query
{
    public static function create(): self
    {
        return new self();
    }
}
