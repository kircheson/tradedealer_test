<?php

namespace App\Controller;

use App\Entity\Car;
use App\Entity\CreditProgram;
use App\Entity\CreditRequest;
use App\Service\CreditCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/v1')]
class CreditController extends AbstractController
{
    /**
     * @throws \Exception
     */
    #[Route('/credit/calculate', name: 'credit_calculate', methods: ['GET'])]
    public function calculate(Request $request, CreditCalculatorService $calculatorService): JsonResponse
    {
        $price = $request->query->getInt('price');
        $initialPayment = $request->query->get('initialPayment');
        $loanTerm = $request->query->getInt('loanTerm');

        if (!$price || !$initialPayment || !$loanTerm) {
            return $this->json(['error' => 'Missing required parameters'], 400);
        }

        $initialPayment = floatval(str_replace(',', '.', $initialPayment));

        if ($initialPayment <= 0 || $initialPayment >= $price) {
            return $this->json(['error' => 'Invalid initial payment'], 400);
        }

        if ($loanTerm <= 0 || $loanTerm > 120) {
            return $this->json(['error' => 'Invalid loan term'], 400);
        }

        $result = $calculatorService->calculate($price, $initialPayment, $loanTerm);

        return $this->json($result);
    }

    #[Route('/request', name: 'credit_request', methods: ['POST'])]
    public function request(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['carId'], $data['programId'], $data['initialPayment'], $data['loanTerm'])) {
            return $this->json(['success' => false, 'error' => 'Missing required parameters'], 400);
        }

        $car = $entityManager->getRepository(Car::class)->find($data['carId']);
        $program = $entityManager->getRepository(CreditProgram::class)->find($data['programId']);

        if (!$car || !$program) {
            return $this->json(['success' => false, 'error' => 'Car or credit program not found'], 404);
        }

        $creditRequest = new CreditRequest();
        $creditRequest->setCar($car);
        $creditRequest->setProgram($program);
        $creditRequest->setInitialPayment($data['initialPayment']);
        $creditRequest->setLoanTerm($data['loanTerm']);

        $entityManager->persist($creditRequest);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
}
