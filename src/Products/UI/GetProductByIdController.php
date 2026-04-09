<?php

declare(strict_types=1);

namespace App\Products\UI;

use App\Products\Application\GetProductById\GetProductByIdQuery;
use App\Shared\Application\Query\QueryBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetProductByIdController
{
    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $product = $this->queryBus->ask(GetProductByIdQuery::create($id));

        if ($product === null) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id'    => $product->getId(),
            'name'  => $product->getName(),
            'price' => $product->getPrice(),
        ], Response::HTTP_OK);
    }
}
