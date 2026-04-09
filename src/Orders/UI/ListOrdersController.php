<?php

declare(strict_types=1);

namespace App\Orders\UI;

use App\Orders\Application\GetOrders\GetOrdersQuery;
use App\Shared\Application\Query\QueryBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ListOrdersController
{
    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $orders = $this->queryBus->ask(GetOrdersQuery::create());

        $data = array_map(
            fn($order) => [
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
            ],
            $orders
        );

        return new JsonResponse($data, Response::HTTP_OK);
    }
}
