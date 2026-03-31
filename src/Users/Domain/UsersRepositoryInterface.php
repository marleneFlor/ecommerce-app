<?php

declare(strict_types=1);

namespace App\Users\Domain;

use App\Entity\User;

interface UsersRepositoryInterface
{
    /** @return User[] */
    public function findAll(): array;

    public function searchById(string $id): ?User;
}
