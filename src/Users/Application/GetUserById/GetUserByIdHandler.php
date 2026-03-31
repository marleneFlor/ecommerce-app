<?php

declare(strict_types=1);

namespace App\Users\Application\GetUserById;

use App\Entity\User;
use App\Shared\Application\Query\QueryHandler;
use App\Users\Domain\UsersRepositoryInterface;

final class GetUserByIdHandler implements QueryHandler
{
    public function __construct(private readonly UsersRepositoryInterface $repository)
    {
    }

    public function __invoke(GetUserByIdQuery $query): ?User
    {
        return $this->repository->searchById($query->id);
    }
}
