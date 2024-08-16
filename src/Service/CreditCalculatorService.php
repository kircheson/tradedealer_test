<?php

namespace App\Service;

use App\Entity\CreditProgram;
use Doctrine\ORM\EntityManagerInterface;

class CreditCalculatorService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function calculate(int $price, float $initialPayment, int $loanTerm): array
    {
        $program = $this->selectCreditProgram($price, $initialPayment, $loanTerm);

        if (!$program) {
            throw new \Exception('Подходящей кредитной программы не найдено');
        }

        $monthlyPayment = $this->calculateMonthlyPayment($price, $initialPayment, $loanTerm, $program->getInterestRate());

        return [
            'programId' => $program->getId(),
            'interestRate' => $program->getInterestRate(),
            'monthlyPayment' => $monthlyPayment,
            'title' => $program->getTitle(),
        ];
    }

    private function selectCreditProgram(int $price, float $initialPayment, int $loanTerm): ?CreditProgram
    {
        $repository = $this->entityManager->getRepository(CreditProgram::class);

        // Расчет примерного ежемесячного платежа для проверки условия
        $loanAmount = $price - $initialPayment;
        $monthlyRate = 0.123 / 12; // Используем 12.3% как пример
        $estimatedMonthlyPayment = $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $loanTerm)) / (pow(1 + $monthlyRate, $loanTerm) - 1);

        if ($initialPayment > 200000 && $estimatedMonthlyPayment <= 10000 && $loanTerm < 60) {
            // Ищем программу с процентной ставкой 12.3%
            $program = $repository->findOneBy(['interestRate' => 12.3]);
            if ($program) {
                return $program;
            }
        }

        // Если условия не выполнены или программа не найдена, ищем другую подходящую программу
        // Здесь можно добавить дополнительную логику выбора программы, вне ТЗ тестового
        $programs = $repository->findAll();
        foreach ($programs as $program) {
            $monthlyPayment = $this->calculateMonthlyPayment($price, $initialPayment, $loanTerm, $program->getInterestRate());
            if ($monthlyPayment >= 9800) {
                return $program;
            }
        }

        // Если ни одна программа не подошла, возвращаем null или первую доступную программу
        return $programs[0] ?? null;
    }

    private function calculateMonthlyPayment(int $price, float $initialPayment, int $loanTerm, float $interestRate): int
    {
        $loanAmount = $price - $initialPayment;
        $monthlyRate = $interestRate / 12 / 100;
        $monthlyPayment = $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $loanTerm)) / (pow(1 + $monthlyRate, $loanTerm) - 1);
        return round($monthlyPayment);
    }
}
