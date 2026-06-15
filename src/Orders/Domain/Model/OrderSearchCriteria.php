<?php

declare(strict_types=1);

namespace App\Orders\Domain\Model;

final readonly class OrderSearchCriteria
{
    private function __construct(
        public ?int $userId,
        public ?int $productId,
        public ?\DateTimeInterface $createdFrom,
        public ?\DateTimeInterface $createdTo,
        public Pagination $pagination,
    ) {
    }

    public static function create(
        ?int $userId = null,
        ?int $productId = null,
        ?\DateTimeInterface $createdFrom = null,
        ?\DateTimeInterface $createdTo = null,
        ?Pagination $pagination = null,
    ): self {
        return new self(
            $userId,
            $productId,
            $createdFrom,
            $createdTo,
            $pagination ?? Pagination::create(),
        );
    }
}
