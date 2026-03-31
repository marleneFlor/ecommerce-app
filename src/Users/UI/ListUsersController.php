<?php

declare(strict_types=1);

namespace App\Users\UI;

use App\Shared\Application\Query\QueryBus;
use App\Users\Application\GetUsers\GetUsersQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ListUsersController
{
    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        //$id = $request->get('id');
        $users = $this->queryBus->ask(GetUsersQuery::create());
        

        $data = array_map(
            fn($user) => [
                'id'      => $user->getId(),
                'email'   => $user->getEmail(),
                'address' => $user->getAddress(),
            ],
            $users
        );

        return new JsonResponse($data, Response::HTTP_OK);
    }
}