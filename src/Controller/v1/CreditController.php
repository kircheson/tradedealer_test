<?php

namespace App\Controller\v1;

use App\Service\CreditCalculatorService;
use App\Service\CreditService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/credit')]
class CreditController extends AbstractController
{
    /**
     * @throws \Exception
     */
    #[Route('/calculate', name: 'api_v1_credit_calculate', methods: ['GET'])]
    public function calculate(Request $request, CreditCalculatorService $calculatorService): JsonResponse
    {
        $price = $request->query->getInt('price');
        $initialPayment = $request->query->get('initialPayment');
        $loanTerm = $request->query->getInt('loanTerm');

        if (!$price || !$initialPayment || !$loanTerm) {
            return $this->json(['error' => 'Отсутствие необходимых параметров'], 400);
        }

        $initialPayment = floatval(str_replace(',', '.', $initialPayment));

        if ($initialPayment <= 0 || $initialPayment >= $price) {
            return $this->json(['error' => 'Неверный первоначальный взнос'], 400);
        }

        if ($loanTerm <= 0 || $loanTerm > 120) {
            return $this->json(['error' => 'Invalid loan term'], 400);
        }

        $result = $calculatorService->calculate($price, $initialPayment, $loanTerm);

        return $this->json($result);
    }
}
