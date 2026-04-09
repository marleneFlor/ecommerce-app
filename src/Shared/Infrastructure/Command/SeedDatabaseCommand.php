<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Command;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed',
    description: 'Seeds the database. Creates tables if missing, otherwise truncates and reseeds.',
)]
final class SeedDatabaseCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $conn = $this->em->getConnection();

        $io->info('Truncating existing data...');
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['orders_products', 'orders', 'products', 'users'] as $table) {
            $conn->executeStatement("TRUNCATE TABLE `{$table}`");
        }
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS = 1');

        // Seed users
        $user1 = new User();
        $user1->setEmail('alice@example.com');
        $user1->setAddress('123 Main St, Springfield');

        $user2 = new User();
        $user2->setEmail('bob@example.com');
        $user2->setAddress('456 Oak Ave, Shelbyville');

        $this->em->persist($user1);
        $this->em->persist($user2);

        // Seed products
        $laptop = new Product();
        $laptop->setName('Laptop');
        $laptop->setPrice(999.99);

        $phone = new Product();
        $phone->setName('Smartphone');
        $phone->setPrice(699.99);

        $headphones = new Product();
        $headphones->setName('Headphones');
        $headphones->setPrice(149.99);

        $this->em->persist($laptop);
        $this->em->persist($phone);
        $this->em->persist($headphones);

        // Seed orders
        $order1 = new Order();
        $order1->setUser($user1);
        $order1->addProduct($laptop);
        $order1->addProduct($headphones);

        $order2 = new Order();
        $order2->setUser($user2);
        $order2->addProduct($phone);

        $this->em->persist($order1);
        $this->em->persist($order2);

        $this->em->flush();

        $io->success('Database seeded: 2 users, 3 products, 2 orders.');

        return Command::SUCCESS;
    }
}
