<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CreditProgramRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreditProgramRepository::class)]
#[ApiResource]
class CreditProgram
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private ?float $interestRate = null;

    /**
     * @var Collection<int, CreditRequest>
     */
    #[ORM\OneToMany(targetEntity: CreditRequest::class, mappedBy: 'program')]
    private Collection $creditRequests;

    public function __construct()
    {
        $this->creditRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getInterestRate(): ?float
    {
        return $this->interestRate;
    }

    public function setInterestRate(float $interestRate): static
    {
        $this->interestRate = $interestRate;

        return $this;
    }

    /**
     * @return Collection<int, CreditRequest>
     */
    public function getCreditRequests(): Collection
    {
        return $this->creditRequests;
    }

    public function addCreditRequest(CreditRequest $creditRequest): static
    {
        if (!$this->creditRequests->contains($creditRequest)) {
            $this->creditRequests->add($creditRequest);
            $creditRequest->setProgram($this);
        }

        return $this;
    }

    public function removeCreditRequest(CreditRequest $creditRequest): static
    {
        if ($this->creditRequests->removeElement($creditRequest)) {
            // set the owning side to null (unless already changed)
            if ($creditRequest->getProgram() === $this) {
                $creditRequest->setProgram(null);
            }
        }

        return $this;
    }
}
