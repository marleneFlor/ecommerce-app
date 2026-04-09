<?php

declare(strict_types=1);

namespace App\Products\Application\GetProductById;

use App\Shared\Application\Query\Query;

final class GetProductByIdQuery implements Query
{
    private function __construct(public readonly string $id)
    {
    }

    public static function create(string $id): self
    {
        return new self($id);
    }
}
