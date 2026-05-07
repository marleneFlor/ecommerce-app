<?php

declare(strict_types=1);

namespace App\Users\Domain;

final class UserNotFoundException extends \RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("User with id {$id} not found.");
    }
}
