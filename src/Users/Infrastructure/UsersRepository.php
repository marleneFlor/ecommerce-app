<?php

declare(strict_types=1);

namespace App\Users\Infrastructure;

use App\Entity\User;
use App\Users\Domain\UsersRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UsersRepository extends ServiceEntityRepository implements UsersRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /** @return User[] */
    public function findAll(): array
    {
        return $this->createQueryBuilder('u')
            ->getQuery()
            ->getResult();
    }

    public function searchById(string $id): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
