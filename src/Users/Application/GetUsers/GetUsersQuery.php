<?php

declare(strict_types=1);

namespace App\Users\Application\GetUsers;

use App\Shared\Application\Query\Query;

final class GetUsersQuery implements Query
{
    public static function create(): self
    {
        return new self();
    }
}
