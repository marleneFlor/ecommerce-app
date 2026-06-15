<?php

declare(strict_types=1);

namespace App\Orders\Domain\Model;

final readonly class Pagination
{
    private function __construct(
        public int $offset,
        public int $limit,
    ) {
    }

    public static function create(int $offset = 0, int $limit = 20): self
    {
        return new self($offset, $limit);
    }
}
