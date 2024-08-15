<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CreditRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreditRequestRepository::class)]
#[ApiResource]
class CreditRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'creditRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Car $car = null;

    #[ORM\ManyToOne(inversedBy: 'creditRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CreditProgram $program = null;

    #[ORM\Column]
    private ?float $initialPayment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(?Car $car): static
    {
        $this->car = $car;

        return $this;
    }

    public function getProgram(): ?CreditProgram
    {
        return $this->program;
    }

    public function setProgram(?CreditProgram $program): static
    {
        $this->program = $program;

        return $this;
    }

    public function getInitialPayment(): ?float
    {
        return $this->initialPayment;
    }

    public function setInitialPayment(float $initialPayment): static
    {
        $this->initialPayment = $initialPayment;

        return $this;
    }
}
