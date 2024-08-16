<?php

namespace App\Service;

use App\Entity\Car;
use App\Entity\CreditProgram;
use App\Entity\CreditRequest;
use Doctrine\ORM\EntityManagerInterface;

class CreditService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createCreditRequest(array $data): bool
    {
        $car = $this->entityManager->getRepository(Car::class)->find($data['carId']);
        $program = $this->entityManager->getRepository(CreditProgram::class)->find($data['programId']);

        if (!$car || !$program) {
            return false;
        }

        $creditRequest = new CreditRequest();
        $creditRequest->setCar($car);
        $creditRequest->setProgram($program);
        $creditRequest->setInitialPayment($data['initialPayment']);
        $creditRequest->setLoanTerm($data['loanTerm']);

        $this->entityManager->persist($creditRequest);
        $this->entityManager->flush();

        return true;

    }
}
