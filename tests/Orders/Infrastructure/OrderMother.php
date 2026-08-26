<?php

declare(strict_types=1);

namespace App\Tests\Orders\Infrastructure;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;

/**
 * Builds Order fixtures for the tests.
 *
 * Ids and createdAt are written by reflection because Doctrine owns them
 * at runtime and the entities expose no setter for them.
 */
final class OrderMother
{
    /** @param int[] $productIds */
    public static function create(
        int $id,
        ?int $userId = null,
        array $productIds = [],
        ?string $createdAt = null,
    ): Order {
        $order = new Order();
        self::write($order, 'id', $id);

        if ($userId !== null) {
            $user = new User();
            self::write($user, 'id', $userId);
            $order->setUser($user);
        }

        foreach ($productIds as $productId) {
            $product = new Product();
            self::write($product, 'id', $productId);
            $order->addProduct($product);
        }

        if ($createdAt !== null) {
            self::write($order, 'createdAt', new \DateTimeImmutable($createdAt));
        }

        return $order;
    }

    private static function write(object $entity, string $property, mixed $value): void
    {
        (new \ReflectionProperty($entity, $property))->setValue($entity, $value);
    }
}
