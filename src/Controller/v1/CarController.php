<?php

namespace App\Controller\v1;

use App\Entity\Car;
use App\Service\CarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cars')]
class CarController extends AbstractController
{
    #[Route('/', name: 'api_v1_car_list', methods: ['GET'])]
    public function list(Request $request, CarService $carService): JsonResponse
    {

        $carList = $carService->list();

        return $this->json($carList, 200);
    }

    #[Route('/{id}', name: 'api_v1_car_show', methods: ['GET'])]
    public function get(int $id, CarService $carService ): JsonResponse
    {
        $car = $carService->get($id);

        return $this->json($car, empty($car) ? 404 : 200 );
    }
}
