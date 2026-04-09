<?php

declare(strict_types=1);

namespace App\Orders\UI;

use App\Orders\Application\GetOrderById\GetOrderByIdQuery;
use App\Shared\Application\Query\QueryBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetOrderByIdController
{
    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $order = $this->queryBus->ask(GetOrderByIdQuery::create($id));

        if ($order === null) {
            return new JsonResponse(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id'        => $order->getId(),
            'createdAt' => $order->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'user'      => $order->getUser() ? [
                'id'    => $order->getUser()->getId(),
                'email' => $order->getUser()->getEmail(),
            ] : null,
            'products'  => array_map(
                fn($product) => [
                    'id'    => $product->getId(),
                    'name'  => $product->getName(),
                    'price' => $product->getPrice(),
                ],
                $order->getProducts()->toArray()
            ),
        ], Response::HTTP_OK);
    }
}
