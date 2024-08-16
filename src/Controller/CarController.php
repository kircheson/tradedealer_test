<?php

namespace App\Controller;

use App\Entity\Car;
use App\Entity\Brand;
use App\Entity\Model;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/v1')]
class CarController extends AbstractController
{
    #[Route('/cars', name: 'car_list', methods: ['GET'])]
    public function list(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $brand = $request->query->get('brand');

        $repository = $entityManager->getRepository(Car::class);
        $queryBuilder = $repository->createQueryBuilder('c')
            ->leftJoin('c.brand', 'b');

        if ($brand) {
            $queryBuilder->andWhere('b.name = :brand')
                ->setParameter('brand', $brand);
        }

        $paginator = new Paginator($queryBuilder);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $data = [];
        foreach ($paginator as $car) {
            $data[] = [
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

        return $this->json([
            'data' => $data,
            'total' => $paginator->count(),
            'page' => $page,
            'limit' => $limit
        ]);
    }

    #[Route('/cars', name: 'car_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Проверка наличия необходимых данных
        if (!isset($data['brand_id'], $data['model_id'], $data['photo'], $data['price'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        // Проверка существования бренда и модели
        $brand = $entityManager->getRepository(Brand::class)->find($data['brand_id']);
        $model = $entityManager->getRepository(Model::class)->find($data['model_id']);

        if (!$brand || !$model) {
            return $this->json(['error' => 'Invalid brand or model'], 400);
        }

        $car = new Car();
        $car->setBrand($brand);
        $car->setModel($model);
        $car->setPhoto($data['photo']);
        $car->setPrice($data['price']);

        $entityManager->persist($car);
        $entityManager->flush();

        return $this->json([
            'message' => 'Car created successfully',
            'id' => $car->getId()
        ], 201);
    }

    #[Route('/cars/{id}', name: 'car_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $car = $entityManager->getRepository(Car::class)->find($id);

        if (!$car) {
            return $this->json(['error' => 'Car not found'], 404);
        }

        $data = [
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

        return $this->json($data);
    }
}
