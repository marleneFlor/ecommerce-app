<?php

declare(strict_types=1);

namespace App\Tests\Orders\Application\SearchOrdersByCriteria;

use App\Entity\Order;
use App\Orders\Application\SearchOrdersByCriteria\SearchOrdersByCriteriaHandler;
use App\Orders\Application\SearchOrdersByCriteria\SearchOrdersByCriteriaQuery;
use App\Orders\Domain\Model\OrderSearchCriteria;
use App\Orders\Domain\OrdersRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SearchOrdersByCriteriaHandlerTest extends TestCase
{
    private OrdersRepositoryInterface&MockObject $repository;
    private SearchOrdersByCriteriaHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(OrdersRepositoryInterface::class);
        $this->handler = new SearchOrdersByCriteriaHandler($this->repository);
    }

    public function testItSearchesWithNoFiltersUsingDefaultPagination(): void
    {
        $this->expectCriteriaAndReturn(
            fn (OrderSearchCriteria $criteria): bool
                => $this->assertCriteria($criteria, null, null, null, null, 0, 20),
            [],
        );

        $this->handler->__invoke(SearchOrdersByCriteriaQuery::create());
    }

    public function testItFiltersByUserId(): void
    {
        $this->expectCriteriaAndReturn(
            fn (OrderSearchCriteria $criteria): bool
                => $this->assertCriteria($criteria, 42, null, null, null, 0, 20),
            [],
        );

        $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(userId: 42));
    }

    public function testItFiltersByProductId(): void
    {
        $this->expectCriteriaAndReturn(
            fn (OrderSearchCriteria $criteria): bool
                => $this->assertCriteria($criteria, null, 15, null, null, 0, 20),
            [],
        );

        $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(productId: 15));
    }

    public function testItFiltersByCreatedFrom(): void
    {
        $this->expectCriteriaAndReturn(
            fn (OrderSearchCriteria $criteria): bool
                => $this->assertCriteria($criteria, null, null, '2026-01-01', null, 0, 20),
            [],
        );

        $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(createdFrom: '2026-01-01'));
    }

    public function testItFiltersByCreatedTo(): void
    {
        $this->expectCriteriaAndReturn(
            fn (OrderSearchCriteria $criteria): bool
                => $this->assertCriteria($criteria, null, null, null, '2026-01-31', 0, 20),
            [],
        );

        $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(createdTo: '2026-01-31'));
    }

    public function testItFiltersByDateRange(): void
    {
        $this->expectCriteriaAndReturn(
            fn (OrderSearchCriteria $criteria): bool
                => $this->assertCriteria($criteria, null, null, '2026-01-01', '2026-01-31', 0, 20),
            [],
        );

        $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(
            createdFrom: '2026-01-01',
            createdTo: '2026-01-31',
        ));
    }

    public function testItCombinesAllFilters(): void
    {
        $this->expectCriteriaAndReturn(
            fn (OrderSearchCriteria $criteria): bool
                => $this->assertCriteria($criteria, 7, 9, '2026-02-01', '2026-02-10', 40, 10),
            [],
        );

        $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(
            userId: 7,
            productId: 9,
            createdFrom: '2026-02-01',
            createdTo: '2026-02-10',
            offset: 40,
            limit: 10,
        ));
    }

    public function testItAppliesCustomPagination(): void
    {
        $this->expectCriteriaAndReturn(
            fn (OrderSearchCriteria $criteria): bool
                => $this->assertCriteria($criteria, null, null, null, null, 30, 5),
            [],
        );

        $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(offset: 30, limit: 5));
    }

    public function testItReturnsOrdersFromRepository(): void
    {
        $orders = [new Order(), new Order()];

        $this->expectCriteriaAndReturn(
            fn (OrderSearchCriteria $criteria): bool
                => $this->assertCriteria($criteria, 1, null, null, null, 0, 20),
            $orders,
        );

        $result = $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(userId: 1));

        self::assertSame($orders, $result);
    }

    public function testItThrowsWhenCreatedFromIsInvalidDate(): void
    {
        $this->repository->expects($this->never())->method('searchByCriteria');
        $this->expectException(\Exception::class);

        $this->handler->__invoke(SearchOrdersByCriteriaQuery::create(createdFrom: 'invalid-date'));
    }

    /** @param Order[] $return */
    private function expectCriteriaAndReturn(callable $criteriaAssertion, array $return): void
    {
        $this->repository
            ->expects($this->once())
            ->method('searchByCriteria')
            ->with($this->callback($criteriaAssertion))
            ->willReturn($return);
    }

    private function assertCriteria(
        OrderSearchCriteria $criteria,
        ?int $expectedUserId,
        ?int $expectedProductId,
        ?string $expectedCreatedFrom,
        ?string $expectedCreatedTo,
        int $expectedOffset,
        int $expectedLimit,
    ): bool {
        self::assertSame($expectedUserId, $criteria->userId);
        self::assertSame($expectedProductId, $criteria->productId);

        if ($expectedCreatedFrom === null) {
            self::assertNull($criteria->createdFrom);
        } else {
            self::assertInstanceOf(\DateTimeInterface::class, $criteria->createdFrom);
            self::assertSame($expectedCreatedFrom, $criteria->createdFrom?->format('Y-m-d'));
        }

        if ($expectedCreatedTo === null) {
            self::assertNull($criteria->createdTo);
        } else {
            self::assertInstanceOf(\DateTimeInterface::class, $criteria->createdTo);
            self::assertSame($expectedCreatedTo, $criteria->createdTo?->format('Y-m-d'));
        }

        self::assertSame($expectedOffset, $criteria->pagination->offset);
        self::assertSame($expectedLimit, $criteria->pagination->limit);

        return true;
    }
}
