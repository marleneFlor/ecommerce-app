<?php

declare(strict_types=1);

namespace App\Users\Application\GetUsers;

use App\Entity\User;
use App\Shared\Application\Query\QueryHandler;
use App\Users\Domain\UsersRepositoryInterface;

final class GetUsersHandler implements QueryHandler
{
    public function __construct(private readonly UsersRepositoryInterface $repository)
    {
    }

    /** @return User[] */
    public function __invoke(GetUsersQuery $query): array
    {
        return $this->repository->findAll();
    }
}
