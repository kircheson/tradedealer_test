<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use App\Entity\Model;
use App\Entity\Car;
use App\Entity\CreditProgram;
use App\Entity\CreditRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create Brands
        $brand1 = new Brand();
        $brand1->setName('Toyota');
        $manager->persist($brand1);

        $brand2 = new Brand();
        $brand2->setName('Honda');
        $manager->persist($brand2);

        // Create Models
        $model1 = new Model();
        $model1->setName('Corolla');
        $model1->setBrand($brand1);
        $manager->persist($model1);

        $model2 = new Model();
        $model2->setName('Civic');
        $model2->setBrand($brand2);
        $manager->persist($model2);

        // Create Cars
        $car1 = new Car();
        $car1->setBrand($brand1);
        $car1->setModel($model1);
        $car1->setPhoto('toyota_corolla.jpg');
        $car1->setPrice(25000);
        $manager->persist($car1);

        $car2 = new Car();
        $car2->setBrand($brand2);
        $car2->setModel($model2);
        $car2->setPhoto('honda_civic.jpg');
        $car2->setPrice(22000);
        $manager->persist($car2);

        // Create Credit Programs
        $program1 = new CreditProgram();
        $program1->setTitle('Alfa Energy');
        $program1->setInterestRate(12.3);
        $manager->persist($program1);

        $program2 = new CreditProgram();
        $program2->setTitle('T Energy');
        $program2->setInterestRate(10.8);
        $manager->persist($program2);

        // Create Credit Requests
        $request1 = new CreditRequest();
        $request1->setCar($car1);
        $request1->setProgram($program1);
        $request1->setInitialPayment(5000);
        $request1->setLoanTerm(60); // 60 months
        $manager->persist($request1);

        $request2 = new CreditRequest();
        $request2->setCar($car2);
        $request2->setProgram($program2);
        $request2->setInitialPayment(4000);
        $request2->setLoanTerm(48); // 48 months
        $manager->persist($request2);

        $manager->flush();
    }
}


