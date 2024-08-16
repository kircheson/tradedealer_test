<?php

namespace App\Service;

use App\Entity\CreditProgram;
use Doctrine\ORM\EntityManagerInterface;

class CreditCalculatorService
{
    // Минимальный ежемесячный платёж по ТЗ
    const MONTHLY_MINIMUM = 9800;
    // Первоначальный платёж по ТЗ
    const DOWN_PAYMENT = 200000;
    // Максимальный ежемесячный платёж по ТЗ
    const MONTHLY_MAXIMUM = 10000;
    // Максимальный срок кредита в месяцах
    const MAXIMUM_LOAN_TERM = 60;
    // Пример процентной ставки (12,3%)
    const INTEREST_RATE = 0.123;
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

        $loanAmount = $price - $initialPayment;
        $monthlyRate = self::INTEREST_RATE / 12; // Используем 12.3% как пример
        $estimatedMonthlyPayment = $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $loanTerm)) / (pow(1 + $monthlyRate, $loanTerm) - 1);

        if ($initialPayment > self::DOWN_PAYMENT && $estimatedMonthlyPayment <= self::MONTHLY_MAXIMUM && $loanTerm < self::MAXIMUM_LOAN_TERM) {
            $program = $repository->findOneBy(['interestRate' => self::INTEREST_RATE * 100]);
            if ($program) {
                return $program;
            }
        }

        // Если условия не выполнены или программа не найдена, ищем другую подходящую программу
        $programs = $repository->findAll();
        foreach ($programs as $program) {
            $monthlyPayment = $this->calculateMonthlyPayment($price, $initialPayment, $loanTerm, $program->getInterestRate());
            if ($monthlyPayment >= self::MONTHLY_MINIMUM) {
                return $program;
            }
        }

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
