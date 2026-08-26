<?php

declare(strict_types=1);

namespace App\Tests\Orders\Application\SearchOrdersByCriteria;

use App\Entity\Order;
use App\Orders\Application\SearchOrdersByCriteria\SearchOrdersByCriteriaHandler;
use App\Orders\Application\SearchOrdersByCriteria\SearchOrdersByCriteriaQuery;
use App\Tests\Orders\Infrastructure\OrderMother;
use App\Tests\Orders\Infrastructure\SearchOrderByCriteriaRepositoryFake;
use PHPUnit\Framework\TestCase;

/*
    NINGUN DATO
    ALGUN DATO ->
        FECHA INCORRECTA PARA DATETIME
    TODOS LOS DATOS

    INPUT OK, DB SIN DATOS
    INPUT OK, DB CON DATOS MATCH
*/
final class SearchOrdersByCriteriaHandlerTest extends TestCase
{
    private SearchOrderByCriteriaRepositoryFake $repository;
    private SearchOrdersByCriteriaHandler $handler;

    // podemos poner givenNOrders con un faker??
    private function givenThreeOrders(): void
    {
        $this->repository->add(
            OrderMother::create(id: 1, userId: 42, productIds: [15], createdAt: '2026-01-01 00:00:00'),
            OrderMother::create(id: 2, userId: 42, productIds: [15, 99], createdAt: '2026-03-01 00:00:00'),
            OrderMother::create(id: 3, userId: 7, productIds: [15], createdAt: '2026-02-01 00:00:00'),
        );
    }

    /**
     * @param Order[] $orders
     *
     * @return int[]
     */
    private function idsOf(array $orders): array
    {
        return array_map(static fn (Order $order): int => (int) $order->getId(), $orders);
    }

    protected function setUp(): void
    {
        $this->repository = new SearchOrderByCriteriaRepositoryFake();
        $this->handler = new SearchOrdersByCriteriaHandler($this->repository);
    }

    /* NINGUN DATO: sin filtros devuelve todo, del mas nuevo al mas viejo */
    public function testItSearchesWithNoFiltersUsingDefaultPagination(): void
    {
        $this->givenThreeOrders();

        $orders = $this->handler->__invoke(SearchOrdersByCriteriaQuery::create());

        // We can also return the orders and check the order of the ids
        $this->assertSame([2, 3, 1], $this->idsOf($orders));

        $criteria = $this->repository->lastCriteria();
        $this->assertNotNull($criteria);
        $this->assertNull($criteria->userId);
        $this->assertNull($criteria->productId);
        $this->assertNull($criteria->createdAt);
        $this->assertSame(0, $criteria->pagination->offset);
        $this->assertSame(20, $criteria->pagination->limit);
    }

    /* ALGUN DATO: solo el usuario */
    public function testItSearchesFilteringOnlyByUser(): void
    {
        $this->givenThreeOrders();

        $orders = $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(userId: 42));

        $this->assertSame([2, 1], $this->idsOf($orders));
    }

    /* ALGUN DATO: solo el producto */
    public function testItSearchesFilteringOnlyByProduct(): void
    {
        $this->givenThreeOrders();

        $orders = $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(productId: 99));

        $this->assertSame([2], $this->idsOf($orders));
    }

    /* TODOS LOS DATOS + INPUT OK, DB CON DATOS MATCH */
    public function testItSearchesWithAllTheFiltersAtOnce(): void
    {
        $this->givenThreeOrders();

        $orders = $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(
            userId: 42,
            productId: 99,
            createdAt: '2026-03-01',
            offset: 0,
            limit: 20,
        ));

        $this->assertSame([2], $this->idsOf($orders));
    }


    /* INPUT OK, DB SIN DATOS */
    public function testItReturnsNothingWhenThereAreNoOrdersStored(): void
    {
        $orders = $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(userId: 42));

        $this->assertSame([], $orders);
        $this->assertSame(1, $this->repository->timesSearched());
    }

    /* INPUT OK, DB CON DATOS PERO SIN MATCH */
    public function testItReturnsNothingWhenNoOrderMatchesTheCriteria(): void
    {
        $this->givenThreeOrders();

        $orders = $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(userId: 999));

        $this->assertSame([], $orders);
    }


    /* FECHA INCORRECTA PARA DATETIME */
    public function testItThrowsWhenCreatedAtIsNotAValidDate(): void
    {
        $this->expectException(\DateMalformedStringException::class);

        $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(createdAt: 'invalid-date'));
    }

    public function testItDoesNotReachTheRepositoryWhenTheDateIsInvalid(): void
    {
        try {
            $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(createdAt: 'invalid-date'));
        } catch (\DateMalformedStringException) {
            // nada
        }

        // no llega a buscar porque falló
        $this->assertSame(0, $this->repository->timesSearched());
    }

}
