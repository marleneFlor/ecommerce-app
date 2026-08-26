<?php

declare(strict_types=1);

namespace App\Orders\UI;

use App\Orders\Application\SearchOrdersByCriteria\SearchOrdersByCriteriaQuery;
use App\Shared\Application\Query\QueryBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SearchOrdersController
{
    public function __construct(private readonly QueryBus $queryBus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $userId    = $request->query->get('userId');
        $productId = $request->query->get('productId');
        $createdAt = $request->query->get('createdAt');

        $orders = $this->queryBus->ask(SearchOrdersByCriteriaQuery::create(
            userId:    $userId !== null ? (int) $userId : null,
            productId: $productId !== null ? (int) $productId : null,
            createdAt: $createdAt,
            offset:    (int) $request->query->get('offset', 0),
            limit:     (int) $request->query->get('limit', 20),
        ));

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
