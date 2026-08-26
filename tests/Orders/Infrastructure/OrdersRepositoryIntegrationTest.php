<?php

declare(strict_types=1);

namespace App\Tests\Orders\Infrastructure;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Orders\Domain\Model\OrderSearchCriteria;
use App\Orders\Domain\Model\Pagination;
use App\Orders\Infrastructure\OrdersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * TODO: Create e2e test to send request to the GET endpoint, and check the response, it should be the same as the repository returns.
 * Faker?
 * Implementation test: same behaviour SearchOrderByCriteriaRepositoryFake fakes,
 * verified here against the real Doctrine repository and a real MySQL database.
 *
 * Requires the test database to exist and be migrated:
 *   make db-create-test
 *   make migrate-test
 */
final class OrdersRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private OrdersRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->repository = $container->get(OrdersRepository::class);
    }

    protected function tearDown(): void
    {
        // Delete in FK-safe order: join table -> orders -> products/users.
        // Truncate is better?
        $connection = $this->em->getConnection();
        $connection->executeStatement('DELETE FROM orders_products');
        $connection->executeStatement('DELETE FROM orders');
        $connection->executeStatement('DELETE FROM products');
        $connection->executeStatement('DELETE FROM users');
    }

    private function createUser(string $email): User
    {
        $user = (new User())
            ->setEmail($email)
            ->setAddress('some address');

        $this->em->persist($user);

        return $user;
    }

    private function createProduct(string $name): Product
    {
        $product = (new Product())
            ->setName($name)
            ->setPrice(10.0);

        $this->em->persist($product);

        return $product;
    }

    /** @param Product[] $products */
    private function createOrder(User $user, array $products, string $createdAt): Order
    {
        $order = new Order();
        $order->setUser($user);

        foreach ($products as $product) {
            $order->addProduct($product);
        }

        (new \ReflectionProperty(Order::class, 'createdAt'))
            ->setValue($order, new \DateTime($createdAt));

        $this->em->persist($order);

        return $order;
    }

    /** @return Order[] */
    private function idsOf(array $orders): array
    {
        return array_map(static fn (Order $order): int => (int) $order->getId(), $orders);
    }

    /**
     * @return array{0: User, 1: User, 2: Product, 3: Product, 4: Order, 5: Order, 6: Order}
     */
    private function givenThreeOrders(): array
    {
        $userA = $this->createUser('user-a@example.com');
        $userB = $this->createUser('user-b@example.com');
        $productA = $this->createProduct('product-a');
        $productB = $this->createProduct('product-b');

        $order1 = $this->createOrder($userA, [$productA], '2026-01-01 00:00:00');
        $order2 = $this->createOrder($userA, [$productA, $productB], '2026-03-01 00:00:00');
        $order3 = $this->createOrder($userB, [$productA], '2026-02-01 00:00:00');

        $this->em->flush();

        return [$userA, $userB, $productA, $productB, $order1, $order2, $order3];
    }

    public function testItSearchesWithNoFiltersOrderedFromNewestToOldest(): void
    {
        [, , , , $order1, $order2, $order3] = $this->givenThreeOrders();

        $orders = $this->repository->searchByCriteria(OrderSearchCriteria::create());

        $this->assertSame(
            $this->idsOf([$order2, $order3, $order1]),
            $this->idsOf($orders),
        );
    }

    public function testItFiltersOnlyByUser(): void
    {
        [$userA, , , , $order1, $order2] = $this->givenThreeOrders();

        $orders = $this->repository->searchByCriteria(OrderSearchCriteria::create(userId: $userA->getId()));

        $this->assertSame($this->idsOf([$order2, $order1]), $this->idsOf($orders));
    }

    public function testItFiltersOnlyByProduct(): void
    {
        [, , , $productB, , $order2] = $this->givenThreeOrders();

        $orders = $this->repository->searchByCriteria(OrderSearchCriteria::create(productId: $productB->getId()));

        $this->assertSame($this->idsOf([$order2]), $this->idsOf($orders));
    }

    public function testItFiltersWithAllTheCriteriaAtOnce(): void
    {
        [$userA, , , $productB, , $order2] = $this->givenThreeOrders();

        $orders = $this->repository->searchByCriteria(OrderSearchCriteria::create(
            userId: $userA->getId(),
            productId: $productB->getId(),
            createdAt: new \DateTimeImmutable('2026-03-01'),
        ));

        $this->assertSame($this->idsOf([$order2]), $this->idsOf($orders));
    }

    public function testItReturnsNothingWhenThereAreNoOrdersStored(): void
    {
        $orders = $this->repository->searchByCriteria(OrderSearchCriteria::create(userId: 999));

        $this->assertSame([], $orders);
    }

    public function testItReturnsNothingWhenNoOrderMatchesTheCriteria(): void
    {
        $this->givenThreeOrders();

        $orders = $this->repository->searchByCriteria(OrderSearchCriteria::create(userId: 999999));

        $this->assertSame([], $orders);
    }

    public function testItAppliesPagination(): void
    {
        [, , , , $order1, $order2, $order3] = $this->givenThreeOrders();

        $orders = $this->repository->searchByCriteria(OrderSearchCriteria::create(
            pagination: Pagination::create(offset: 1, limit: 1),
        ));

        $this->assertSame($this->idsOf([$order3]), $this->idsOf($orders));
    }
}
