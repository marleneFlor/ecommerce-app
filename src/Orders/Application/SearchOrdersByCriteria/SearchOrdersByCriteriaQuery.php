<?php

declare(strict_types=1);

namespace App\Orders\Application\SearchOrdersByCriteria;

use App\Shared\Application\Query\Query;

final readonly class SearchOrdersByCriteriaQuery implements Query
{
    private function __construct(
        public ?int $userId,
        public ?int $productId,
        public ?string $createdFrom,
        public ?string $createdTo,
        public int $offset,
        public int $limit,
    ) {
    }

    public static function create(
        ?int $userId = null,
        ?int $productId = null,
        ?string $createdFrom = null,
        ?string $createdTo = null,
        int $offset = 0,
        int $limit = 20,
    ): self {
        return new self($userId, $productId, $createdFrom, $createdTo, $offset, $limit);
    }
}
