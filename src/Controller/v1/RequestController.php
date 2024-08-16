<?php

namespace App\Controller\v1;

use App\Service\CreditCalculatorService;
use App\Service\CreditService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/request')]
class RequestController extends AbstractController
{
    #[Route('', name: 'api_v1_request', methods: ['POST'])]
    public function request(Request $request, CreditService $creditService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['carId'], $data['programId'], $data['initialPayment'], $data['loanTerm'])) {
            return $this->json(['success' => false], 400);
        }
        $result = $creditService->createCreditRequest($data);

        return $this->json(['success' => $result], 200);
    }
}
