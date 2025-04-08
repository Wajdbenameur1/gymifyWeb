<?php

namespace App\Command;

use App\Entity\Customer;
use App\Entity\Produit;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-sample-data',
    description: 'Creates sample data in the database',
)]
class CreateSampleDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Create customers
        $customer1 = new Customer();
        $customer1->setName('John Doe')
            ->setEmail('john@example.com')
            ->setPhone('123-456-7890')
            ->setAddress('123 Main St, City');

        $customer2 = new Customer();
        $customer2->setName('Jane Smith')
            ->setEmail('jane@example.com')
            ->setPhone('987-654-3210')
            ->setAddress('456 Oak St, Town');

        $this->entityManager->persist($customer1);
        $this->entityManager->persist($customer2);

        // Create products
        $produit1 = new Produit();
        $produit1->setNom('Laptop')
            ->setPrix(999.99)
            ->setStock(10)
            ->setCategorie('Electronics')
            ->setImagePath('laptop.jpg');

        $produit2 = new Produit();
        $produit2->setNom('Smartphone')
            ->setPrix(499.99)
            ->setStock(15)
            ->setCategorie('Electronics')
            ->setImagePath('phone.jpg');

        $produit3 = new Produit();
        $produit3->setNom('Headphones')
            ->setPrix(79.99)
            ->setStock(5)
            ->setCategorie('Accessories')
            ->setImagePath('headphones.jpg');

        $this->entityManager->persist($produit1);
        $this->entityManager->persist($produit2);
        $this->entityManager->persist($produit3);

        // Create orders
        $order1 = new Order();
        $order1->setCustomer($customer1)
            ->setOrderDate(new \DateTime())
            ->setStatus('Completed')
            ->setTotalAmount(1079.98);

        $orderItem1 = new OrderItem();
        $orderItem1->setOrder($order1)
            ->setProduit($produit1)
            ->setQuantity(1)
            ->setUnitPrice($produit1->getPrix());

        $orderItem2 = new OrderItem();
        $orderItem2->setOrder($order1)
            ->setProduit($produit3)
            ->setQuantity(1)
            ->setUnitPrice($produit3->getPrix());

        $this->entityManager->persist($order1);
        $this->entityManager->persist($orderItem1);
        $this->entityManager->persist($orderItem2);

        $this->entityManager->flush();

        $io->success('Sample data has been created successfully!');

        return Command::SUCCESS;
    }
} 