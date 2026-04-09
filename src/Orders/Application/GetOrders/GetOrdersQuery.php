<?php

declare(strict_types=1);

namespace App\Orders\Application\GetOrders;

use App\Shared\Application\Query\Query;

final class GetOrdersQuery implements Query
{
    public static function create(): self
    {
        return new self();
    }
}
