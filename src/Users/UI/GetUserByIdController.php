<?php

declare(strict_types=1);

namespace App\Users\UI;

use App\Shared\Application\Query\QueryBus;
use App\Users\Application\GetUserById\GetUserByIdQuery;
use App\Users\Domain\UserNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetUserByIdController
{
    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        try {
            $user = $this->queryBus->ask(GetUserByIdQuery::create($id));
        } catch (UserNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id'      => $user->getId(),
            'email'   => $user->getEmail(),
            'address' => $user->getAddress(),
        ], Response::HTTP_OK);
    }
}
