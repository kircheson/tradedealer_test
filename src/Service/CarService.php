<?php

namespace App\Service;

use App\Entity\Brand;
use App\Entity\Car;
use App\Entity\CreditProgram;
use App\Entity\CreditRequest;
use App\Entity\Model;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CarService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function carToArray(Car $car): array
    {
        return [
            'id' => $car->getId(),
            'brand' => [
                'id' => $car->getBrand()->getId(),
                'name' => $car->getBrand()->getName(),
            ],
            'model' => [
                'id' => $car->getModel()->getId(),
                'name' => $car->getModel()->getName(),
            ],
            'photo' => $car->getPhoto(),
            'price' => $car->getPrice(),
        ];
    }

    public function list(): array
    {
        $repository = $this->entityManager->getRepository(Car::class);

        $allCars = $repository->findAll();

        $data = [];
        foreach ($allCars as $car) {
            $data[] = $this->carToArray($car);
        }

        return $data;
    }

    public function get(int $id): array
    {
        $car = $this->entityManager->getRepository(Car::class)->find($id);

        if (!$car) {
            return [];
        }

        return $this->carToArray($car);
    }
}
