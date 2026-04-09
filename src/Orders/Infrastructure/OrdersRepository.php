<?php

declare(strict_types=1);

namespace App\Orders\Infrastructure;

use App\Entity\Order;
use App\Orders\Domain\OrdersRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
final class OrdersRepository extends ServiceEntityRepository implements OrdersRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /** @return Order[] */
    public function findAll(): array
    {
        return $this->createQueryBuilder('o')
            ->getQuery()
            ->getResult();
    }

    public function searchById(string $id): ?Order
    {
        return $this->createQueryBuilder('o')
            ->where('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
