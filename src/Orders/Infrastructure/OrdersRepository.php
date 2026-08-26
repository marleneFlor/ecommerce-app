<?php

declare(strict_types=1);

namespace App\Orders\Infrastructure;

use App\Entity\Order;
use App\Orders\Domain\Model\OrderSearchCriteria;
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

    /** @return Order[] */
    public function searchByCriteria(OrderSearchCriteria $criteria): array
    {
        $qb = $this->createQueryBuilder('o');

        if ($criteria->userId !== null) {
            $qb->andWhere('o.user = :userId')
               ->setParameter('userId', $criteria->userId);
        }

        if ($criteria->productId !== null) {
            $qb->innerJoin('o.products', 'p')
               ->andWhere('p.id = :productId')
               ->setParameter('productId', $criteria->productId);
        }

        if ($criteria->createdAt !== null) {
            $qb->andWhere('o.createdAt = :createdAt')
               ->setParameter('createdAt', $criteria->createdAt);
        }

        return $qb->orderBy('o.createdAt', 'DESC')
            ->setFirstResult($criteria->pagination->offset)
            ->setMaxResults($criteria->pagination->limit)
            ->getQuery()
            ->getResult();
    }
}
