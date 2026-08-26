<?php

declare(strict_types=1);

namespace App\Tests\Orders\Infrastructure;

use App\Entity\Order;
use App\Orders\Domain\Model\OrderSearchCriteria;
use App\Orders\Domain\OrdersRepositoryInterface;

/**
 * In-memory double of OrdersRepository.
 */
final class SearchOrderByCriteriaRepositoryFake implements OrdersRepositoryInterface
{
    /** @var Order[] */
    private array $orders = [];

    /** @var OrderSearchCriteria[] */
    private array $receivedCriteria = [];

    public function add(Order ...$orders): void
    {
        foreach ($orders as $order) {
            $this->orders[] = $order;
        }
    }

    public function lastCriteria(): ?OrderSearchCriteria
    {
        if ($this->receivedCriteria === []) {
            return null;
        }

        return $this->receivedCriteria[array_key_last($this->receivedCriteria)];
    }

    public function timesSearched(): int
    {
        return count($this->receivedCriteria);
    }

    /** @return Order[] */
    public function findAll(): array
    {
        return $this->orders;
    }

    public function searchById(string $id): ?Order
    {
        foreach ($this->orders as $order) {
            if ((string) $order->getId() === $id) {
                return $order;
            }
        }

        return null;
    }

    /** @return Order[] */
    public function searchByCriteria(OrderSearchCriteria $criteria): array
    {
        $this->receivedCriteria[] = $criteria;

        $matches = array_values(array_filter(
            $this->orders,
            static fn (Order $order): bool => self::matchesUser($order, $criteria->userId)
                && self::matchesProduct($order, $criteria->productId)
                && self::matchesCreatedAt($order, $criteria->createdAt),
        ));

        usort(
            $matches,
            static fn (Order $left, Order $right): int => ($right->getCreatedAt()?->getTimestamp() ?? 0)
                <=> ($left->getCreatedAt()?->getTimestamp() ?? 0),
        );

        return array_slice($matches, $criteria->pagination->offset, $criteria->pagination->limit);
    }

    private static function matchesUser(Order $order, ?int $userId): bool
    {
        return $userId === null || $order->getUser()?->getId() === $userId;
    }

    private static function matchesProduct(Order $order, ?int $productId): bool
    {
        if ($productId === null) {
            return true;
        }

        foreach ($order->getProducts() as $product) {
            if ($product->getId() === $productId) {
                return true;
            }
        }

        return false;
    }

    private static function matchesCreatedAt(Order $order, ?\DateTimeInterface $createdAt): bool
    {
        if ($createdAt === null) {
            return true;
        }

        $orderCreatedAt = $order->getCreatedAt();

        return $orderCreatedAt !== null
            && $orderCreatedAt->format('Y-m-d H:i:s') === $createdAt->format('Y-m-d H:i:s');
    }
}

