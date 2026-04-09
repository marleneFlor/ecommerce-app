<?php

declare(strict_types=1);

namespace App\Products\UI;

use App\Products\Application\GetProducts\GetProductsQuery;
use App\Shared\Application\Query\QueryBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ListProductsController
{
    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $products = $this->queryBus->ask(GetProductsQuery::create());

        $data = array_map(
            fn($product) => [
                'id'    => $product->getId(),
                'name'  => $product->getName(),
                'price' => $product->getPrice(),
            ],
            $products
        );

        return new JsonResponse($data, Response::HTTP_OK);
    }
}
